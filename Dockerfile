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

# Instalar dependencias (PHP 8.2 es compatible)
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

COPY . /app


# ============================================
# STAGE 2: APP (PHP-FPM + Nginx + Supervisor)
# ============================================
FROM php:8.2-fpm AS app

RUN apt-get update && apt-get install -y \
    nginx supervisor unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

COPY --from=vendor /app /var/www

# Copiar configuración de Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copiar supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord"]
