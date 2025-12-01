FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    nginx supervisor git unzip libpng-dev libonig-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring gd zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar proyecto
COPY . .

# Instalar dependencias sin dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar config de Nginx
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default

# Copiar supervisor config
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer el puerto que Render requiere
EXPOSE 10000

CMD ["/usr/bin/supervisord"]
