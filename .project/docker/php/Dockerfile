FROM in2code/php-dev:8.2-fpm

COPY zz_xdebug.ini /usr/local/etc/php/conf.d/zz_xdebug.ini

USER root

# Install CURL extension
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

USER www-data
