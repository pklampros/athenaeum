# SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
# SPDX-License-Identifier: AGPL-3.0-or-later

#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

if [ "$1" == "clean" ]; then
    podman rm locahost/athenaeum-builder:latest
fi

#!/bin/bash

# Name of the container
CONTAINER_NAME="athenaeum-builder"

# Command to start the container if it's not running
# Replace this with your actual podman run command
START_COMMAND="podman run -it --volume=../:/src/ \
        --workdir=/src \
        --name=${CONTAINER_NAME} \
        ${CONTAINER_NAME}:latest \
        /bin/bash"

# Check if the container is running
if podman ps --filter "name=^${CONTAINER_NAME}$" --filter "status=running" --format "{{.Names}}" | grep -q "^${CONTAINER_NAME}$"; then
    echo "Container '$CONTAINER_NAME' is already running."
else
    echo "Container '$CONTAINER_NAME' is not running. Starting it..."
    
    # Check if the container exists but is not running
    if podman ps -a --filter "name=^${CONTAINER_NAME}$" --format "{{.Names}}" | grep -q "^${CONTAINER_NAME}$"; then
        podman start "$CONTAINER_NAME"
    else
        eval "$START_COMMAND"
    fi
fi

podman exec -it localhost/athenaeum-builder:latest /bin/bash
