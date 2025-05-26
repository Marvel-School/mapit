@echo off
REM MapIt Deployment Script for Windows
REM This script automates the deployment process on Windows

setlocal enabledelayedexpansion

set APP_NAME=mapit
set DOCKER_COMPOSE_FILE=docker-compose.yml
set BACKUP_DIR=./backups
set LOG_FILE=./deployment.log

REM Function to log messages
:log
echo [%date% %time%] %~1
echo [%date% %time%] %~1 >> %LOG_FILE%
goto :eof

:error
echo [%date% %time%] ERROR: %~1
echo [%date% %time%] ERROR: %~1 >> %LOG_FILE%
goto :eof

REM Check prerequisites
:check_prerequisites
call :log "Checking prerequisites..."

docker --version >nul 2>&1
if errorlevel 1 (
    call :error "Docker is not installed or not in PATH"
    exit /b 1
)

docker-compose --version >nul 2>&1
if errorlevel 1 (
    call :error "Docker Compose is not installed or not in PATH"
    exit /b 1
)

call :log "Prerequisites check passed"
goto :eof

REM Create backup
:create_backup
if "%~1"=="skip-backup" (
    call :log "Skipping backup as requested"
    goto :eof
)

call :log "Creating backup..."

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Generate timestamp for backup name
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
set "BACKUP_NAME=mapit_backup_%YYYY%%MM%%DD%_%HH%%Min%%Sec%"

REM Backup database
if exist "./database/mapit.db" (
    copy "./database/mapit.db" "%BACKUP_DIR%\%BACKUP_NAME%_database.db" >nul
    call :log "Database backup created: %BACKUP_DIR%\%BACKUP_NAME%_database.db"
)

REM Backup uploads (using 7zip if available)
if exist "./storage/uploads" (
    if exist "C:\Program Files\7-Zip\7z.exe" (
        "C:\Program Files\7-Zip\7z.exe" a "%BACKUP_DIR%\%BACKUP_NAME%_uploads.zip" "./storage/uploads/" >nul
        call :log "Uploads backup created: %BACKUP_DIR%\%BACKUP_NAME%_uploads.zip"
    ) else (
        call :log "7-Zip not found, skipping uploads backup"
    )
)

call :log "Backup completed"
goto :eof

REM Deploy application
:deploy
call :log "Starting deployment..."

call :log "Building Docker images..."
docker-compose -f %DOCKER_COMPOSE_FILE% build
if errorlevel 1 (
    call :error "Failed to build Docker images"
    exit /b 1
)

call :log "Starting containers..."
docker-compose -f %DOCKER_COMPOSE_FILE% up -d
if errorlevel 1 (
    call :error "Failed to start containers"
    exit /b 1
)

call :log "Waiting for services to be ready..."
timeout /t 10 /nobreak >nul

REM Check if services are running
docker-compose -f %DOCKER_COMPOSE_FILE% ps | findstr "Up" >nul
if errorlevel 1 (
    call :error "Deployment failed - some services are not running"
    docker-compose -f %DOCKER_COMPOSE_FILE% logs
    exit /b 1
) else (
    call :log "Deployment successful!"
    call :log "Application is available at: http://localhost:8080"
)
goto :eof

REM Run tests
:run_tests
call :log "Running tests..."

REM Check if app container is running
docker-compose -f %DOCKER_COMPOSE_FILE% ps app | findstr "Up" >nul
if errorlevel 1 (
    call :error "App container is not running. Cannot run tests."
    goto :eof
)

REM Run PHPUnit tests inside the container
docker-compose -f %DOCKER_COMPOSE_FILE% exec app php vendor/bin/phpunit --configuration phpunit.xml
if errorlevel 1 (
    call :error "Some tests failed"
) else (
    call :log "All tests passed!"
)
goto :eof

REM Show status
:show_status
call :log "Application Status:"
docker-compose -f %DOCKER_COMPOSE_FILE% ps

echo.
call :log "Container logs (last 20 lines):"
docker-compose -f %DOCKER_COMPOSE_FILE% logs --tail=20
goto :eof

REM Stop application
:stop_app
call :log "Stopping application..."
docker-compose -f %DOCKER_COMPOSE_FILE% down
call :log "Application stopped"
goto :eof

REM Cleanup
:cleanup
call :log "Cleaning up..."

REM Remove unused Docker images
docker image prune -f

call :log "Cleanup completed"
goto :eof

REM Show help
:show_help
echo MapIt Deployment Script for Windows
echo.
echo Usage: %~nx0 [COMMAND] [OPTIONS]
echo.
echo Commands:
echo   deploy              Deploy the application
echo   test                Run tests
echo   status              Show application status
echo   stop                Stop the application
echo   restart             Restart the application
echo   cleanup             Clean up old files and Docker images
echo   backup              Create a backup
echo   help                Show this help message
echo.
echo Options:
echo   skip-backup         Skip backup creation during deployment
echo   no-tests            Skip running tests during deployment
echo.
echo Examples:
echo   %~nx0 deploy                    # Full deployment with backup and tests
echo   %~nx0 deploy skip-backup        # Deploy without creating backup
echo   %~nx0 deploy no-tests           # Deploy without running tests
echo   %~nx0 restart                   # Restart the application
goto :eof

REM Main script logic
if "%~1"=="" goto show_help
if "%~1"=="help" goto show_help

if "%~1"=="deploy" (
    call :check_prerequisites
    if errorlevel 1 exit /b 1
    call :create_backup %~2
    call :deploy
    if "%~2" NEQ "no-tests" if "%~3" NEQ "no-tests" call :run_tests
) else if "%~1"=="test" (
    call :run_tests
) else if "%~1"=="status" (
    call :show_status
) else if "%~1"=="stop" (
    call :stop_app
) else if "%~1"=="restart" (
    call :stop_app
    timeout /t 2 /nobreak >nul
    call :deploy
) else if "%~1"=="cleanup" (
    call :cleanup
) else if "%~1"=="backup" (
    call :create_backup
) else (
    call :error "Unknown command: %~1"
    call :show_help
    exit /b 1
)
