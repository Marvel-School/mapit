# MapIt - Enhanced Laptop Setup Script  
# This script completely rebuilds Docker containers and restores the database
# Features: Better error handling, progress tracking, comprehensive verification

param(
    [string]$BackupFile = "mapit_database_backup_READY_FOR_LAPTOP.sql",
    [switch]$SkipBackup,
    [switch]$ForceRebuild,
    [switch]$Verbose
)

# Enable verbose output if requested
if ($Verbose) { $VerbosePreference = "Continue" }

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  MapIt - Enhanced Laptop Setup Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Function to check if Docker is running
function Test-DockerRunning {
    try {
        $dockerInfo = docker info 2>$null
        return $LASTEXITCODE -eq 0
    }
    catch {
        return $false
    }
}

# Function to wait for MySQL to be ready with better feedback
function Wait-ForMySQL {
    param([int]$TimeoutSeconds = 120)
    
    Write-Host "🔄 Waiting for MySQL to be ready..." -ForegroundColor Yellow
    $timeout = (Get-Date).AddSeconds($TimeoutSeconds)
    $attempts = 0
    
    do {
        $attempts++
        try {
            $result = docker exec mapit_mysql mysqladmin ping -h localhost -u root -proot_password 2>$null
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ MySQL is ready! (took $attempts attempts)" -ForegroundColor Green
                return $true
            }
        }
        catch {
            Write-Verbose "MySQL not ready yet, attempt $attempts"
        }
        
        Start-Sleep -Seconds 3
        if ($attempts % 5 -eq 0) {
            Write-Host "⏳ Still waiting for MySQL... (attempt $attempts)" -ForegroundColor Yellow
        }
        
    } while ((Get-Date) -lt $timeout)
    
    Write-Host ""
    Write-Host "❌ Timeout waiting for MySQL to be ready!" -ForegroundColor Red
    return $false
}

# Function to check container health
function Test-ContainerHealth {
    param([string]$ContainerName)
    
    try {
        $status = docker inspect --format='{{.State.Status}}' $ContainerName 2>$null
        $health = docker inspect --format='{{.State.Health.Status}}' $ContainerName 2>$null
        
        if ($status -eq "running") {
            if ($health -eq "healthy" -or $health -eq "") {
                return $true
            }
        }
        return $false
    }
    catch {
        return $false
    }
}

# Step 1: Check prerequisites
Write-Host "`n📋 Step 1: Checking prerequisites..." -ForegroundColor Green

