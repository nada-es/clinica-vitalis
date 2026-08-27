FROM php:8.3-cli

RUN docker-php-ext-install mysqli

COPY vitalis/ /var/www/html/

WORKDIR /var/www/html

EXPOSE 8080

ENV PORT=8080

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT"]
