FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk --no-cache add \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    # autoconf \
    # gcc \
    # g++ \
    # make \
    # libc-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo pdo_mysql 
    # && pecl install redis \
    # && docker-php-ext-enable redis \
    # && apk del autoconf gcc g++ make libc-dev

RUN echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini && \
echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini
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
