#!/bin/bash

# Quick CORS fix script - run this directly on your server
# This removes the duplicate CORS configuration from web.php

echo "Fixing duplicate CORS headers..."

# Backup the current config
cp /path/to/your/api/config/web.php /path/to/your/api/config/web.php.backup

# Remove the duplicate CORS response configuration
# This sed command removes the problematic response configuration block
sed -i '/'\''response'\'' => \[/,/\],/d' /path/to/your/api/config/web.php

echo "CORS fix applied. Restarting web server..."

# Restart web server
if systemctl is-active --quiet apache2; then
    sudo systemctl restart apache2
    echo "Apache2 restarted"
elif systemctl is-active --quiet nginx; then
    sudo systemctl restart nginx
    sudo systemctl restart php-fpm
    echo "Nginx and PHP-FPM restarted"
fi

echo "CORS fix complete! Test your frontend now."
