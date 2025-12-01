# ============================================
# STAGE 1: Composer dependencies
# ============================================
FROM php:8.2-cli AS composer_stage

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear carpeta y copiar todo el proyecto
WORKDIR /var/www
COPY . .

# Instalar dependencias SIN DEV y SIN SCRIPTS (para evitar artisan)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts


# ============================================
# STAGE 2: PHP-FPM para producción
# ============================================
FROM php:8.2-fpm AS app

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

# Copiamos el resultado del stage de Composer
COPY --from=composer_stage /var/www /var/www

# Dar permisos correctos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]

