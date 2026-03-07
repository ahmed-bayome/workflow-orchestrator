:; # ------------------------------------------------------------------
:; # Polyglot: valid Windows batch AND valid bash.
:; # Windows:   .\manager.bat
:; # Mac/Linux: bash manager.bat
:; # ------------------------------------------------------------------
:; DIR="$(cd "$(dirname "$0")" && pwd)"; BACKEND="$DIR/backend"; FRONTEND="$DIR/frontend"
:; R='\033[0m'; BOLD='\033[1m'; CYAN='\033[36m'; YEL='\033[33m'; GRN='\033[32m'; RED='\033[31m'; MAG='\033[35m'; DIM='\033[2m'
:; info(){ printf "${CYAN}  i  ${R}%s\n" "$1"; }
:; ok(){   printf "${GRN}  v  ${R}%s\n" "$1"; }
:; warn(){ printf "${YEL}  !  ${R}%s\n" "$1"; }
:; err(){  printf "${RED}  x  ${R}%s\n" "$1"; }
:; step(){ printf "\n${MAG}  >> ${BOLD}%s${R}\n" "$1"; }
:;
:; install_tools_unix(){
:;   PLATFORM="$(uname -s)"
:;   step "Checking required tools..."
:;
:;   # Homebrew — Mac only
:;   if [ "$PLATFORM" = "Darwin" ]; then
:;     if ! command -v brew >/dev/null 2>&1; then
:;       step "Installing Homebrew..."
:;       /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
:;       eval "$(/opt/homebrew/bin/brew shellenv 2>/dev/null || /usr/local/bin/brew shellenv 2>/dev/null)"
:;       ok "Homebrew installed."
:;     else
:;       ok "Homebrew: already installed."
:;     fi
:;   fi
:;
:;   # PHP
:;   if ! command -v php >/dev/null 2>&1; then
:;     step "Installing PHP..."
:;     if [ "$PLATFORM" = "Darwin" ]; then
:;       brew install php
:;     elif command -v apt-get >/dev/null 2>&1; then
:;       sudo apt-get update -qq && sudo apt-get install -y php php-cli php-mbstring php-xml php-curl php-zip php-sqlite3 unzip
:;     elif command -v dnf >/dev/null 2>&1; then
:;       sudo dnf install -y php php-cli php-mbstring php-xml php-curl php-zip php-pdo
:;     fi
:;     ok "PHP installed."
:;   else
:;     ok "PHP: $(php -r 'echo PHP_VERSION;') already installed."
:;   fi
:;
:;   # Composer
:;   if ! command -v composer >/dev/null 2>&1; then
:;     step "Installing Composer..."
:;     if [ "$PLATFORM" = "Darwin" ]; then
:;       brew install composer
:;     else
:;       php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
:;       php composer-setup.php --quiet && rm composer-setup.php
:;       sudo mv composer.phar /usr/local/bin/composer && sudo chmod +x /usr/local/bin/composer
:;     fi
:;     ok "Composer installed."
:;   else
:;     ok "Composer: already installed."
:;   fi
:;
:;   # Node.js
:;   if ! command -v node >/dev/null 2>&1; then
:;     step "Installing Node.js..."
:;     if [ "$PLATFORM" = "Darwin" ]; then
:;       brew install node
:;     elif command -v apt-get >/dev/null 2>&1; then
:;       curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
:;       sudo apt-get install -y nodejs
:;     elif command -v dnf >/dev/null 2>&1; then
:;       sudo dnf install -y nodejs npm
:;     fi
:;     ok "Node.js installed."
:;   else
:;     ok "Node.js: $(node -v) already installed."
:;   fi
:; }
:;
:; show_menu(){
:;   clear
:;   printf "\n${CYAN}  ============================================${R}\n"
:;   printf "${CYAN}      Workflow Orchestrator Manager${R}\n"
:;   printf "${DIM}      Platform: $(uname -s)${R}\n"
:;   printf "${CYAN}  ============================================${R}\n\n"
:;   printf "  ${YEL}[1]${R}  ${BOLD}Full setup${R}            ${DIM}| tools, deps, .env, migrate, seed${R}\n"
:;   printf "  ${YEL}[2]${R}  ${BOLD}Run the app${R}           ${DIM}| API, Queue, Reverb, Frontend${R}\n"
:;   printf "  ${YEL}[3]${R}  ${BOLD}Install dependencies${R}  ${DIM}| Composer + npm${R}\n"
:;   printf "  ${YEL}[4]${R}  ${BOLD}Pump data${R}             ${DIM}| runs DataPumpSeeder${R}\n"
:;   printf "  ${YEL}[5]${R}  ${BOLD}Reset database${R}        ${DIM}| fresh migrations + seeders${R}\n"
:;   printf "  ${YEL}[6]${R}  ${BOLD}Exit${R}\n\n"
:; }
:;
:; run_app(){
:;   step "Launching all services..."
:;   if [ "$(uname -s)" = "Darwin" ]; then
:;     osascript <<APPLESCRIPT
:; tell application "Terminal"
:;     do script "cd '$BACKEND' && printf '\\033]0;API\\007' && php -S localhost:8000 -t public"
:;     tell application "System Events" to keystroke "t" using command down
:;     do script "cd '$BACKEND' && printf '\\033]0;Queue\\007' && php artisan queue:work" in front window
:;     tell application "System Events" to keystroke "t" using command down
:;     do script "cd '$BACKEND' && printf '\\033]0;Reverb\\007' && php artisan reverb:start" in front window
:;     tell application "System Events" to keystroke "t" using command down
:;     do script "cd '$FRONTEND' && printf '\\033]0;Frontend\\007' && npm run dev -- --open" in front window
:; end tell
:; APPLESCRIPT
:;     ok "Opened Terminal with 4 tabs."
:;   else
:;     TERM_BIN=""
:;     for t in gnome-terminal xterm konsole xfce4-terminal lxterminal; do
:;       command -v "$t" >/dev/null 2>&1 && TERM_BIN="$t" && break
:;     done
:;     if [ -n "$TERM_BIN" ]; then
:;       for svc in "API|php -S localhost:8000 -t public|$BACKEND" "Queue|php artisan queue:work|$BACKEND" "Reverb|php artisan reverb:start|$BACKEND" "Frontend|npm run dev -- --open|$FRONTEND"; do
:;         TITLE="${svc%%|*}"; REST="${svc#*|}"; CMD="${REST%%|*}"; CWD="${REST##*|}"
:;         if [ "$TERM_BIN" = "gnome-terminal" ]; then gnome-terminal --title="$TITLE" -- bash -c "cd '$CWD' && $CMD; exec bash" &
:;         else $TERM_BIN -e bash -c "cd '$CWD' && $CMD; exec bash" &; fi
:;       done
:;       ok "Launched 4 windows via $TERM_BIN."
:;     else
:;       warn "No GUI terminal found — running in background. Logs -> ./logs/"
:;       mkdir -p "$DIR/logs"
:;       nohup bash -c "cd '$BACKEND'  && php -S localhost:8000 -t public" > "$DIR/logs/api.log"      2>&1 &
:;       nohup bash -c "cd '$BACKEND'  && php artisan queue:work"          > "$DIR/logs/queue.log"    2>&1 &
:;       nohup bash -c "cd '$BACKEND'  && php artisan reverb:start"        > "$DIR/logs/reverb.log"   2>&1 &
:;       nohup bash -c "cd '$FRONTEND' && npm run dev -- --open"           > "$DIR/logs/frontend.log" 2>&1 &
:;       ok "All 4 services started in background."
:;     fi
:;   fi
:; }
:;
:; install_deps(){
:;   step "Installing Composer dependencies..."
:;   composer install --no-interaction -d "$BACKEND" && ok "Backend ready." || err "Composer failed."
:;   step "Installing npm dependencies..."
:;   (cd "$FRONTEND" && npm install) && ok "Frontend ready." || err "npm install failed."
:; }
:;
:; pump_data(){
:;   step "Running DataPumpSeeder..."
:;   (cd "$BACKEND" && php artisan db:seed --class=DataPumpSeeder) && ok "Data pumped successfully." || err "DataPumpSeeder failed."
:; }
:;
:; reset_db(){
:;   step "Fresh migrations + seeders..."
:;   (cd "$BACKEND" && php artisan migrate:fresh --seed --force) && ok "Database reset." || err "Reset failed."
:; }
:;
:; full_setup(){
:;   step "Full setup started..."
:;   install_tools_unix
:;   install_deps
:;   ENV_FILE="$BACKEND/.env"; ENV_EXAMPLE="$BACKEND/.env.example"
:;   [ ! -f "$ENV_FILE" ] && [ -f "$ENV_EXAMPLE" ] && cp "$ENV_EXAMPLE" "$ENV_FILE" && ok ".env created."
:;   (cd "$BACKEND" && php artisan key:generate)       && ok "App key generated."
:;   (cd "$BACKEND" && php artisan jwt:secret --force) && ok "JWT secret generated."
:;   sed -i.bak -e 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' -e 's/^DB_DATABASE=.*/DB_DATABASE=database\/database.sqlite/' -e 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/' -e 's/^BROADCAST_CONNECTION=.*/BROADCAST_CONNECTION=reverb/' -e 's/^MAIL_MAILER=.*/MAIL_MAILER=log/' "$ENV_FILE" && ok ".env configured."
:;   grep -q "REVERB_APP_ID" "$ENV_FILE" || printf '\nREVERB_APP_ID=808080\nREVERB_APP_KEY=p4wrtmbchpdt0bkcszul\nREVERB_APP_SECRET=your-reverb-secret\nREVERB_HOST=localhost\nREVERB_PORT=8080\nREVERB_SCHEME=http\nVITE_REVERB_APP_KEY=p4wrtmbchpdt0bkcszul\nVITE_REVERB_HOST=localhost\nVITE_REVERB_PORT=8080\nVITE_REVERB_SCHEME=http\n' >> "$ENV_FILE"
:;   DB_PATH="$BACKEND/database/database.sqlite"
:;   [ ! -f "$DB_PATH" ] && mkdir -p "$(dirname "$DB_PATH")" && touch "$DB_PATH" && ok "SQLite file created."
:;   (cd "$BACKEND" && php artisan migrate:fresh --seed --force) && ok "Migrations + seeders done."
:;   printf 'VITE_API_BASE_URL=http://localhost:8000/api\nVITE_WS_HOST=localhost\nVITE_WS_PORT=8080\nVITE_WS_KEY=p4wrtmbchpdt0bkcszul\nVITE_WS_CLUSTER=mt1\n' > "$FRONTEND/.env" && ok "Frontend .env written."
:;   printf "\n${GRN}${BOLD}  Setup complete! Select [2] to run the app.${R}\n"
:; }
:;
:; while true; do
:;   show_menu
:;   printf "  ${CYAN}Choose an option (1-6):${R} "; read -r CHOICE; printf "\n"
:;   case "$CHOICE" in
:;     1) full_setup ;;   2) run_app ;;      3) install_deps ;;
:;     4) pump_data ;;    5) reset_db ;;     6) printf "${CYAN}  Bye!${R}\n"; exit 0 ;;
:;     *) warn "Invalid — enter 1 to 6." ;;
:;   esac
:;   printf "\n  Press Enter to return to the menu..."; read -r
:; done
:; exit
:: ----------------------------------------------------------------
:: Windows batch section — bash already exited above
:: ":;" lines are treated as harmless labels by cmd.exe
:: ----------------------------------------------------------------
@echo off
setlocal EnableDelayedExpansion
set "DIR=%~dp0"
set "DIR=%DIR:~0,-1%"
set "BACKEND=%DIR%\backend"
set "FRONTEND=%DIR%\frontend"

