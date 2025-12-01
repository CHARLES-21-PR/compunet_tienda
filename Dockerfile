FROM php:8.2-fpm AS composer_stage
WORKDIR /app

COPY composer.json composer.lock ./
RUN apt-get update && apt-get install -y git unzip
RUN docker-php-ext-install pdo pdo_mysql

# Instalar dependencias sin dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el resto del proyecto
COPY . .

# --------------------------------------------------------------------

FROM php:8.2-fpm
WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer_stage /app ./

RUN php artisan config:cache
RUN php artisan route:cache

RUN chown -R www-data:www-data storage bootstrap/cache
