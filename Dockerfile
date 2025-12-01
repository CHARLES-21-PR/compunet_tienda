# Etapa 1 — Composer (solo para instalar dependencias)
FROM composer:2 AS composer_stage
WORKDIR /app

# Copiar solo composer.json y composer.lock
COPY composer.json composer.lock ./

# Instalar dependencias sin dev y sin scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copiar el resto del proyecto
COPY . .


# Etapa 2 — PHP-FPM para producción
FROM php:8.2-fpm
WORKDIR /var/www/html

# Extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar el proyecto desde stage 1
COPY --from=composer_stage /app ./

# NO correr artisan aquí — Render lo ejecuta en runtime
# Los comandos se ejecutan luego en "Pre-Deploy Command" o al iniciar

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache
