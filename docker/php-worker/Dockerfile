FROM php:7.4-fpm

RUN apt-get update && apt-get install -y supervisor
RUN mkdir -p /var/log/supervisor

RUN pecl install xdebug-2.9.5 \
    && docker-php-ext-enable xdebug

COPY php.ini $PHP_INI_DIR

COPY supervisord.conf /etc/supervisord.conf

ADD start.sh /
RUN chmod +x /start.sh

CMD ["/start.sh"]

EXPOSE 9000

WORKDIR /var/www
