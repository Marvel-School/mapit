@echo off
cd /d "%~dp0"

echo ==========================================
echo  MapIt Laptop Setup - Quick Start
echo ==========================================
echo.
echo This script will:
echo  1. Rebuild Docker containers
echo  2. Restore the database backup
echo  3. Start the application
echo.
echo Make sure Docker Desktop is running first!
echo.
pause

echo.
echo Starting PowerShell setup script...
echo.

powershell.exe -ExecutionPolicy Bypass -File "laptop_setup.ps1"

echo.
echo ==========================================
echo Setup process completed!
echo ==========================================
echo.
echo Your MapIt application should now be running at:
echo.
echo   Main App:    http://localhost
echo   Admin Panel: http://localhost/admin
echo.
echo Default admin login:
echo   Username: admin
echo   Password: admin123
echo.
echo Press any key to exit...
pause >nul
