#!/bin/bash

# install container
podman build --tag athenaeum-container .

# if you are having webpack problems try npm install (necessary anyway after make distclean)

podman run \
    --volume=../:/src/ \
    --workdir=/src \
    athenaeum-container:latest \
#    -i
#    makeApp.sh
#    /bin/bash  -c "pwd" #/src/makeApp.sh

#        "php build/tools/composer.phar update && make"
#make
