#!/bin/bash
set -e
export HOME=/root
export COMPOSER_HOME=/root
exec sudo -H composer self-update
exec composer install
