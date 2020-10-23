#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
#apt-get update -yqq
#apt-get install git -yqq

apk add --no-cache zip libzip-dev
docker-php-ext-configure zip --with-libzip
docker-php-ext-install zip
