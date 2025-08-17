#!/bin/bash

# Lottery System Cron Job Setup Script
# This script helps set up cron jobs for auto-generating results

echo "=== Lottery System Cron Job Setup ==="
echo ""

# Get current directory
PROJECT_DIR=$(pwd)

echo "Project directory: $PROJECT_DIR"
echo ""

# Create log directory
mkdir -p "$PROJECT_DIR/runtime/logs"

echo "Suggested cron jobs to add to your crontab:"
echo "==========================================="
echo ""

echo "# Auto-generate results every minute"
echo "* * * * * cd $PROJECT_DIR && php yii result/auto-generate >> $PROJECT_DIR/runtime/logs/auto-generate.log 2>&1"
echo ""

echo "# Lock expired results every 5 minutes"  
echo "*/5 * * * * cd $PROJECT_DIR && php yii result/lock-expired >> $PROJECT_DIR/runtime/logs/lock-expired.log 2>&1"
echo ""

echo "# Optional: Daily stats report at 11:59 PM"
echo "59 23 * * * cd $PROJECT_DIR && php yii result/stats >> $PROJECT_DIR/runtime/logs/daily-stats.log 2>&1"
echo ""

echo "To add these to your crontab:"
echo "1. Run: crontab -e"
echo "2. Copy and paste the lines above"
echo "3. Save and exit"
echo ""

echo "To test the commands manually:"
echo "=============================="
echo "cd $PROJECT_DIR"
echo "php yii result/auto-generate"
echo "php yii result/stats"
echo ""

echo "Log files will be created in: $PROJECT_DIR/runtime/logs/"
echo ""

echo "Setup complete!"
