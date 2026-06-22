FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl zip unzip git supervisor \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        pdo pdo_mysql mbstring exif pcntl bcmath xml zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


WORKDIR /opt/backend

#Copy project files
COPY ./ ./
# Set permissions for php
RUN chown -R www-data:www-data /opt/backend \
    && find /opt/backend/storage /opt/backend/bootstrap/cache -type d -exec chmod 775 {} \; \
    && find /opt/backend/storage /opt/backend/bootstrap/cache -type f -exec chmod 664 {} \;


# Expose port 9000 and start PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
