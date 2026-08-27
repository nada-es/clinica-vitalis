FROM php:8.3-apache

# Remove conflicting MPM modules from mods-enabled
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Enable mpm_prefork explicitly
RUN a2enmod mpm_prefork

# Copy vitalis app into Apache document root
COPY vitalis/ /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]

