@echo off
setlocal

:: Start everything in a new Windows Terminal window with tabs
:: 'start' makes it run asynchronously so this script can continue and exit
start wt ^
  -d "%~dp0backend" cmd /k "title Orchestrator: API && php -S localhost:8000 -t public" ; ^
  new-tab -d "%~dp0backend" cmd /k "title Orchestrator: Queue && php artisan queue:work" ; ^
  new-tab -d "%~dp0backend" cmd /k "title Orchestrator: Reverb && php artisan reverb:start" ; ^
  new-tab -d "%~dp0frontend" cmd /k "title Orchestrator: Frontend && npm run dev"

:: Exit this script immediately to close its window
exit

