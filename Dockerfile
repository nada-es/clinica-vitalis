FROM php:8.5-apache

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Copy app into container
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]

