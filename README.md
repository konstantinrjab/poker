###Composer
docker run --rm --interactive --tty --volume ${PWD}:/app composer install --ignore-platform-reqs --no-scripts
docker run --rm --interactive --tty --volume ${PWD}:/app composer dump-autoload -o

###Migrate
docker exec lum_php php artisan migrate:fresh --seed

###Cache clear
docker exec lum_php bash -c "php artisan cache:clear && php artisan route:cache && php artisan config:clear && php artisan view:clear"

###Docker compose
DOCKER_USER=$(id -u):$(id -g) docker-compose -f "./docker/docker-compose.yml" up -d --build
DOCKER_USER=$(id -u):$(id -g) docker-compose -f "./docker/docker-compose.yml" down
