FROM php:8.2-apache

# Install required extensions and dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd \
    && docker-php-ext-enable pdo_mysql

# Copy your php.ini
# COPY /path/to/php.ini /usr/local/etc/php/

# Enable Apache mod_rewrite
RUN a2enmod rewrite
