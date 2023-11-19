#!/bin/bash

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $SCRIPT_DIR

#podman start athenaeum-builder:latest

podman run \
    --volume=../:/src/ \
    --workdir=/src \
    athenaeum-builder:latest \
