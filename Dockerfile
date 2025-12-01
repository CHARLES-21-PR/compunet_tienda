# ==========================================================
# STAGE 1: Composer install
# ==========================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

COPY . /app
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader


# ==========================================================
# STAGE 2: Final (PHP-FPM + Nginx + Supervisor)
# ==========================================================
FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    nginx supervisor unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

# Copiar código
WORKDIR /var/www
COPY --from=vendor /app /var/www

# Copiar configuración de Nginx
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Crear symlink
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configurar permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copiar configuración de Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

# Supervisor iniciará Nginx + PHP-FPM
CMD ["/usr/bin/supervisord"]
