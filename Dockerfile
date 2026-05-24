# syntax=docker/dockerfile:1.6

# -----------------------------------------------------------------------------
# Stage 1: build front-end assets with Vite
# -----------------------------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 2: download PHP dependencies with Composer (cache layer)
# Only composer.json/lock are copied here so changes to app code don't
# invalidate the dependency download cache.
# -----------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader \
        --prefer-dist

# -----------------------------------------------------------------------------
# Stage 3: runtime PHP-FPM image
# -----------------------------------------------------------------------------
FROM php:8.4-fpm

# System packages needed for Laravel + extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libicu-dev \
        default-mysql-client \
        netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions required by Laravel + project deps
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Composer binary (used at build time + at runtime by entrypoint as fallback)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Pre-downloaded packages, then the rest of the app, then the built assets.
COPY --from=vendor /app/vendor /var/www/vendor
COPY . /var/www
COPY --from=assets /app/public/build /var/www/public/build

# Now that the full app is in place, regenerate the optimized autoloader so
# the classmap includes app/, database/factories/, and database/seeders/.
# (package:discover runs from the entrypoint, after .env is in place.)
RUN composer dump-autoload --optimize --no-dev

# Permissions for Laravel writable paths
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Entrypoint bootstraps the app (key:generate, migrate, etc.) then starts php-fpm
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
