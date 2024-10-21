# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

# install container
podman build -t athenaeum-builder \
--build-arg PHP_EXTRA_CONFIGURE_ARGS="--enable-mailparse" \
-<< EOF
FROM php:8.2-cli
RUN apt-get update && apt-get install -y libzip-dev libpng-dev git
RUN pecl install mailparse     && docker-php-ext-enable mailparse && docker-php-ext-install zip gd
RUN curl -L "https://bit.ly/n-install" | bash -s -- -y lts
ENV COMPOSER_ALLOW_SUPERUSER=1
#ENV build_tools_directory="/usr/bin"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#RUN curl -sS https://getcomposer.org/installer | php -d memory_limit=4096M && \
#    mv composer.phar /usr/local/bin && \\
#    php -d memory_limit=4096M /usr/local/bin/composer.phar install --prefer-dist

#CMD mv /usr/local/bin/composer.phar /src/build/ && /src/makeApp.sh n
ENV PATH="/root/n/bin:${PATH}"
RUN npm i -g npm-check
CMD /src/makeApp.sh n
EOF
