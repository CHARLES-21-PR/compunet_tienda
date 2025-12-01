# ============================================
# STAGE 1: Composer dependencies
# ============================================
FROM php:8.2-fpm AS composer_stage

RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts


# ============================================
# STAGE 2: NGINX + PHP-FPM
# ============================================
FROM php:8.2-fpm

# Instalar Nginx + dependencias
RUN apt-get update && apt-get install -y nginx supervisor \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

# Traemos Laravel ya procesado
COPY --from=composer_stage /var/www /var/www

# Copiar configuración de Nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copiar config de supervisor
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD ["supervisord", "-n"]
