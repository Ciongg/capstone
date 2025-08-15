### Step 1: Node.js for frontend (Vite)
FROM node:18 AS node-builder

WORKDIR /app
COPY . .

# Install npm dependencies and build production assets
RUN npm install
RUN npm run build


### Step 2: PHP for Laravel backend
FROM php:8.2-fpm

WORKDIR /var/www

# Install PHP extensions and system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libxml2-dev libzip-dev libpng-dev libjpeg-dev libonig-dev \
    sqlite3 libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel project into container
COPY --chown=www-data:www-data . /var/www

# Copy built frontend assets from node-builder
COPY --from=node-builder /app/public/build /var/www/public/build

# Install PHP dependencies
RUN composer install --optimize-autoloader

# Clear and cache config/routes
RUN php artisan config:clear
RUN php artisan config:cache
RUN php artisan route:cache

# Run migrations and seed the database
RUN php artisan migrate --force --seed

# Expose port 8000 (you can change this to 80 if needed)
EXPOSE 8000

# Start Laravel server (for testing; for production, use Nginx + PHP-FPM)
CMD php artisan serve --host=0.0.0.0 --port=8000
