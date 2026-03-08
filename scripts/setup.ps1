# Setup script for Dynamic Workflow Orchestrator

Write-Host "Starting setup..." -ForegroundColor Cyan

# 0. Pre-flight Checks
Write-Host "`n--- Pre-flight Checks ---" -ForegroundColor Yellow
$dependencies = @("php", "composer", "npm")
foreach ($dep in $dependencies) {
    if (!(Get-Command $dep -ErrorAction SilentlyContinue)) {
        Write-Error "Error: $dep is not installed or not in PATH. Please install it and try again."
        exit 1
    }
}
Write-Host "All dependencies found." -ForegroundColor Green

# 1. Backend Setup
Write-Host "`n--- Setting up Backend ---" -ForegroundColor Yellow
Set-Location "$PSScriptRoot/../backend"

Write-Host "Installing composer dependencies..."
composer install --no-interaction
if ($LASTEXITCODE -ne 0) { Write-Error "Composer install failed."; exit 1 }

Write-Host "Creating .env file..."
if (!(Test-Path .env)) {
    Copy-Item .env.example .env
}

Write-Host "Generating app key..."
php artisan key:generate

Write-Host "Generating JWT secret..."
php artisan jwt:secret --force

Write-Host "Configuring .env file..."
$envContent = Get-Content .env
$envContent = $envContent -replace '^#?\s?DB_CONNECTION=.*', 'DB_CONNECTION=sqlite'
$envContent = $envContent -replace '^#?\s?DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite'
$envContent = $envContent -replace '^#?\s?QUEUE_CONNECTION=.*', 'QUEUE_CONNECTION=database'
$envContent = $envContent -replace '^#?\s?BROADCAST_CONNECTION=.*', 'BROADCAST_CONNECTION=reverb'
$envContent = $envContent -replace '^#?\s?MAIL_MAILER=.*', 'MAIL_MAILER=log'
$envContent | Set-Content .env

# Add Reverb keys if not present
if (!(Select-String -Path .env -Pattern "REVERB_APP_ID")) {
    $reverbConfig = @"

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
"@
    Add-Content -Path .env -Value $reverbConfig
}

# Create sqlite database file if it doesn't exist
$dbPath = "database/database.sqlite"
if (!(Test-Path $dbPath)) {
    New-Item -Path $dbPath -ItemType File
}

Write-Host "Running migrations and seeders..."
php artisan migrate:fresh --seed --force
if ($LASTEXITCODE -ne 0) { Write-Error "Migration failed."; exit 1 }

# 2. Frontend Setup
Write-Host "`n--- Setting up Frontend ---" -ForegroundColor Yellow
Set-Location "$PSScriptRoot/../frontend"

Write-Host "Installing npm dependencies..."
npm install
if ($LASTEXITCODE -ne 0) { Write-Error "NPM install failed."; exit 1 }

Write-Host "Configuring frontend .env file..."
$frontendEnv = @"
VITE_API_BASE_URL=http://localhost:8000/api
VITE_WS_HOST=localhost
VITE_WS_PORT=8080
VITE_WS_KEY=p4wrtmbchpdt0bkcszul
VITE_WS_CLUSTER=mt1
"@
Set-Content -Path .env -Value $frontendEnv

Write-Host "`nSetup complete! Use Option 2 in the Manager to run the application." -ForegroundColor Green
Set-Location "$PSScriptRoot/.."
