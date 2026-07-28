FROM php:8.2-apache

# Instala las extensiones de MySQL que tu proyecto necesita
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita el mod_rewrite de Apache
RUN a2enmod rewrite

# Copia todo tu proyecto a la carpeta pública del servidor
COPY ./BASEWEBDEFCOP/ /var/www/html/

# Expone el puerto 80 para la web
EXPOSE 80
