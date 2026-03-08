@echo off
setlocal
:menu
cls
echo ==========================================
echo    Dynamic Workflow Orchestrator Manager
echo ==========================================
echo 1. Setup (First time install)
echo 2. Run Application
echo 3. Reset Database
echo 4. Pump Test Data (Populate UI)
echo 5. Exit
echo ==========================================
set /p choice="Enter your choice (1-5): "

if "%choice%"=="1" goto setup
if "%choice%"=="2" goto run
if "%choice%"=="3" goto reset
if "%choice%"=="4" goto pump
if "%choice%"=="5" goto exit
goto menu

:setup
call "%~dp0scripts\setup.bat"
pause
goto menu

:run
call "%~dp0scripts\run.bat"
goto exit

:reset
call "%~dp0scripts\reset-db.bat"
goto menu

:pump
call "%~dp0scripts\pump-data.bat"
goto menu

:exit
exit