:MENU
cls
echo.
echo   ============================================
echo       Workflow Orchestrator Manager
echo       Platform: Windows
echo   ============================================
echo.
echo   [1]  Full setup            ^| tools, deps, .env, migrate, seed
echo   [2]  Run the app           ^| API, Queue, Reverb, Frontend
echo   [3]  Install dependencies  ^| Composer + npm
echo   [4]  Pump data             ^| runs DataPumpSeeder
echo   [5]  Reset database        ^| fresh migrations + seeders
echo   [6]  Exit
echo.
set /p CHOICE="  Choose an option (1-6): "
if "%CHOICE%"=="1" goto FULL_SETUP
if "%CHOICE%"=="2" goto RUN_APP
if "%CHOICE%"=="3" goto INSTALL_DEPS
if "%CHOICE%"=="4" goto PUMP_DATA
if "%CHOICE%"=="5" goto RESET_DB
if "%CHOICE%"=="6" goto EXIT
echo   Invalid choice — enter 1 to 6.
pause & goto MENU

:FULL_SETUP
echo.
echo   ^>^> Full setup started...
echo   ^>^> Checking required tools...
echo.

:: ── PHP ──────────────────────────────────────────────────────
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo   ^>^> PHP not found. Downloading PHP 8.3...
    echo $ErrorActionPreference = 'Stop' > "%TEMP%\install_php.ps1"
    echo $zip = Join-Path $env:TEMP 'php.zip' >> "%TEMP%\install_php.ps1"
    echo $dst = 'C:\php' >> "%TEMP%\install_php.ps1"
    echo Write-Host '  Finding latest PHP 8.3 build...' >> "%TEMP%\install_php.ps1"
    echo $page = Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/' -UseBasicParsing >> "%TEMP%\install_php.ps1"
    echo $file = ($page.Links.href ^| Where-Object { $_ -match 'php-8\.3\.\d+-Win32-vs16-x64\.zip$' } ^| Select-Object -First 1) >> "%TEMP%\install_php.ps1"
    echo if (-not $file) { throw 'Could not find PHP 8.3 download link' } >> "%TEMP%\install_php.ps1"
    echo $url = 'https://windows.php.net/downloads/releases/' + $file >> "%TEMP%\install_php.ps1"
    echo Write-Host "  Downloading $file ..." >> "%TEMP%\install_php.ps1"
    echo Invoke-WebRequest -Uri $url -OutFile $zip -UseBasicParsing >> "%TEMP%\install_php.ps1"
    echo Write-Host '  Extracting to C:\php ...' >> "%TEMP%\install_php.ps1"
    echo Expand-Archive -Path $zip -DestinationPath $dst -Force >> "%TEMP%\install_php.ps1"
    echo Remove-Item $zip >> "%TEMP%\install_php.ps1"
    echo Copy-Item "$dst\php.ini-development" "$dst\php.ini" >> "%TEMP%\install_php.ps1"
    echo $ini = Get-Content "$dst\php.ini" >> "%TEMP%\install_php.ps1"
    echo $ini = $ini -replace ';extension=mbstring','extension=mbstring' >> "%TEMP%\install_php.ps1"
    echo $ini = $ini -replace ';extension=openssl','extension=openssl' >> "%TEMP%\install_php.ps1"
    echo $ini = $ini -replace ';extension=pdo_sqlite','extension=pdo_sqlite' >> "%TEMP%\install_php.ps1"
    echo $ini = $ini -replace ';extension=sqlite3','extension=sqlite3' >> "%TEMP%\install_php.ps1"
    echo $ini = $ini -replace ';extension=curl','extension=curl' >> "%TEMP%\install_php.ps1"
    echo $ini = $ini -replace ';extension=zip','extension=zip' >> "%TEMP%\install_php.ps1"
    echo $ini = $ini -replace ';extension=fileinfo','extension=fileinfo' >> "%TEMP%\install_php.ps1"
    echo $ini ^| Set-Content "$dst\php.ini" >> "%TEMP%\install_php.ps1"
    echo $syspath = [System.Environment]::GetEnvironmentVariable('PATH','Machine') >> "%TEMP%\install_php.ps1"
    echo if ($syspath -notlike '*C:\php*') { [System.Environment]::SetEnvironmentVariable('PATH', $syspath + ';C:\php', 'Machine') } >> "%TEMP%\install_php.ps1"
    echo Write-Host '  v  PHP ready.' >> "%TEMP%\install_php.ps1"
    powershell -NoProfile -ExecutionPolicy Bypass -File "%TEMP%\install_php.ps1"
    if %ERRORLEVEL% neq 0 (
        echo   x  PHP install failed. Please install manually: https://www.php.net/downloads
        pause & goto MENU
    )
    set "PATH=%PATH%;C:\php"
    echo   v  PHP installed to C:\php
) else (
    for /f "tokens=*" %%v in ('php -r "echo PHP_VERSION;"') do echo   v  PHP: %%v already installed.
)

