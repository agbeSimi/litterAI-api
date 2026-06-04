FROM php:8.2-apache

# Installation des extensions PHP requises pour communiquer avec MySQL/Aiven
RUN docker-php-ext-install pdo pdo_mysql

# On indique à Apache que le point d'entrée de Symfony est dans le dossier public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Activation du module de réécriture d'URL d'Apache (indispensable pour les routes de l'API)
RUN a2enmod rewrite

# On copie tout le code de ton application dans le conteneur
COPY . /var/www/html

# Installation de Composer et des dépendances de production
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# On donne les droits d'écriture à Apache sur les dossiers de cache et de logs de Symfony
RUN chown -R www-data:www-data /var/www/html/var

WORKDIR /var/www/html

EXPOSE 80
