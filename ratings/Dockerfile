FROM php:8.1-apache

RUN apt-get update && apt-get install -yqq unzip libzip-dev autoconf zlib1g-dev \
    && pecl install grpc \
    && docker-php-ext-install pdo_mysql opcache zip

# relax permissions on status
COPY conf/status.conf /etc/apache2/mods-available/status.conf
# Enable Apache mod_rewrite and status
RUN a2enmod rewrite && a2enmod status

WORKDIR /var/www/html

COPY html/ /var/www/html

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install

# This is important. Symfony needs write permissions and we
# dont know the context in which the container will run, i.e.
# which user will be forced from the outside so better play
# safe for this simple demo.
RUN rm -Rf /var/www/var/*
RUN chown -R www-data /var/www
RUN chmod -R 777 /var/www
