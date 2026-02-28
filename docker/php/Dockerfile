FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libxml2-dev \
 && docker-php-ext-install pdo pdo_mysql intl zip \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
