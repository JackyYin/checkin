#!/bin/bash
exec php /var/www/html/artisan queue:work --sleep=3 --tries=3 --daemon >> /var/log/laravel-worker.log 2>&1
