FROM php:8.3-fpm

# Instalar dependencias de sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_mysql mysqli zip

# Configurar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html