# 🚀 MapIt Production Deployment Checklist

## ✅ Step 1: Create DigitalOcean Droplet

1. **Go to DigitalOcean** and create a new droplet:
   - **Image**: Ubuntu 22.04 (LTS) x64
   - **Plan**: Basic - $12/month (2GB RAM, 1 vCPU, 50GB SSD)
   - **Region**: Amsterdam 3 (closest to Netherlands)
   - **Authentication**: SSH Keys (add your public key from below)
   - **Hostname**: `mapit-production`

2. **Add your SSH public key** when creating the droplet:
   ```
   ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDDzqYEfKvrj5x73GFC+NJ2BLzO0IzXYoloByep+fo/xlyNxGDU3t9aLLl2dbu06LwQA4uWGEHkUH8D3cSpUwULd7bL7UU5aOYPiJHog5F2p8Ys9pBRNUzEjBMX2xWEA2TTzLxyWRJTuuCk/DU/PVOnaXdCF1YVAo5hWlAtr3jz0QZ5gbsUox1Z/MpTTn7+2ELW/Jxwlm9hkkzFJtG5Hu3XPHUBTEvBOXPeW0r0yEFWN14IyvlTHrPxlq6bd9/1junRWnBE1pArx5qFu2OyA4teNneKOT0S8dwlwCAkg0xVZ99DzuLuMT01cGT+utLO3xVD3e569kZzOsR5wbe6zOldIBbOC02RlOyvnT6ropwkUlA+SaGxdkS26DI8WJndktGbvDoYfLZ6aK9EgQGhrjEC0kfI1EbgyErMwJSyySNe8xLtJkc8wIj2ouN+8lNiZvRq3yqPRuNRlqv3jVZFiT/rwrMbkGSKIhqSxNifVB39GIVCxxqhztLu2F9OcATtZ0IKZRJo5QwaWtMrDZHW3lVEM+xLVdT96PviQxAuISNS0Q78DaLO4FoutD13W3RByxbgK6TBQRsAjVLoNY0oSJtSh1juzFJwg+rrKqlhy3tB7pqozZrCDiUIPsxaWtG4quhpNt94p33brN9MxQSMYx6ZOESotOeSbs7KVys0IHL0Pw== deploy@mapitedu.nl
   ```

3. **Note the server IP address** (e.g., `167.99.123.45`)

## ✅ Step 2: Set Up Server

1. **SSH into your server**:
   ```bash
   ssh root@YOUR_SERVER_IP
   ```

2. **Download and run the server setup script**:
   ```bash
   curl -L https://raw.githubusercontent.com/yourusername/mapit/main/server-setup.sh -o server-setup.sh
   chmod +x server-setup.sh
   ./server-setup.sh
   ```

3. **Add the deploy user's SSH key**:
   ```bash
   echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDDzqYEfKvrj5x73GFC+NJ2BLzO0IzXYoloByep+fo/xlyNxGDU3t9aLLl2dbu06LwQA4uWGEHkUH8D3cSpUwULd7bL7UU5aOYPiJHog5F2p8Ys9pBRNUzEjBMX2xWEA2TTzLxyWRJTuuCk/DU/PVOnaXdCF1YVAo5hWlAtr3jz0QZ5gbsUox1Z/MpTTn7+2ELW/Jxwlm9hkkzFJtG5Hu3XPHUBTEvBOXPeW0r0yEFWN14IyvlTHrPxlq6bd9/1junRWnBE1pArx5qFu2OyA4teNneKOT0S8dwlwCAkg0xVZ99DzuLuMT01cGT+utLO3xVD3e569kZzOsR5wbe6zOldIBbOC02RlOyvnT6ropwkUlA+SaGxdkS26DI8WJndktGbvDoYfLZ6aK9EgQGhrjEC0kfI1EbgyErMwJSyySNe8xLtJkc8wIj2ouN+8lNiZvRq3yqPRuNRlqv3jVZFiT/rwrMbkGSKIhqSxNifVB39GIVCxxqhztLu2F9OcATtZ0IKZRJo5QwaWtMrDZHW3lVEM+xLVdT96PviQxAuISNS0Q78DaLO4FoutD13W3RByxbgK6TBQRsAjVLoNY0oSJtSh1juzFJwg+rrKqlhy3tB7pqozZrCDiUIPsxaWtG4quhpNt94p33brN9MxQSMYx6ZOESotOeSbs7KVys0IHL0Pw== deploy@mapitedu.nl" >> /home/deploy/.ssh/authorized_keys
   chmod 600 /home/deploy/.ssh/authorized_keys
   chown deploy:deploy /home/deploy/.ssh/authorized_keys
   ```

