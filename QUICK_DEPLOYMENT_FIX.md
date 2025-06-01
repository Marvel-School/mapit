# 🚀 MapIt Deployment - Quick Fix Guide

## Server IP: 142.93.136.145

## ✅ Step 1: Set Up GitHub Secrets (URGENT)

Go to your GitHub repository: **Settings → Secrets and variables → Actions**

Add these secrets exactly as shown:

### **Server Connection**
- `SSH_USER` = `deploy`
- `PRODUCTION_HOST` = `142.93.136.145`
- `SSH_PRIVATE_KEY` = (copy the full private key from GITHUB_SECRETS_GENERATED.md - starts with `-----BEGIN OPENSSH PRIVATE KEY-----`)

### **Security Keys** (already generated)
- `JWT_SECRET` = `bXFpek9NeHRyWkFjc2RWVFV6VW5pc2JWck1ndWFxcFI=`
- `ENCRYPTION_KEY` = `T1hVdk1Yc2NQcm5kTXNVTHNHc3FhR0RLeEdzbE5GdVY=`

### **Database**
- `DB_PASSWORD` = `0ieGSrIAft8H2NctQkEO`
- `DB_ROOT_PASSWORD` = `avXGfdmw85snHforb7kl`
- `REDIS_PASSWORD` = `WdpPbYa9uFq2i80J`

### **API Keys**
- `GOOGLE_MAPS_API_KEY` = `AIzaSyDsR974k1b4C4zkmFjFFYR3eSX3HWJ66A0`
- `WEATHER_API_KEY` = `your_weather_api_key_here` (or leave empty for now)

### **Email** (update with your details)
- `MAIL_HOST` = `smtp.gmail.com`
- `MAIL_PORT` = `587`
- `MAIL_USERNAME` = `your-email@gmail.com`
- `MAIL_PASSWORD` = `your-app-password`

### **Monitoring**
- `GRAFANA_ADMIN_PASSWORD` = `aWW07cNxx6DJ`
- `ADMIN_EMAIL` = `admin@mapitedu.nl`

## ✅ Step 2: Set Up Server

1. **SSH to your server**:
   ```bash
   ssh root@142.93.136.145
   ```

2. **Run the quick setup script**:
   ```bash
   # Download and run server setup
   curl -L https://raw.githubusercontent.com/Marvel-School/mapit/main/server-setup.sh -o server-setup.sh
   chmod +x server-setup.sh
   ./server-setup.sh
   ```

3. **If the script fails, run manual setup**:
   ```bash
   # Update system
   apt update && apt upgrade -y
   
   # Install Docker
   curl -fsSL https://get.docker.com -o get-docker.sh
   sh get-docker.sh
   
   # Install Docker Compose
   apt install -y docker-compose-plugin
   
   # Create deploy user
   useradd -m -s /bin/bash deploy
   usermod -aG docker deploy
   usermod -aG sudo deploy
   
   # Set up SSH for deploy user
   mkdir -p /home/deploy/.ssh
   echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDDzqYEfKvrj5x73GFC+NJ2BLzO0IzXYoloByep+fo/xlyNxGDU3t9aLLl2dbu06LwQA4uWGEHkUH8D3cSpUwULd7bL7UU5aOYPiJHog5F2p8Ys9pBRNUzEjBMX2xWEA2TTzLxyWRJTuuCk/DU/PVOnaXdCF1YVAo5hWlAtr3jz0QZ5gbsUox1Z/MpTTn7+2ELW/Jxwlm9hkkzFJtG5Hu3XPHUBTEvBOXPeW0r0yEFWN14IyvlTHrPxlq6bd9/1junRWnBE1pArx5qFu2OyA4teNneKOT0S8dwlwCAkg0xVZ99DzuLuMT01cGT+utLO3xVD3e569kZzOsR5wbe6zOldIBbOC02RlOyvnT6ropwkUlA+SaGxdkS26DI8WJndktGbvDoYfLZ6aK9EgQGhrjEC0kfI1EbgyErMwJSyySNe8xLtJkc8wIj2ouN+8lNiZvRq3yqPRuNRlqv3jVZFiT/rwrMbkGSKIhqSxNifVB39GIVCxxqhztLu2F9OcATtZ0IKZRJo5QwaWtMrDZHW3lVEM+xLVdT96PviQxAuISNS0Q78DaLO4FoutD13W3RByxbgK6TBQRsAjVLoNY0oSJtSh1juzFJwg+rrKqlhy3tB7pqozZrCDiUIPsxaWtG4quhpNt94p33brN9MxQSMYx6ZOESotOeSbs7KVys0IHL0Pw== deploy@mapitedu.nl" > /home/deploy/.ssh/authorized_keys
   
   chmod 700 /home/deploy/.ssh
   chmod 600 /home/deploy/.ssh/authorized_keys
   chown -R deploy:deploy /home/deploy/.ssh
   
   # Create deployment directory
   mkdir -p /opt/mapit
   chown deploy:deploy /opt/mapit
   
   echo "Server setup complete!"
   ```

## ✅ Step 3: Test SSH Connection

Test that you can connect as the deploy user:
```bash
ssh deploy@142.93.136.145
```

## ✅ Step 4: Deploy

Once GitHub secrets are set up and server is ready:

```bash
# From your local machine
cd c:\Projects\mapit
git add .
git commit -m "Fix deployment workflow"
git push origin main
```

## ✅ Step 5: Check Results

1. **Watch GitHub Actions**: Go to your repo → Actions tab
2. **Check your site**: http://142.93.136.145
3. **Health check**: http://142.93.136.145/health

## 🆘 If Still Having Issues

1. **Check GitHub Actions logs** for specific errors
2. **SSH to server and check**:
   ```bash
   ssh deploy@142.93.136.145
   cd /opt/mapit/current
   sudo docker compose -f docker-compose.production.yml ps
   sudo docker compose -f docker-compose.production.yml logs
   ```

---

**Next**: Once this basic deployment works, we can set up SSL/HTTPS and domain configuration for mapitedu.nl!
