# MapIt Production Deployment Guide

This guide provides step-by-step instructions for deploying MapIt to production on DigitalOcean with automated CI/CD, SSL certificates, and monitoring.

## 📋 Prerequisites

- DigitalOcean droplet (Ubuntu 22.04 LTS, minimum 2GB RAM)
- Domain name `mapitedu.nl` configured to point to your server
- GitHub repository with your MapIt code
- GitHub account with Actions enabled

## 🚀 Quick Start

### 1. Server Setup

1. **Create DigitalOcean Droplet**:
   - Ubuntu 22.04 LTS
   - Minimum 2GB RAM, 1 vCPU, 50GB disk
   - Enable monitoring and backups

2. **Run Server Setup Script**:
   ```bash
   # SSH into your server
   ssh root@your-server-ip
   
   # Download and run setup script
   wget https://raw.githubusercontent.com/yourusername/mapit/main/server-setup.sh
   chmod +x server-setup.sh
   ./server-setup.sh
   ```

### 2. Configure GitHub Secrets

Go to your GitHub repository → Settings → Secrets and variables → Actions, and add these secrets:

#### Server Connection
- `SSH_PRIVATE_KEY`: Your private SSH key for deployment
- `SSH_USER`: `deploy`
- `PRODUCTION_HOST`: Your server IP address

#### Database
- `DB_PASSWORD`: Strong password for production database
- `DB_ROOT_PASSWORD`: Strong password for MySQL root user

#### API Keys
- `GOOGLE_MAPS_API_KEY`: Your Google Maps API key
- `WEATHER_API_KEY`: Your weather service API key

#### Security
- `JWT_SECRET`: Strong secret for JWT tokens (generate with: `openssl rand -base64 32`)
- `ENCRYPTION_KEY`: Strong encryption key (generate with: `openssl rand -base64 32`)

#### Email Configuration
- `MAIL_HOST`: Your SMTP server hostname
- `MAIL_PORT`: SMTP port (usually 587)
- `MAIL_USERNAME`: SMTP username
- `MAIL_PASSWORD`: SMTP password

#### Monitoring
- `GRAFANA_ADMIN_PASSWORD`: Password for Grafana admin user
- `ADMIN_EMAIL`: Your admin email address

### 3. Configure Domain DNS

Point your domain to your server:
- **A Record**: `mapitedu.nl` → `your-server-ip`
- **A Record**: `www.mapitedu.nl` → `your-server-ip`

### 4. Set Up SSH Key

