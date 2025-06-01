# GitHub Secrets Configuration Guide

## Required GitHub Secrets

### Server Configuration
```
SSH_PRIVATE_KEY=your-ssh-private-key-content
SSH_USER=deploy
PRODUCTION_HOST=your-digitalocean-server-ip
```

### Database Secrets
```
DB_PASSWORD=your-strong-database-password
DB_ROOT_PASSWORD=your-strong-mysql-root-password
```

### API Keys (move from .env to secrets)
```
GOOGLE_MAPS_API_KEY=AIzaSyDsR974k1b4C4zkmFjFFYR3eSX3HWJ66A0
WEATHER_API_KEY=your-weather-api-key
```

### Security Keys
Generate strong keys using PowerShell:
```powershell
# Generate JWT Secret
$jwtSecret = [System.Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes((1..32 | ForEach {[char](Get-Random -Min 65 -Max 90)})))

# Generate Encryption Key  
$encryptionKey = [System.Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes((1..32 | ForEach {[char](Get-Random -Min 65 -Max 90)})))

Write-Host "JWT_SECRET=$jwtSecret"
Write-Host "ENCRYPTION_KEY=$encryptionKey"
```

### Email Configuration
```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### Monitoring
```
GRAFANA_ADMIN_PASSWORD=your-grafana-password
ADMIN_EMAIL=admin@mapitedu.nl
```

## How to Add Secrets

1. Go to your GitHub repository
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Add each secret with the exact name and value above

## Verification

After adding secrets, you can verify by:
1. Going to the Actions tab
2. Running the workflow manually
3. Checking that no secrets appear in logs (they should show as ***)

## Security Best Practices

- ✅ Never commit real API keys to git
- ✅ Use strong, unique passwords
- ✅ Enable 2FA on all accounts
- ✅ Regularly rotate secrets
- ✅ Monitor access logs

## Current Status

- [ ] SSH keys generated and configured
- [ ] DigitalOcean server created
- [ ] Domain DNS configured
- [ ] GitHub secrets added
- [ ] First deployment completed
- [ ] SSL certificates configured
- [ ] Monitoring setup verified
