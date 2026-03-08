#!/bin/bash

echo "Starting setup..."

# 0. Pre-flight Checks
echo -e "\n--- Pre-flight Checks ---"
dependencies=("php" "composer" "npm")
for dep in "${dependencies[@]}"; do
    if ! command -v $dep &> /dev/null; then
        echo "Error: $dep is not installed or not in PATH. Please install it and try again."
        exit 1
    fi
done
echo "All dependencies found."

# 1. Backend Setup
echo -e "\n--- Setting up Backend ---"
cd "$(dirname "$0")/../backend"

echo "Installing composer dependencies..."
composer install --no-interaction
if [ $? -ne 0 ]; then echo "Composer install failed."; exit 1; fi

echo "Creating .env file..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

echo "Generating app key..."
php artisan key:generate

echo "Generating JWT secret..."
php artisan jwt:secret --force

echo "Configuring .env file..."
# Use sed for simple replacements
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/^#\?DB_DATABASE=.*/DB_DATABASE=database\/database.sqlite/' .env
sed -i 's/^BROADCAST_CONNECTION=.*/BROADCAST_CONNECTION=reverb/' .env
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/' .env
sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=log/' .env

# Add Reverb keys if not present
if ! grep -q "REVERB_APP_ID" .env; then
    cat >> .env <<EOF

REVERB_APP_ID=808080
REVERB_APP_KEY=p4wrtmbchpdt0bkcszul
REVERB_APP_SECRET=your-reverb-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="p4wrtmbchpdt0bkcszul"
VITE_REVERB_HOST="localhost"
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME="http"
EOF
fi

# Create sqlite database file
touch database/database.sqlite

echo "Running migrations and seeders..."
php artisan migrate:fresh --seed --force
if [ $? -ne 0 ]; then echo "Migration failed."; exit 1; fi

# 2. Frontend Setup
echo -e "\n--- Setting up Frontend ---"
cd "../frontend"

echo "Installing npm dependencies..."
npm install
if [ $? -ne 0 ]; then echo "NPM install failed."; exit 1; fi

echo "Configuring frontend .env file..."
cat > .env <<EOF
VITE_API_BASE_URL=http://localhost:8000/api
VITE_WS_HOST=localhost
VITE_WS_PORT=8080
VITE_WS_KEY=p4wrtmbchpdt0bkcszul
VITE_WS_CLUSTER=mt1
EOF

echo -e "\nSetup complete! Use Option 2 in the Manager to run the application."
