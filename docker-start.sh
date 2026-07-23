#!/bin/bash
set -e

echo "==> Lien stockage..."
php artisan storage:link --force 2>/dev/null || true

echo "==> Migrations..."
php artisan migrate --force 2>/dev/null || true

echo "==> Cache config/routes/vues..."
php artisan config:cache  2>/dev/null || true
php artisan route:cache   2>/dev/null || true
php artisan view:cache    2>/dev/null || true

echo "==> Correction MPM Apache..."
a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

echo "==> Configuration du port ${PORT:-80}..."
sed -i "s/^Listen 80\$/Listen ${PORT:-80}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf

echo "==> Démarrage Apache sur port ${PORT:-80}..."
exec apache2-foreground
