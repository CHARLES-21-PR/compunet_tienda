# -------- Etapa 1: Composer --------
FROM composer:2 AS composer_stage
WORKDIR /app

# Copiar archivos composer
COPY composer.json composer.lock ./

# Instalar dependencias sin dev para producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar todo el proyecto
COPY . .

# -------- Etapa 2: PHP-FPM --------
FROM php:8.2-fpm
WORKDIR /var/www/html

# Extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar app desde composer stage
COPY --from=composer_stage /app .

# Permisos correctos
RUN chown -R www-data:www-data storage bootstrap/cache

# Caches
RUN php artisan config:cache && php artisan route:cache
