#!/bin/sh

# MapIt Production Backup Script
# This script creates automated backups of the MySQL database

set -e

BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Database connection details
DB_HOST="mysql"
DB_NAME="mapit_production"
DB_USER="mapit_prod_user"
DB_PASSWORD="${DB_PASSWORD}"

echo "Starting backup process at $(date)"

# Create backup directory if it doesn't exist
mkdir -p ${BACKUP_DIR}

# Create database backup
echo "Creating database backup..."
mysqldump -h ${DB_HOST} -u ${DB_USER} -p${DB_PASSWORD} \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    --complete-insert \
    --extended-insert \
    ${DB_NAME} > ${BACKUP_DIR}/mapit_backup_${DATE}.sql

# Compress the backup
gzip ${BACKUP_DIR}/mapit_backup_${DATE}.sql

echo "Database backup completed: mapit_backup_${DATE}.sql.gz"

# Create a backup of the storage directory
echo "Creating storage backup..."
tar -czf ${BACKUP_DIR}/storage_backup_${DATE}.tar.gz -C /var/www/html storage/

echo "Storage backup completed: storage_backup_${DATE}.tar.gz"

# Clean up old backups (keep only last 30 days)
echo "Cleaning up old backups..."
find ${BACKUP_DIR} -name "mapit_backup_*.sql.gz" -mtime +${RETENTION_DAYS} -delete
find ${BACKUP_DIR} -name "storage_backup_*.tar.gz" -mtime +${RETENTION_DAYS} -delete

echo "Backup process completed at $(date)"

# Optional: Upload to cloud storage (uncomment if needed)
# aws s3 cp ${BACKUP_DIR}/mapit_backup_${DATE}.sql.gz s3://your-backup-bucket/
# aws s3 cp ${BACKUP_DIR}/storage_backup_${DATE}.tar.gz s3://your-backup-bucket/