## ✅ Step 3: Configure GitHub Secrets

1. **Go to your GitHub repository**
2. **Click Settings** → **Secrets and variables** → **Actions**
3. **Add these secrets** (click "New repository secret" for each):

### Required Secrets:
- `SSH_USER` = `deploy`
- `PRODUCTION_HOST` = `YOUR_SERVER_IP` (replace with actual IP)
- `SSH_PRIVATE_KEY` = (copy from GITHUB_SECRETS_GENERATED.md)
- `JWT_SECRET` = `bXFpek9NeHRyWkFjc2RWVFV6VW5pc2JWck1ndWFxcFI=`
- `ENCRYPTION_KEY` = `T1hVdk1Yc2NQcm5kTXNVTHNHc3FhR0RLeEdzbE5GdVY=`
- `DB_PASSWORD` = `0ieGSrIAft8H2NctQkEO`
- `DB_ROOT_PASSWORD` = `avXGfdmw85snHforb7kl`
- `GOOGLE_MAPS_API_KEY` = `AIzaSyDsR974k1b4C4zkmFjFFYR3eSX3HWJ66A0`
- `WEATHER_API_KEY` = `your_weather_api_key_here`
- `MAIL_HOST` = `smtp.gmail.com`
- `MAIL_PORT` = `587`
- `MAIL_USERNAME` = `your-email@gmail.com`
- `MAIL_PASSWORD` = `your-app-password`
- `REDIS_PASSWORD` = `WdpPbYa9uFq2i80J`
- `GRAFANA_ADMIN_PASSWORD` = `aWW07cNxx6DJ`
- `ADMIN_EMAIL` = `admin@mapitedu.nl`

## ✅ Step 4: Configure Domain DNS

1. **Go to your domain registrar** (where you bought mapitedu.nl)
2. **Add these DNS records**:
   - **A Record**: `mapitedu.nl` → `YOUR_SERVER_IP`
   - **A Record**: `www.mapitedu.nl` → `YOUR_SERVER_IP`
3. **Wait for DNS propagation** (5-60 minutes)

## ✅ Step 5: Deploy Application

1. **Commit and push all changes**:
   ```bash
   git add .
   git commit -m "Production deployment setup complete"
   git push origin main
   ```

2. **Watch the deployment**:
   - Go to GitHub → Actions tab
   - Watch the "Deploy to Production" workflow
   - Monitor for any errors

## ✅ Step 6: Configure SSL Certificates

After successful deployment and DNS propagation:

1. **SSH to your server as deploy user**:
   ```bash
   ssh deploy@YOUR_SERVER_IP
   ```

2. **Run SSL setup**:
   ```bash
   sudo /opt/mapit/current/docker/production/scripts/setup-ssl.sh
   ```

## ✅ Step 7: Verify Deployment

1. **Check health endpoint**: `https://mapitedu.nl/health`
2. **Access your site**: `https://mapitedu.nl`
3. **Check monitoring**: `https://mapitedu.nl:3000` (Grafana)

## 🎯 Success Criteria

- [ ] DigitalOcean droplet created
- [ ] Server setup script completed
- [ ] GitHub secrets configured
- [ ] Domain DNS configured
- [ ] First deployment successful
- [ ] SSL certificates installed
- [ ] Site accessible via HTTPS
- [ ] Health monitoring working

## 🆘 If Something Goes Wrong

1. **Check GitHub Actions logs** for deployment errors
2. **SSH to server** and check container status:
   ```bash
   cd /opt/mapit/current
   docker compose ps
   docker compose logs
   ```
3. **Check health script**:
   ```bash
   /opt/mapit/scripts/health-check.sh
   ```

## 📞 Ready for Next Steps?

Once everything is working:
- Your Google Maps API key is now secure in GitHub secrets
- You can continue developing locally with your existing setup
- Push changes to `main` branch for automatic deployment
- Monitor your application via Grafana dashboard
