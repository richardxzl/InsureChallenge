# Usar una imagen base de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias necesarias, incluyendo git
RUN apt-get update && apt-get install -y git unzip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código de la aplicación al contenedor
COPY . /var/www/html

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Instalar las dependencias de Composer
RUN composer install

# Crear el enlace simbólico para el almacenamiento
RUN php artisan storage:link

# Dar permisos a las carpetas de almacenamiento y caché
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Generar la clave de la aplicación
RUN php artisan key:generate

# Exponer el puerto 80
EXPOSE 80
