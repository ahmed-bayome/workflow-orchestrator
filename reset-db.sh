#!/bin/bash
cd "$(dirname "$0")/backend"
echo "Resetting and seeding database..."
php artisan migrate:fresh --seed
echo "Database reset successfully."
