FROM php:8.1-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
        git \
        curl \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        soap \
        gd \
        zip \
        bcmath \
        intl \
        exif \
        opcache \
    # Epic 0/1 queue jobs (retries, TRTYPE=90 polling) need QUEUE_CONNECTION=redis
    # to actually delay — phpredis, not the predis composer package which isn't installed.
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

RUN git config --system --add safe.directory /var/www/html

ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} app && useradd -u ${UID} -g app -m app
USER app

EXPOSE 9000
CMD ["php-fpm"]
