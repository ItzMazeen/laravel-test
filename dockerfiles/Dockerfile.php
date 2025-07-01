# Stage 1: Composer dependencies
FROM composer:2.5 AS composer

WORKDIR /app

# Copy only the files needed for composer install
COPY composer.json composer.lock ./

# Install PHP dependencies without dev dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist \
    # Remove composer cache to save space
    && composer clear-cache

# Stage 2: Node dependencies and asset building
FROM node:18-alpine AS node

WORKDIR /app

COPY package*.json ./
COPY vite.config.js ./

COPY --from=composer /app/vendor /app/vendor

COPY resources/ resources/
COPY public/ public/

# Install JS dependencies
RUN npm ci

# Build assets
RUN npm run build \
    # Clean npm cache to save space
    && npm cache clean --force

# Stage 3: Final slim runtime
FROM php:8.1-fpm-alpine AS runtime

# Use a non-root user
RUN addgroup -g 1000 laravel && \
    adduser -u 1000 -G laravel -h /home/laravel -D laravel

# Install only runtime PHP extensions using a single RUN to reduce layers
RUN apk add --no-cache --virtual .build-deps \
        libxml2-dev \
        icu-dev \
        oniguruma-dev \
    && apk add --no-cache \
        libxml2 \
        icu \
        bash \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo \
        pdo_mysql \
        xml \
        bcmath \
        mbstring \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/* \
    # Slim down the image by removing unneeded files
    && rm -rf /usr/local/lib/php/doc/* \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /var/cache/apk/* \
    && rm -rf /usr/share/man

WORKDIR /var/www/laravel

# Copy application code and built assets
COPY --chown=laravel:laravel . .
COPY --chown=laravel:laravel --from=composer /app/vendor ./vendor 
COPY --chown=laravel:laravel --from=node /app/public/build ./public/build


# Remove development files not needed in production
RUN rm -rf \
    node_modules \
    tests \
    .git \
    .github \
    .gitignore \
    .editorconfig \
    docker \
    docker-compose.yml \
    README.md \
    phpunit.xml \
    CHANGELOG.md \
    .env.example \
    *.log \
    webpack.mix.js \
    package.json \
    package-lock.json \
    composer.lock \
    vite.config.js \
    tailwind.config.js \
    postcss.config.js

# Set proper permissions
RUN chmod -R 755 storage bootstrap/cache && \
    chown -R laravel:laravel storage bootstrap/cache

# Switch to non-root user
USER laravel

EXPOSE 9000

CMD ["php-fpm"]