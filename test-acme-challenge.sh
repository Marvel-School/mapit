#!/bin/bash

echo "=== Testing ACME Challenge Endpoint ==="
echo "Testing mapitedu.nl/.well-known/acme-challenge/test"
echo ""

# Test the ACME challenge endpoint
echo "1. Testing ACME challenge path accessibility:"
curl -v "http://mapitedu.nl/.well-known/acme-challenge/test" 2>&1 | head -20

echo ""
echo "2. Testing with a test file creation:"
echo "This would create a test file in the certbot directory on the server"

echo ""
echo "3. Next steps after this works:"
echo "   - Run certbot to generate SSL certificates"
echo "   - Switch nginx to HTTPS configuration" 
echo "   - Update Docker Compose to use HTTPS config"

echo ""
echo "=== End Test ==="
