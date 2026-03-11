FROM dunglas/frankenphp:php8.2.30-bookworm

RUN docker-php-ext-install intl

COPY . /app
WORKDIR /app

RUN composer install --optimize-autoloader --no-scripts --no-interaction

CMD ["vendor/bin/heroku-php-apache2 public"]