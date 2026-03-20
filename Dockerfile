FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    git \
    zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_mysql mbstring zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# Update virtual host to point to app directory instead of /var/www/html if needed,
# but CakePHP docs usually say to point it to the app's webroot.
RUN sed -ri -e 's!/var/www/html!/var/www/html/acp_demo/webroot!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/acp_demo/webroot!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
