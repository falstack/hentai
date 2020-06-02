FROM composer:latest

RUN composer config -g repo.packagist composer "https://mirrors.aliyun.com/composer/" \
    && apk add autoconf \
    && pecl install swoole
