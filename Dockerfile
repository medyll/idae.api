FROM php:7.4-apache

# Install system deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    pkg-config \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions commonly used by the project
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) gd mbstring bcmath xml zip pcntl

# Install and enable mongodb extension via PECL
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Enable apache modules and set document root to /var/www/html/web
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}>!g' /etc/apache2/apache2.conf

# Turn on short_open_tag (project uses short tags `<?` in many files)
RUN echo "short_open_tag=On" > /usr/local/etc/php/conf.d/short-open-tag.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --quiet --install-dir=/usr/local/bin --filename=composer

# Copy app sources
WORKDIR /var/www/html
COPY . /var/www/html

# Copy entrypoint and make executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Install PHP dependencies inside the container (web/bin composer.json)
WORKDIR /var/www/html/web/bin
RUN composer install --no-interaction --prefer-dist || true

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
