FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        openssl \
    && docker-php-ext-install pdo_pgsql pgsql \
    && a2enmod rewrite headers ssl \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n    Options -Indexes\n    Require all granted\n</Directory>\n' \
       > /etc/apache2/conf-available/security-lab.conf \
    && printf 'ServerName localhost\n' \
       > /etc/apache2/conf-available/servername.conf \
    && a2enconf security-lab servername \
    && mkdir -p /etc/apache2/ssl \
    && openssl req -x509 -nodes -newkey rsa:2048 -sha256 -days 825 \
        -keyout /etc/apache2/ssl/localhost.key \
        -out /etc/apache2/ssl/localhost.crt \
        -subj '/C=PE/ST=Local/L=Local/O=Service Desk FIIS/OU=Security Lab V2/CN=localhost' \
        -addext 'subjectAltName=DNS:localhost,IP:127.0.0.1' \
        -addext 'keyUsage=digitalSignature,keyEncipherment' \
        -addext 'extendedKeyUsage=serverAuth' \
    && rm -rf /var/lib/apt/lists/*

COPY php-v2.ini /usr/local/etc/php/conf.d/zz-security-lab-v2.ini
COPY apache-v2.conf /etc/apache2/sites-available/security-lab-v2.conf

RUN a2dissite 000-default.conf default-ssl.conf >/dev/null 2>&1 || true \
    && a2ensite security-lab-v2.conf \
    && apache2ctl configtest

WORKDIR /var/www/html
