#!/usr/bin/env bash
set -euo pipefail

# Diretório base da aplicação dentro do container
APP_DIR="/var/www/html"
cd "$APP_DIR"

# Parâmetros de conexão (podem ser sobrescritos via env)
DB_HOST="${DB_HOST:-db}"
DB_NAME="${DB_NAME:-yii2}"
DB_USER="${DB_USER:-yii2}"
DB_PASS="${DB_PASS:-yii2}"

MAX_ATTEMPTS=60
ATTEMPT=0

echo "[entrypoint] Waiting for database at ${DB_HOST}..."

until php -r "try { new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASS}'); echo 'ok'; } catch (Throwable \$e) { exit(1); }" >/dev/null 2>&1; do
  ATTEMPT=$((ATTEMPT+1))
  if [ "$ATTEMPT" -ge "$MAX_ATTEMPTS" ]; then
    echo "[entrypoint] Database did not become ready after ${MAX_ATTEMPTS} attempts"
    exit 1
  fi
  sleep 1
done

echo "[entrypoint] Database is ready."

# Ensure necessary directories exist and are writable for Yii and Codeception
ensure_dirs() {
  echo "[entrypoint] Ensuring Yii and test directories exist and are writable..."

  DIRS=(
    "src/Infrastructure/Yii/runtime"
    "src/Infrastructure/Yii/web/assets"
    "src/Infrastructure/Yii/tests/_output"
    "src/Infrastructure/Yii/tests/_data"
  )

  for d in "${DIRS[@]}"; do
    if [ ! -d "$APP_DIR/$d" ]; then
      echo "[entrypoint] Creating directory: $d"
      mkdir -p "$APP_DIR/$d"
    fi
  done

  # Make them writable by web server user (www-data) and group
  chown -R www-data:www-data "$APP_DIR/src/Infrastructure/Yii/runtime" "$APP_DIR/src/Infrastructure/Yii/web/assets" "$APP_DIR/src/Infrastructure/Yii/tests/_output" || true
  chmod -R 0775 "$APP_DIR/src/Infrastructure/Yii/runtime" "$APP_DIR/src/Infrastructure/Yii/web/assets" "$APP_DIR/src/Infrastructure/Yii/tests/_output" || true

  echo "[entrypoint] Directories prepared."
}

ensure_dirs

# Run PHP unit tests (root) if binary exists
if [ -f vendor/bin/phpunit ]; then
  echo "[entrypoint] Running root PHPUnit tests..."
  vendor/bin/phpunit --testdox
else
  echo "[entrypoint] vendor/bin/phpunit not found — skipping root phpunit."
fi

# Before running Codeception, ensure again that test runtime dirs exist (assets might be published during functional tests)
ensure_dirs

# Run Codeception tests for the Yii subproject (if installed)
if [ -d src/Infrastructure/Yii ] && [ -f src/Infrastructure/Yii/vendor/bin/codecept ]; then
  echo "[entrypoint] Running Codeception tests (src/Infrastructure/Yii)..."
  (cd src/Infrastructure/Yii && vendor/bin/codecept run)
else
  echo "[entrypoint] Codeception binary not found in src/Infrastructure/Yii/vendor — skipping Codeception tests."
fi

# Execute migrations (não interativo)
echo "[entrypoint] Running Yii migrations..."
php src/Infrastructure/Yii/yii migrate --interactive=0

echo "[entrypoint] Migrations finished — starting Apache."

# Start Apache in foreground (usa entrypoint do php image)
exec docker-php-entrypoint apache2-foreground