FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip \
    libpng-dev libxml2-dev oniguruma-dev \
    icu-dev freetype-dev libjpeg-turbo-dev \
    openldap-dev libzip-dev \
    mariadb-client mariadb-connector-c

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql mbstring exif pcntl bcmath \
        gd xml intl ldap zip

# CA interna de IES Pacifico para LDAP STARTTLS (172.16.90.55)
COPY docker/certs/ies-pacifico-ldap-ca.crt /usr/local/share/ca-certificates/ies-pacifico-ldap-ca.crt
RUN update-ca-certificates \
    && echo 'TLS_CACERT /etc/ssl/certs/ca-certificates.crt' >> /etc/openldap/ldap.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage/framework/views \
             storage/framework/cache \
             storage/framework/sessions \
             storage/logs \
             bootstrap/cache \
    && composer install --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
