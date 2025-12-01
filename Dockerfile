FROM php:8.4-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev nginx \
    && docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 1. Copiar archivos de composer
COPY composer.json composer.lock /var/www/

# 2. Primer composer sin scripts (evita el error de artisan)
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts

# 3. Copiar todo el proyecto
COPY . /var/www/

# 4. Segundo composer con scripts (artisan ya existe)
RUN composer install --no-dev --prefer-dist --no-interaction

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar config de Nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
