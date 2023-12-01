# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

# install container
podman build -t athenaeum-tester \
--build-arg PHP_EXTRA_CONFIGURE_ARGS="--enable-mailparse" \
-<< EOF
FROM php:8.2-cli
RUN apt-get update && apt-get install -y npm libzip-dev libpng-dev
RUN pecl install mailparse     && docker-php-ext-enable mailparse && docker-php-ext-install zip gd
RUN cd /var/www && git clone --recurse-submodules -j8 https://github.com/nextcloud/server html && \
    git config --global --add safe.directory /var/www/html && \
    cd html && git checkout stable27 && git submodule update --init --recursive
RUN cd /var/www/html && \
    ./occ maintenance:install --verbose --database=sqlite --database-name=nextcloud \
    --database-host=127.0.0.1 --database-port=$DB_PORT --database-user=root \
    --database-pass=rootpassword --admin-user admin --admin-pass admin
#RUN ../../occ app:enable --force athenaeum
ENV COMPOSER_ALLOW_SUPERUSER=1
#ENV build_tools_directory="/usr/bin"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#RUN curl -sS https://getcomposer.org/installer | php -d memory_limit=4096M && \
#    mv composer.phar /usr/local/bin && \\
#    php -d memory_limit=4096M /usr/local/bin/composer.phar install --prefer-dist

#CMD mv /usr/local/bin/composer.phar /src/build/ && /src/makeApp.sh n
CMD /src/makeApp.sh n
EOF
