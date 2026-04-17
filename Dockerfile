FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        unzip \
        curl \
        libpq-dev \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install gd pdo pdo_pgsql pgsql \
        && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN a2enmod rewrite

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
