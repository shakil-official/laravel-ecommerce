FROM php:8.3-fpm

LABEL maintainer="Bangla Puzzle assignment" \
      description="PHP-FPM 8.3 image for the Laravel e-commerce project"

# --- System libraries & PHP extensions required by Laravel + image handling ---
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip curl \
        libicu-dev libzip-dev libonig-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl zip gd bcmath exif pcntl opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --- Composer ----------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# --- PHP configuration ---------------------------------------------------------
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# --- Entrypoint (installs deps, migrates, seeds, links storage) --------------
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
