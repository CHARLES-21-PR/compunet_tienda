# ============================================
# STAGE 1: Composer dependencies
# ============================================
FROM php:8.2-cli AS composer_stage

RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Instalar dependencias sin dev y sin scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts



# ============================================
# STAGE 2: Producción (PHP-FPM + Nginx)
# ============================================
FROM php:8.2-fpm

# Instalar Nginx
RUN apt-get update && apt-get install -y nginx \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

# Copiar proyecto ya con vendor
COPY --from=composer_stage /var/www /var/www

# Copiar config de nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Render necesita este puerto
EXPOSE 80

# Arrancar nginx + php-fpm juntos
CMD service nginx start && php-fpm
