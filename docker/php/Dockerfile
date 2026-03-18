FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
