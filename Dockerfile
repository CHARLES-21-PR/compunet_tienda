FROM php:8.2-fpm

WORKDIR /var/www

# Dependencias de PHP
RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    nginx \
    && docker-php-ext-install pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------
# 1. COPIAR TODO EL PROYECTO (incluye artisan)
# ---------------------------------------------------------
COPY . .

# 2. AHORA sí ejecutar composer install
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Permisos correctos
RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar config de Nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && exec nginx -g 'daemon off;'"]

