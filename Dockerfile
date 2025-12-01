# ============================================
# STAGE 1: Composer + dependencias Laravel
# ============================================
FROM php:8.2-cli AS vendor

RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar TODO el proyecto
COPY . /app

# Instalar dependencias sin scripts (para evitar artisan)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ============================================
# STAGE 2: PHP-FPM + Nginx con supervisor
# ============================================
FROM php:8.2-fpm AS app

RUN apt-get update && apt-get install -y \
    nginx supervisor \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

# Copiar vendors ya instalados
COPY --from=vendor /app /var/www

# Copiar config de nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copiar supervisord config
COPY ./docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Permisos Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord"]

