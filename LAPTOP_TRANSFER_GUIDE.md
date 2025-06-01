# 💻 MapIt Laptop Transfer Guide

## 🚀 Quick Setup on Your Laptop

### 📦 What to Transfer

You need to copy your **entire MapIt project folder** to your laptop. This includes:

**Essential Files:**
- ✅ All application code (`app/`, `config/`, `public/`, etc.)
- ✅ Docker configuration (`docker-compose.yml`, `Dockerfile`, `docker/`)
- ✅ Database backup files (`backups/` folder)
- ✅ Setup scripts (`laptop_setup.ps1`, `SETUP_LAPTOP.bat`)

**Transfer Methods:**

#### Option 1: Copy Entire Project Folder (Recommended)
```
📁 C:\Projects\mapit\  
   ├── 📄 laptop_setup.ps1          ← NEW: Main setup script
   ├── 📄 SETUP_LAPTOP.bat          ← NEW: Double-click setup
   ├── 📁 backups\                  ← Database backups
   ├── 📁 app\                      ← Application code
   ├── 📁 config\                   ← Configuration
   ├── 📁 docker\                   ← Docker configs
   ├── 📁 public\                   ← Web assets
   ├── 📄 docker-compose.yml        ← Docker setup
   └── ... (all other files)
```

#### Option 2: Use the Backup ZIP
- Transfer `MapIt_Database_Backup_2025-06-01.zip` 
- Extract it to get backup files only
- Then copy the full project separately

### 🛠️ Setup Steps on Your Laptop

#### Method 1: Double-Click Setup (Easiest)
1. **Copy project folder** to your laptop (e.g., `C:\Projects\mapit\`)
2. **Start Docker Desktop** and wait for it to be ready
3. **Double-click** `SETUP_LAPTOP.bat`
4. **Follow prompts** and wait for completion
5. **Visit** http://localhost when done

#### Method 2: PowerShell (Advanced)
1. Copy project folder to laptop
2. Open PowerShell as Administrator
3. Navigate to project: `cd C:\Path\To\Your\mapit`
4. Run setup: `.\laptop_setup.ps1`
5. Visit http://localhost when done

#### Method 3: Manual (Full Control)
```powershell
# 1. Copy project to laptop
# 2. Start Docker Desktop
# 3. Open PowerShell in project directory

# Build and start containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Wait for MySQL, then restore database
Get-Content "backups\mapit_full_backup_2025-06-01_20-10-47.sql" | docker-compose exec -T mysql mysql -u root -proot_password

# Verify
docker-compose logs
```

### 📋 Prerequisites on Laptop

1. **Docker Desktop** installed and running
   - Download: https://www.docker.com/products/docker-desktop
   - Make sure it's running before setup

2. **PowerShell** (already on Windows)
   - No additional installation needed

3. **Available Ports**: 80, 443, 3306, 6379
   - Make sure these aren't used by other applications

### 🔍 Verification Checklist

After setup, verify these work:

#### ✅ Application Access
- [ ] Main app loads: http://localhost
- [ ] Login page works: http://localhost/login
- [ ] Admin panel accessible: http://localhost/admin

#### ✅ Admin Login Test
- [ ] Username: `admin`
- [ ] Password: `admin123`
- [ ] Dashboard loads after login

#### ✅ Contact Management
- [ ] Admin contacts page: http://localhost/admin/contacts
- [ ] Shows 5 test contacts
- [ ] Can view individual contact: http://localhost/admin/contacts/1
- [ ] Status updates work (no CSRF errors)

#### ✅ Destinations
- [ ] Destinations page: http://localhost/destinations
- [ ] Shows 16 featured destinations
- [ ] Images load properly

### 🛠️ Troubleshooting

#### If setup script fails:
```powershell
# Check Docker status
docker version
docker-compose version

# Check containers
docker-compose ps

# View logs
docker-compose logs mysql
docker-compose logs php
docker-compose logs nginx
```

#### If database restore fails:
```powershell
# Manual database restore
docker-compose exec mysql mysql -u root -proot_password -e "DROP DATABASE IF EXISTS mapit; CREATE DATABASE mapit;"
Get-Content "backups\mapit_full_backup_2025-06-01_20-10-47.sql" | docker-compose exec -T mysql mysql -u root -proot_password
```

#### If admin login doesn't work:
```powershell
# Check if admin user exists
docker-compose exec mysql mysql -u root -proot_password -e "USE mapit; SELECT username, email, role FROM users WHERE role='admin';"
```

#### If styling looks broken:
- Check if `public/css/admin.css` exists and is not empty
- Verify nginx container is running: `docker-compose ps`
- Check browser console for 404 errors on CSS files

### 📊 What You'll Get

After successful setup:
- **Complete MapIt application** with all features
- **Admin panel** with fixed styling and CSRF protection
- **Contact management** system with 5 test contacts
- **16 featured destinations** with images
- **29 achievement badges** system
- **User management** with admin account ready to use

### 🎯 File Size Reference

Your project should be approximately:
- **Project folder:** ~20-50 MB (without node_modules/vendor if any)
- **Database backup:** 50 KB
- **Docker images:** Will be downloaded (~500MB total)

### 🔑 Important Notes

1. **Backup files** contain real test data - perfect for development
2. **Admin account** is pre-configured and ready to use
3. **All CSRF issues** have been fixed
4. **Styling problems** have been resolved
5. **Docker setup** is production-ready

### 🚀 Next Steps After Setup

1. **Test contact management** functionality
2. **Explore admin features** 
3. **Add your own destinations**
4. **Customize styling** if needed
5. **Start developing new features**

---

**Need help?** The setup script provides detailed error messages and troubleshooting steps!
