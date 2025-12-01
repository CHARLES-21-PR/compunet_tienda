# -----------------------------------------------------
# Etapa 1: Composer (solo para instalar dependencias)
# -----------------------------------------------------
FROM composer:2 AS build

WORKDIR /app

# Copiar archivos necesarios para composer
COPY composer.json composer.lock ./

# Instalar dependencias (sin dev para producción)
RUN composer install --no-dev --optimize-autoloader --no-interaction


# -----------------------------------------------------
# Etapa 2: Imagen final Laravel + PHP-FPM + Nginx
# -----------------------------------------------------
FROM php:8.2-fpm

WORKDIR /var/www/html

# Instalar extensiones de PHP necesarias para Laravel
RUN apt-get update && apt-get install -y \
    nginx \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql

# Copiar Nginx config
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copiar la app completa
COPY . .

# Copiar los vendor instalados desde la etapa build
COPY --from=build /app/vendor ./vendor

# Permisos para almacenamiento y cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Exponer puerto 80 (Render lo escaneará)
EXPOSE 80

# Iniciar Nginx + PHP-FPM al mismo tiempo
CMD service nginx start && php-fpm