:: ── Composer ─────────────────────────────────────────────────
where composer >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo   ^>^> Composer not found. Downloading...
    echo Invoke-WebRequest -Uri 'https://getcomposer.org/composer.phar' -OutFile 'C:\php\composer.phar' -UseBasicParsing > "%TEMP%\install_composer.ps1"
    echo Set-Content -Path 'C:\php\composer.bat' -Value '@php C:\php\composer.phar %%*' >> "%TEMP%\install_composer.ps1"
    echo Write-Host '  v  Composer ready.' >> "%TEMP%\install_composer.ps1"
    powershell -NoProfile -ExecutionPolicy Bypass -File "%TEMP%\install_composer.ps1"
    if %ERRORLEVEL% neq 0 (
        echo   x  Composer install failed.
        pause & goto MENU
    )
    set "PATH=%PATH%;C:\php"
    echo   v  Composer installed.
) else (
    echo   v  Composer: already installed.
)

:: ── Node.js ──────────────────────────────────────────────────
where node >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo   ^>^> Node.js not found. Downloading Node.js LTS...
    echo $ErrorActionPreference = 'Stop' > "%TEMP%\install_node.ps1"
    echo $msi = Join-Path $env:TEMP 'node.msi' >> "%TEMP%\install_node.ps1"
    echo Write-Host '  Finding latest Node.js LTS...' >> "%TEMP%\install_node.ps1"
    echo $index = Invoke-WebRequest -Uri 'https://nodejs.org/dist/latest-v22.x/' -UseBasicParsing >> "%TEMP%\install_node.ps1"
    echo $file = ($index.Links.href ^| Where-Object { $_ -match 'node-v.*-x64\.msi$' } ^| Select-Object -First 1) >> "%TEMP%\install_node.ps1"
    echo if (-not $file) { throw 'Could not find Node.js MSI' } >> "%TEMP%\install_node.ps1"
    echo $url = 'https://nodejs.org/dist/latest-v22.x/' + $file >> "%TEMP%\install_node.ps1"
    echo Write-Host "  Downloading $file ..." >> "%TEMP%\install_node.ps1"
    echo Invoke-WebRequest -Uri $url -OutFile $msi -UseBasicParsing >> "%TEMP%\install_node.ps1"
    echo Write-Host '  Installing (this may take a minute)...' >> "%TEMP%\install_node.ps1"
    echo Start-Process msiexec -ArgumentList '/i',$msi,'/qn','/norestart' -Wait >> "%TEMP%\install_node.ps1"
    echo Remove-Item $msi >> "%TEMP%\install_node.ps1"
    echo $p = [System.Environment]::GetEnvironmentVariable('PATH','Machine') + ';' + [System.Environment]::GetEnvironmentVariable('PATH','User') >> "%TEMP%\install_node.ps1"
    echo [System.Environment]::SetEnvironmentVariable('PATH', $p, 'Process') >> "%TEMP%\install_node.ps1"
    echo Write-Host '  v  Node.js ready.' >> "%TEMP%\install_node.ps1"
    powershell -NoProfile -ExecutionPolicy Bypass -File "%TEMP%\install_node.ps1"
    if %ERRORLEVEL% neq 0 (
        echo   x  Node.js install failed. Please install manually: https://nodejs.org
        pause & goto MENU
    )
    for /f "tokens=*" %%i in ('powershell -NoProfile -Command "[System.Environment]::GetEnvironmentVariable('PATH','Machine') + ';' + [System.Environment]::GetEnvironmentVariable('PATH','User')"') do set "PATH=%%i"
    echo   v  Node.js installed.
) else (
    for /f "tokens=*" %%v in ('node -v') do echo   v  Node.js: %%v already installed.
)

