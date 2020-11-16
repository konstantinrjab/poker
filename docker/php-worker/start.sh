#bash
php-fpm &
/usr/bin/supervisord -n -c /etc/supervisord.conf
