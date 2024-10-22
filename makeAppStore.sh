# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

# if you are having webpack problems try npm install (necessary anyway after make distclean)

if ! type php > /dev/null; then
    echo "----"
    echo "php is missing, perhaps run through container/makeAppContainer.sh?"
    echo "----"
fi

if [ $1 = "y" ]; then
    composer update
else
    echo "Skipping composer update"
fi

composer i --no-dev

make appstore
