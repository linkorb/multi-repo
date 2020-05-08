FROM php:7.4
USER root
RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip libzip4 libzip-dev ssh libyaml-dev nodejs npm && \
    curl -sSL https://getcomposer.org/composer.phar -o /usr/bin/composer && \
    chmod +x /usr/bin/composer && \
    composer selfupdate && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    mkdir /app

RUN pecl install yaml-2.1.0 && docker-php-ext-enable yaml

WORKDIR /app
