#!/bin/bash
cd /var/www/html && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:clear
