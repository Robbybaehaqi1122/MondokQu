#!/bin/bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT_DIR"

echo "[post-pull] Building frontend assets..."
npm ci
npm run build

echo "[post-pull] Clearing Laravel caches..."
php artisan config:clear
php artisan view:clear
php artisan cache:clear

echo "[post-pull] Assets ready."
