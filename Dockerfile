FROM php:7.4-apache

# Install system deps (include dev libs needed to build PHP extensions)
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
    libwebp-dev \
    libonig-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libsasl2-dev \
    libkrb5-dev \
    cmake \
    build-essential \
    pkg-config \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions commonly used by the project
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" gd mbstring bcmath xml zip pcntl intl opcache

# MongoDB driver. 1.9.2 is the last 1.9.x release built against PHP 7.4 and is
# compatible with mongodb/mongodb ^1.6 (see web/bin/composer.json).
# Fail the build if the extension does not end up loaded.
ARG MONGODB_EXT_VERSION=1.9.2
RUN pecl channel-update pecl.php.net \
    && pecl install "mongodb-${MONGODB_EXT_VERSION}" \
    && docker-php-ext-enable mongodb \
    && php -m | grep -q '^mongodb$'

# PHP runtime settings. NOTE: web/.user.ini is ignored under mod_php (it only
# applies to CGI/FastCGI), so the values the app relies on are set here.
RUN { \
      echo "short_open_tag = On"; \
      echo "date.timezone = Europe/Paris"; \
      echo "memory_limit = 512M"; \
      echo "max_execution_time = 300"; \
      echo "post_max_size = 120M"; \
      echo "upload_max_filesize = 120M"; \
      echo "max_input_vars = 5000"; \
      echo "session.auto_start = 0"; \
    } > /usr/local/etc/php/conf.d/zz-idae.ini

# Apache: document root is web/, .htaccess must be honoured (routing lives in
# web/.htaccess), and CONF_INC is the bootstrap path every entry script reads
# from $_SERVER.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
RUN a2enmod rewrite setenvif \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory /var/www/html/web/>!g' /etc/apache2/apache2.conf \
    && sed -ri -e '/<Directory \/var\/www\/html\/web\/>/,/<\/Directory>/ s!AllowOverride None!AllowOverride All!' /etc/apache2/apache2.conf \
    && printf 'SetEnv CONF_INC /var/www/html/web/conf.inc.php\n' > /etc/apache2/conf-available/idae-env.conf \
    && a2enconf idae-env

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --quiet --install-dir=/usr/local/bin --filename=composer

# Dependencies first so source changes do not invalidate the composer layer
WORKDIR /var/www/html/web/bin
COPY web/bin/composer.json web/bin/composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

# Copy app sources
WORKDIR /var/www/html
COPY . /var/www/html
RUN composer dump-autoload --working-dir=/var/www/html/web/bin --optimize

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://localhost/info.php > /dev/null || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
