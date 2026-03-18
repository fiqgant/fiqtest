FROM php:8.4-fpm-alpine

# Install system dependencies + PHP extension deps
RUN apk add --no-cache \
    bash \
    curl \
    git \
    zip \
    unzip \
    nodejs \
    npm \
    mysql-client \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev

# Install PHP extensions
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install pdo pdo_mysql bcmath mbstring xml gd zip \
    && docker-php-ext-enable opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy package files and install node deps
COPY package.json ./
RUN npm install

# Copy the rest of the application
COPY . .

# Build frontend assets
RUN npm run build

# Run composer scripts
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy PHP-FPM config
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
