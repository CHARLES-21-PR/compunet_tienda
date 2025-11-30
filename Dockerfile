# Etapa 1: Composer
FROM composer:2 AS composer_stage
WORKDIR /app

# Copiar archivos esenciales para composer
COPY composer.json composer.lock ./

# Instalar dependencias sin dev y sin scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el resto del proyecto
COPY . .



# Etapa 2: PHP-FPM
FROM php:8.2-fpm

WORKDIR /var/www/html

# Extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-install pdo pdo_mysql zip mbstring tokenizer xml

# Copiar aplicación del primer stage
COPY --from=composer_stage /app ./

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache
