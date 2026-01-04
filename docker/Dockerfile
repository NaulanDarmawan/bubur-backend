# Kita mulai dari image PHP 8.2 resmi
FROM php:8.2-fpm

# Install system dependencies (alat-alat dasar Linux)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions yang wajib buat Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Redis extension (PENTING: Karena kamu mau pakai Redis)
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer (Manajer paket PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set folder kerja di dalam container
WORKDIR /var/www