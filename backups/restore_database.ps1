# MapIt Database Restore Script
# Run this script in PowerShell from your mapit project directory

param(
    [Parameter(Mandatory=$false)]
    [string]$BackupFile = "backups\mapit_full_backup_2025-06-01_20-10-47.sql",
    
    [Parameter(Mandatory=$false)]
    [switch]$Force = $false
)

Write-Host "🔄 MapIt Database Restore Script" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Green

# Check if Docker is running
Write-Host "📋 Checking Docker status..." -ForegroundColor Yellow
try {
    docker version | Out-Null
    Write-Host "✅ Docker is running" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
    exit 1
}

# Check if backup file exists
if (!(Test-Path $BackupFile)) {
    Write-Host "❌ Backup file not found: $BackupFile" -ForegroundColor Red
    Write-Host "Available backup files:" -ForegroundColor Yellow
    Get-ChildItem backups\*.sql | Select-Object Name | Format-Table -HideTableHeaders
    exit 1
}

Write-Host "📁 Using backup file: $BackupFile" -ForegroundColor Cyan
$BackupSize = (Get-Item $BackupFile).Length
Write-Host "📏 Backup file size: $([math]::Round($BackupSize/1KB, 2)) KB" -ForegroundColor Cyan

# Start Docker containers
Write-Host "🚀 Starting Docker containers..." -ForegroundColor Yellow
docker-compose up -d

# Wait for MySQL to be ready
Write-Host "⏳ Waiting for MySQL to be ready..." -ForegroundColor Yellow
$retries = 0
$maxRetries = 30
do {
    $retries++
    Start-Sleep -Seconds 2
    $mysqlReady = docker-compose exec mysql mysql -u root -proot_password -e "SELECT 1;" 2>$null
    if ($mysqlReady) {
        Write-Host "✅ MySQL is ready" -ForegroundColor Green
        break
    }
    Write-Host "⏳ Waiting... (attempt $retries/$maxRetries)" -ForegroundColor Yellow
} while ($retries -lt $maxRetries)

if ($retries -ge $maxRetries) {
    Write-Host "❌ MySQL failed to start within timeout period" -ForegroundColor Red
    exit 1
}

# Check if database exists and warn user
$dbExists = docker-compose exec mysql mysql -u root -proot_password -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'mapit';" 2>$null
if ($dbExists -and !$Force) {
    Write-Host "⚠️  Database 'mapit' already exists!" -ForegroundColor Yellow
    $response = Read-Host "Do you want to continue? This will overwrite existing data. (y/N)"
    if ($response -ne "y" -and $response -ne "Y") {
        Write-Host "❌ Restore cancelled by user" -ForegroundColor Red
        exit 0
    }
}

# Perform the restore
Write-Host "📦 Restoring database from backup..." -ForegroundColor Yellow
try {
    Get-Content $BackupFile | docker-compose exec -T mysql mysql -u root -proot_password
    Write-Host "✅ Database restore completed successfully!" -ForegroundColor Green
} catch {
    Write-Host "❌ Database restore failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Verify the restore
Write-Host "🔍 Verifying restore..." -ForegroundColor Yellow

# Check databases
$databases = docker-compose exec mysql mysql -u root -proot_password -e "SHOW DATABASES;" 2>$null
if ($databases -match "mapit") {
    Write-Host "✅ Database 'mapit' exists" -ForegroundColor Green
} else {
    Write-Host "❌ Database 'mapit' not found after restore" -ForegroundColor Red
    exit 1
}

# Check tables
$tables = docker-compose exec mysql mysql -u root -proot_password -e "USE mapit; SHOW TABLES;" 2>$null
$tableCount = ($tables -split "`n" | Where-Object { $_ -match "^\|" -and $_ -notmatch "Tables_in_mapit" }).Count
Write-Host "✅ Found $tableCount tables in database" -ForegroundColor Green

# Check for admin user
$adminUser = docker-compose exec mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) as count FROM users WHERE role='admin';" 2>$null
if ($adminUser -match "1") {
    Write-Host "✅ Admin user found" -ForegroundColor Green
} else {
    Write-Host "⚠️  No admin user found - you may need to create one" -ForegroundColor Yellow
}

# Check for contact data
$contactCount = docker-compose exec mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) as count FROM contacts;" 2>$null | Select-String -Pattern '\d+'
if ($contactCount) {
    $count = ($contactCount -split "`n" | Where-Object { $_ -match '^\d+$' })[0]
    Write-Host "✅ Found $count contacts in database" -ForegroundColor Green
}

Write-Host ""
Write-Host "🎉 Database restore completed successfully!" -ForegroundColor Green
Write-Host "🌐 You can now access:" -ForegroundColor Cyan
Write-Host "   • Main App: http://localhost" -ForegroundColor White
Write-Host "   • Admin Panel: http://localhost/admin" -ForegroundColor White
Write-Host "   • Admin Login: username=admin, password=admin123" -ForegroundColor White
Write-Host ""
Write-Host "📝 Run 'docker-compose logs' to check for any issues" -ForegroundColor Yellow
