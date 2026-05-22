# 1. On utilise l'image avec le serveur web Apache intégré
FROM php:8.3-apache

# 2. Installation des dépendances et extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql

# 3. ÉTAPE CLÉ : Configurer Apache pour cibler le dossier "public" de Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Activer le module de réécriture d'URL d'Apache (indispensable pour les routes Symfony)
RUN a2enmod rewrite

# 5. Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# 6. Dossier de travail
WORKDIR /var/www
COPY . .

# 7. Installation des paquets Symfony
RUN /usr/local/bin/composer install --no-dev --optimize-autoloader --no-scripts

# 8. Permissions
RUN chown -R www-data:www-data /var/www

# 9. On ouvre le port 80 pour que Render puisse s'y connecter
EXPOSE 80