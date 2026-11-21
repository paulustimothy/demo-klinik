
# ==========================================
# 1. Build frontend assets (Vite)
# ==========================================
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY . .

RUN if [ -f package-lock.json ]; then \
        npm ci; \
    else \
        npm install; \
    fi \
    && npm run build


# ==========================================
# 2. Laravel + PHP + Apache
# ==========================================
FROM php:8.3-apache-bookworm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure GD if it is not already installed
RUN if ! php -r "exit(extension_loaded('gd') ? 0 : 1);"; then \
        docker-php-ext-configure gd --with-freetype --with-jpeg; \
    fi

# Install PHP extensions only if they are not already loaded
RUN set -eux; \
    extensions=""; \
    for ext in pdo_sqlite sqlite3 mbstring exif pcntl bcmath gd zip intl curl opcache; do \
        if ! php -r "exit(extension_loaded('$ext') ? 0 : 1);"; then \
            extensions="$extensions $ext"; \
        fi; \
    done; \
    if [ -n "$extensions" ]; then \
        docker-php-ext-install -j"$(nproc)" $extensions; \
    fi

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Laravel source code
COPY . .

# Copy compiled Vite assets
COPY --from=frontend /app/public/build ./public/build

# Install Laravel dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Create SQLite database
RUN mkdir -p database \
    && touch database/database.sqlite

# Create Laravel writable directories
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Configure Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/000-default.conf

# Enable Apache modules
RUN a2enmod rewrite headers

# Configure Apache to listen on port 3000
RUN sed -i 's/Listen 80/Listen 3000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:3000>/' \
    /etc/apache2/sites-available/000-default.conf

# Allow Laravel .htaccess
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' >> /etc/apache2/apache2.conf

# Set Laravel permissions
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    database \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 3000

# Run migrations when the container starts
CMD ["sh", "-c", "php artisan migrate --force && apache2-foreground"]
