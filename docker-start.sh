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

echo "==> Démarrage Apache sur port ${PORT:-80}..."
exec apache2-foreground
