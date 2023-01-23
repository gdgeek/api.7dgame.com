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

COPY ./advanced/ /var/www/html/advanced
RUN chmod -R 777 /var/www/html/advanced/backend/runtime
RUN chmod -R 777 /var/www/html/advanced/backend/web/assets
RUN chmod -R 777 /var/www/html/advanced/console/runtime
RUN chmod -R 777 /var/www/html/advanced/api/runtime
RUN chmod -R 777 /var/www/html/advanced/api/web/assets

COPY files/api/config/main-local.php /var/www/html/advanced/api/config/
COPY files/api/config/params-local.php /var/www/html/advanced/api/config/
COPY files/api/web/index.php /var/www/html/advanced/api/web/
COPY files/api/web/robots.txt /var/www/html/advanced/api/web/
COPY files/common/config/main-local.php /var/www/html/advanced/common/config/
COPY files/common/config/params-local.php /var/www/html/advanced/common/config/
COPY files/console/config/main-local.php /var/www/html/advanced/console/config/
COPY files/console/config/params-local.php /var/www/html/advanced/console/config/
COPY files/backend/config/main-local.php /var/www/html/advanced/backend/config/
COPY files/backend/config/params-local.php /var/www/html/advanced/backend/config/
COPY files/backend/web/index.php /var/www/html/advanced/backend/web/
COPY files/backend/web/robots.txt /var/www/html/advanced/backend/web/
# RUN chmod -R 755 /var/www/html/advanced/yii

# Copy HTTP server config
COPY 000-default.conf /etc/apache2/sites-available/
