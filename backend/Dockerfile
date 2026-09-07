FROM php:8.4-fpm-alpine
RUN apk add --no-cache postgresql-dev libzip-dev icu-dev oniguruma-dev libpng-dev freetype-dev libjpeg-turbo-dev postgresql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql intl opcache gd zip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
RUN printf "memory_limit=512M\nupload_max_filesize=50M\npost_max_size=50M\n" > /usr/local/etc/php/conf.d/99-qms-import.ini
RUN chown -R www-data:www-data storage bootstrap/cache
CMD ["php-fpm"]