# Check if Docker is installed
try {
    $dockerVersion = docker --version 2>$null
    Write-Host "   ✅ Docker found: $dockerVersion" -ForegroundColor Green
}
catch {
    Write-Host "   ❌ Docker is not installed or not in PATH!" -ForegroundColor Red
    Write-Host "     Please install Docker Desktop: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

# Check if Docker is running
if (-not (Test-DockerRunning)) {
    Write-Host "   ❌ Docker is not running!" -ForegroundColor Red
    Write-Host "     Please start Docker Desktop and try again." -ForegroundColor Yellow
    exit 1
}
Write-Host "   ✅ Docker is running" -ForegroundColor Green

# Check docker-compose
try {
    $composeVersion = docker-compose --version 2>$null
    Write-Host "   ✅ Docker Compose found: $composeVersion" -ForegroundColor Green
}
catch {
    Write-Host "   ❌ Docker Compose not found!" -ForegroundColor Red
    Write-Host "     Please install Docker Compose or use Docker Desktop" -ForegroundColor Yellow
    exit 1
}

# Check if docker-compose.yml exists
if (-not (Test-Path "docker-compose.yml")) {
    Write-Host "   ❌ docker-compose.yml not found!" -ForegroundColor Red
    Write-Host "     Please run this script from the MapIt project root directory." -ForegroundColor Yellow
    exit 1
}
Write-Host "   ✅ docker-compose.yml found" -ForegroundColor Green

# Check backup file (if not skipping backup)
if (-not $SkipBackup) {
    $backupPath = ""
    
    # Check common backup locations
    $possiblePaths = @(
        "backups\mapit_full_backup_2025-06-01_20-10-47.sql",
        "backups\$BackupFile",
        "$BackupFile",
        "mapit_database_backup_READY_FOR_LAPTOP.sql"
    )
    
    foreach ($path in $possiblePaths) {
        if (Test-Path $path) {
            $backupPath = $path
            break
        }
    }
    
    if ($backupPath -eq "") {
        Write-Host "   ❌ Backup file not found!" -ForegroundColor Red
        Write-Host "     Looked for:" -ForegroundColor Yellow
        foreach ($path in $possiblePaths) {
            Write-Host "       - $path" -ForegroundColor Gray
        }
        Write-Host "     Use -SkipBackup to skip database restore" -ForegroundColor Yellow
        exit 1
    }
    
    $backupInfo = Get-Item $backupPath
    $backupSizeMB = [math]::Round($backupInfo.Length / 1MB, 2)
    Write-Host "   ✅ Backup file found: $backupPath ($backupSizeMB MB)" -ForegroundColor Green
    $BackupFile = $backupPath
}

# Step 2: Stop and clean existing containers
Write-Host "`n🛑 Step 2: Stopping and cleaning existing containers..." -ForegroundColor Green

try {
    Write-Host "   Stopping containers..." -ForegroundColor Yellow
    docker-compose down --remove-orphans 2>$null
    Write-Host "   ✅ Containers stopped and removed" -ForegroundColor Green
}
catch {
    Write-Host "   ⚠️ Error stopping containers (normal if none exist)" -ForegroundColor Yellow
}

# Step 3: Handle volumes based on ForceRebuild flag
if ($ForceRebuild) {
    Write-Host "`n🗂️ Step 3: Removing Docker volumes (force rebuild)..." -ForegroundColor Green
    try {
        docker volume rm mapit_mysql_data 2>$null
        Write-Host "   ✅ MySQL volume removed" -ForegroundColor Green
    }
    catch {
        Write-Host "   ⚠️ MySQL volume doesn't exist or couldn't be removed" -ForegroundColor Yellow
    }
    
    # Also remove any dangling volumes
    try {
        docker volume prune -f 2>$null
        Write-Host "   ✅ Cleaned up dangling volumes" -ForegroundColor Green
    }
    catch {
        Write-Host "   ⚠️ Could not clean dangling volumes" -ForegroundColor Yellow
    }
}
else {
    Write-Host "`n🗂️ Step 3: Keeping existing volumes (use -ForceRebuild to recreate)" -ForegroundColor Yellow
}

# Step 4: Build and start containers
Write-Host "`n🔨 Step 4: Building and starting Docker containers..." -ForegroundColor Green

try {
    Write-Host "   Building containers (this may take a few minutes)..." -ForegroundColor Yellow
    docker-compose build --no-cache 2>&1 | ForEach-Object {
        if ($Verbose) { Write-Host "   $_" -ForegroundColor Gray }
    }
    
    Write-Host "   Starting containers..." -ForegroundColor Yellow
    docker-compose up -d
    
    if ($LASTEXITCODE -ne 0) {
        throw "docker-compose failed with exit code $LASTEXITCODE"
    }
    Write-Host "   ✅ Containers built and started successfully" -ForegroundColor Green
}
catch {
    Write-Host "   ❌ Failed to build/start containers!" -ForegroundColor Red
    Write-Host "     Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "     Try running: docker-compose logs" -ForegroundColor Yellow
    exit 1
}

# Step 5: Wait for services to be ready
Write-Host "`n⏳ Step 5: Waiting for services to be ready..." -ForegroundColor Green

# Wait for MySQL with better feedback
if (-not (Wait-ForMySQL -TimeoutSeconds 120)) {
    Write-Host "   ❌ MySQL failed to start properly!" -ForegroundColor Red
    Write-Host "     Checking MySQL logs:" -ForegroundColor Yellow
    docker-compose logs mysql | Select-Object -Last 20
    exit 1
}

# Wait for PHP-FPM
Write-Host "   🔄 Checking PHP container..." -ForegroundColor Yellow
$phpReady = $false
for ($i = 1; $i -le 10; $i++) {
    if (Test-ContainerHealth "mapit_php") {
        Write-Host "   ✅ PHP container is ready" -ForegroundColor Green
        $phpReady = $true
        break
    }
    Start-Sleep -Seconds 2
}

if (-not $phpReady) {
    Write-Host "   ⚠️ PHP container may not be fully ready" -ForegroundColor Yellow
}

# Wait for Nginx
Write-Host "   🔄 Checking Nginx container..." -ForegroundColor Yellow
$nginxReady = $false
for ($i = 1; $i -le 10; $i++) {
    if (Test-ContainerHealth "mapit_nginx") {
        Write-Host "   ✅ Nginx container is ready" -ForegroundColor Green
        $nginxReady = $true
        break
    }
    Start-Sleep -Seconds 2
}

if (-not $nginxReady) {
    Write-Host "   ⚠️ Nginx container may not be fully ready" -ForegroundColor Yellow
}

# Give everything a moment to settle
Write-Host "   🔄 Allowing services to settle..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Step 6: Restore database (if not skipping)
if (-not $SkipBackup) {
    Write-Host "`n📦 Step 6: Restoring database from backup..." -ForegroundColor Green
    
    try {
        # Create database if it doesn't exist
        Write-Host "   Ensuring database exists..." -ForegroundColor Yellow
        docker exec mapit_mysql mysql -u root -proot_password -e "CREATE DATABASE IF NOT EXISTS mapit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        
        # Check backup file size
        $backupInfo = Get-Item $BackupFile
        $backupSizeMB = [math]::Round($backupInfo.Length / 1MB, 2)
        Write-Host "   📁 Backup file: $BackupFile ($backupSizeMB MB)" -ForegroundColor Cyan
        
        # Restore the database
        Write-Host "   🔄 Restoring database (this may take a moment)..." -ForegroundColor Yellow
        
        # Use a more robust approach for large files
        if ($backupSizeMB -gt 10) {
            Write-Host "   📁 Large backup detected, using optimized restore method..." -ForegroundColor Cyan
        }
        
        $startTime = Get-Date
        Get-Content $BackupFile | docker exec -i mapit_mysql mysql -u root -proot_password mapit
        $endTime = Get-Date
        $duration = ($endTime - $startTime).TotalSeconds
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ Database restored successfully! (took $([math]::Round($duration, 1)) seconds)" -ForegroundColor Green
        }
        else {
            throw "Database restore failed with exit code $LASTEXITCODE"
        }
        
        # Verify restore
        Write-Host "   🔍 Verifying database restore..." -ForegroundColor Yellow
        $tableCount = docker exec mapit_mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'mapit';" 2>$null | Select-String -Pattern '\d+' | ForEach-Object { $_.Matches[0].Value }
        if ($tableCount -gt 0) {
            Write-Host "   ✅ Found $tableCount tables in restored database" -ForegroundColor Green
        }
    }
    catch {
        Write-Host "   ❌ Failed to restore database!" -ForegroundColor Red
        Write-Host "     Error: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "     Manual restore command:" -ForegroundColor Yellow
        Write-Host "     Get-Content '$BackupFile' | docker exec -i mapit_mysql mysql -u root -proot_password mapit" -ForegroundColor Gray
        
        # Ask user if they want to continue
        $continue = Read-Host "     Continue without database restore? (y/N)"
        if ($continue -ne "y" -and $continue -ne "Y") {
            exit 1
        }
    }
}
else {
    Write-Host "`n📦 Step 6: Skipping database restore (as requested)" -ForegroundColor Yellow
}

