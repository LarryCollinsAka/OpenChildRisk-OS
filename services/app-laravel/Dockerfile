# ================================================
# OpenChildRisk OS — Laravel Application Dockerfile
# ================================================

FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    autoconf \
    g++ \
    make \
    oniguruma-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    zip \
    pcntl \
    bcmath \
    mbstring

# Install Redis extension via pecl
# autoconf + g++ + make installed above for this
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# Install Composer from official image
COPY --from=composer:2.9 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Build frontend assets
COPY package*.json ./
RUN npm ci --ignore-scripts
RUN npm run build

# Set correct permissions
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Expose Laravel port
EXPOSE 8000

# Start Laravel server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]