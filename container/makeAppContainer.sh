# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

#podman start athenaeum-builder:latest

if [ "$1" == "clean" ]; then
    podman rm -f athenaeum-builder
    podman run \
        --volume=../:/src/ \
        --workdir=/src \
        --name=athenaeum-builder \
        athenaeum-builder:latest \
        /bin/bash -c "npm install && /src/makeApp.sh n"
else
    podman start athenaeum-builder
    podman exec athenaeum-builder /bin/bash -c "/src/makeApp.sh n"
fi
