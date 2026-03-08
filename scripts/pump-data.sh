#!/bin/bash
cd "$(dirname "$0")/../backend"
echo "Pumping realistic test data (users and requests)..."
php artisan db:seed --class=DataPumpSeeder
echo "Data pump complete."
