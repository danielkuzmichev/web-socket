FROM php:8.2-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && docker-php-ext-install sockets \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY composer.json composer.lock ./

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --optimize-autoloader

COPY . .
