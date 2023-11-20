#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

podman run -it --volume=../:/src/ \
    --workdir=/src \
     athenaeum-builder:latest \
     /bin/bash
