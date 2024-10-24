# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

#podman start athenaeum-builder:latest

podman run \
    --volume=../:/src/ \
    --workdir=/src \
    athenaeum-builder:latest \
    /src/makeApp.sh y
