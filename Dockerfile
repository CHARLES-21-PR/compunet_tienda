# ===========================
# STAGE 1: Composer Vendor
# ===========================
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY . /app


# ===========================
# STAGE 2: App (PHP-FPM + Nginx)
# ===========================
FROM php:8.2-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    nginx supervisor unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

# Copiar proyecto
WORKDIR /var/www
COPY --from=vendor /app /var/www

# Permisos correctos
RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar config Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copiar config Supervisor (necesario en Render)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord"]
