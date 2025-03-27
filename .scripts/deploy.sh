#!/bin/bash
set -e

echo "Deployment started ..."

# Enter maintenance mode or return true
# if already is in maintenance mode
echo "Putting application into maintenance mode..."
(php artisan down) || true

# Install composer dependencies
echo "Installing composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear the old cache
echo "Clearing old cache..."
php artisan clear-compiled

# Recreate cache
echo "Recreating cache..."
php artisan optimize

# Installing npm dependencies
echo "Installing npm dependencies..."
npm install

# Compile npm assets
echo "Compiling npm assets..."
npm run build
if [ $? -ne 0 ]; then
  echo "npm run build failed! Please check the error messages above."
  echo "Deployment aborted."
  exit 1
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Run optimizations and clearing
echo "Bringing application out of maintenance mode..."
php artisan optimize:clear

# Exit maintenance mode
echo "Bringing application out of maintenance mode..."
php artisan up

echo "Deployment finished successfully!"
exit
