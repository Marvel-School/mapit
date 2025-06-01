@echo off
echo =======================================
echo MapIt Database Restore - Quick Start
echo =======================================
echo.

cd /d "%~dp0\.."

echo Current directory: %CD%
echo.

echo Starting PowerShell restore script...
powershell.exe -ExecutionPolicy Bypass -File "backups\restore_database.ps1"

echo.
echo =======================================
echo Restore process completed!
echo =======================================
echo.
echo Press any key to exit...
pause >nul
