@echo off
setlocal

:: Check if Windows Terminal (wt.exe) is available
where wt >nul 2>nul
if %ERRORLEVEL% equ 0 (
    echo Using Windows Terminal...
    start wt ^
      -d "%~dp0..\backend" cmd /k "title Orchestrator: API && php -S localhost:8000 -t public" ; ^
      new-tab -d "%~dp0..\backend" cmd /k "title Orchestrator: Queue && php artisan queue:work" ; ^
      new-tab -d "%~dp0..\backend" cmd /k "title Orchestrator: Reverb && php artisan reverb:start" ; ^
      new-tab -d "%~dp0..\frontend" cmd /k "title Orchestrator: Frontend && npm run dev -- --open"
) else (
    echo Windows Terminal not found. Falling back to individual CMD windows...
    start "Orchestrator: API" /d "%~dp0..\backend" cmd /k "php -S localhost:8000 -t public"
    start "Orchestrator: Queue" /d "%~dp0..\backend" cmd /k "php artisan queue:work"
    start "Orchestrator: Reverb" /d "%~dp0..\backend" cmd /k "php artisan reverb:start"
    start "Orchestrator: Frontend" /d "%~dp0..\frontend" cmd /k "npm run dev -- --open"
)

:: Exit this script immediately to close its window
exit
