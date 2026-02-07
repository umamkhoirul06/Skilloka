# =============================================================================
# Skilloka Backend - Multi-stage Dockerfile
# PHP-FPM 8.2 with production optimizations
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Composer Dependencies
# -----------------------------------------------------------------------------
FROM composer:2.6 AS composer-deps

WORKDIR /app

COPY backend/composer.json backend/composer.lock ./

# Install dependencies without dev packages for production
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# -----------------------------------------------------------------------------
# Stage 2: Frontend Assets Build
# -----------------------------------------------------------------------------
FROM node:20-alpine AS frontend-build

WORKDIR /app

COPY backend/package*.json ./
RUN npm ci --only=production

COPY backend/resources ./resources
COPY backend/vite.config.js ./
COPY backend/tailwind.config.js ./
COPY backend/postcss.config.js ./

RUN npm run build

# -----------------------------------------------------------------------------
# Stage 3: Base PHP Image
# -----------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    libpq-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    intl \
    bcmath \
    opcache \
    mbstring \
    pcntl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Clean up
RUN apk del $PHPIZE_DEPS linux-headers \
    && rm -rf /var/cache/apk/* /tmp/*

# Create app user
RUN addgroup -g 1000 -S appgroup && \
    adduser -u 1000 -S appuser -G appgroup

WORKDIR /var/www/html

# -----------------------------------------------------------------------------
# Stage 4: Development Image
# -----------------------------------------------------------------------------
FROM base AS development

# Install dev dependencies
RUN apk add --no-cache git vim

# Install Xdebug for development
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS linux-headers

# Copy dev PHP configuration
COPY docker/backend/conf.d/php-dev.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/backend/conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Set permissions
RUN chown -R appuser:appgroup /var/www/html

USER appuser

EXPOSE 9000

CMD ["php-fpm"]

# -----------------------------------------------------------------------------
# Stage 5: Production Image
# -----------------------------------------------------------------------------
FROM base AS production

# Copy PHP production configuration
COPY docker/backend/conf.d/php-prod.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/backend/conf.d/opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
COPY docker/backend/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy application
COPY --from=composer-deps /app/vendor ./vendor
COPY backend/ .

# Copy built frontend assets
COPY --from=frontend-build /app/public/build ./public/build

# Install composer and optimize autoloader
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# Set permissions
RUN chown -R appuser:appgroup /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Security: Remove unnecessary files
RUN rm -rf /var/www/html/.git \
    /var/www/html/.env.example \
    /var/www/html/tests \
    /var/www/html/phpunit.xml

USER appuser

EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

CMD ["php-fpm"]
