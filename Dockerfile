FROM php:8.4-fpm

WORKDIR /var/www

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev nginx \
    && docker-php-ext-install pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar composer.json y lock
COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# Copiar proyecto
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar config nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# EXPOSE no importa en Render, pero lo dejamos
EXPOSE 8080

# Arrancar todo
CMD ["sh", "-c", "php-fpm -F & nginx -g 'daemon off;'"]

