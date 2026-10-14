FROM php:8.3-cli-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    PUPPETEER_SKIP_DOWNLOAD=true \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends \
      git unzip curl ca-certificates \
      libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libsqlite3-dev \
      wkhtmltopdf pdftk-java chromium nodejs npm \
      fonts-dejavu fonts-liberation \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
      storage/logs storage/app/reports storage/app/templates bootstrap/cache \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && npm install puppeteer @libsql/client --prefix . --ignore-scripts=false

COPY docker/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh && chmod +x /entrypoint.sh

ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 10000
CMD ["/entrypoint.sh"]
