FROM php:8.4-fpm

WORKDIR /var/www

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip git libpng-dev libzip-dev libonig-dev nginx \
    && docker-php-ext-install pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Eliminar configs por defecto
RUN rm -f /etc/nginx/conf.d/* /etc/nginx/sites-enabled/default

# Copiar config de Nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copiar composer.json e instalar dependencias
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Copiar proyecto
COPY . .

# Permisos Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
