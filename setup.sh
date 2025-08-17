#!/bin/bash

# Satta King API Setup and Test Script
# Run this script on your server after uploading the files

echo "==================================="
echo "Satta King API Setup & Test Script"
echo "==================================="

# Check if we're in the right directory
if [ ! -f "yii" ]; then
    echo "Error: Please run this script from your Yii2 project root directory"
    exit 1
fi

echo
echo "1. Setting up permissions..."
chmod +x yii
chmod 755 runtime/
chmod 755 web/assets/

echo "2. Running database migrations..."
php yii migrate --interactive=0

echo
echo "3. Testing console commands..."
echo "Running stats command..."
php yii result/stats

echo
echo "4. Generating test data..."
php yii result/generate-test

echo
echo "5. Testing API endpoints..."

# Test public endpoints
echo "Testing GET /info:"
curl -X GET "https://api.sattaking.app/info" -H "Content-Type: application/json"
echo

echo
echo "Testing GET /results/today:"
curl -X GET "https://api.sattaking.app/results/today" -H "Content-Type: application/json"
echo

echo
echo "Testing GET /slots/today:"
curl -X GET "https://api.sattaking.app/slots/today" -H "Content-Type: application/json"
echo

# Test admin login
echo
echo "Testing admin login:"
LOGIN_RESPONSE=$(curl -s -X POST "https://api.sattaking.app/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}')

echo $LOGIN_RESPONSE | jq '.'

# Extract token for further testing
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.access_token // empty')

if [ ! -z "$TOKEN" ]; then
    echo
    echo "Testing authenticated endpoint with token..."
    curl -X GET "https://api.sattaking.app/auth/me" \
      -H "Content-Type: application/json" \
      -H "Authorization: Bearer $TOKEN"
    echo
fi

echo
echo "==================================="
echo "Setup completed!"
echo "==================================="
echo
echo "Next steps:"
echo "1. Set up cron job for auto-generation:"
echo "   * * * * * cd $(pwd) && php yii result/auto-generate"
echo
echo "2. Update CORS origins in controllers for production"
echo
echo "3. Test your frontend integration"
echo
echo "API Documentation: $(pwd)/API_DOCS.md"
echo "==================================="
