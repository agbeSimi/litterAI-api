# Utilise l'image PHP 8.2-fpm pour la performance
FROM php:8.2-fpm

# Installation des dépendances système minimales pour Symfony
RUN apt-get update && apt-get install -y \
    libicu-dev \
    git \
    unzip \
    && docker-php-ext-install intl

# Installation de Composer pour gérer tes dépendances PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dossier de travail dans le conteneur
WORKDIR /var/www

# Copie l'intégralité du projet
COPY . .

# Installation des dépendances en mode production (plus rapide)
RUN composer install --no-dev --optimize-autoloader

# On donne les droits au serveur web sur les dossiers de cache et logs
RUN chown -R www-data:www-data var/
