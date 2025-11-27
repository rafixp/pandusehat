# ============================================
# Stage 1 - Build Composer Dependencies
# ============================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
RUN composer dump-autoload --optimize


# ============================================
# Stage 2 - Build Frontend (jika pakai Vite)
# ============================================
FROM node:20 AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY . .
RUN npm run build


# ============================================
# Stage 3 - Final Runtime (PHP + Nginx)
# ============================================
FROM php:8.2-fpm

# Install extensions
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring tokenizer xml gd \
    && apt-get clean

WORKDIR /var/www/html

# Copy Laravel source
COPY . .

# Copy vendor & built assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Copy nginx config
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Nginx port
EXPOSE 80

# Start Nginx + PHP-FPM
CMD service nginx start && php-fpm
