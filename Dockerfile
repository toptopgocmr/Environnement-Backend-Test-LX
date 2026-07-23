# ── Build stage : Composer ───────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
      --no-dev \
      --optimize-autoloader \
      --no-scripts \
      --no-interaction \
      --ignore-platform-reqs

# ── Runtime stage : PHP + Apache ────────────────────────────────────────────
FROM php:8.3-apache

# Extensions système requises
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
        libexif-dev libzip-dev libonig-dev libxml2-dev \
        unzip git zip curl \
    && docker-php-ext-configure gd \
          --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
          gd exif pdo pdo_mysql zip opcache \
          mbstring bcmath xml fileinfo \
    && rm -rf /var/lib/apt/lists/*

# OPcache production
RUN { \
      echo 'opcache.memory_consumption=128'; \
      echo 'opcache.interned_strings_buffer=8'; \
      echo 'opcache.max_accelerated_files=10000'; \
      echo 'opcache.revalidate_freq=60'; \
      echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Apache : activer mod_rewrite + pointer sur /public
RUN a2enmod rewrite \
    && a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' \
        /etc/apache2/apache2.conf \
    && printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' >> /etc/apache2/apache2.conf

# Note : le port dynamique Railway est appliqué au runtime dans docker-start.sh,
# car Apache ne sait pas interpréter la syntaxe bash ${PORT:-80} dans ports.conf.

WORKDIR /var/www/html

# Copier le code et le vendor installé
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Garantir l'existence des dossiers storage (git ne versionne pas les dossiers vides)
RUN mkdir -p storage/framework/sessions storage/framework/views \
             storage/framework/cache/data storage/framework/testing \
             storage/logs

# Permissions storage
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Script de démarrage
COPY --chmod=755 docker-start.sh /docker-start.sh

EXPOSE 80
CMD ["/docker-start.sh"]
