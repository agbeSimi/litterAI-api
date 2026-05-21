FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql

ENV APACHE_DOCUMENT_ROOT /var/www/public

RUN echo "<VirtualHost *:80>\n\
    DocumentRoot \${APACHE_DOCUMENT_ROOT}\n\
    <Directory \${APACHE_DOCUMENT_ROOT}>\n\
        AllowOverride All\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www
COPY . .

RUN /usr/local/bin/composer install --no-dev --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /var/www

EXPOSE 80