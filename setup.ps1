# Setup script for Dynamic Workflow Orchestrator

Write-Host "Starting setup..." -ForegroundColor Cyan

# 1. Backend Setup
Write-Host "`n--- Setting up Backend ---" -ForegroundColor Yellow
Set-Location backend

Write-Host "Installing composer dependencies..."
composer install --no-interaction

Write-Host "Creating .env file..."
Copy-Item .env.example .env

Write-Host "Generating app key..."
php artisan key:generate

Write-Host "Generating JWT secret..."
php artisan jwt:secret --force

Write-Host "Configuring .env file..."
(Get-Content .env) -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=sqlite' `
             -replace 'DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite' `
              -replace 'QUEUE_CONNECTION=.*', 'QUEUE_CONNECTION=database' `
              -replace 'BROADCAST_CONNECTION=.*', 'BROADCAST_CONNECTION=reverb' `
              -replace 'MAIL_MAILER=.*', 'MAIL_MAILER=log' | Set-Content .env

# Add Reverb keys to backend .env
$reverbConfig = @"

REVERB_APP_ID=808080
REVERB_APP_KEY=p4wrtmbchpdt0bkcszul
REVERB_APP_SECRET=your-reverb-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
"@
Add-Content -Path .env -Value $reverbConfig

# Create sqlite database file if it doesn't exist
$dbPath = "database/database.sqlite"
if (!(Test-Path $dbPath)) {
    New-Item -Path $dbPath -ItemType File
}

Write-Host "Running migrations and seeders..."
php artisan migrate:fresh --seed --force

# 2. Frontend Setup
Write-Host "`n--- Setting up Frontend ---" -ForegroundColor Yellow
Set-Location ../frontend

Write-Host "Installing npm dependencies..."
npm install

Write-Host "Configuring frontend .env file..."
# Ensure .env exists
if (!(Test-Path .env)) {
    New-Item -Path .env -ItemType File
}

$frontendEnv = @"
VITE_API_BASE_URL=http://localhost:8000/api
VITE_WS_HOST=localhost
VITE_WS_PORT=8080
VITE_WS_KEY=p4wrtmbchpdt0bkcszul
VITE_WS_CLUSTER=mt1
"@
Set-Content -Path .env -Value $frontendEnv

Write-Host "`nSetup complete! You can now run the application using run.bat or follow the manual start instructions in the README." -ForegroundColor Green
Set-Location ..
