# Stage 1: builder - instala dependências PHP/composer (root + src/Infrastructure/Yii)
FROM composer:2 AS builder

WORKDIR /app

# Copia tudo e instala deps tanto do root quanto do sub-projeto Yii
COPY . /app

# Instala dependências PHP do projeto raiz (incluindo dependências de dev para testes)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Instala dependências do sub-projeto Yii (src/Infrastructure/Yii)
RUN if [ -f src/Infrastructure/Yii/composer.json ]; then \
      cd src/Infrastructure/Yii && composer install --no-interaction --prefer-dist --optimize-autoloader; \
    fi

# Stage 2: runtime - PHP Apache com extensões necessárias
FROM php:8.2-apache

# Instala dependências do sistema e extensões PHP necessárias (inclui curl para healthcheck)
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
     curl \
     libzip-dev libicu-dev libonig-dev libxml2-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev zip unzip git \
  && docker-php-ext-configure gd --with-jpeg --with-freetype \
  && docker-php-ext-install -j$(nproc) pdo pdo_mysql intl mbstring zip xml gd \
  && a2enmod rewrite headers \
  && rm -rf /var/lib/apt/lists/*

# Copia a aplicação do stage builder
COPY --from=builder /app /var/www/html

# Ajusta permissões e expõe porta
RUN chown -R www-data:www-data /var/www/html \
  && chmod +x /var/www/html/docker/entrypoint.sh

WORKDIR /var/www/html

# Define document root para a aplicação Yii
RUN sed -ri -e 's!/var/www/html!/var/www/html/src/Infrastructure/Yii/web!g' /etc/apache2/sites-available/*.conf \
  && sed -ri -e 's!DocumentRoot .*!DocumentRoot /var/www/html/src/Infrastructure/Yii/web!g' /etc/apache2/apache2.conf /etc/apache2/sites-available/*.conf

EXPOSE 80

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["apache2-foreground"]