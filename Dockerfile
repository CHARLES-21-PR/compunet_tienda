# Etapa 1: Composer
FROM composer:2 AS composer_stage
WORKDIR /app

# Copiar solo composer.json y composer.lock primero (cache más eficiente)
COPY composer.json composer.lock ./

# Instalar dependencias sin dev y sin scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el resto del proyecto
COPY . .

# Etapa 2: PHP-FPM Productivo
FROM php:8.2-fpm
WORKDIR /var/www/html

# Extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar archivos ya procesados
COPY --from=composer_stage /app ./

# Scripts artisan después de copiar y con .env cargado por Render
RUN php artisan package:discover
RUN php artisan config:cache
RUN php artisan route:cache

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Puerto
EXPOSE 9000

CMD ["php-fpm"]

