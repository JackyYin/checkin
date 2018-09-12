#!/bin/bash
exec find /var/www/html/storage -type d | xargs -n1 chmod 777 &&
    find /var/www/html/storage -type f | xargs -n1 chmod 666
