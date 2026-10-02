#!/bin/bash
set -e

echo "🚀 Deploying Budget App to production (budget.kilograms.lv)..."

# Ensure local changes are pushed
echo "📦 Pushing to GitHub (main)..."
git push origin main

# Execute remote deploy on hetzner-test
echo "🌐 Pulling and updating on server..."
ssh hetzner-test << 'EOF'
    set -e
    cd /var/www/budget.kilograms.lv
    
    echo "⬇️  Pulling latest changes from git..."
    git pull origin main
    
    echo "⚡ Running migrations..."
    php artisan migrate --force
    
    echo "🧹 Clearing application cache..."
    php artisan optimize:clear
    
    echo "🔒 Fixing storage and cache permissions..."
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    
    echo "✅ Production deployment completed successfully!"
EOF
