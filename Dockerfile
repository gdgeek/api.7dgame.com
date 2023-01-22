# Dockerfile-app

# Use PHP 5.6 with Apache for the base image
FROM  php:8.0.13-apache

# Enable the Rewrite Apache mod
RUN cd /etc/apache2/mods-enabled && \
    ln -s ../mods-available/rewrite.load && \
    ln -s ../mods-available/headers.load 

# Install required PHP extensions
# -- GD
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ./advanced /var/www/html

# Copy HTTP server config
COPY 000-default.conf /etc/apache2/sites-available/
