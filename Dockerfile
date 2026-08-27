FROM php:8.3-apache

RUN docker-php-ext-install mysqli

RUN sed -i 's/^LoadModule mpm_event_module/LoadModule mpm_prefork_module/' /etc/apache2/mods-available/mpm.conf \
    && sed -i '/<IfModule mpm_event_module>/,/<\/IfModule>/s/^/#/' /etc/apache2/mods-available/mpm.conf

COPY vitalis/ /var/www/html/

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
