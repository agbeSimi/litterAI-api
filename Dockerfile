FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    # && docker-php-ext-install intl zip pdo pdo_mysql
    && docker-php-ext-install intl zip pdo pdo_pgsql
RUN a2enmod rewrite headers

# ✅ Chemin hardcodé, pas de variable qui risque de ne pas s'interpoler
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        Header always set Access-Control-Allow-Origin "*"\n\
        Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"\n\
        Header always set Access-Control-Allow-Headers "Content-Type, Accept, Authorization"\n\
        RewriteEngine On\n\
        RewriteCond %{REQUEST_METHOD} OPTIONS\n\
        RewriteRule ^(.*)$ $1 [R=200,L]\n\
        FallbackResource /index.php\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# ✅ WORKDIR aligné avec DocumentRoot
WORKDIR /var/www/html
COPY . .

RUN /usr/local/bin/composer install --no-dev --optimize-autoloader --no-scripts

ENV APP_ENV=prod
RUN chown -R www-data:www-data /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
EXPOSE 80
CMD ["docker-entrypoint.sh"]