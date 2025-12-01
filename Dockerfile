FROM php:8.4-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev nginx \
    && docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
