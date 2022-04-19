#!/bin/bash
#
# Container Remote Shell v1.0.0
#
#  usage ... container_rsock.sh host_name user_name user_password socket_file remote_soket_file
#
#  ex.1) for Docker
#        # ./container_rsock.sh docker.hogebar.jp docker passwd  /tmp/docker.sock
#        # docker -H unix:///tmp/docker.sock ps
#
#  ex.2) for Podman
#        # ./container_rsock.sh podman.hogebar.jp podman passwd  /tmp/podman.sock /var/run/podman/podman.sock
#        # podman-remote --url unix:///tmp/podman.sock ps
#

if [ -n "$SSH_PASSWORD" ]; then
    echo $SSH_PASSWORD
    exit 0
fi

#
if [ $# -lt 3 ]; then
    exit 1
fi

printf -v SSH_HOST '%q' "$1"
printf -v SSH_USER '%q' "$2"
printf -v SSH_PASS '%q' "$3"

#
if [ "$4" != "" ]; then
    printf -v LLSOCKET '%q' "$4"
else
    LLSOCKET=/tmp/lticontainer_${SSH_HOST}.sock
fi
#
if [ "$5" != "" ]; then
    printf -v RTSOCKET '%q' "$5"
else
    RTSOCKET=/var/run/docker.sock
fi

WEBGROUP=`groups`

#
export SSH_PASSWORD=$SSH_PASS
export SSH_ASKPASS=$0
export DISPLAY=:0.0

rm -f $LLSOCKET
ps ax | grep ssh | grep "${LLSOCKET}:${RTSOCKET}" | awk -F" " '{print $1}' | xargs kill -9 > /dev/null 2>&1

#
setsid ssh -oStrictHostKeyChecking=no -oServerAliveInterval=120 -oServerAliveCountMax=3 -fNL ${LLSOCKET}:${RTSOCKET} ${SSH_USER}@${SSH_HOST} 

#
CNT=0
while [ ! -e $LLSOCKET ]; do
    sleep 1
    CNT=`expr $CNT + 1`
    if [ $CNT -gt 5 ]; then
        exit 1
    fi
done

chmod g+rw $LLSOCKET
chgrp $WEBGROUP $LLSOCKET

