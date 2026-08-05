FROM dunglas/frankenphp:1-php8.3

RUN apt-get update && apt-get install -y \
        git curl unzip zip \
        libpng-dev libonig-dev libxml2-dev libzip-dev libsqlite3-dev sqlite3 \
        libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["docker/entrypoint.sh"]
