FROM php:8.5-cli-alpine

# Временные зависимости для сборки расширений + рантайм-зависимости
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
        libpng-dev \
        oniguruma-dev \
        libxml2-dev \
        freetype-dev \
        libjpeg-turbo-dev \
    && apk add --no-cache \
        git \
        zip \
        unzip \
        postgresql-libs \
        libpng \
        oniguruma \
        libxml2 \
        freetype \
        libjpeg-turbo \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Продакшен-настройки PHP
RUN { \
        echo "memory_limit = 256M"; \
        echo "upload_max_filesize = 20M"; \
        echo "post_max_size = 25M"; \
        echo "opcache.enable = 1"; \
        echo "opcache.enable_cli = 1"; \
        echo "opcache.memory_consumption = 128"; \
        echo "opcache.interned_strings_buffer = 8"; \
        echo "opcache.max_accelerated_files = 10000"; \
        echo "opcache.validate_timestamps = 0"; \
        echo "opcache.save_comments = 1"; \
        echo "expose_php = Off"; \
        echo "display_errors = Off"; \
        echo "log_errors = On"; \
        echo "error_log = /proc/self/fd/2"; \
    } > /usr/local/etc/php/conf.d/zz-production.ini

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

WORKDIR /var/www/html

CMD ["php", "artisan", "telegram:poll"]
