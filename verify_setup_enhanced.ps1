# MapIt - Setup Verification Script
# This script verifies that your MapIt installation is working correctly

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  MapIt - Setup Verification Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$allGood = $true
$issues = @()

# Function to add issue
function Add-Issue {
    param([string]$Issue)
    $script:allGood = $false
    $script:issues += $Issue
}

Write-Host "`n🔍 Verifying MapIt installation..." -ForegroundColor Green

# Check 1: Docker containers
Write-Host "`n1. Checking Docker containers..." -ForegroundColor Yellow

$expectedContainers = @{
    "mapit_nginx" = "Web Server"
    "mapit_php" = "PHP Application" 
    "mapit_mysql" = "MySQL Database"
    "mapit_redis" = "Redis Cache"
}

foreach ($container in $expectedContainers.Keys) {
    try {
        $status = docker inspect --format='{{.State.Status}}' $container 2>$null
        if ($status -eq "running") {
            Write-Host "   ✅ $($expectedContainers[$container]) is running" -ForegroundColor Green
        }
        else {
            Write-Host "   ❌ $($expectedContainers[$container]) is not running" -ForegroundColor Red
            Add-Issue "$($expectedContainers[$container]) container is not running"
        }
    }
    catch {
        Write-Host "   ❌ $($expectedContainers[$container]) not found" -ForegroundColor Red
        Add-Issue "$($expectedContainers[$container]) container not found"
    }
}

# Check 2: Database connectivity and data
Write-Host "`n2. Checking database..." -ForegroundColor Yellow

try {
    # Test basic connection
    $dbTest = docker exec mapit_mysql mysql -u root -proot_password -e "SELECT 1;" 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✅ Database connection successful" -ForegroundColor Green
        
        # Check if mapit database exists
        $dbExists = docker exec mapit_mysql mysql -u root -proot_password -e "SHOW DATABASES LIKE 'mapit';" 2>$null
        if ($dbExists -match "mapit") {
            Write-Host "   ✅ MapIt database exists" -ForegroundColor Green
            
            # Check tables
            $tables = docker exec mapit_mysql mysql -u root -proot_password -e "USE mapit; SHOW TABLES;" 2>$null
            $tableCount = ($tables -split "`n" | Where-Object { $_ -match "^\|" -and $_ -notmatch "Tables_in_mapit" }).Count
            if ($tableCount -gt 0) {
                Write-Host "   ✅ Found $tableCount database tables" -ForegroundColor Green
            }
            else {
                Write-Host "   ❌ No tables found in database" -ForegroundColor Red
                Add-Issue "Database has no tables"
            }
            
            # Check for admin user
            $adminUser = docker exec mapit_mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) FROM users WHERE role='admin';" 2>$null | Select-String -Pattern '\d+' | ForEach-Object { $_.Matches[0].Value }
            if ($adminUser -gt 0) {
                Write-Host "   ✅ Admin user exists" -ForegroundColor Green
            }
            else {
                Write-Host "   ❌ No admin user found" -ForegroundColor Red
                Add-Issue "No admin user found in database"
            }
            
            # Check for contact data
            $contactCount = docker exec mapit_mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) FROM contacts;" 2>$null | Select-String -Pattern '\d+' | ForEach-Object { $_.Matches[0].Value }
            if ($contactCount -gt 0) {
                Write-Host "   ✅ Found $contactCount contacts" -ForegroundColor Green
            }
            else {
                Write-Host "   ⚠️ No contacts found (this may be normal)" -ForegroundColor Yellow
            }
            
            # Check for destinations
            $destCount = docker exec mapit_mysql mysql -u root -proot_password -e "USE mapit; SELECT COUNT(*) FROM destinations;" 2>$null | Select-String -Pattern '\d+' | ForEach-Object { $_.Matches[0].Value }
            if ($destCount -gt 0) {
                Write-Host "   ✅ Found $destCount destinations" -ForegroundColor Green
            }
            else {
                Write-Host "   ⚠️ No destinations found" -ForegroundColor Yellow
            }
            
        }
        else {
            Write-Host "   ❌ MapIt database does not exist" -ForegroundColor Red
            Add-Issue "MapIt database does not exist"
        }
    }
    else {
        Write-Host "   ❌ Cannot connect to database" -ForegroundColor Red
        Add-Issue "Cannot connect to MySQL database"
    }
}
catch {
    Write-Host "   ❌ Database check failed: $($_.Exception.Message)" -ForegroundColor Red
    Add-Issue "Database check failed"
}

# Check 3: Web server response
Write-Host "`n3. Checking web server..." -ForegroundColor Yellow

