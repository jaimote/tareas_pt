# Usa la imagen oficial de PHP-FPM 8.3
FROM php:8.3-fpm

# Instala extensiones de PHP y herramientas necesarias
RUN apt-get update && apt-get install -y \
    git \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instala la extensión MySQL para MariaDB
RUN docker-php-ext-install pdo_mysql intl

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala PHPUnit
RUN composer global require phpunit/phpunit

# Instala las extensiones de imagen
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install gd

WORKDIR /var/www/html