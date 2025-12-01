FROM php:8.4-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    nginx \
    && docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Primero solo composer.json
COPY composer.json composer.lock ./

# Instalar dependencias SIN scripts
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# Copiar todo el proyecto (artisán incluido)
COPY . .

# Ejecutar scripts post-autoload-dump
RUN composer dump-autoload --optimize && composer run-script post-autoload-dump

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]

