# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilita mod_rewrite (para rutas amigables, si lo usas)
RUN a2enmod rewrite

# Habilita extensiones necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copia todo el proyecto al servidor Apache
COPY . /var/www/html/

# Cambia la raíz web de Apache para que apunte a la carpeta public
#RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# Exponer el puerto 80 (Render lo usará internamente)
EXPOSE 80
