# Production Dockerfile for MapIt Application
FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure GD with JPEG and FreeType support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/cache \
    && mkdir -p /var/www/html/storage/uploads \
    && mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage

# Use default PHP-FPM configuration and customize it
RUN echo 'listen = 0.0.0.0:9000' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_children = 50' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.start_servers = 5' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.min_spare_servers = 5' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_spare_servers = 35' >> /usr/local/etc/php-fpm.d/www.conf

# Basic PHP production settings
RUN echo 'memory_limit = 256M' > /usr/local/etc/php/conf.d/production.ini \
    && echo 'max_execution_time = 30' >> /usr/local/etc/php/conf.d/production.ini \
    && echo 'upload_max_filesize = 10M' >> /usr/local/etc/php/conf.d/production.ini \
    && echo 'post_max_size = 20M' >> /usr/local/etc/php/conf.d/production.ini \
    && echo 'expose_php = Off' >> /usr/local/etc/php/conf.d/production.ini \
    && echo 'display_errors = Off' >> /usr/local/etc/php/conf.d/production.ini \
    && echo 'log_errors = On' >> /usr/local/etc/php/conf.d/production.ini \
    && echo 'date.timezone = Europe/Amsterdam' >> /usr/local/etc/php/conf.d/production.ini

# Create entrypoint script  
RUN echo '#!/bin/bash' > /usr/local/bin/entrypoint.sh \
    && echo 'set -e' >> /usr/local/bin/entrypoint.sh \
    && echo 'if [ ! -f /var/www/html/database/mapit.db ]; then' >> /usr/local/bin/entrypoint.sh \
    && echo '    touch /var/www/html/database/mapit.db' >> /usr/local/bin/entrypoint.sh \
    && echo '    chown www-data:www-data /var/www/html/database/mapit.db' >> /usr/local/bin/entrypoint.sh \
    && echo '    chmod 664 /var/www/html/database/mapit.db' >> /usr/local/bin/entrypoint.sh \
    && echo 'fi' >> /usr/local/bin/entrypoint.sh \
    && echo 'chown -R www-data:www-data /var/www/html/storage' >> /usr/local/bin/entrypoint.sh \
    && echo 'chmod -R 777 /var/www/html/storage' >> /usr/local/bin/entrypoint.sh \
    && echo 'exec "$@"' >> /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
