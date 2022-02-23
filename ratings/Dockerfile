FROM php:8.1-apache

RUN apt-get update && apt-get install -yqq unzip libzip-dev autoconf zlib1g-dev \
    && docker-php-ext-install pdo_mysql opcache zip

COPY --from=instanacedricziel/otel-shop-utility-ext-grpc:1.43.0 /grpc.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/

RUN docker-php-ext-enable grpc

# relax permissions on status
COPY conf/status.conf /etc/apache2/mods-available/status.conf
# Enable Apache mod_rewrite and status
RUN a2enmod rewrite && a2enmod status

WORKDIR /var/www/html

COPY html/ /var/www/html

COPY --from=composer /usr/bin/composer /usr/bin/composer
ADD html/composer.lock composer.lock
ADD html/composer.json composer.json
RUN composer install

# This is important. Symfony needs write permissions and we
# dont know the context in which the container will run, i.e.
# which user will be forced from the outside so better play
# safe for this simple demo.
RUN rm -Rf /var/www/var/*
RUN chown -R www-data /var/www
RUN chmod -R 777 /var/www
