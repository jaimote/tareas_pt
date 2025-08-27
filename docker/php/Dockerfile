# docker/php/Dockerfile

# Usamos la imagen oficial de PHP 8.3 con FPM, que es el motor de ejecución
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Instalamos extensiones necesarias para CodeIgniter y PHPUnit
# (xdebug es útil para obtener reportes de cobertura de código en las pruebas)
RUN apk add --no-cache $PHPIZE_DEPS icu-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo pdo_mysql mysqli intl

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer