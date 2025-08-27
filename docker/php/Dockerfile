FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxml2-dev \
    libicu-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    cron \
    procps \
    vim \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql opcache gd exif intl zip mysqli

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html