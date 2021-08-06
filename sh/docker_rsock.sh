#!/bin/bash
#
#  ./docker_rsock.sh docker.hogebar.jp docker passwd  /tmp
#  docker -H unix:///tmp/mdlds_docker.hogebar.jp.sock ps
#

if [ -n "$PASSWORD" ]; then
    echo "$PASSWORD"
    exit 0
fi

#
#
if [ $# -lt 3 ]; then
    exit 1
fi

printf -v SSH_HOST '%q' "$1"
printf -v SSH_USER '%q' "$2"
printf -v SSH_PASS '%q' "$3"

if [ "$4" != "" ]; then
    printf -v SOCK_DIR '%q' "$4"
else
    SOCK_DIR='/tmp'
fi

WEBGROUP=`groups`
LLSOCKET=${SOCK_DIR}/mdlds_${SSH_HOST}.sock
RTSOCKET=/var/run/docker.sock

#
export PASSWORD=$SSH_PASS
export SSH_ASKPASS=$0
export DISPLAY=:0.0

rm -f $LLSOCKET
#
setsid ssh -o StrictHostKeyChecking=no -fNL $LLSOCKET:$RTSOCKET $SSH_USER@$SSH_HOST 

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
