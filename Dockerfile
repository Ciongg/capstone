# ---------- Base PHP image ----------
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# ---------- Install system dependencies ----------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# ---------- Install PHP extensions ----------
RUN docker-php-ext-install pdo pdo_pgsql

# ---------- Install Composer ----------
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ---------- Copy project files including .env ----------
COPY . .

# ---------- Install PHP dependencies ----------
RUN composer install --optimize-autoloader

# ---------- Set correct APP_URL for Vite ----------
ARG APP_URL=https://capstone-3zq9.onrender.com
ENV APP_URL=${APP_URL}
ENV ASSET_URL=${APP_URL}

# ---------- Install Node dependencies & build frontend ----------
RUN npm install
RUN npm run build

# ---------- Set permissions for storage and cache ----------
RUN chown -R www-data:www-data storage bootstrap/cache

# ---------- Expose port ----------
EXPOSE 10000

# ---------- Start Laravel ----------
CMD php artisan migrate:fresh --seed && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=10000