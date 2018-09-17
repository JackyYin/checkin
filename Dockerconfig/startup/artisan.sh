#!/bin/bash
set -e
exec php /var/www/html/artisan config:cache
exec php /var/www/html/artisan route:cache
