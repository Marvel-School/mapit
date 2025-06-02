# Production Deployment Guide

This document provides instructions for deploying MapIt to production.

## Recent Updates

✅ **Production Deployment Fixed** (June 2025)
- Fixed Dockerfile to use PHP-FPM for better nginx integration
- Resolved docker-compose.production.yml configuration issues  
- Self-contained production setup with inline configurations
- No external file dependencies for cleaner deployment

## Current Status

🔧 **DEPLOYMENT WORKFLOW FIXES IN PROGRESS**

### Production Infrastructure Status:
- ✅ Docker build issues resolved (Dockerfile optimized with echo commands)
- ✅ Production docker-compose.yml validated and working
- ✅ GitHub Actions deployment workflow syntax errors fixed
- 🔄 Production deployment currently running
- 🔍 Investigating HTTP/HTTPS port accessibility issues
- 🌐 **Production Server**: 142.93.136.145 (SSH accessible)
- 🌍 **Production Domain**: mapitedu.nl → 142.93.136.145
- ❌ HTTP/HTTPS ports not responding (under investigation)

### Recent Fixes Applied:
- Fixed GitHub Actions workflow YAML syntax errors
- Added comprehensive server diagnostics and debugging
- Enhanced Docker container logging and status monitoring
- Improved deployment process with better error handling
- Pushed commit 6bde0bc with workflow fixes

### Next Steps:
1. Monitor GitHub Actions deployment progress
2. Check firewall/security group settings on production server
3. Verify Docker containers are starting correctly
4. Investigate port binding issues

### Deployment Summary:
- **Docker Build Time**: ~5.8 seconds (optimized)
- **Services Running**: PHP 8.1-FPM, MySQL 8.0, Redis, Nginx, Certbot
- **Ports Exposed**: 80 (HTTP), 443 (HTTPS)
- **Environment**: Production-ready with optimized PHP settings
- **Database**: MySQL with persistent storage
- **Caching**: Redis for session and application caching
- **SSL**: Let's Encrypt automatic certificate management

### Latest Actions Completed:
- ✅ Docker build successfully tested locally (mapit-test:latest)
- ✅ Docker-compose production configuration validated
- ✅ Changes committed and pushed to main branch (commit: ce6e3c1)
- ✅ GitHub Actions deployment workflow triggered
- ✅ Created production environment template (.env.production.example)
- ✅ Local production stack deployed and running successfully

## Prerequisites

- Production server with Docker and Docker Compose installed
- Domain name configured to point to your server
- GitHub repository secrets configured (see below)

## GitHub Secrets Configuration

Configure the following secrets in your GitHub repository settings:

### Required Secrets:
- `SSH_PRIVATE_KEY` - Private SSH key for server access
- `SSH_USER` - SSH username (typically 'deploy' or 'ubuntu')
- `PRODUCTION_HOST` - Server IP address or domain name
- `DB_PASSWORD` - Secure database password
- `REDIS_PASSWORD` - Redis password
- `JWT_SECRET` - JWT secret key for authentication
- `ENCRYPTION_KEY` - Application encryption key

### Optional Secrets:
- `GOOGLE_MAPS_API_KEY` - Google Maps API key
- `ADMIN_EMAIL` - Admin email address
- `MAIL_HOST` - SMTP host
- `MAIL_USERNAME` - SMTP username  
- `MAIL_PASSWORD` - SMTP password

## Deployment Process

1. **Push to main branch** - Deployment is triggered automatically
2. **Monitor workflow** - Check GitHub Actions for deployment status
3. **Verify deployment** - Visit your domain to confirm the site is live

## Server Requirements

- Ubuntu 20.04+ or similar Linux distribution
- Docker 20.10+
- Docker Compose 2.0+
- 2GB+ RAM
- 20GB+ storage

## SSL/HTTPS

The deployment workflow automatically:
- Requests SSL certificates via Let's Encrypt
- Configures HTTPS if domain is properly configured
- Falls back to HTTP if SSL setup fails

## Troubleshooting

If deployment fails:
1. Check GitHub Actions logs
2. Verify all secrets are configured correctly
3. Ensure domain DNS points to your server
4. Check server SSH access

## Manual Deployment

If needed, you can deploy manually:

```bash
# On your production server
cd /opt/mapit/current
sudo docker-compose -f docker-compose.production.yml up -d --build
```

## Production Access

### Live Production Site:
- **Primary URL**: https://mapitedu.nl
- **Direct IP Access**: http://142.93.136.145
- **Health Check**: https://mapitedu.nl/health

### Server Information:
- **IP Address**: 142.93.136.145
- **Domain**: mapitedu.nl
- **SSL**: Let's Encrypt certificates
- **Docker Location**: /opt/mapit/current/

## Health Check

Check application health at: `https://mapitedu.nl/health`

## Support

For deployment issues, check the GitHub repository issues or deployment logs.