try {
    $webTest = Invoke-WebRequest -Uri "http://localhost" -TimeoutSec 10 -UseBasicParsing
    if ($webTest.StatusCode -eq 200) {
        Write-Host "   ✅ Main website responding (Status: $($webTest.StatusCode))" -ForegroundColor Green
        
        # Check if it contains expected content
        if ($webTest.Content -match "MapIt|mapit" -or $webTest.Content.Length -gt 1000) {
            Write-Host "   ✅ Website content looks good" -ForegroundColor Green
        }
        else {
            Write-Host "   ⚠️ Website content may be incomplete" -ForegroundColor Yellow
        }
    }
    else {
        Write-Host "   ❌ Website returned status: $($webTest.StatusCode)" -ForegroundColor Red
        Add-Issue "Website returned unexpected status code"
    }
}
catch {
    Write-Host "   ❌ Cannot reach website: $($_.Exception.Message)" -ForegroundColor Red
    Add-Issue "Cannot reach website at http://localhost"
}

# Check 4: Admin interface
Write-Host "`n4. Checking admin interface..." -ForegroundColor Yellow

try {
    $adminTest = Invoke-WebRequest -Uri "http://localhost/admin" -TimeoutSec 10 -UseBasicParsing
    if ($adminTest.StatusCode -eq 200 -or $adminTest.StatusCode -eq 302) {
        Write-Host "   ✅ Admin interface responding (Status: $($adminTest.StatusCode))" -ForegroundColor Green
    }
    else {
        Write-Host "   ❌ Admin interface returned status: $($adminTest.StatusCode)" -ForegroundColor Red
        Add-Issue "Admin interface not responding properly"
    }
}
catch {
    Write-Host "   ❌ Cannot reach admin interface: $($_.Exception.Message)" -ForegroundColor Red
    Add-Issue "Cannot reach admin interface"
}

# Check 5: File permissions and structure
Write-Host "`n5. Checking file structure..." -ForegroundColor Yellow

$criticalFiles = @(
    "app\Controllers\Admin\ContactController.php",
    "app\Views\admin\contacts\index.php",
    "app\Views\admin\contacts\show.php",
    "public\css\admin.css",
    "docker-compose.yml"
)

foreach ($file in $criticalFiles) {
    if (Test-Path $file) {
        Write-Host "   ✅ $file exists" -ForegroundColor Green
    }
    else {
        Write-Host "   ❌ $file missing" -ForegroundColor Red
        Add-Issue "Critical file missing: $file"
    }
}

# Check 6: Docker logs for errors
Write-Host "`n6. Checking for errors in logs..." -ForegroundColor Yellow

try {
    $recentLogs = docker-compose logs --tail=50 2>$null
    $errorCount = ($recentLogs | Select-String -Pattern "error|ERROR|fatal|FATAL|exception|Exception").Count
    
    if ($errorCount -eq 0) {
        Write-Host "   ✅ No recent errors found in logs" -ForegroundColor Green
    }
    elseif ($errorCount -lt 5) {
        Write-Host "   ⚠️ Found $errorCount minor errors in logs" -ForegroundColor Yellow
    }
    else {
        Write-Host "   ❌ Found $errorCount errors in logs" -ForegroundColor Red
        Add-Issue "Multiple errors found in container logs"
    }
}
catch {
    Write-Host "   ⚠️ Could not check container logs" -ForegroundColor Yellow
}

# Final assessment
Write-Host "`n========================================" -ForegroundColor Cyan

if ($allGood) {
    Write-Host "🎉 VERIFICATION PASSED!" -ForegroundColor Green
    Write-Host ""
    Write-Host "✅ Your MapIt installation is working correctly!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🌐 Access your application:" -ForegroundColor Cyan
    Write-Host "   • Main site: http://localhost" -ForegroundColor White
    Write-Host "   • Admin:     http://localhost/admin" -ForegroundColor White
    Write-Host ""
    Write-Host "🔑 Admin credentials:" -ForegroundColor Cyan
    Write-Host "   • Username: admin" -ForegroundColor White
    Write-Host "   • Password: admin123" -ForegroundColor White
}
else {
    Write-Host "⚠️ VERIFICATION FOUND ISSUES" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Issues found:" -ForegroundColor Red
    foreach ($issue in $issues) {
        Write-Host "   • $issue" -ForegroundColor Red
    }
    Write-Host ""
    Write-Host "💡 Troubleshooting suggestions:" -ForegroundColor Cyan
    Write-Host "   • Try rebuilding: .\laptop_setup_enhanced.ps1 -ForceRebuild" -ForegroundColor Gray
    Write-Host "   • Check logs: docker-compose logs" -ForegroundColor Gray
    Write-Host "   • Restart containers: docker-compose restart" -ForegroundColor Gray
    Write-Host "   • Ensure Docker Desktop is running" -ForegroundColor Gray
}

Write-Host "========================================" -ForegroundColor Cyan

# Offer to show logs if there are issues
if (-not $allGood) {
    $showLogs = Read-Host "`n🔍 Would you like to see recent container logs? (y/N)"
    if ($showLogs -eq "y" -or $showLogs -eq "Y") {
        Write-Host "`nRecent container logs:" -ForegroundColor Yellow
        docker-compose logs --tail=20
    }
}

Write-Host "`n✅ Verification completed!" -ForegroundColor Green
