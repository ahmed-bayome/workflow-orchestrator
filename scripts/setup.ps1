# Setup script for Dynamic Workflow Orchestrator

Write-Host "Starting setup..." -ForegroundColor Cyan

# --- Environment Pre-configuration ---
# Force TLS 1.2 for secure downloads (required for fresh Windows installs)
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$toolsDir = "$PSScriptRoot/.tools"
if (!(Test-Path $toolsDir)) { New-Item -ItemType Directory -Path $toolsDir | Out-Null }

# --- Helper Functions ---
function Refresh-Environment {
    Write-Host "Refreshing PATH..." -ForegroundColor Gray
    $env:Path = "$toolsDir\php;$toolsDir\node;$env:Path"
}

function Download-WithRetry {
    param([string]$name, [string]$url, [string]$outPath)
    $maxRetries = 3
    $retryCount = 0
    $success = $false

    while (-not $success -and $retryCount -lt $maxRetries) {
        try {
            Write-Host "Downloading $name (Attempt $($retryCount + 1))..." -ForegroundColor Yellow
            Invoke-WebRequest -Uri $url -OutFile $outPath -UseBasicParsing -TimeoutSec 60
            if (Test-Path $outPath) { $success = $true }
        } catch {
            $retryCount++
            Write-Host "Download failed. Retrying in 2 seconds..." -ForegroundColor Gray
            Start-Sleep -Seconds 2
        }
    }

    if (-not $success) {
        Write-Error "CRITICAL: Failed to download $name after $maxRetries attempts. Please check your internet connection."
        exit 1
    }
}

function Download-Manual {
    param([string]$name, [string]$url, [string]$zipPath, [string]$extractDir)
    
    if (Test-Path $extractDir) { return } # Skip if already exists
    
    Download-WithRetry -name $name -url $url -outPath $zipPath
    
    Write-Host "Extracting $name..." -ForegroundColor Gray
    if (!(Test-Path $extractDir)) { New-Item -ItemType Directory -Path $extractDir | Out-Null }
    Expand-Archive -Path $zipPath -DestinationPath $extractDir -Force
    Remove-Item $zipPath -ErrorAction SilentlyContinue
}

function Setup-PortablePHP {
    $phpUrl = "https://windows.php.net/downloads/releases/php-8.2.12-Win32-vs16-x64.zip"
    $phpDir = "$toolsDir\php"
    if (!(Test-Path "$phpDir\php.exe")) {
        Download-Manual -name "PHP" -url $phpUrl -zipPath "$toolsDir\php.zip" -extractDir $phpDir
        
        # Configure php.ini
        if (Test-Path "$phpDir\php.ini-development") {
            Copy-Item "$phpDir\php.ini-development" "$phpDir\php.ini" -Force
            $ini = Get-Content "$phpDir\php.ini"
            $ini = $ini -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
            $ini = $ini -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
            $ini = $ini -replace ';extension=sqlite3', 'extension=sqlite3'
            $ini = $ini -replace ';extension=mbstring', 'extension=mbstring'
            $ini = $ini -replace ';extension=openssl', 'extension=openssl'
            $ini = $ini -replace ';extension=curl', 'extension=curl'
            $ini | Set-Content "$phpDir\php.ini"
        } else {
            Write-Error "Extraction failed: php.ini-development not found in $phpDir"
            exit 1
        }
    }
}

function Setup-PortableComposer {
    $compDir = "$toolsDir\php"
    if (!(Test-Path "$compDir\composer.phar")) {
        Write-Host "`nConfiguring Composer..." -ForegroundColor Yellow
        Download-WithRetry -name "Composer" -url "https://getcomposer.org/composer.phar" -outPath "$compDir\composer.phar"
        # Create a batch shim for composer
        "@echo off`nphp %~dp0composer.phar %*" | Set-Content "$compDir\composer.bat"
    }
}

function Setup-PortableNode {
    $nodeUrl = "https://nodejs.org/dist/v20.10.0/node-v20.10.0-win-x64.zip"
    $nodeDir = "$toolsDir\node_temp"
    $finalDir = "$toolsDir\node"
    if (!(Test-Path "$finalDir\node.exe")) {
        Download-Manual -name "Node.js" -url $nodeUrl -zipPath "$toolsDir\node.zip" -extractDir $nodeDir
        # Move files from nested folder to $toolsDir\node
        if (!(Test-Path $finalDir)) { New-Item -ItemType Directory -Path $finalDir | Out-Null }
        $innerFolder = Get-ChildItem $nodeDir | Select-Object -First 1
        Move-Item "$($innerFolder.FullName)\*" "$finalDir" -Force
        Remove-Item $nodeDir -Recurse -ErrorAction SilentlyContinue
    }
}

function Install-WithWinget {
    param([string]$name, [string]$wingetId)
    Write-Host "`n$name is missing. Attempting winget..." -ForegroundColor Yellow
    if (!(Get-Command winget -ErrorAction SilentlyContinue)) { return $false }
    winget install --id $wingetId --silent --accept-package-agreements --accept-source-agreements
    if ($LASTEXITCODE -eq 0) { Refresh-Environment; return $true }
    return $false
}

# 0. Pre-flight Checks & "A to Z" Installation
Write-Host "`n--- Pre-flight Checks ---" -ForegroundColor Yellow

# PHP Check
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    if (!(Install-WithWinget -name "PHP" -wingetId "PHP.PHP")) {
        Setup-PortablePHP
    }
}

# Composer Check
if (!(Get-Command composer -ErrorAction SilentlyContinue)) {
    if (!(Install-WithWinget -name "Composer" -wingetId "GetComposer.Composer")) {
        Setup-PortableComposer
    }
}

# Node Check
if (!(Get-Command npm -ErrorAction SilentlyContinue)) {
    if (!(Install-WithWinget -name "Node.js" -wingetId "OpenJS.NodeJS.LTS")) {
        Setup-PortableNode
    }
}

Refresh-Environment
Write-Host "All dependencies ready (using portable tools if necessary)." -ForegroundColor Green

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
