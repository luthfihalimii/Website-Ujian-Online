#!/bin/bash

# Start Laravel Octane with FrankenPHP in watch mode (auto-reload on file changes)
echo "Starting Laravel Octane with FrankenPHP (Development Mode with Watch)..."
echo "Server will be available at: http://127.0.0.1:8000"
echo "Auto-reload is enabled - the server will restart when you modify files"
echo "Press Ctrl+C to stop the server"
echo ""

# Run Octane with FrankenPHP in watch mode
php artisan octane:frankenphp --port=8000 --workers=auto --max-requests=500 --watch