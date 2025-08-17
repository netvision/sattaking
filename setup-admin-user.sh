#!/bin/bash

# Script to set up the default admin user for Satta King API
# Run this on your server after uploading the UserController.php

echo "Setting up default admin user for Satta King API..."

# Navigate to your API directory (update this path)
cd /path/to/your/api

# Create the default admin user
echo "Creating admin user (username: admin, password: admin123)..."
php yii user/create-admin

echo ""
echo "User setup complete!"
echo ""
echo "You can now login to the admin panel with:"
echo "Username: admin"
echo "Password: admin123"
echo ""
echo "Other useful commands:"
echo "  php yii user/list                    - List all users"
echo "  php yii user/reset-password admin newpass - Reset admin password"
echo "  php yii user/generate-token admin   - Generate new access token"
