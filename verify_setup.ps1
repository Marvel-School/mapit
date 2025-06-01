# MapIt Setup Verification Script
# Run this after setup to verify everything is working

Write-Host "🔍 MapIt Setup Verification" -ForegroundColor Green
Write-Host "===========================" -ForegroundColor Green
Write-Host ""

# Check if Docker containers are running
Write-Host "📋 Checking Docker containers..." -ForegroundColor Yellow
try {
    $containers = docker-compose ps --services --filter "status=running"
    $runningContainers = $containers | Where-Object { $_ -ne "" }
    
    if ($runningContainers.Count -ge 3) {
        Write-Host "✅ Docker containers are running" -ForegroundColor Green
        Write-Host "   Running services: $($runningContainers -join ', ')" -ForegroundColor Cyan
    } else {
        Write-Host "⚠️  Some containers may not be running" -ForegroundColor Yellow
        docker-compose ps
    }
} catch {
    Write-Host "❌ Failed to check Docker containers" -ForegroundColor Red
    Write-Host "Make sure you're in the MapIt project directory" -ForegroundColor Yellow
}

Write-Host ""

# Check database connection
Write-Host "🗄️  Checking database connection..." -ForegroundColor Yellow
try {
    $dbCheck = docker-compose exec -T mysql mysql -u root -proot_password -e "SELECT 1 as test;" 2>$null
    if ($dbCheck -match "test") {
        Write-Host "✅ Database connection successful" -ForegroundColor Green
    } else {
        Write-Host "❌ Database connection failed" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Database connection failed" -ForegroundColor Red
}

# Check if mapit database exists
Write-Host "📊 Checking MapIt database..." -ForegroundColor Yellow
try {
    $dbExists = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SELECT 1;" 2>$null
    if ($dbExists) {
        Write-Host "✅ MapIt database exists" -ForegroundColor Green
        
        # Check table count
        $tables = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SHOW TABLES;" 2>$null
        $tableCount = ($tables -split "`n" | Where-Object { $_ -match "^\|" -and $_ -notmatch "Tables_in_mapit" }).Count
        Write-Host "   📋 Found $tableCount tables" -ForegroundColor Cyan
        
        # Check admin user
        $adminCheck = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SELECT username FROM users WHERE role='admin' LIMIT 1;" 2>$null
        if ($adminCheck -match "admin") {
            Write-Host "   👤 Admin user exists" -ForegroundColor Cyan
        } else {
            Write-Host "   ⚠️  Admin user not found" -ForegroundColor Yellow
        }
        
        # Check contacts
        $contactCount = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) FROM contacts;" 2>$null | Select-String -Pattern '\d+'
        if ($contactCount) {
            $count = ($contactCount.Line -split '\|')[1].Trim()
            Write-Host "   📧 Found $count contacts" -ForegroundColor Cyan
        }
        
        # Check destinations
        $destCount = docker-compose exec -T mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) FROM destinations;" 2>$null | Select-String -Pattern '\d+'
        if ($destCount) {
            $count = ($destCount.Line -split '\|')[1].Trim()
            Write-Host "   🗺️  Found $count destinations" -ForegroundColor Cyan
        }
        
    } else {
        Write-Host "❌ MapIt database not found" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Failed to check MapIt database" -ForegroundColor Red
}

Write-Host ""

# Check web access
Write-Host "🌐 Checking web access..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost" -Method HEAD -TimeoutSec 5 -ErrorAction Stop
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Main application accessible at http://localhost" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Main application returned status: $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Main application not accessible at http://localhost" -ForegroundColor Red
    Write-Host "   Check if nginx container is running" -ForegroundColor Yellow
}

# Check admin access
try {
    $adminResponse = Invoke-WebRequest -Uri "http://localhost/admin" -Method HEAD -TimeoutSec 5 -ErrorAction Stop
    if ($adminResponse.StatusCode -eq 200 -or $adminResponse.StatusCode -eq 302) {
        Write-Host "✅ Admin panel accessible at http://localhost/admin" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Admin panel returned status: $($adminResponse.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Admin panel not accessible at http://localhost/admin" -ForegroundColor Red
}

Write-Host ""

# Summary
Write-Host "📋 Verification Summary" -ForegroundColor Green
Write-Host "======================" -ForegroundColor Green
Write-Host ""
Write-Host "If all checks passed ✅, your MapIt installation is ready!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Access your application:" -ForegroundColor Cyan
Write-Host "   • Main App:    http://localhost" -ForegroundColor White
Write-Host "   • Admin Panel: http://localhost/admin" -ForegroundColor White
Write-Host "   • Login Page:  http://localhost/login" -ForegroundColor White
Write-Host ""
Write-Host "🔑 Default Admin Credentials:" -ForegroundColor Cyan
Write-Host "   • Username: admin" -ForegroundColor White
Write-Host "   • Password: admin123" -ForegroundColor White
Write-Host ""

if ($PSBoundParameters.ContainsKey('Pause') -or $args -contains '-Pause') {
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}
