# MapIt Development Cleanup Script
# Run this if test files accidentally get created

param(
    [switch]$Force,
    [switch]$Preview
)

Write-Host "🧹 MapIt Development Cleanup Tool" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan

# Define patterns of files that should be cleaned up
$patterns = @(
    "test-*.php",
    "debug-*.php", 
    "verify-*.php",
    "*verification*.php",
    "test-*.html",
    "auto-login.php",
    "check-*.php",
    "setup-*.php",
    "comprehensive-*.php",
    "docker-auto-login.php",
    "clear-*.php",
    "*_COMPLETE.md",
    "cleanup_*.ps1"
)

$filesToRemove = @()

# Scan for files to remove
foreach ($pattern in $patterns) {
    $files = Get-ChildItem -Path . -Filter $pattern -Recurse -File -ErrorAction SilentlyContinue
    $filesToRemove += $files
}

if ($filesToRemove.Count -eq 0) {
    Write-Host "✅ No test files found. Project is clean!" -ForegroundColor Green
    exit 0
}

Write-Host "Found $($filesToRemove.Count) files to remove:" -ForegroundColor Yellow
foreach ($file in $filesToRemove) {
    $relativePath = $file.FullName -replace [regex]::Escape($PWD.Path + "\"), ""
    Write-Host "  📄 $relativePath" -ForegroundColor Red
}

if ($Preview) {
    Write-Host "`n👁️  Preview mode - no files were removed." -ForegroundColor Blue
    Write-Host "Run without -Preview to actually remove these files." -ForegroundColor Blue
    exit 0
}

if (-not $Force) {
    $confirmation = Read-Host "`nDo you want to remove these files? (y/N)"
    if ($confirmation -ne 'y' -and $confirmation -ne 'Y') {
        Write-Host "❌ Cleanup cancelled." -ForegroundColor Yellow
        exit 0
    }
}

# Remove the files
$removedCount = 0
foreach ($file in $filesToRemove) {
    try {
        Remove-Item $file.FullName -Force
        $removedCount++
        Write-Host "🗑️  Removed: $($file.Name)" -ForegroundColor Red
    }
    catch {
        Write-Host "❌ Failed to remove: $($file.Name) - $($_.Exception.Message)" -ForegroundColor DarkRed
    }
}

Write-Host "`n✅ Cleanup complete! Removed $removedCount files." -ForegroundColor Green

# Check git status for any staged test files
if (Get-Command git -ErrorAction SilentlyContinue) {
    $stagedTestFiles = git diff --cached --name-only | Where-Object { 
        $_ -match "(test-|debug-|verify-|verification)" 
    }
    
    if ($stagedTestFiles) {
        Write-Host "`n⚠️  Warning: Staged test files detected in git:" -ForegroundColor Yellow
        $stagedTestFiles | ForEach-Object { Write-Host "   📄 $_" -ForegroundColor Yellow }
        Write-Host "Run: git reset HEAD <filename> to unstage them" -ForegroundColor Yellow
    }
}

Write-Host "`n💡 Tip: Add test files to .gitignore to prevent this in the future!" -ForegroundColor Blue
