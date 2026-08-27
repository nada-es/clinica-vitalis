FROM php:8.3-apache

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Enable mod_rewrite
RUN a2dismod mpm_event && a2enmod mpm_prefork && a2enmod rewrite

# Copy app into container
COPY vitalis/ /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]

