# Use the official PHP 8.3 image with PHP-FPM
FROM php:8.1-fpm

# Install necessary PHP extensions for MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files to the working directory
COPY . /var/www/html

# Run Composer install to get dependencies
RUN composer install

# Set permissions for the working directory
RUN chown -R www-data:www-data /var/www/html


