# Imagen base con Apache y PHP
FROM php:8.2-apache

# Habilita mod_rewrite (útil para URLs amigables)
RUN a2enmod rewrite

# Habilita extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copia el contenido del proyecto al contenedor
COPY . /var/www/html/

# Da permisos adecuados
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Configura DocumentRoot a /
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html|' /etc/apache2/sites-available/000-default.conf

# Habilita todos los accesos
RUN echo "<Directory /var/www/html>\nOptions Indexes FollowSymLinks\nAllowOverride All\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/custom-perms.conf \
    && a2enconf custom-perms

# Exponer el puerto
EXPOSE 80
