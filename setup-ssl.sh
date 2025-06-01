#!/bin/bash
# SSL Setup Script for MapIt Production

set -e

echo "🔒 Setting up SSL certificates for mapitedu.nl..."

# Create directories
mkdir -p /opt/mapit/current/docker/production/ssl
mkdir -p /var/www/certbot

# Stop any existing containers
docker compose -f /opt/mapit/current/docker-compose.production.yml down || true

# Start containers without SSL first
echo "📦 Starting containers in HTTP-only mode..."
docker compose -f /opt/mapit/current/docker-compose.production.yml up -d nginx php mysql redis

# Wait for nginx to be ready
echo "⏳ Waiting for nginx to be ready..."
sleep 30

# Test if the site is accessible via HTTP
echo "🌐 Testing HTTP connectivity..."
if curl -f -s http://mapitedu.nl/ > /dev/null; then
    echo "✅ HTTP site is accessible"
else
    echo "❌ HTTP site is not accessible - check nginx logs"
    docker logs mapit_nginx_prod
    exit 1
fi

# Get SSL certificates
echo "🔐 Requesting SSL certificates..."
docker compose -f /opt/mapit/current/docker-compose.production.yml run --rm certbot

# Check if certificates were created
if [ -d "/var/lib/docker/volumes/mapit_certbot_conf/_data/live/mapitedu.nl" ]; then
    echo "✅ SSL certificates obtained successfully"
    
    # Now switch to HTTPS configuration
    echo "🔄 Switching to HTTPS configuration..."
    
    # Copy the HTTPS configuration
    cp /opt/mapit/current/docker/production/nginx/default.conf /tmp/https-config.conf
    
    # Update nginx to use HTTPS config
    docker cp /tmp/https-config.conf mapit_nginx_prod:/etc/nginx/conf.d/default.conf
    
    # Reload nginx
    docker exec mapit_nginx_prod nginx -s reload
    
    echo "🚀 SSL setup complete!"
    echo "🌍 Your site should now be available at:"
    echo "   HTTP:  http://mapitedu.nl"
    echo "   HTTPS: https://mapitedu.nl"
    
else
    echo "❌ Failed to obtain SSL certificates"
    echo "🔍 Check certbot logs:"
    docker logs mapit_certbot
    exit 1
fi

# Setup auto-renewal
echo "🔄 Setting up certificate auto-renewal..."
cat > /etc/cron.d/certbot-renew << 'EOF'
0 12 * * * root cd /opt/mapit/current && docker compose -f docker-compose.production.yml run --rm certbot renew --quiet && docker exec mapit_nginx_prod nginx -s reload
EOF

echo "✅ Auto-renewal configured"
echo "🎉 SSL setup completed successfully!"
