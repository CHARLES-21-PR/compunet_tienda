FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    nginx \
    unzip \
    git \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql zip gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos de composer primero
COPY composer.json composer.lock ./

# Instalar dependencias (sin ejecutar artisan)
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts

# Copiar el proyecto
COPY . .

# Copiar config de Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Dar permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Crear carpeta para php-fpm
RUN mkdir -p /run/php

EXPOSE 80

# Iniciar php-fpm y nginx
CMD service php8.2-fpm start && nginx -g "daemon off;"
