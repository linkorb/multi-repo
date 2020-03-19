FROM php:7.4
USER root
RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip libzip4 libzip-dev && \
    curl -sSL https://getcomposer.org/composer.phar -o /usr/bin/composer && \
    chmod +x /usr/bin/composer && \
    composer selfupdate && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    mkdir /app

RUN pecl install apcu \
  && docker-php-ext-enable apcu

WORKDIR /app
