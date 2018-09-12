#!/bin/bash
exec php /var/www/html/artisan config:cache &&
    php /var/www/html/artisan route:cache
