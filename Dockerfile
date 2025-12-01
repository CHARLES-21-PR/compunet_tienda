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

# Instalar dependencias sin scripts ni dev
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

COPY . /app



# ============================================
# STAGE 2: Producción (Nginx + PHP-FPM + Supervisor)
# ============================================
FROM php:8.2-fpm AS app

RUN apt-get update && apt-get install -y \
    nginx supervisor unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

# Copiamos el vendor generado
COPY --from=vendor /app /var/www

# Configurar permisos Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copiar config Nginx y Supervisor
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
