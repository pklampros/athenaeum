#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

# install container
podman build -t athenaeum-builder \
--build-arg PHP_EXTRA_CONFIGURE_ARGS="--enable-mailparse" \
-<< EOF
FROM php:8.2-cli
RUN apt-get update && apt-get install -y npm
RUN pecl install mailparse && docker-php-ext-enable mailparse
ENV COMPOSER_ALLOW_SUPERUSER=1
CMD /src/makeApp.sh
EOF

podman run \
    --volume=../:/src/ \
    --workdir=/src \
    athenaeum-builder:latest \
