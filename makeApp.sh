#!/bin/bash

# if you are having webpack problems try npm install (necessary anyway after make distclean)

if ! type php > /dev/null; then
    echo "----"
    echo "php is missing, perhaps run through container/makeAppContainer.sh?"
    echo "----"
fi

php build/tools/composer.phar update
make
