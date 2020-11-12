FROM php:7.4

RUN apt-get update && apt-get install -y supervisor
RUN mkdir -p /var/log/supervisor

RUN pecl install xdebug-2.9.5 \
    && docker-php-ext-enable xdebug

COPY php.ini $PHP_INI_DIR

COPY supervisord.conf /etc/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisord.conf"]
