# Use an official PHP image with FPM
FROM php:8.2-fpm

# Install system dependencies with updated apt commands
RUN apt-get update --allow-releaseinfo-change && apt-get install -y --no-install-recommends \
    apt-utils \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set correct permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose port (Render sets $PORT dynamically)
EXPOSE 8000

# Start Laravel development server, binding to the Render $PORT environment variable
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