1. **Generate SSH Key** (if you don't have one):
   ```powershell
   ssh-keygen -t rsa -b 4096 -C "deploy@mapitedu.nl"
   ```

2. **Add Public Key to Server**:
   ```bash
   # Copy your public key content
   Get-Content ~/.ssh/id_rsa.pub | Set-Clipboard
   
   # SSH to server and add key
   ssh root@your-server-ip
   echo "your-public-key-here" >> /home/deploy/.ssh/authorized_keys
   chmod 600 /home/deploy/.ssh/authorized_keys
   chown deploy:deploy /home/deploy/.ssh/authorized_keys
   ```

3. **Add Private Key to GitHub Secrets**:
   - Copy content of `~/.ssh/id_rsa`
   - Add as `SSH_PRIVATE_KEY` secret in GitHub

### 5. Deploy Application

1. **Push to main branch**:
   ```powershell
   git add .
   git commit -m "Initial production deployment"
   git push origin main
   ```

2. **Monitor Deployment**:
   - Go to GitHub → Actions tab
   - Watch the deployment workflow
   - Check for any errors

### 6. Set Up SSL Certificates

After successful deployment and DNS propagation:

```bash
ssh deploy@your-server-ip
sudo /opt/mapit/current/docker/production/scripts/setup-ssl.sh
```

## 🔧 Local Development Workflow

### Development Setup
1. **Work locally** with your existing setup using `docker-compose.yml`
2. **Test changes** thoroughly in your local environment
3. **Commit and push** to trigger automatic deployment

### Environment Management
- **Local**: Uses `.env` file with development settings
- **Production**: Uses environment variables from GitHub secrets
- **Google Maps API**: Secured in production, exposed only locally

### Database Management
- **Local**: Uses Docker MySQL with local data
- **Production**: Separate production database with automated backups

## 📊 Monitoring and Maintenance

### Health Monitoring
```bash
# Check application health
curl https://mapitedu.nl/health

# Check server health
ssh deploy@your-server-ip
/opt/mapit/scripts/health-check.sh
```

### Log Management
```bash
# View application logs
ssh deploy@your-server-ip
tail -f /opt/mapit/shared/logs/app.log

# View nginx logs
docker compose -f /opt/mapit/current/docker-compose.yml logs nginx

# View database logs
docker compose -f /opt/mapit/current/docker-compose.yml logs mysql
```

### Monitoring Dashboard
- **Grafana**: `https://mapitedu.nl:3000`
- **Prometheus**: `https://mapitedu.nl:9090`
- Default login: admin / (your GRAFANA_ADMIN_PASSWORD)

### Backup Management
```bash
# Manual backup
ssh deploy@your-server-ip
docker compose -f /opt/mapit/current/docker-compose.yml run --rm backup

# Check backup status
ls -la /opt/mapit/backups/
```

## 🔐 Security Features

### SSL/HTTPS
- Automatic Let's Encrypt certificates
- HTTPS redirect
- HSTS headers
- Modern TLS configuration

### Firewall
- UFW configured to allow only SSH, HTTP, HTTPS
- Fail2ban for intrusion prevention
- Rate limiting in Nginx

### Application Security
- Environment variables secured in GitHub secrets
- Database credentials not exposed in code
- Security headers configured
- File upload restrictions

## 🚨 Troubleshooting

### Common Issues

1. **Deployment Failed**:
   ```bash
   # Check GitHub Actions logs
   # SSH to server and check containers
   ssh deploy@your-server-ip
   cd /opt/mapit/current
   docker compose ps
   docker compose logs
   ```

2. **SSL Certificate Issues**:
   ```bash
   # Check certificate status
   docker compose logs certbot
   
   # Manual certificate renewal
   /opt/mapit/scripts/renew-ssl.sh
   ```

3. **Database Connection Issues**:
   ```bash
   # Check database container
   docker compose logs mysql
   
   # Test database connection
   docker compose exec mysql mysql -u mapit_prod_user -p mapit_production
   ```

4. **Performance Issues**:
   ```bash
   # Check resource usage
   htop
   docker stats
   
   # Check logs for errors
   tail -f /opt/mapit/shared/logs/app.log
   ```

### Emergency Procedures

1. **Rollback to Previous Version**:
   ```bash
   ssh deploy@your-server-ip
   cd /opt/mapit
   
   # List available releases
   ls -la releases/
   
   # Switch to previous release
   ln -sfn releases/PREVIOUS_RELEASE_DIR current
   cd current && docker compose restart
   ```

2. **Emergency Maintenance Mode**:
   ```bash
   # Enable maintenance mode
   ssh deploy@your-server-ip
   cd /opt/mapit/current
   docker compose exec php php artisan down
   
   # Disable maintenance mode
   docker compose exec php php artisan up
   ```

## 📈 Scaling Considerations

### Vertical Scaling
- Upgrade DigitalOcean droplet size
- Adjust Docker resource limits
- Optimize database configuration

### Horizontal Scaling
- Set up load balancer
- Configure database replication
- Implement Redis clustering

### Performance Optimization
- Enable OPcache (already configured)
- Configure Redis caching
- Optimize database queries
- Set up CDN for static assets

## 📞 Support

### Log Locations
- Application: `/opt/mapit/shared/logs/app.log`
- Nginx: `/var/log/nginx/`
- System: `/var/log/syslog`

### Useful Commands
```bash
# Application status
docker compose ps

# Restart application
docker compose restart

# Update application
cd /opt/mapit/current && docker compose pull && docker compose up -d

# Database backup
docker compose run --rm backup

# Clear application cache
docker compose exec php php artisan cache:clear
```

### Getting Help
1. Check application logs first
2. Review GitHub Actions deployment logs
3. Test with health check endpoints
4. Check system resources and Docker status

---

**Remember**: Always test changes in your local development environment before deploying to production!
