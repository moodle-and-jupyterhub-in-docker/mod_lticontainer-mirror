#!/bin/bash
#
# Container Remote Check Shell v1.0.0
#
#  usage ... check_rsock.sh <docker/podman> host_name user_name user_password command
#
#       ex.) ./check_rsock.sh docker jupyterhub.nsl.tuis.ac.jp docker pass images
#
#

if [ $# -lt 5 ]; then
    echo "usage .... "$0" <docker/podman> host user password command"
    exit 1
fi

CTRSYS=$1
if [ "$1"!="podman" ]; then
    CTRSYS="docker"
fi
HOST=$2
USER=$3
PASS=$4
COMD=$5


if [ "$CTRSYS"="docker" ]; then
    ./container_rsock.sh $HOST $USER $PASS /tmp/docker_tmp.sock
    docker -H unix:///tmp/docker_tmp.sock $COMD
else
    ./container_rsock.sh $HOST $USER $PASS /tmp/podmant_tmp.sock /var/run/podman/podman.sock
    podman-remote --url unix:///tmp/podman_tmp.sock $CMD
fi

