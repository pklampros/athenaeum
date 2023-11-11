#!/bin/bash

# if you are having webpack problems try npm install (necessary anyway after make distclean)

if ! type php > /dev/null; then
    echo "----"
    echo "php is missing, perhaps run through container/makeAppContainer.sh?"
    echo "----"
fi

if [ $1 = "y" ]; then
    php build/tools/composer.phar update
else
    echo "Skippint composer update"
fi
make
