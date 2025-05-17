# Base image for PHP with Swoole support
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    libcurl4-openssl-dev pkg-config libssl-dev \
    libmcrypt-dev libreadline-dev libicu-dev \
    zlib1g-dev g++ nginx supervisor redis-server inotify-tools

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory
COPY . /var/www/html

COPY redis.conf /usr/local/etc/redis/redis.conf

# RUN chmod -R 775 public/docs
# RUN chown -R www-data:www-data public/docs
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# RUN php artisan scribe:generate --verbose

# Update Laravel with dependencies
RUN composer update -W

# Install Laravel dependencies
RUN composer install

# Expose port
EXPOSE 8000

# CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
CMD ["php-fpm"]

# Copy the entrypoint script
# COPY watch.sh /usr/local/bin/watch.sh
# RUN chmod +x /usr/local/bin/watch.sh

# Set the entrypoint
# ENTRYPOINT ["watch.sh"]
