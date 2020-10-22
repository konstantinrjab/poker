###Composer
docker run --rm --interactive --tty --volume ${PWD}:/app composer install --ignore-platform-reqs --no-scripts
docker run --rm --interactive --tty --volume ${PWD}:/app composer dump-autoload -o

###Cache clear
docker exec poker_php bash -c "php artisan cache:clear && php artisan route:cache && php artisan config:clear && php artisan view:clear"
docker exec poker_php bash -c "php /var/www/vendor/phpunit/phpunit/phpunit --migrate-configuration"
###Docker compose
DOCKER_USER=$(id -u):$(id -g) docker-compose -f "./docker/docker-compose.yml" up -d --build
DOCKER_USER=$(id -u):$(id -g) docker-compose -f "./docker/docker-compose.yml" down

###Run BE locally (Win)
1. Copy .env.example to .env
2. docker run --rm --interactive --tty --volume ${PWD}:/app composer install --ignore-platform-reqs --no-scripts
3. docker-compose -f "./docker/docker-compose.yml" up -d --build
4. api url http://localhost:80/
5. if something is wrong try to clear cache docker exec poker_php bash -c "php artisan cache:clear && php artisan route:cache && php artisan config:clear && php artisan view:clear"

`###Connect to docker redis
docker exec -it poker_redis sh
redis-cli
