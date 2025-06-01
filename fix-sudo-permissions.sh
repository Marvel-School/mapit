#!/bin/bash

# 🔧 Automated Sudo Permission Fix for Deploy User
# This script configures passwordless sudo for the deploy user

set -e

echo "🔧 Configuring passwordless sudo for deploy user..."

# Check if we're running as root
if [[ $EUID -eq 0 ]]; then
    echo "✅ Running as root - proceeding with configuration"
    
    # Create sudoers file for deploy user
    echo "deploy ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/deploy
    chmod 440 /etc/sudoers.d/deploy
    
    echo "✅ Created /etc/sudoers.d/deploy with correct permissions"
    
    # Verify the configuration
    echo "🔍 Verifying sudo configuration for deploy user..."
    sudo -l -U deploy
    
    # Test sudo access
    echo "🧪 Testing sudo access for deploy user..."
    if sudo -u deploy sudo whoami >/dev/null 2>&1; then
        echo "✅ Deploy user can successfully use sudo without password"
    else
        echo "❌ Deploy user sudo test failed"
        exit 1
    fi
    
    echo "🎉 Sudo permissions configured successfully!"
    
elif id deploy >/dev/null 2>&1; then
    echo "ℹ️ Running as non-root user, attempting to configure with current sudo access..."
    
    # Try to configure with current sudo
    echo "deploy ALL=(ALL) NOPASSWD:ALL" | sudo tee /etc/sudoers.d/deploy >/dev/null
    sudo chmod 440 /etc/sudoers.d/deploy
    
    echo "✅ Attempted to configure sudo permissions"
    echo "🔍 Please verify by running: sudo -u deploy sudo whoami"
    
else
    echo "❌ Deploy user does not exist. Please create it first:"
    echo "   sudo useradd -m -s /bin/bash deploy"
    echo "   sudo mkdir -p /home/deploy/.ssh"
    echo "   sudo chown deploy:deploy /home/deploy/.ssh"
    echo "   sudo chmod 700 /home/deploy/.ssh"
    exit 1
fi

echo ""
echo "📋 Next steps:"
echo "1. Test deployment: git push origin main"
echo "2. Monitor deployment in GitHub Actions"
echo "3. Access your app at: http://142.93.136.145"
echo "4. Set up domain DNS: mapitedu.nl → 142.93.136.145"
