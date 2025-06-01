@echo off
echo ========================================
echo   MapIt - Laptop Setup (Enhanced)
echo ========================================
echo.
echo This will rebuild your MapIt Docker containers
echo and restore the database backup.
echo.
echo Make sure Docker Desktop is running before continuing!
echo.
pause

echo.
echo Starting enhanced setup script...
echo.

powershell.exe -ExecutionPolicy Bypass -File "laptop_setup_enhanced.ps1"

echo.
echo Setup script completed!
echo.
pause
