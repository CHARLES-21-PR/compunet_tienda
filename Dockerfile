# ===========================
# STAGE 1: Composer dependencies
# ===========================
FROM php:8.2-fpm AS composer_stage

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install zip

# Instalar Composer dentro del contenedor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar archivos necesarios para instalar dependencias
WORKDIR /var/www/html
COPY composer.json composer.lock ./

# Instalar dependencias sin dev
RUN composer install --no-dev --optimize-autoloader --no-interaction


# ===========================
# STAGE 2: App final
# ===========================
FROM php:8.2-fpm AS app

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip

WORKDIR /var/www/html

# Copiar todos los archivos del proyecto
COPY . .

# Copiar dependencias instaladas en el stage anterior
COPY --from=composer_stage /var/www/html/vendor ./vendor

# Asignar permisos (importante para storage y cache)
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
