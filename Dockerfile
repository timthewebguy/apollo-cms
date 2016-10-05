FROM php:7.0-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y mysql-client libmysqlclient-dev \ 
      && docker-php-ext-install mysqli

COPY public/ /var/www/html/
#COPY config/php.ini /usr/local/etc/php/
