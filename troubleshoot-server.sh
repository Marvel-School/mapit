#!/bin/bash

echo "=== MapIt Server Troubleshooting Script ==="
echo "This script will help diagnose connectivity and deployment issues"
echo ""

# Check if we're in the right directory
if [ ! -f "docker-compose.production.yml" ]; then
    echo "❌ Error: docker-compose.production.yml not found"
    echo "Please run this script from the mapit directory"
    exit 1
fi

echo "✅ Found docker-compose.production.yml"

# Check container status
echo ""
echo "🔍 Checking Docker container status..."
echo "Running containers:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo "All containers (including stopped):"
docker ps -a --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Check docker-compose services
echo ""
echo "🐳 Checking docker-compose services..."
docker-compose -f docker-compose.production.yml ps

# Check logs for any errors
echo ""
echo "📋 Checking recent container logs..."
echo ""
echo "=== Nginx logs ==="
docker logs mapit_nginx_prod --tail 20 2>/dev/null || echo "Nginx container not running"

echo ""
echo "=== PHP logs ==="
docker logs mapit_php_prod --tail 20 2>/dev/null || echo "PHP container not running"

echo ""
echo "=== MySQL logs ==="
docker logs mapit_mysql_prod --tail 10 2>/dev/null || echo "MySQL container not running"

# Check nginx configuration
echo ""
echo "🔧 Checking nginx configuration..."
docker exec mapit_nginx_prod nginx -t 2>/dev/null || echo "Cannot check nginx config - container not running"

# Check network connectivity
echo ""
echo "🌐 Checking network connectivity..."
echo "Local port 80:"
netstat -ln | grep :80 || echo "Port 80 not listening"

echo ""
echo "Local port 443:"
netstat -ln | grep :443 || echo "Port 443 not listening"

# Check firewall status
echo ""
echo "🔥 Checking firewall status..."
ufw status || echo "UFW not available"

# Check disk space
echo ""
echo "💾 Checking disk space..."
df -h

# Check memory usage
echo ""
echo "🧠 Checking memory usage..."
free -h

echo ""
echo "=== Troubleshooting Complete ==="
echo ""
echo "🔧 Common fixes:"
echo "1. If containers are not running: docker-compose -f docker-compose.production.yml up -d"
echo "2. If nginx config is invalid: check docker/production/nginx/ files"
echo "3. If port 80/443 not listening: check if containers are bound to ports"
echo "4. If out of disk space: clean up with docker system prune"
echo "5. If out of memory: restart containers or server"
