FROM php:8.2-apache

# Atualiza pacotes
RUN apt-get update && apt-get install -y \
    git \
    zip unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libssl-dev \
    libmemcached-dev \
    && docker-php-ext-install pdo pdo_mysql intl mbstring zip xml gd

# Instala extensões do PECL
RUN pecl install memcached && docker-php-ext-enable memcached

# Configura Apache
RUN a2enmod rewrite

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Cria usuário vscode para evitar root
RUN useradd -ms /bin/bash vscode \
    && chown -R vscode:vscode /var/www/html
USER vscode

WORKDIR /var/www/html
