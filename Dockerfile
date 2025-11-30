# Etapa 1: Composer
FROM composer:2 AS composer_stage
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader


# Etapa 2: PHP-FPM Productivo
FROM php:8.2-fpm
WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer_stage /app .

RUN php artisan config:cache
RUN php artisan route:cache

RUN chown -R www-data:www-data storage bootstrap/cache

# Comando de arranque en Render
CMD ["php-fpm"]