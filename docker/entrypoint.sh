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

# Run PHP unit tests (root) if binary exists
if [ -f vendor/bin/phpunit ]; then
  echo "[entrypoint] Running root PHPUnit tests..."
  vendor/bin/phpunit --testdox
else
  echo "[entrypoint] vendor/bin/phpunit not found — skipping root phpunit."
fi

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