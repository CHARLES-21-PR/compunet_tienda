# ============================================
# STAGE 1: Composer dependencies
# ============================================
FROM php:8.4-fpm AS composer_stage

WORKDIR /var/www

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev nginx \
    && docker-php-ext-install pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar composer.json y composer.lock primero
COPY composer.json composer.lock ./

# Instalar dependencias sin dev, sin scripts
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# Copiar el resto del proyecto
COPY . .

# Dar permisos correctos
RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar config de Nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Exponer puerto
EXPOSE 80

# CMD para Render: PHP-FPM y Nginx en primer plano
CMD ["sh", "-c", "php-fpm -F && nginx -g 'daemon off;'"]

