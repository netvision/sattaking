#!/bin/bash

# Simple deployment script to upload CORS fixes
# Run this from your local machine

echo "Uploading CORS configuration fixes..."

# Upload the updated web.php config
scp config/web.php your-server:/path/to/your/api/config/

# Upload updated controllers
scp controllers/AuthController.php your-server:/path/to/your/api/controllers/
scp controllers/SlotController.php your-server:/path/to/your/api/controllers/
scp controllers/ResultController.php your-server:/path/to/your/api/controllers/
scp controllers/InfoController.php your-server:/path/to/your/api/controllers/

echo "Files uploaded. Please restart your web server for changes to take effect."
echo ""
echo "Commands to run on your server:"
echo "sudo systemctl restart apache2"
echo "# OR"
echo "sudo systemctl restart nginx"
echo "sudo systemctl restart php-fpm"
