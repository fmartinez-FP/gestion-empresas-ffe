FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip \
    libpng-dev libxml2-dev oniguruma-dev \
    icu-dev freetype-dev libjpeg-turbo-dev \
    openldap-dev libzip-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql mbstring exif pcntl bcmath \
        gd xml intl ldap zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
