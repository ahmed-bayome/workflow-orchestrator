@echo off
setlocal
cd /d "%~dp0..\backend"
echo Resetting and seeding database...
php artisan migrate:fresh --seed
echo Database reset successfully.
pause
