FROM ubuntu:16.04
EXPOSE 80
USER root
MAINTAINER jackyyin

RUN apt-get update \
    && apt-get install -y locales \
    && locale-gen en_US.UTF-8

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

RUN apt-get update \
    && apt-get install -y curl wget zip unzip git tar vim tmux sudo software-properties-common apache2 supervisor\
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php7.2 \
    && apt-get install -y php7.2-pdo php7.2-bcmath php7.2-fpm php7.2-gd php7.2-mysql \
       php7.2-pgsql php7.2-imap php7.2-memcached php7.2-mbstring php7.2-xml php7.2-zip\
       php-pear php7.2-dev\ 
    && mkdir /run/php \
    && apt-get remove -y --purge software-properties-common \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && mkdir -p /var/www/html/vendor /var/log/supervisor /var/log/php-fpm

#install Xdebug
RUN pecl install xdebug

# composer install
WORKDIR /var/www/html
RUN wget https://getcomposer.org/composer.phar -O /usr/local/bin/composer \
    && chmod 755 /usr/local/bin/composer

# configurations
COPY ./Dockerconfig/supervisord1.conf  /etc/supervisor/supervisord.conf
COPY ./Dockerconfig/supervisord2.conf  /etc/supervisor/conf.d/supervisord.conf
COPY ./Dockerconfig/xdebug.ini         /etc/php/7.2/mods-available/xdebug.ini
RUN sed -i '/DocumentRoot/ s#/html#/html/public#g' /etc/apache2/sites-available/000-default.conf \
    && sed -i '/DocumentRoot/ s#/html#/html/public#g' /etc/apache2/sites-available/default-ssl.conf \
    && a2enmod rewrite \
    && phpenmod xdebug \ 
    && service apache2 restart

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
