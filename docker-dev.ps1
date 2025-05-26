# MapIt Docker Development Script
# This script provides common Docker operations for local development

param(
    [Parameter(Position=0)]
    [string]$Action = "help"
)

function Show-Help {
    Write-Host "MapIt Docker Development Script" -ForegroundColor Green
    Write-Host "===============================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Usage: .\docker-dev.ps1 [action]" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Actions:" -ForegroundColor Cyan
    Write-Host "  up       - Start all services" -ForegroundColor White
    Write-Host "  down     - Stop all services" -ForegroundColor White
    Write-Host "  restart  - Restart all services" -ForegroundColor White
    Write-Host "  build    - Build all images" -ForegroundColor White
    Write-Host "  rebuild  - Force rebuild all images" -ForegroundColor White
    Write-Host "  logs     - Show logs for all services" -ForegroundColor White
    Write-Host "  status   - Show status of all services" -ForegroundColor White
    Write-Host "  shell    - Access PHP container shell" -ForegroundColor White
    Write-Host "  mysql    - Access MySQL shell" -ForegroundColor White
    Write-Host "  install  - Install Composer dependencies" -ForegroundColor White
    Write-Host "  seed     - Run database seeder" -ForegroundColor White
    Write-Host "  test     - Run PHPUnit tests" -ForegroundColor White
    Write-Host "  clean    - Clean up Docker resources" -ForegroundColor White
    Write-Host "  help     - Show this help message" -ForegroundColor White
}

function Start-Services {
    Write-Host "Starting MapIt services..." -ForegroundColor Green
    docker-compose up -d
    Write-Host "Services started! Access the application at http://localhost" -ForegroundColor Green
}

function Stop-Services {
    Write-Host "Stopping MapIt services..." -ForegroundColor Yellow
    docker-compose down
    Write-Host "Services stopped." -ForegroundColor Green
}

function Restart-Services {
    Write-Host "Restarting MapIt services..." -ForegroundColor Yellow
    docker-compose restart
    Write-Host "Services restarted." -ForegroundColor Green
}

function Build-Images {
    Write-Host "Building MapIt images..." -ForegroundColor Green
    docker-compose build
    Write-Host "Images built successfully." -ForegroundColor Green
}

function Rebuild-Images {
    Write-Host "Force rebuilding MapIt images..." -ForegroundColor Green
    docker-compose build --no-cache
    Write-Host "Images rebuilt successfully." -ForegroundColor Green
}

function Show-Logs {
    Write-Host "Showing logs for all services..." -ForegroundColor Green
    docker-compose logs -f
}

function Show-Status {
    Write-Host "MapIt Services Status:" -ForegroundColor Green
    docker-compose ps
}

function Access-Shell {
    Write-Host "Accessing PHP container shell..." -ForegroundColor Green
    docker-compose exec php bash
}

function Access-MySQL {
    Write-Host "Accessing MySQL shell..." -ForegroundColor Green
    Write-Host "Default credentials: user=mapit_user, password=mapit_password, database=mapit" -ForegroundColor Yellow
    docker-compose exec mysql mysql -u mapit_user -pmapit_password mapit
}

function Install-Dependencies {
    Write-Host "Installing Composer dependencies..." -ForegroundColor Green
    docker-compose exec php composer install
    Write-Host "Dependencies installed." -ForegroundColor Green
}

function Run-Seeder {
    Write-Host "Running database seeder..." -ForegroundColor Green
    docker-compose exec php php scripts/DatabaseSeeder.php
    Write-Host "Database seeded." -ForegroundColor Green
}

function Run-Tests {
    Write-Host "Running PHPUnit tests..." -ForegroundColor Green
    docker-compose exec php vendor/bin/phpunit
}

function Clean-Resources {
    Write-Host "Cleaning up Docker resources..." -ForegroundColor Yellow
    docker-compose down -v --remove-orphans
    docker system prune -f
    Write-Host "Cleanup completed." -ForegroundColor Green
}

# Main script logic
switch ($Action.ToLower()) {
    "up" { Start-Services }
    "down" { Stop-Services }
    "restart" { Restart-Services }
    "build" { Build-Images }
    "rebuild" { Rebuild-Images }
    "logs" { Show-Logs }
    "status" { Show-Status }
    "shell" { Access-Shell }
    "mysql" { Access-MySQL }
    "install" { Install-Dependencies }
    "seed" { Run-Seeder }
    "test" { Run-Tests }
    "clean" { Clean-Resources }
    "help" { Show-Help }
    default { 
        Write-Host "Unknown action: $Action" -ForegroundColor Red
        Show-Help
    }
}
