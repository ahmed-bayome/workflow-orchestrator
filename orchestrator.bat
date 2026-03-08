@echo off
setlocal
:menu
cls
echo ==========================================
echo    Dynamic Workflow Orchestrator Manager
echo ==========================================
echo 1. Run Application
echo 2. Reset Database
echo 3. Pump Test Data (Populate UI)
echo 4. Exit
echo ==========================================
set /p choice="Enter your choice (1-4): "

if "%choice%"=="1" goto run
if "%choice%"=="2" goto reset
if "%choice%"=="3" goto pump
if "%choice%"=="4" goto exit
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
