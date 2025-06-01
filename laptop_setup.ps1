# MapIt Laptop Setup Script
# This script will rebuild Docker containers and restore the database backup
# Run this script from your MapIt project directory on your laptop

param(
    [Parameter(Mandatory=$false)]
    [switch]$Force = $false,
    
    [Parameter(Mandatory=$false)]
    [switch]$SkipBuild = $false,
    
    [Parameter(Mandatory=$false)]
    [string]$BackupFile = "backups\mapit_full_backup_2025-06-01_20-10-47.sql"
)

Write-Host "🚀 MapIt Laptop Setup Script" -ForegroundColor Green
Write-Host "============================" -ForegroundColor Green
Write-Host ""

# Function to check if command exists
function Test-Command($cmdname) {
    return [bool](Get-Command -Name $cmdname -ErrorAction SilentlyContinue)
}

# Check prerequisites
Write-Host "📋 Checking prerequisites..." -ForegroundColor Yellow

# Check Docker
if (!(Test-Command "docker")) {
    Write-Host "❌ Docker is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Docker Desktop first: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

# Check Docker Compose
if (!(Test-Command "docker-compose")) {
    Write-Host "❌ Docker Compose is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Docker Compose or use Docker Desktop" -ForegroundColor Yellow
    exit 1
}

# Check if Docker is running
try {
    docker version | Out-Null
    Write-Host "✅ Docker is running" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
    exit 1
}

# Check if we're in the right directory
if (!(Test-Path "docker-compose.yml")) {
    Write-Host "❌ docker-compose.yml not found in current directory" -ForegroundColor Red
    Write-Host "Please run this script from your MapIt project root directory" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ All prerequisites met" -ForegroundColor Green
Write-Host ""

# Check for backup file
if (!(Test-Path $BackupFile)) {
    Write-Host "❌ Backup file not found: $BackupFile" -ForegroundColor Red
    Write-Host "Available backup files:" -ForegroundColor Yellow
    if (Test-Path "backups") {
        Get-ChildItem backups\*.sql | Select-Object Name, @{Name="Size(KB)";Expression={[math]::Round($_.Length/1KB, 2)}} | Format-Table -AutoSize
    } else {
        Write-Host "No backups folder found. Please ensure you have the backup files." -ForegroundColor Red
    }
    exit 1
}

Write-Host "📁 Using backup file: $BackupFile" -ForegroundColor Cyan
$BackupSize = (Get-Item $BackupFile).Length
Write-Host "📏 Backup file size: $([math]::Round($BackupSize/1KB, 2)) KB" -ForegroundColor Cyan
Write-Host ""

# Stop any existing containers
Write-Host "🛑 Stopping existing containers..." -ForegroundColor Yellow
docker-compose down 2>$null
Write-Host "✅ Existing containers stopped" -ForegroundColor Green
Write-Host ""

# Build or rebuild containers
if (!$SkipBuild) {
    Write-Host "🔨 Building Docker containers..." -ForegroundColor Yellow
    Write-Host "This may take several minutes on first run..." -ForegroundColor Cyan
    
    try {
        docker-compose build --no-cache
        Write-Host "✅ Docker containers built successfully" -ForegroundColor Green
    } catch {
        Write-Host "❌ Failed to build Docker containers" -ForegroundColor Red
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "⏭️  Skipping container build (--SkipBuild flag used)" -ForegroundColor Yellow
}

Write-Host ""

# Start containers
Write-Host "🚀 Starting containers..." -ForegroundColor Yellow
try {
    docker-compose up -d
    Write-Host "✅ Containers started successfully" -ForegroundColor Green
} catch {
    Write-Host "❌ Failed to start containers" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Wait for MySQL to be ready
Write-Host "⏳ Waiting for MySQL to be ready..." -ForegroundColor Yellow
$retries = 0
$maxRetries = 60
$mysqlReady = $false

do {
    $retries++
    Start-Sleep -Seconds 2
    
    try {
        $result = docker-compose exec -T mysql mysql -u root -proot_password -e "SELECT 1;" 2>$null
        if ($result) {
            $mysqlReady = $true
            Write-Host "✅ MySQL is ready" -ForegroundColor Green
            break
        }
    } catch {
        # Continue waiting
    }
    
    if ($retries % 10 -eq 0) {
        Write-Host "⏳ Still waiting for MySQL... (attempt $retries/$maxRetries)" -ForegroundColor Yellow
    }
} while ($retries -lt $maxRetries)

if (!$mysqlReady) {
    Write-Host "❌ MySQL failed to start within timeout period" -ForegroundColor Red
    Write-Host "Checking container logs..." -ForegroundColor Yellow
    docker-compose logs mysql
    exit 1
}

Write-Host ""

# Check if database exists and handle accordingly
Write-Host "🔍 Checking existing database..." -ForegroundColor Yellow
$dbExists = $false
try {
    $result = docker-compose exec -T mysql mysql -u root -proot_password -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'mapit';" 2>$null
    if ($result -match "mapit") {
        $dbExists = $true
    }
} catch {
    # Database doesn't exist or other error
}

if ($dbExists -and !$Force) {
    Write-Host "⚠️  Database 'mapit' already exists!" -ForegroundColor Yellow
    $response = Read-Host "Do you want to overwrite it? This will delete all existing data. (y/N)"
    if ($response -ne "y" -and $response -ne "Y") {
        Write-Host "❌ Setup cancelled by user" -ForegroundColor Red
        exit 0
    }
}

# Restore database
Write-Host "📦 Restoring database from backup..." -ForegroundColor Yellow
try {
    Get-Content $BackupFile | docker-compose exec -T mysql mysql -u root -proot_password
    Write-Host "✅ Database restored successfully!" -ForegroundColor Green
} catch {
    Write-Host "❌ Database restore failed" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Verify the restoration
Write-Host "🔍 Verifying database restoration..." -ForegroundColor Yellow

# Check database exists
$databases = docker-compose exec -T mysql mysql -u root -proot_password -e "SHOW DATABASES;" 2>$null
if ($databases -match "mapit") {
    Write-Host "✅ Database 'mapit' exists" -ForegroundColor Green
} else {
    Write-Host "❌ Database 'mapit' not found after restore" -ForegroundColor Red
    exit 1
}

# Check tables
try {
    $tables = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SHOW TABLES;" 2>$null
    $tableCount = ($tables -split "`n" | Where-Object { $_ -match "^\|" -and $_ -notmatch "Tables_in_mapit" }).Count
    Write-Host "✅ Found $tableCount tables in database" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Could not verify table count" -ForegroundColor Yellow
}

# Check for admin user
try {
    $adminCheck = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SELECT username, email, role FROM users WHERE role='admin' LIMIT 1;" 2>$null
    if ($adminCheck -match "admin") {
        Write-Host "✅ Admin user found" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Admin user not found" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Could not verify admin user" -ForegroundColor Yellow
}

# Check for contact data
try {
    $contactCheck = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) as count FROM contacts;" 2>$null | Select-String -Pattern '\d+'
    if ($contactCheck) {
        $count = ($contactCheck -split "`n" | Where-Object { $_ -match '^\d+$' })[0]
        Write-Host "✅ Found $count contacts in database" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️  Could not verify contact data" -ForegroundColor Yellow
}

Write-Host ""

# Final status
Write-Host "🎉 MapIt setup completed successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Your application is now available at:" -ForegroundColor Cyan
Write-Host "   • Main Application: http://localhost" -ForegroundColor White
Write-Host "   • Admin Panel:      http://localhost/admin" -ForegroundColor White
Write-Host ""
Write-Host "🔑 Default Admin Credentials:" -ForegroundColor Cyan
Write-Host "   • Username: admin" -ForegroundColor White
Write-Host "   • Password: admin123" -ForegroundColor White
Write-Host ""
Write-Host "📊 What's included:" -ForegroundColor Cyan
Write-Host "   • Complete user system with admin account" -ForegroundColor White
Write-Host "   • Contact management system (5 test contacts)" -ForegroundColor White
Write-Host "   • 16 featured destinations" -ForegroundColor White
Write-Host "   • Complete badge/achievement system (29 badges)" -ForegroundColor White
Write-Host "   • Fixed admin interface with proper styling" -ForegroundColor White
Write-Host "   • Secure CSRF protection on all forms" -ForegroundColor White
Write-Host ""
Write-Host "🔧 Useful commands:" -ForegroundColor Cyan
Write-Host "   • View logs:           docker-compose logs" -ForegroundColor White
Write-Host "   • Stop containers:     docker-compose down" -ForegroundColor White
Write-Host "   • Start containers:    docker-compose up -d" -ForegroundColor White
Write-Host "   • Rebuild containers:  docker-compose build --no-cache" -ForegroundColor White
Write-Host ""
Write-Host "✅ Setup complete! Happy coding! 🚀" -ForegroundColor Green
