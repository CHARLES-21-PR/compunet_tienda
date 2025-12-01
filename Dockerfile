# ============================
# ETAPA 1: Composer
# ============================
FROM composer:2 AS composer_stage
WORKDIR /app

# Copiar composer.json y composer.lock
COPY composer.json composer.lock ./

# Instalar dependencias sin dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar resto del proyecto
COPY . .

# ============================
# ETAPA 2: PHP-FPM
# ============================
FROM php:8.2-fpm

WORKDIR /var/www/html

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar archivos desde la etapa Composer
COPY --from=composer_stage /app /var/www/html

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Opcional: cache de Laravel (NO falla)
RUN php artisan config:clear || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true

CMD ["php-fpm"]
