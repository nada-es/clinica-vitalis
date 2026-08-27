FROM php:8.5-apache

# Disable conflicting MPM modules
RUN a2dismod mpm_prefork mpm_worker mpm_event 2>/dev/null || true

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Enable mpm_prefork explicitly
RUN a2enmod mpm_prefork

# Copy app into container
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]

