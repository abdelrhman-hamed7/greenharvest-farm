FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads/products /var/www/html/storage/sessions \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/storage

EXPOSE 80
