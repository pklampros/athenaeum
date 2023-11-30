# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

podman run -it \
    --volume=../:/var/www/html/apps/athenaeum \
    --workdir=/var/www/html/apps/athenaeum \
     athenaeum-tester:latest \
     /bin/bash
