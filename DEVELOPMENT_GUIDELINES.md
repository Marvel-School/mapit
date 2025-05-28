# MapIt Development Guidelines

## File Management Rules

### ❌ NEVER CREATE THESE FILES IN PRODUCTION
- `test-*.php` - Temporary test files
- `debug-*.php` - Debug scripts
- `verify-*.php` - Verification scripts  
- `*verification*.php` - Any verification files
- `test-*.html` - HTML test files
- `auto-login.php` - Auto-login helpers
- `check-*.php` - Database check scripts
- `setup-test-data.php` - Test data setup
- `cleanup_*.ps1` - Cleanup scripts

### ✅ PROPER DEVELOPMENT PRACTICES

#### For Testing New Features:
1. **Use a separate branch**: `git checkout -b feature/your-feature-name`
2. **Create test files in a dedicated folder**: `tests/` or `dev-tools/`
3. **Use proper naming**: `tests/integration/map-marker-test.php`

#### For Debugging:
1. **Use proper logging**: Add to `storage/logs/app.log`
2. **Use development environment variables**: Set `APP_DEBUG=true` in `.env.local`
3. **Use browser dev tools**: Console, Network tab, etc.

#### For Database Testing:
1. **Use migrations**: Create proper migration files
2. **Use seeders**: For test data in development
3. **Use separate test database**: Configure in `.env.testing`

### 🔧 DEVELOPMENT WORKFLOW

#### Before Starting Work:
```bash
# Create feature branch
git checkout -b feature/your-feature

# Ensure you're in development mode
cp .env.local .env
```

#### During Development:
- Test in browser developer tools
- Use `console.log()` for JavaScript debugging
- Use `error_log()` for PHP debugging (logs to `storage/logs/`)
- Never create files directly in `public/` for testing

#### Before Committing:
```bash
# Check for test files
git status | grep -E "(test-|debug-|verify-)"

# If any exist, remove them:
rm test-*.php debug-*.php verify-*.php

# Then commit clean code
git add .
git commit -m "Your feature description"
```

### 🚨 RED FLAGS - Stop and Clean If You See:
- Multiple `test-*.php` files in git status
- Files with `debug-` prefix being committed
- HTML files in `public/` that aren't part of the main app
- Any `auto-login.php` or similar helper files

### 🧹 EMERGENCY CLEANUP
If test files reappear, run this PowerShell command from project root:
```powershell
Get-ChildItem -Recurse -Include "test-*.php","debug-*.php","verify-*.php","*verification*.php","test-*.html","auto-login.php","check-*.php","setup-*.php","cleanup_*.ps1" | Remove-Item -Force
```

### 📁 PROPER PROJECT STRUCTURE
```
c:\Projects\mapit\
├── app/           # Core application
├── public/        # Web-accessible files ONLY
├── tests/         # Proper test files (if needed)
├── dev-tools/     # Development utilities (gitignored)
├── storage/       # Logs, cache, uploads
└── vendor/        # Composer dependencies
```

## Remember
- **Public folder is sacred** - Only production files belong there
- **Use branches** for experimental work
- **Clean up before committing** - Always check git status
- **Document changes** in commit messages, not temporary files

---
*This document was created to prevent the reappearance of test files in the MapIt project.*
