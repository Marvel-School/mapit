#!/bin/bash

echo "=== MapIt SSL Certificate Setup Script ==="
echo "This script will generate SSL certificates for mapitedu.nl"
echo ""

# Check if we're in the right directory
if [ ! -f "docker-compose.production.yml" ]; then
    echo "❌ Error: docker-compose.production.yml not found"
    echo "Please run this script from the mapit directory"
    exit 1
fi

echo "✅ Found docker-compose.production.yml"

# Check if containers are running
echo ""
echo "🔍 Checking container status..."
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Test ACME challenge endpoint
echo ""
echo "🧪 Testing ACME challenge endpoint..."
ACME_TEST=$(curl -s -o /dev/null -w "%{http_code}" "http://mapitedu.nl/.well-known/acme-challenge/test" 2>/dev/null || echo "000")

if [ "$ACME_TEST" = "404" ]; then
    echo "✅ ACME challenge path is accessible (404 for non-existent file is expected)"
    ACME_READY=true
elif [ "$ACME_TEST" = "403" ]; then
    echo "❌ ACME challenge path is blocked (403 Forbidden)"
    echo "Need to fix nginx configuration"
    ACME_READY=false
else
    echo "⚠️  Unexpected response code: $ACME_TEST"
    echo "Continuing anyway..."
    ACME_READY=true
fi

# Generate SSL certificates if ACME is ready
if [ "$ACME_READY" = true ]; then
    echo ""
    echo "🔐 Generating SSL certificates..."
    
    # Run certbot to generate certificates
    docker run --rm \
        -v certbot_conf:/etc/letsencrypt \
        -v certbot_www:/var/www/certbot \
        certbot/certbot certonly \
        --webroot \
        --webroot-path=/var/www/certbot \
        --email admin@mapitedu.nl \
        --agree-tos \
        --no-eff-email \
        -d mapitedu.nl \
        -d www.mapitedu.nl \
        --non-interactive

    if [ $? -eq 0 ]; then
        echo "✅ SSL certificates generated successfully!"
        
        # Switch to HTTPS configuration
        echo ""
        echo "🔄 Switching to HTTPS configuration..."
        
        # Update docker-compose to use HTTPS config
        sed -i 's|http-only.conf|https.conf|g' docker-compose.production.yml
        
        # Restart nginx to load new config
        docker-compose -f docker-compose.production.yml restart nginx
        
        echo "✅ Switched to HTTPS configuration"
        echo ""
        echo "🎉 SSL setup complete!"
        echo "🌐 Your site should now be accessible at: https://mapitedu.nl"
        
    else
        echo "❌ SSL certificate generation failed"
        echo "Check the certbot logs for details"
    fi
else
    echo ""
    echo "❌ Cannot proceed with SSL setup - ACME challenge path not accessible"
    echo "Please fix the nginx configuration first"
fi

echo ""
echo "=== Script Complete ==="
