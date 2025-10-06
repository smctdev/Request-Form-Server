FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    npm \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    autoconf \
    pkg-config \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip gd

# ✅ Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Set working directory
WORKDIR /var/www

# Install Composer (Laravel's dependency manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your Laravel project into the container
COPY . /var/www

# Set file permissions (optional)
RUN chown -R www-data:www-data /var/www

# Set up permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8004

CMD ["php-fpm"]
