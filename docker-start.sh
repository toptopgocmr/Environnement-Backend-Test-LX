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

echo "==> Démarrage Apache sur port ${PORT:-80}..."
exec apache2-foreground
