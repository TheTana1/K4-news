
FROM php:8.5.2-fpm

# Устанавливаем системные зависимости и расширения PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*



# Устанавливаем Composer (менеджер пакетов)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN pecl install redis && docker-php-ext-enable redis
# Устанавливаем рабочую директорию (совпадает с volume в compose)
WORKDIR /var/www/html
EXPOSE 9000
CMD ["php-fpm"]