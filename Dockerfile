FROM php:8.2-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y git zip unzip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json ./
RUN composer install

COPY . .