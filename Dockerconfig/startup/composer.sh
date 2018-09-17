#!/bin/bash
set -e
export HOME=/root
export COMPOSER_HOME=/root
cd /var/www/html
exec composer install
