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
#ENV build_tools_directory="/usr/bin"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


#RUN curl -sS https://getcomposer.org/installer | php -d memory_limit=4096M && \
#    mv composer.phar /usr/local/bin && \\
#    php -d memory_limit=4096M /usr/local/bin/composer.phar install --prefer-dist

#CMD mv /usr/local/bin/composer.phar /src/build/ && /src/makeApp.sh n
CMD /src/makeApp.sh n
EOF

podman run \
    --volume=../:/src/ \
    --workdir=/src \
    athenaeum-builder:latest \
