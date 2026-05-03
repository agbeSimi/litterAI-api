# Utilise PHP 8.3 pour satisfaire les dépendances de ton projet
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www
COPY . .

# L'installation fonctionnera maintenant car la version de PHP est correcte
RUN /usr/local/bin/composer install --no-dev --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data var/
