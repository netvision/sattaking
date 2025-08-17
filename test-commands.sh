#!/bin/bash

# Test script for lottery console commands

echo "=== Testing Lottery Console Commands ==="
echo ""

cd "$(dirname "$0")"

echo "System timezone information:"
echo "============================"
echo "System date: $(date)"
echo "System timezone: $(date +%Z)"
echo ""

echo "1. Testing result/stats command (should show IST time):"
echo "====================================================="
php yii result/stats
echo ""

echo "2. Testing result/auto-generate command:"
echo "======================================="
php yii result/auto-generate
echo ""

echo "Test complete!"
echo ""
echo "If timezone shows 'Asia/Kolkata' and time looks correct, setup is working!"
echo "If you see any errors above, check:"
echo "- PHP is installed and accessible"
echo "- Database connection is working"  
echo "- All required models exist"
