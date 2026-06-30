#!/bin/bash
set -euo pipefail

ENVIRONMENT="${1:-production}"
DEPLOY_DIR="$(dirname "$0")"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')

echo ""
echo "============================================"
echo "  Mondok Qu Docker Deployment"
echo "  Environment: ${ENVIRONMENT}"
echo "  Timestamp:   ${TIMESTAMP}"
echo "============================================"
echo ""

# Validate .env file
if [ ! -f "${DEPLOY_DIR}/.env" ]; then
    echo "[ERROR] .env file not found!"
    echo "  Copy .env.docker to .env and configure it first."
    echo "  cp .env.docker .env"
    exit 1
fi

# Pull latest code
echo "[STEP 1/7] Pulling latest code..."
git -C "${DEPLOY_DIR}" fetch origin main
git -C "${DEPLOY_DIR}" reset --hard origin/main

# Rebuild application image
echo "[STEP 2/7] Building Docker image..."
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" build --no-cache app

# Restart services with new image
echo "[STEP 3/7] Restarting services..."
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" up -d --no-deps app nginx scheduler queue-worker

# Run migrations
echo "[STEP 4/7] Running database migrations..."
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" exec -T app php artisan migrate --force

# Clear and rebuild caches
echo "[STEP 5/7] Caching configuration..."
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" exec -T app php artisan config:cache
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" exec -T app php artisan route:cache
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" exec -T app php artisan view:cache

# Create storage link
echo "[STEP 6/7] Creating storage link..."
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" exec -T app php artisan storage:link || true

# Restart queue worker
echo "[STEP 7/7] Restarting queue worker..."
docker compose -f "${DEPLOY_DIR}/docker-compose.yml" exec -T app php artisan queue:restart

echo ""
echo "============================================"
echo "  Deployment completed successfully!"
echo "  Environment: ${ENVIRONMENT}"
echo "============================================"
