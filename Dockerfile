# ============================================
# STAGE 1: Composer
# ============================================
FROM php:8.2-cli AS vendor

RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

# Instalar dependencias SIN DEV y SIN SCRIPTS
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

COPY . /app


# ============================================
# STAGE 2: APP (PHP-FPM + NGINX + SUPERVISOR)
# ============================================
FROM php:8.2-fpm

# Instalar paquetes
RUN apt-get update && apt-get install -y \
    nginx supervisor unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

# Copiar aplicación
COPY --from=vendor /app /var/www

# Copiar configuración de NGINX y SUPERVISOR
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
