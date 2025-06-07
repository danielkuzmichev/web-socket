FROM php:8.2-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Установка Redis и Xdebug
RUN pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug

# Настройка Xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Создаем директорию для логов
RUN mkdir -p /var/log && touch /var/log/xdebug.log && chmod 777 /var/log/xdebug.log

COPY composer.json composer.lock ./

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

COPY . .