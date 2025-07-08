# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilita mod_rewrite (para rutas amigables, si lo usas)
RUN a2enmod rewrite

# Habilita extensiones necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copia todos los archivos del proyecto al servidor Apache
COPY . /var/www/html/

# Exponer el puerto 80 (Render lo usará internamente)
EXPOSE 80
