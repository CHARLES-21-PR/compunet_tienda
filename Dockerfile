FROM php:8.2-fpm

# Instalar dependencias (Agregando supervisor y limpiando)
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    unzip \
    git \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql zip gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos de composer
COPY composer.json composer.lock ./

# Copiar el proyecto
COPY . .

RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts



# Copiar config de Nginx
# Asegúrate de que este archivo Nginx APUNTE A UN SOCKET TCP (php:9000 o 127.0.0.1:9000)
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# 1. Copiar el archivo de configuración de Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Dar permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Crear carpeta para php-fpm (necesaria para el socket o si PHP-FPM la requiere)
RUN mkdir -p /run/php

# Exponer el puerto de Nginx
EXPOSE 80

# 2. Comando final: Iniciar el supervisor
# Supervisord inicia ambos procesos (Nginx y PHP-FPM) y se ejecuta en primer plano (PID 1)
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]