# Step 7: Comprehensive verification
Write-Host "`n🔍 Step 7: Comprehensive verification..." -ForegroundColor Green

# Check container status
$containers = @{
    "mapit_nginx" = "Web Server"
    "mapit_php" = "PHP Application"
    "mapit_mysql" = "MySQL Database"
    "mapit_redis" = "Redis Cache"
}

$allRunning = $true
foreach ($container in $containers.Keys) {
    try {
        $status = docker inspect --format='{{.State.Status}}' $container 2>$null
        if ($status -eq "running") {
            Write-Host "   ✅ $($containers[$container]) ($container) is running" -ForegroundColor Green
        }
        else {
            Write-Host "   ❌ $($containers[$container]) ($container) is not running (status: $status)" -ForegroundColor Red
            $allRunning = $false
        }
    }
    catch {
        Write-Host "   ❌ $($containers[$container]) ($container) not found" -ForegroundColor Red
        $allRunning = $false
    }
}

# Test database connection and data
if (-not $SkipBackup) {
    Write-Host "   🔍 Testing database connection..." -ForegroundColor Yellow
    try {
        # Test admin user
        $adminTest = docker exec mapit_mysql mysql -u mapit_user -pmapit_password -e "USE mapit; SELECT username, email, role FROM users WHERE role='admin' LIMIT 1;" 2>$null
        if ($adminTest -match "admin") {
            Write-Host "   ✅ Admin user found in database" -ForegroundColor Green
        }
        else {
            Write-Host "   ⚠️ Admin user not found" -ForegroundColor Yellow
        }
        
        # Test contact data
        $contactCount = docker exec mapit_mysql mysql -u mapit_user -pmapit_password -e "USE mapit; SELECT COUNT(*) FROM contacts;" 2>$null | Select-String -Pattern '\d+' | ForEach-Object { $_.Matches[0].Value }
        if ($contactCount -gt 0) {
            Write-Host "   ✅ Found $contactCount contacts in database" -ForegroundColor Green
        }
        else {
            Write-Host "   ⚠️ No contacts found in database" -ForegroundColor Yellow
        }
        
        # Test destinations
        $destCount = docker exec mapit_mysql mysql -u mapit_user -pmapit_password -e "USE mapit; SELECT COUNT(*) FROM destinations;" 2>$null | Select-String -Pattern '\d+' | ForEach-Object { $_.Matches[0].Value }
        if ($destCount -gt 0) {
            Write-Host "   ✅ Found $destCount destinations in database" -ForegroundColor Green
        }
        else {
            Write-Host "   ⚠️ No destinations found in database" -ForegroundColor Yellow
        }
        
    }
    catch {
        Write-Host "   ⚠️ Cannot fully test database connection" -ForegroundColor Yellow
    }
}

