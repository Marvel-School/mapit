#!/bin/bash

# 🔧 Automated Sudo Permission Fix for Deploy User
# This script configures passwordless sudo for the deploy user

set -e

echo "🔧 Configuring passwordless sudo for deploy user..."

# Check if we're running as root
if [[ $EUID -eq 0 ]]; then
    echo "✅ Running as root - proceeding with configuration"
    
    # Ensure deploy user exists
    if ! id deploy >/dev/null 2>&1; then
        echo "📝 Creating deploy user..."
        useradd -m -s /bin/bash deploy
        mkdir -p /home/deploy/.ssh
        chown deploy:deploy /home/deploy/.ssh
        chmod 700 /home/deploy/.ssh
        
        # Copy SSH keys from root if they exist
        if [ -f /root/.ssh/authorized_keys ]; then
            cp /root/.ssh/authorized_keys /home/deploy/.ssh/authorized_keys
            chown deploy:deploy /home/deploy/.ssh/authorized_keys
            chmod 600 /home/deploy/.ssh/authorized_keys
            echo "✅ SSH keys copied to deploy user"
        fi
    fi
    
    # Create sudoers file for deploy user
    echo "deploy ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/deploy
    chmod 440 /etc/sudoers.d/deploy
    
    echo "✅ Created /etc/sudoers.d/deploy with correct permissions"
    
    # Verify the configuration
    echo "🔍 Verifying sudo configuration for deploy user..."
    
    # Check sudoers syntax
    if visudo -c -f /etc/sudoers.d/deploy >/dev/null 2>&1; then
        echo "✅ Sudoers file syntax is correct"
    else
        echo "❌ Sudoers file syntax error"
        exit 1
    fi
    
    # Test sudo access
    echo "🧪 Testing sudo access for deploy user..."
    if sudo -u deploy sudo whoami >/dev/null 2>&1; then
        echo "✅ Deploy user can successfully use sudo without password"
    else
        echo "❌ Deploy user sudo test failed"
        exit 1
    fi
    
    # Test Docker access (if Docker is installed)
    if command -v docker >/dev/null 2>&1; then
        if sudo -u deploy sudo docker --version >/dev/null 2>&1; then
            echo "✅ Deploy user can use Docker with sudo"
        else
            echo "⚠️ Deploy user cannot use Docker with sudo (Docker may not be installed yet)"
        fi
    fi
    
    echo "🎉 Sudo permissions configured successfully!"
    
elif id deploy >/dev/null 2>&1; then
    echo "ℹ️ Running as deploy user, attempting to configure with current sudo access..."
    
    # Test if we can sudo at all
    if ! sudo whoami >/dev/null 2>&1; then
        echo "❌ Current user cannot use sudo. Need root access to fix this."
        echo "💡 Try one of these methods:"
        echo "   1. SSH as root: ssh root@your-server-ip"
        echo "   2. Use DigitalOcean Console as root"
        echo "   3. Contact DigitalOcean support for root access"
        exit 1
    fi
    
    # Try to configure with current sudo
    echo "deploy ALL=(ALL) NOPASSWD:ALL" | sudo tee /etc/sudoers.d/deploy >/dev/null
    sudo chmod 440 /etc/sudoers.d/deploy
    
    echo "✅ Attempted to configure sudo permissions"
    
    # Test the configuration
    echo "🧪 Testing new sudo configuration..."
    if sudo whoami >/dev/null 2>&1; then
        echo "✅ Passwordless sudo is now working"
    else
        echo "❌ Passwordless sudo test failed"
        exit 1
    fi
    
else
    echo "❌ Deploy user does not exist and we're not running as root."
    echo "💡 Please run this script as root or create the deploy user first:"
    echo "   sudo useradd -m -s /bin/bash deploy"
    echo "   sudo mkdir -p /home/deploy/.ssh"
    echo "   sudo chown deploy:deploy /home/deploy/.ssh"
    echo "   sudo chmod 700 /home/deploy/.ssh"
    exit 1
fi

echo ""
echo "📋 Next steps:"
echo "1. Test sudo: sudo -u deploy sudo whoami (should return 'root')"
echo "2. Test deployment: git push origin main"
echo "3. Monitor deployment in GitHub Actions"
echo "4. Access your app at: http://142.93.136.145"
echo "5. Set up domain DNS: mapitedu.nl → 142.93.136.145"
