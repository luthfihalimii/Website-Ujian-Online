#!/bin/bash

# Start Laravel Octane with FrankenPHP
echo "Starting Laravel Octane with FrankenPHP..."
echo "Server will be available at: http://127.0.0.1:8000"
echo "Press Ctrl+C to stop the server"
echo ""

# Run Octane with FrankenPHP
php artisan octane:frankenphp --port=8000 --workers=auto --max-requests=500