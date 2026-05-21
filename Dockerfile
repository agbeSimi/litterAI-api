FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql

ENV APACHE_DOCUMENT_ROOT /var/www/public

# 1. On active les modules de réécriture ET d'en-têtes (headers)
RUN a2enmod rewrite headers

# 2. On configure Apache pour forcer les autorisations CORS
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot \${APACHE_DOCUMENT_ROOT}\n\
    <Directory \${APACHE_DOCUMENT_ROOT}>\n\
        AllowOverride All\n\
        Require all granted\n\
        \n\
        # --- FORÇAGE DU CORS --- \n\
        Header always set Access-Control-Allow-Origin \"*\"\n\
        Header always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS\"\n\
        Header always set Access-Control-Allow-Headers \"Content-Type, Accept, Authorization\"\n\
        \n\
        RewriteEngine On\n\
        # Si c'est une requête de vérification (OPTIONS), on répond 200 OK tout de suite\n\
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

EXPOSE 80