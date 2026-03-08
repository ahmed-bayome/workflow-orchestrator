@echo off
setlocal
cd /d "%~dp0..\backend"
echo Pumping realistic test data (users and requests)...
php artisan db:seed --class=DataPumpSeeder
echo Data pump complete.
pause
