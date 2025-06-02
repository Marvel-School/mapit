# Production Deployment Guide

This document provides instructions for deploying MapIt to production.

## Recent Updates

✅ **Production Deployment Fixed** (June 2025)
- Fixed Dockerfile to use PHP-FPM for better nginx integration
- Resolved docker-compose.production.yml configuration issues  
- Self-contained production setup with inline configurations
- No external file dependencies for cleaner deployment

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

## Health Check

Check application health at: `https://your-domain.com/health`

## Support

For deployment issues, check the GitHub repository issues or deployment logs.