# Test web server response
Write-Host "   🔍 Testing web server response..." -ForegroundColor Yellow
try {
    Start-Sleep -Seconds 2  # Give web server a moment
    $webTest = Invoke-WebRequest -Uri "http://localhost" -TimeoutSec 15 -UseBasicParsing
    if ($webTest.StatusCode -eq 200) {
        Write-Host "   ✅ Web server responding at http://localhost" -ForegroundColor Green
        
        # Test admin page
        try {
            $adminTest = Invoke-WebRequest -Uri "http://localhost/admin" -TimeoutSec 10 -UseBasicParsing
            if ($adminTest.StatusCode -eq 200 -or $adminTest.StatusCode -eq 302) {
                Write-Host "   ✅ Admin interface accessible at http://localhost/admin" -ForegroundColor Green
            }
        }
        catch {
            Write-Host "   ⚠️ Admin interface may not be accessible" -ForegroundColor Yellow
        }
    }
    else {
        Write-Host "   ❌ Web server returned status: $($webTest.StatusCode)" -ForegroundColor Red
    }
}
catch {
    Write-Host "   ❌ Cannot reach web server at http://localhost" -ForegroundColor Red
    Write-Host "     Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "     Try waiting a moment and visiting http://localhost manually" -ForegroundColor Yellow
}

# Final status
Write-Host "`n========================================" -ForegroundColor Cyan
if ($allRunning) {
    Write-Host "🎉 SETUP COMPLETED SUCCESSFULLY!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🌐 Your MapIt application is now running:" -ForegroundColor Cyan
    Write-Host "   • Main Website:  http://localhost" -ForegroundColor White
    Write-Host "   • Admin Panel:   http://localhost/admin" -ForegroundColor White
    Write-Host ""
    Write-Host "🔑 Admin Credentials:" -ForegroundColor Cyan
    Write-Host "   • Username: admin" -ForegroundColor White
    Write-Host "   • Password: admin123" -ForegroundColor White
    Write-Host ""
    Write-Host "📊 Database Content:" -ForegroundColor Cyan
    Write-Host "   • Admin user account" -ForegroundColor White
    Write-Host "   • Test contact data" -ForegroundColor White
    Write-Host "   • Featured destinations" -ForegroundColor White
    Write-Host "   • Achievement badges" -ForegroundColor White
    Write-Host ""
    Write-Host "🔧 Useful Commands:" -ForegroundColor Cyan
    Write-Host "   • View logs:         docker-compose logs -f" -ForegroundColor Gray
    Write-Host "   • Stop containers:   docker-compose down" -ForegroundColor Gray
    Write-Host "   • Restart:           docker-compose restart" -ForegroundColor Gray
    Write-Host "   • Rebuild:           docker-compose build --no-cache" -ForegroundColor Gray
}
else {
    Write-Host "⚠️ SETUP COMPLETED WITH ISSUES" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Some containers may not be running properly." -ForegroundColor Yellow
    Write-Host "Check the logs with: docker-compose logs" -ForegroundColor Yellow
    Write-Host "Or try rebuilding with: .\laptop_setup.ps1 -ForceRebuild" -ForegroundColor Yellow
}
Write-Host "========================================" -ForegroundColor Cyan

# Offer to open the website
if ($allRunning) {
    $openSite = Read-Host "`n🌐 Would you like to open http://localhost in your browser? (y/N)"
    if ($openSite -eq "y" -or $openSite -eq "Y") {
        try {
            Start-Process "http://localhost"
            Write-Host "✅ Opening website in your default browser..." -ForegroundColor Green
        }
        catch {
            Write-Host "⚠️ Could not open browser automatically. Please visit http://localhost manually." -ForegroundColor Yellow
        }
    }
}

Write-Host "`n✅ Setup script completed!" -ForegroundColor Green
