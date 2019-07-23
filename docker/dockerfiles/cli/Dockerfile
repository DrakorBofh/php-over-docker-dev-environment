FROM php:7.2-cli
MAINTAINER Fernando Anthony Ristaño <fernando.ristano@gmail.com>

ARG production=no

RUN apt-get update -y && apt-get install -y libpng-dev curl libcurl4-openssl-dev zip unzip
RUN if [ "x$production" != "yes" ] ; then apt-get install -y iputils-ping mysql-client ack vim doxygen; fi


RUN docker-php-ext-install pdo pdo_mysql gd curl bcmath

# Setup xdebug
RUN pecl install xdebug-beta && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

# Use the default production configuration
RUN if [ "x$production" = "yes" ] ; then mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; else mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"; fi
