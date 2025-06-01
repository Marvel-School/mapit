# MapIt Database Backup & Restore Instructions

## 📦 Backup Files Created

The following backup files have been created on **June 1, 2025**:

1. **`mapit_full_backup_2025-06-01_20-10-47.sql`** (50KB)
   - Complete database backup with structure, data, routines, and triggers
   - **Recommended for full setup on laptop**

2. **`mapit_data_only_2025-06-01_20-10-56.sql`** (29KB)
   - Data only backup (no structure)
   - Use when you already have the schema setup

3. **`mapit_schema_only_2025-06-01_20-11-09.sql`** (23KB)
   - Database structure only (no data)
   - Use for creating empty database with same structure

## 🚀 How to Restore on Your Laptop

### Option 1: Full Restore (Recommended)

```bash
# 1. Start your Docker containers
docker-compose up -d

# 2. Wait for MySQL to be ready (check with)
docker-compose logs mysql

# 3. Restore the complete database
docker-compose exec -T mysql mysql -u root -proot_password < mapit_full_backup_2025-06-01_20-10-47.sql
```

### Option 2: Manual Database Creation + Data Import

```bash
# 1. Create database first
docker-compose exec mysql mysql -u root -proot_password -e "CREATE DATABASE IF NOT EXISTS mapit;"

# 2. Import schema
docker-compose exec -T mysql mysql -u root -proot_password mapit < mapit_schema_only_2025-06-01_20-11-09.sql

# 3. Import data
docker-compose exec -T mysql mysql -u root -proot_password mapit < mapit_data_only_2025-06-01_20-10-56.sql
```

### Windows PowerShell Commands

```powershell
# Navigate to your mapit project directory
cd C:\Path\To\Your\mapit\project

# Start containers
docker-compose up -d

# Restore full backup
Get-Content "backups\mapit_full_backup_2025-06-01_20-10-47.sql" | docker-compose exec -T mysql mysql -u root -proot_password
```

## 🔍 Verify Restoration

After restoring, verify your database:

```bash
# Check databases
docker-compose exec mysql mysql -u root -proot_password -e "SHOW DATABASES;"

# Check tables in mapit database
docker-compose exec mysql mysql -u root -proot_password -e "USE mapit; SHOW TABLES;"

# Check contact data (should show 4 test contacts)
docker-compose exec mysql mysql -u root -proot_password -e "USE mapit; SELECT id, name, email, subject, status FROM contacts;"

# Check admin user exists
docker-compose exec mysql mysql -u root -proot_password -e "USE mapit; SELECT id, username, email, role FROM users WHERE role='admin';"
```

## 📋 What's Included in the Backup

✅ **Database Structure:**
- All tables (users, contacts, destinations, trips, badges, logs, etc.)
- Indexes and constraints
- Triggers and stored procedures (if any)

✅ **Test Data:**
- Admin user account (username: `admin`, password: `admin123`)
- 4 test contacts with various statuses
- Sample destinations and trips data
- User badges and achievement data

✅ **Configuration:**
- All table relationships
- Enum values and constraints
- Default values

## 🔐 Default Admin Credentials

- **Username:** `admin`
- **Email:** `admin@mapit.com`  
- **Password:** `admin123`
- **Role:** `admin`

## 🛠️ Troubleshooting

### If restore fails:
1. Make sure MySQL container is fully started
2. Check if database already exists: `docker-compose exec mysql mysql -u root -proot_password -e "DROP DATABASE IF EXISTS mapit;"`
3. Try the manual approach (Option 2)

### If you get permission errors:
1. Make sure Docker is running as administrator
2. Check file permissions on backup files

### If data seems missing:
1. Check the backup file size (should be ~50KB for full backup)
2. Verify you're using the correct database name in your app config
3. Check your docker-compose.yml MySQL configuration matches

## 📱 Access Points After Restore

- **Main App:** http://localhost
- **Admin Panel:** http://localhost/admin
- **Contact Management:** http://localhost/admin/contacts
- **Login Page:** http://localhost/login

## 📅 Backup Information

- **Created:** June 1, 2025 at 8:10 PM
- **MySQL Version:** 8.0
- **Total Tables:** ~8-10 tables
- **Total Records:** ~50+ records across all tables
- **Database Size:** ~50KB with all data
