# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

if [ "$1" == "clean" ]; then
    podman rm athenaeum-builder
    podman run -it --volume=../:/src/ \
        --workdir=/src \
        --name=athenaeum-builder \
        athenaeum-builder:latest \
        /bin/bash
else
    podman start athenaeum-builder
    podman exec -it athenaeum-builder /bin/bash
fi
