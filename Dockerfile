FROM php:8.3-apache

# Disable all mods
RUN a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true

# Clean any stray mods
RUN rm -f /etc/apache2/mods-enabled/* 2>/dev/null || true

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Enable only mpm_prefork (required for PHP)
RUN a2enmod mpm_prefork

# Copy vitalis app into Apache document root
COPY vitalis/ /var/www/html/

WORKDIR /var/www/html
EXPOSE 80

CMD ["apache2-foreground"]

