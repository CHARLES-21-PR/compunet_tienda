# ---------------------------------------------------------
# Etapa 1: Composer + PHP con extensiones necesarias
# ---------------------------------------------------------
FROM php:8.2-fpm AS build

# Instalar dependencias requeridas por Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev

# Extensiones de PHP necesarias para Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring zip xml

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar archivo composer
COPY composer.json composer.lock ./

# Instalar dependencias sin dev
RUN composer install --no-dev --optimize-autoloader

# Copiar todo el proyecto
COPY . .

# ---------------------------------------------------------
# Etapa 2: Producción
# ---------------------------------------------------------
FROM php:8.2-fpm

WORKDIR /var/www/html

# Extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libxml2-dev && \
    docker-php-ext-install pdo pdo_mysql mbstring zip xml

# Copiar aplicación desde build
COPY --from=build /app ./

# Permisos correctos
RUN chown -R www-data:www-data storage bootstrap/cache

# Caches de Laravel
RUN php artisan config:cache && \
    php artisan route:cache
