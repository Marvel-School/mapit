# MapIt Development Workflow

## Current Setup Status
✅ **Local Development**: Ready (development branch)  
🔄 **Production Deployment**: In Progress (main branch → 142.93.136.145)  
🌐 **Domain**: mapitedu.nl  

## Development Workflow

### Local Development (Development Branch)
1. **Switch to development branch**:
   ```bash
   git checkout development
   ```

2. **Start local development environment**:
   ```bash
   docker-compose up -d
   ```

3. **Make your changes** to the codebase

4. **Test locally** at http://localhost

5. **Commit your changes**:
   ```bash
   git add .
   git commit -m "Your feature description"
   ```

### Deploy to Production (Main Branch)
1. **Merge development to main**:
   ```bash
   git checkout main
   git merge development
   ```

2. **Push to trigger production deployment**:
   ```bash
   git push origin main
   ```

3. **Monitor deployment**:
   - GitHub Actions will automatically deploy to 142.93.136.145
   - Check https://github.com/Marvel-School/mapit/actions for status
   - Site will be available at http://mapitedu.nl (and https when SSL is configured)

## Current Deployment Status

### Production Server: 142.93.136.145
- ✅ **Server Connectivity**: Reachable via SSH (port 22)
- ✅ **DNS Configuration**: mapitedu.nl → 142.93.136.145
- 🔄 **HTTP/HTTPS Access**: Currently being fixed (ports 80/443)
- 🔄 **Docker Services**: GitHub Actions deployment in progress

### Recent Fixes Applied
- ✅ Fixed GitHub Actions workflow syntax errors
- ✅ Fixed Docker Compose configuration issues
- ✅ Improved nginx container configuration
- ✅ Added better deployment logging and error handling

### Next Steps
1. Monitor current deployment at: https://github.com/Marvel-School/mapit/actions
2. Once deployment completes, test HTTP access
3. Configure SSL/HTTPS certificates
4. Verify full production functionality

## Troubleshooting

### If deployment fails:
1. Check GitHub Actions logs
2. Verify all required secrets are set in GitHub repository settings
3. Check server logs via SSH if needed

### If local development issues:
1. Rebuild containers: `docker-compose up -d --build`
2. Check container logs: `docker-compose logs`
3. Verify port availability (80, 3306, 6379)