echo.
echo   ^>^> Installing project dependencies...
cd /d "%BACKEND%"
composer install --no-interaction
cd /d "%FRONTEND%"
npm install

cd /d "%BACKEND%"
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo   v  .env created.
    )
)
php artisan key:generate
php artisan jwt:secret --force

powershell -NoProfile -ExecutionPolicy Bypass -Command "$f='%BACKEND%\.env'; $c=(Get-Content $f) -replace 'DB_CONNECTION=.*','DB_CONNECTION=sqlite' -replace 'DB_DATABASE=.*','DB_DATABASE=database/database.sqlite' -replace 'QUEUE_CONNECTION=.*','QUEUE_CONNECTION=database' -replace 'BROADCAST_CONNECTION=.*','BROADCAST_CONNECTION=reverb' -replace 'MAIL_MAILER=.*','MAIL_MAILER=log'; $c|Set-Content $f; if((Get-Content $f -Raw) -notmatch 'REVERB_APP_ID'){Add-Content $f \"`nREVERB_APP_ID=808080`nREVERB_APP_KEY=p4wrtmbchpdt0bkcszul`nREVERB_APP_SECRET=your-reverb-secret`nREVERB_HOST=localhost`nREVERB_PORT=8080`nREVERB_SCHEME=http`nVITE_REVERB_APP_KEY=p4wrtmbchpdt0bkcszul`nVITE_REVERB_HOST=localhost`nVITE_REVERB_PORT=8080`nVITE_REVERB_SCHEME=http\"}"
echo   v  .env configured.

