#!/bin/bash
set -euo pipefail

# ======================================================
# Mondok Qu - Deployment Script (Manual / Non-Docker)
# ======================================================
# Usage: bash deploy.sh [environment]
#   environment: production | staging (default: production)
# ======================================================

ENVIRONMENT="${1:-production}"
DEPLOY_DIR="$(dirname "$0")"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_DIR="${DEPLOY_DIR}/storage/backups"

echo "============================================"
echo "  Mondok Qu Deployment"
echo "  Environment: ${ENVIRONMENT}"
echo "  Timestamp:   ${TIMESTAMP}"
echo "============================================"

# Validate environment file
if [ ! -f "${DEPLOY_DIR}/.env" ]; then
    echo "[ERROR] .env file not found!"
    echo "  Copy .env.example to .env and configure it first."
    exit 1
fi

# Check for git uncommitted changes
if [ -n "$(git -C "${DEPLOY_DIR}" status --porcelain)" ]; then
    echo "[WARNING] There are uncommitted changes in the repository."
    read -p "  Continue anyway? [y/N] " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Maintenance mode
echo "[STEP 1/9] Enabling maintenance mode..."
php "${DEPLOY_DIR}/artisan" down --retry=30 --render="errors::503"

# Backup database (if configured)
echo "[STEP 2/9] Backing up database..."
mkdir -p "${BACKUP_DIR}"
DB_CONNECTION=$(grep -E '^DB_CONNECTION=' "${DEPLOY_DIR}/.env" | cut -d '=' -f2)

if [ "${DB_CONNECTION}" = "sqlite" ]; then
    DB_DATABASE=$(grep -E '^DB_DATABASE=' "${DEPLOY_DIR}/.env" | cut -d '=' -f2)
    if [ -f "${DB_DATABASE}" ]; then
        cp "${DB_DATABASE}" "${BACKUP_DIR}/database_${TIMESTAMP}.sqlite"
        echo "  SQLite backed up."
    fi
elif [ "${DB_CONNECTION}" = "mysql" ]; then
    DB_DATABASE=$(grep -E '^DB_DATABASE=' "${DEPLOY_DIR}/.env" | cut -d '=' -f2)
    DB_USERNAME=$(grep -E '^DB_USERNAME=' "${DEPLOY_DIR}/.env" | cut -d '=' -f2)
    DB_PASSWORD=$(grep -E '^DB_PASSWORD=' "${DEPLOY_DIR}/.env" | cut -d '=' -f2)
    DB_HOST=$(grep -E '^DB_HOST=' "${DEPLOY_DIR}/.env" | cut -d '=' -f2)

    mysqldump \
        --host="${DB_HOST:-127.0.0.1}" \
        --user="${DB_USERNAME}" \
        --password="${DB_PASSWORD}" \
        "${DB_DATABASE}" \
        > "${BACKUP_DIR}/database_${TIMESTAMP}.sql"
    echo "  MySQL backed up to database_${TIMESTAMP}.sql"
fi

# Pull latest code
echo "[STEP 3/9] Pulling latest code..."
git -C "${DEPLOY_DIR}" fetch origin
git -C "${DEPLOY_DIR}" reset --hard origin/main

# Update PHP dependencies
echo "[STEP 4/9] Installing PHP dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install \
    --no-dev \
    --no-progress \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --working-dir="${DEPLOY_DIR}"

# Build frontend assets
echo "[STEP 5/9] Building frontend assets..."
npm --prefix "${DEPLOY_DIR}" ci
npm --prefix "${DEPLOY_DIR}" run build

# Run database migrations
echo "[STEP 6/9] Running database migrations..."
php "${DEPLOY_DIR}/artisan" migrate --force

# Clear and rebuild caches
echo "[STEP 7/9] Caching configuration..."
php "${DEPLOY_DIR}/artisan" config:cache
php "${DEPLOY_DIR}/artisan" route:cache
php "${DEPLOY_DIR}/artisan" view:cache

# Create storage link
echo "[STEP 8/9] Creating storage link..."
php "${DEPLOY_DIR}/artisan" storage:link || true

# Restart queue worker
echo "[STEP 9/9] Restarting queue worker..."
php "${DEPLOY_DIR}/artisan" queue:restart

# Disable maintenance mode
php "${DEPLOY_DIR}/artisan" up

echo ""
echo "============================================"
echo "  Deployment completed successfully!"
echo "  Environment: ${ENVIRONMENT}"
echo "============================================"
