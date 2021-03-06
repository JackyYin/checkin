#Use phusion/baseimage as base image. To make your builds reproducible, make
# sure you lock down to a specific version, not to `latest`!
# See https://github.com/phusion/baseimage-docker/blob/master/Changelog.md for
# a list of version numbers.
FROM phusion/baseimage:master
USER root
MAINTAINER jackyyin
EXPOSE 80 443

# ...put your own build instructions here...
RUN apt-get update \
    && apt-get install -y locales  \
    && locale-gen en_US.UTF-8

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8
ENV HOME /root

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    netcat \
    nginx \
    software-properties-common \
    sudo \
    unzip\
    vim \
    wget \
    build-essential \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get install -y php7.2 \
    && apt-get install -y --no-install-recommends \
    php-memcached \
    php-pear \
    php7.2-fpm \
    php7.2-gd \
    php7.2-mbstring \
    php7.2-mysql \
    php7.2-xml \
    php7.2-dev \
    && pecl install redis \
    && apt-get remove -y --purge software-properties-common

# configurations
COPY ./Dockerconfig/nginx.conf /etc/nginx/nginx.conf
COPY ./Dockerconfig/www.conf   /etc/php/7.2/fpm/pool.d/www.conf
COPY ./Dockerconfig/php.ini    /etc/php/7.2/fpm/php.ini
COPY ./Dockerconfig/php.ini    /etc/php/7.2/cli/php.ini

# Use baseimage-docker's init system.
CMD ["/sbin/my_init"]

### Additional Deamon ###

# Adding additional nginx daemon
RUN mkdir /etc/service/nginx
COPY ./Dockerconfig/service/nginx.sh /etc/service/nginx/run
RUN chmod +x /etc/service/nginx/run

# Adding additional php-fpm daemon
RUN mkdir /etc/service/php-fpm /run/php
COPY ./Dockerconfig/service/php-fpm.sh /etc/service/php-fpm/run
RUN chmod +x /etc/service/php-fpm/run

# Adding additional laravel-worker daemon
RUN mkdir /etc/service/laravel-worker
COPY ./Dockerconfig/service/laravel-worker.sh /etc/service/laravel-worker/run
RUN chmod +x /etc/service/laravel-worker/run

### First Level Startup Process ###

RUN mkdir -p /etc/my_init.d
# Change File Permission
COPY ./Dockerconfig/startup/chmod.sh /etc/my_init.d/chmod.sh
RUN chmod +x /etc/my_init.d/chmod.sh

### Second Level Startup Process ###

# Laravel
COPY ./Dockerconfig/startup/artisan.sh /etc/rc.local
RUN chmod +x /etc/rc.local

WORKDIR /var/www/html

# composer
COPY composer.json .
COPY composer.lock .
RUN wget https://getcomposer.org/composer.phar -O /usr/local/bin/composer \
    && chmod 755 /usr/local/bin/composer \
    && composer install --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html
# Clean up APT when done.
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
