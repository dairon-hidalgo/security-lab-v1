FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        iputils-ping \
    && docker-php-ext-install pdo_pgsql pgsql \
    && a2enmod rewrite headers \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n    Options -Indexes\n    Require all granted\n</Directory>\n' \
       > /etc/apache2/conf-available/security-lab.conf \
    && a2enconf security-lab \
    && rm -rf /var/lib/apt/lists/*

COPY php-v1.ini /usr/local/etc/php/conf.d/zz-security-lab-v1.ini

WORKDIR /var/www/html