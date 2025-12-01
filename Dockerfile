FROM php:8.2-fpm

# Instalar extensiones
RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev nginx \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www

# Copiar TODO primero para que artisan exista
COPY . .

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias SIN EJECUTAR SCRIPTS
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar Nginx config
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
