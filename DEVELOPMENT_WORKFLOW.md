# Development Workflow Guide

This guide explains how to work with the MapIt project using a proper Git workflow that separates development from production.

## Branch Structure

- **`main`** - Production branch (auto-deploys to production)
- **`development`** - Development branch (for local development and testing)
- **`feature/*`** - Feature branches (for specific features)

## Development Workflow

### 1. Start Development Work

```bash
# Switch to development branch
git checkout development

# Pull latest changes
git pull origin development

# Create a feature branch (optional)
git checkout -b feature/your-feature-name
```

### 2. Local Development

Start your local development environment:

```bash
# Start Docker containers
docker-compose up -d

# Check application at http://localhost
```

The development branch includes:
- Local Docker development environment
- Development debugging tools
- Test files and utilities
- Extended .gitignore for development files

### 3. Testing Your Changes

- Test thoroughly in your local environment
- Verify all features work as expected
- Check for any breaking changes
- Test different browsers and screen sizes

### 4. Committing Changes

```bash
# Stage your changes
git add .

# Commit with descriptive message
git commit -m "Add feature: descriptive message about what you built"

# Push to development branch
git push origin development
```

### 5. Deploying to Production

When your changes are ready for production:

```bash
# Switch to main branch
git checkout main

# Merge development changes
git merge development

# Push to main (triggers automatic deployment)
git push origin main
```

**⚠️ Important:** Pushing to `main` immediately triggers production deployment!

## File Organization

### Development Branch Contains:
- `docker-compose.yml` - Local development environment
- `docker/` - Development Docker configurations
- Development debugging tools
- Test files and utilities

### Main Branch Contains:
- `docker-compose.production.yml` - Production configuration
- `.github/workflows/deploy.yml` - Deployment automation
- `PRODUCTION_DEPLOYMENT_GUIDE.md` - Production setup guide
- Clean, production-ready code only

## Best Practices

1. **Never commit directly to main** - Always work in development first
2. **Test thoroughly** - Make sure everything works locally before merging
3. **Use descriptive commit messages** - Help others understand your changes
4. **Keep commits focused** - One feature or fix per commit when possible
5. **Pull before pushing** - Always pull latest changes before pushing

## Emergency Hotfixes

For urgent production fixes:

```bash
# Create hotfix branch from main
git checkout main
git checkout -b hotfix/urgent-fix

# Make minimal fix
# ... edit files ...

# Commit and merge to main
git add .
git commit -m "Hotfix: describe the urgent fix"
git checkout main
git merge hotfix/urgent-fix
git push origin main

# Merge back to development
git checkout development
git merge main
git push origin development
```

## Local Environment

Your local environment (development branch) includes:
- PHP 8.1 with FPM
- Nginx web server
- MySQL 8.0 database
- Redis for caching
- All development tools and debugging features

Access your local application at: `http://localhost`

## Troubleshooting

### Common Issues:

1. **502 Bad Gateway**: Restart containers with `docker-compose restart`
2. **Database connection issues**: Check MySQL container logs
3. **Permission issues**: Ensure proper file permissions in storage/

### Getting Help:

- Check container logs: `docker logs container_name`
- Review application logs in `storage/logs/`
- Check the development branch README for more details

## Production Monitoring

After pushing to main:
- Monitor GitHub Actions for deployment status
- Check production health at: `https://your-domain.com/health`
- Monitor application logs if issues occur
