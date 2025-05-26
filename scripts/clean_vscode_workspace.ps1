# Clean VS Code Cache and Restore Files
# Run this script when VS Code keeps restoring deleted files

# Stop any running VS Code processes
Write-Host "Stopping any running VS Code processes..." -ForegroundColor Yellow
Stop-Process -Name "Code" -Force -ErrorAction SilentlyContinue

# Wait a moment to ensure processes are terminated
Start-Sleep -Seconds 2

# Clear VS Code workspace storage for this project
$storageDir = "$env:APPDATA\Code\User\workspaceStorage"
$projectName = "mapit"

Write-Host "Searching for '$projectName' workspace storage..." -ForegroundColor Yellow

$found = $false
Get-ChildItem -Path $storageDir -Directory | ForEach-Object {
    $storage = $_
    Get-ChildItem -Path $storage.FullName -Filter "workspace.json" -Recurse | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        if ($content -match $projectName) {
            Write-Host "Found matching workspace: $($storage.FullName)" -ForegroundColor Green
            Remove-Item -Path $storage.FullName -Recurse -Force
            Write-Host "Deleted workspace storage" -ForegroundColor Green
            $found = $true
        }
    }
}

if (-not $found) {
    Write-Host "No workspace storage found for $projectName" -ForegroundColor Cyan
}

# Clear VS Code history
Write-Host "Cleaning VS Code history and cache..." -ForegroundColor Yellow
Remove-Item -Path "$env:APPDATA\Code\User\History" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$env:APPDATA\Code\Cache" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$env:APPDATA\Code\CachedData" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$env:APPDATA\Code\User\workspaceStorage\backup*" -Recurse -Force -ErrorAction SilentlyContinue

# List and optionally clean up any unwanted files in your project
$currentDir = Get-Location
$unwantedFiles = @(
    "test_*.php",
    "*_fix.php",
    "*_fix_verification.php",
    "fix_verification.php",
    "*_test.php",
    "*.tmp",
    "*.bak"
)

Write-Host "Searching for unwanted files to delete..." -ForegroundColor Yellow
$filesToDelete = @()
foreach ($pattern in $unwantedFiles) {
    $matches = Get-ChildItem -Path $currentDir -Filter $pattern -Recurse -File
    if ($matches) {
        foreach ($file in $matches) {
            $filesToDelete += $file.FullName
            Write-Host "Found: $($file.FullName)" -ForegroundColor Cyan
        }
    }
}

if ($filesToDelete.Count -gt 0) {
    $confirmation = Read-Host "Do you want to delete these files? (y/n)"
    if ($confirmation -eq 'y') {
        foreach ($file in $filesToDelete) {
            Remove-Item -Path $file -Force
            Write-Host "Deleted: $file" -ForegroundColor Red
        }
    }
}

Write-Host "Workspace cleanup complete!" -ForegroundColor Green
Write-Host "Please restart VS Code for changes to take effect." -ForegroundColor Yellow
