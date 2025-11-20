#!/bin/bash
# Maintenance Reminders Shell Script for Linux/Hostinger
# This script runs the PHP maintenance reminder command

# Set the path to your HandyCRM installation
HANDYCRM_PATH="/home/username/public_html/crm"

# Change to the HandyCRM directory
cd "$HANDYCRM_PATH"

# Log file location
LOG_FILE="$HANDYCRM_PATH/logs/maintenance-reminders.log"

# Create logs directory if it doesn't exist
mkdir -p "$HANDYCRM_PATH/logs"

# Add timestamp to log
echo "=== Maintenance Reminders Started at $(date) ===" >> "$LOG_FILE"

# Run the PHP script and capture output
php maintenance-reminders.php 7 >> "$LOG_FILE" 2>&1

# Check exit code
if [ $? -eq 0 ]; then
    echo "Maintenance reminders completed successfully at $(date)" >> "$LOG_FILE"
else
    echo "Maintenance reminders failed at $(date)" >> "$LOG_FILE"
fi

echo "" >> "$LOG_FILE"