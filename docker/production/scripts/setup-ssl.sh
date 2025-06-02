#!/bin/bash

# SSL Certificate Setup Script for MapIt Production
# This script sets up Let's Encrypt SSL certificates for mapitedu.nl

set -e

DOMAIN="mapitedu.nl"
EMAIL="admin@mapitedu.nl"
CERTBOT_DIR="/opt/mapit/current/ssl/certbot"

echo "Setting up SSL certificates for ${DOMAIN}..."

# Create necessary directories
mkdir -p ${CERTBOT_DIR}/conf
mkdir -p ${CERTBOT_DIR}/www
mkdir -p ${CERTBOT_DIR}/logs

# Initial certificate request
docker run --rm \
    -v ${CERTBOT_DIR}/conf:/etc/letsencrypt \
    -v ${CERTBOT_DIR}/www:/var/www/certbot \
    -v ${CERTBOT_DIR}/logs:/var/log/letsencrypt \
    certbot/certbot \
    certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email ${EMAIL} \
    --agree-tos \
    --no-eff-email \
    -d ${DOMAIN} \
    -d www.${DOMAIN}

# Create renewal script
cat > /opt/mapit/scripts/renew-ssl.sh << 'EOF'
#!/bin/bash
docker run --rm \
    -v /opt/mapit/current/ssl/certbot/conf:/etc/letsencrypt \
    -v /opt/mapit/current/ssl/certbot/www:/var/www/certbot \
    -v /opt/mapit/current/ssl/certbot/logs:/var/log/letsencrypt \
    certbot/certbot \
    renew \
    --webroot \
    --webroot-path=/var/www/certbot

# Reload nginx after renewal
cd /opt/mapit/current && docker compose exec nginx nginx -s reload
EOF

chmod +x /opt/mapit/scripts/renew-ssl.sh

# Add to crontab for automatic renewal
(crontab -l 2>/dev/null; echo "0 2 * * * /opt/mapit/scripts/renew-ssl.sh >> /var/log/ssl-renewal.log 2>&1") | crontab -

echo "SSL certificates setup completed!"
echo "Certificates will be automatically renewed every day at 2 AM"
