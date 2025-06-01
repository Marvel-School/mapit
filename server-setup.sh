#!/bin/bash

# DigitalOcean Server Setup Script for MapIt Production
# Run this script on your DigitalOcean Ubuntu 22.04 server

set -e

echo "🚀 Setting up DigitalOcean server for MapIt production deployment..."

# Update system
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install required packages
echo "📦 Installing required packages..."
apt install -y \
    curl \
    wget \
    git \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release \
    htop \
    nano \
    fail2ban \
    ufw \
    logrotate

# Install Docker
echo "🐳 Installing Docker..."
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Start and enable Docker
systemctl start docker
systemctl enable docker

# Add current user to docker group
usermod -aG docker $USER

# Install Docker Compose (standalone)
echo "🐳 Installing Docker Compose..."
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Create application directory structure
echo "📁 Creating application directories..."
mkdir -p /opt/mapit/{releases,shared,scripts}
mkdir -p /opt/mapit/shared/{storage,logs,ssl}
mkdir -p /var/log/mapit

# Set up firewall
echo "🔥 Configuring firewall..."
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# Configure fail2ban
echo "🛡️ Configuring fail2ban..."
cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 10
EOF

systemctl enable fail2ban
systemctl start fail2ban

# Create deployment user
echo "👤 Creating deployment user..."
useradd -m -s /bin/bash -G docker deploy
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh

# Set up SSH key for deployment (you'll need to add your public key)
echo "🔑 SSH key setup required!"
echo "Add your deployment public key to /home/deploy/.ssh/authorized_keys"
echo "Example: echo 'your-public-key-here' >> /home/deploy/.ssh/authorized_keys"

# Set permissions for deployment directories
chown -R deploy:deploy /opt/mapit
chmod -R 755 /opt/mapit

# Create log rotation configuration
echo "📝 Setting up log rotation..."
cat > /etc/logrotate.d/mapit << 'EOF'
/opt/mapit/shared/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 deploy deploy
    postrotate
        docker compose -f /opt/mapit/current/docker-compose.yml exec nginx nginx -s reload > /dev/null 2>&1 || true
    endscript
}
EOF

# Set up system monitoring
echo "📊 Setting up basic monitoring..."
cat > /etc/cron.d/mapit-monitoring << 'EOF'
# Check disk space every hour
0 * * * * root df -h | grep -E '9[0-9]%|100%' && echo "Warning: Disk space is running low" | mail -s "MapIt Server: Disk Space Alert" admin@mapitedu.nl

# Check memory usage every hour
0 * * * * root free | awk 'NR==2{printf "Memory Usage: %s/%sMB (%.2f%%)\n", $3,$2,$3*100/$2 }' | grep -E '9[0-9]\.|100\.' && echo "Warning: Memory usage is high" | mail -s "MapIt Server: Memory Alert" admin@mapitedu.nl
EOF

# Create backup directories and scripts
echo "💾 Setting up backup system..."
mkdir -p /opt/mapit/backups
cat > /opt/mapit/scripts/system-backup.sh << 'EOF'
#!/bin/bash
# System backup script
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/opt/mapit/backups/system"
mkdir -p ${BACKUP_DIR}

# Backup important system configs
tar -czf ${BACKUP_DIR}/system_config_${DATE}.tar.gz \
    /etc/nginx \
    /etc/ssl \
    /opt/mapit/shared \
    /etc/cron.d/mapit-monitoring \
    /etc/logrotate.d/mapit

# Keep only last 7 days of system backups
find ${BACKUP_DIR} -name "system_config_*.tar.gz" -mtime +7 -delete
EOF

chmod +x /opt/mapit/scripts/system-backup.sh

# Add system backup to cron
echo "0 3 * * * root /opt/mapit/scripts/system-backup.sh" >> /etc/cron.d/mapit-monitoring

# Install performance monitoring tools
echo "⚡ Installing performance monitoring..."
apt install -y htop iotop nethogs

# Create initial health check script
cat > /opt/mapit/scripts/health-check.sh << 'EOF'
#!/bin/bash
# Health check script

echo "=== MapIt Health Check ===" 
echo "Date: $(date)"
echo "Uptime: $(uptime)"
echo ""

echo "=== Docker Status ==="
docker --version
docker compose version
echo ""

echo "=== Container Status ==="
cd /opt/mapit/current 2>/dev/null && docker compose ps || echo "No containers running"
echo ""

echo "=== Disk Usage ==="
df -h
echo ""

echo "=== Memory Usage ==="
free -h
echo ""

echo "=== Network Connections ==="
netstat -tuln | grep -E ':(80|443|3306|6379) '
echo ""

echo "=== Recent Logs ==="
tail -n 5 /opt/mapit/shared/logs/*.log 2>/dev/null || echo "No logs found"
EOF

chmod +x /opt/mapit/scripts/health-check.sh

# Create environment variables template
cat > /opt/mapit/shared/.env.template << 'EOF'
# Production Environment Configuration
APP_ENV=production
APP_DEBUG=false
APP_NAME="MapIt - Travel Destination Mapping"
APP_URL=https://mapitedu.nl

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=mapit_production
DB_USERNAME=mapit_prod_user
DB_PASSWORD=CHANGE_ME
DB_ROOT_PASSWORD=CHANGE_ME

# Redis Configuration
REDIS_PASSWORD=CHANGE_ME

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=1440

# Mail Configuration
MAIL_DRIVER=smtp
MAIL_HOST=CHANGE_ME
MAIL_PORT=587
MAIL_USERNAME=CHANGE_ME
MAIL_PASSWORD=CHANGE_ME
MAIL_ENCRYPTION=tls

# API Keys
GOOGLE_MAPS_API_KEY=CHANGE_ME
WEATHER_API_KEY=CHANGE_ME

# Security
JWT_SECRET=CHANGE_ME
ENCRYPTION_KEY=CHANGE_ME

# Monitoring
GRAFANA_ADMIN_PASSWORD=CHANGE_ME

# Admin Settings
ADMIN_EMAIL=admin@mapitedu.nl
EOF

echo ""
echo "✅ Server setup completed!"
echo ""
echo "📋 Next steps:"
echo "1. Add your SSH public key to /home/deploy/.ssh/authorized_keys"
echo "2. Configure your GitHub secrets for the deployment workflow"
echo "3. Set up your domain DNS to point to this server"
echo "4. Run the SSL setup script after domain is configured"
echo "5. Push your code to trigger the first deployment"
echo ""
echo "📊 Useful commands:"
echo "- Check health: /opt/mapit/scripts/health-check.sh"
echo "- View logs: tail -f /opt/mapit/shared/logs/app.log"
echo "- Restart services: cd /opt/mapit/current && docker compose restart"
echo ""
echo "🔧 Server IP: $(curl -s ifconfig.me)"
echo ""
