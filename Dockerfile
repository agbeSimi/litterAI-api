FROM php:8.3-apache
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN a2enmod rewrite headers
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot \${APACHE_DOCUMENT_ROOT}\n\
    <Directory \${APACHE_DOCUMENT_ROOT}>\n\
        AllowOverride All\n\
        Require all granted\n\
\n\
        Header always set Access-Control-Allow-Origin \"*\"\n\
        Header always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS\"\n\
        Header always set Access-Control-Allow-Headers \"Content-Type, Accept, Authorization\"\n\
\n\
        RewriteEngine On\n\
        RewriteCond %{REQUEST_METHOD} OPTIONS\n\
        RewriteRule ^(.*)$ $1 [R=200,L]\n\
\n\
        FallbackResource /index.php\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www
COPY . .
RUN /usr/local/bin/composer install --no-dev --optimize-autoloader --no-scripts
RUN chown -R www-data:www-data /var/www

# ✅ AJOUTS ICI
ENV APP_ENV=prod
RUN php bin/console cache:clear --env=prod --no-warmup
RUN php bin/console cache:warmup --env=prod
RUN chown -R www-data:www-data /var/www/var

EXPOSE 80