if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
    echo   v  SQLite file created.
)
php artisan migrate:fresh --seed --force

cd /d "%FRONTEND%"
(echo VITE_API_BASE_URL=http://localhost:8000/api & echo VITE_WS_HOST=localhost & echo VITE_WS_PORT=8080 & echo VITE_WS_KEY=p4wrtmbchpdt0bkcszul & echo VITE_WS_CLUSTER=mt1) > .env
echo   v  Frontend .env written.

cd /d "%DIR%"
echo.
echo   Setup complete! Select [2] to run the app.
pause & goto MENU

:RUN_APP
echo.
echo   ^>^> Launching all services...
where wt >nul 2>&1
if %ERRORLEVEL%==0 (
    start wt -d "%BACKEND%" cmd /k "title API && php -S localhost:8000 -t public" ^
        ; new-tab -d "%BACKEND%"  cmd /k "title Queue && php artisan queue:work" ^
        ; new-tab -d "%BACKEND%"  cmd /k "title Reverb && php artisan reverb:start" ^
        ; new-tab -d "%FRONTEND%" cmd /k "title Frontend && npm run dev -- --open"
    echo   v  Opened Windows Terminal with 4 tabs.
) else (
    start cmd /k "title API      && cd /d "%BACKEND%"  && php -S localhost:8000 -t public"
    start cmd /k "title Queue    && cd /d "%BACKEND%"  && php artisan queue:work"
    start cmd /k "title Reverb   && cd /d "%BACKEND%"  && php artisan reverb:start"
    start cmd /k "title Frontend && cd /d "%FRONTEND%" && npm run dev -- --open"
    echo   v  Opened 4 cmd windows.
)
pause & goto MENU

:INSTALL_DEPS
echo.
echo   ^>^> Installing Composer dependencies...
cd /d "%BACKEND%"
composer install --no-interaction
echo   ^>^> Installing npm dependencies...
cd /d "%FRONTEND%"
npm install
cd /d "%DIR%"
pause & goto MENU

:PUMP_DATA
echo.
echo   ^>^> Running DataPumpSeeder...
cd /d "%BACKEND%"
php artisan db:seed --class=DataPumpSeeder
cd /d "%DIR%"
pause & goto MENU

:RESET_DB
echo.
echo   ^>^> Fresh migrations + seeders...
cd /d "%BACKEND%"
php artisan migrate:fresh --seed --force
cd /d "%DIR%"
pause & goto MENU

:EXIT
echo   Bye!
exit /b 0
