#!/bin/bash

# MapIt Application Entrypoint Script
# This script initializes the application on container startup

set -e

echo "Starting MapIt application..."

# Wait for database to be ready (if using external database)
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for MySQL to be ready..."
    while ! mysqladmin ping -h"$DB_HOST" --silent; do
        sleep 1
    done
    echo "MySQL is ready!"
fi

# Create storage directories if they don't exist
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/cache
mkdir -p /var/www/html/storage/uploads

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Create database directory and file for SQLite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    if [ ! -f "$DB_DATABASE" ]; then
        touch "$DB_DATABASE"
        chown www-data:www-data "$DB_DATABASE"
        chmod 664 "$DB_DATABASE"
        
        echo "Initializing SQLite database..."
        # Run database migrations/setup
        php /var/www/html/scripts/setup_database.php
        
        # Seed the database with initial data
        php /var/www/html/scripts/seed_database.php
    fi
fi

# Generate application key if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Set file permissions
chown -R www-data:www-data /var/www/html
find /var/www/html -type f -exec chmod 644 {} \;
find /var/www/html -type d -exec chmod 755 {} \;

echo "MapIt application initialized successfully!"

# Execute the main command
exec "$@"
