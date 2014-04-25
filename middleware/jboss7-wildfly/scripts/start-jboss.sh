#!/bin/bash

if [ "x$PATH_HOME" == "x" ]; then
  . /opt/install-env.sh
fi

echo "### $DIR_JBOSS STARTING... ###"
local_ip="`ifconfig|grep Mask|grep Bcast|awk '{print $2}'|sed 's/addr://'`";

### Get Host config name from argument, defualt is master ###
HOST_CONFIG="$1"
if [ "x$HOST_CONFIG" == "x" ]; then
  HOST_CONFIG="master"
fi

if [ "$HOST_CONFIG" != "master" ] && [ "x$2" == "x" ]; then
  echo "[ERROR] Missing argument of jboss.domain.master.address"
  echo "[Example] $0 $1 127.0.0.1"
  exit 1
fi

### Start JBoss ###
START_JBOSS="$PATH_HOME/$DIR_JBOSS/bin/domain.sh -Djboss.bind.address.management=$local_ip -Djboss.bind.address=$local_ip --host-config=cluster-host-$HOST_CONFIG.xml"
if [ "$HOST_CONFIG" != "master" ]; then
  START_JBOSS="$PATH_HOME/$DIR_JBOSS/bin/domain.sh -Djboss.bind.address.management=$local_ip -Djboss.bind.address=$local_ip -Djboss.domain.master.address=$2 --host-config=cluster-host-$HOST_CONFIG.xml"
fi

echo "[Example] $0 [master|node1|node2]"
echo "[RUN] $START_JBOSS"
$START_JBOSS 2>/dev/null 1>/dev/null &

### Print url and user name and password ###
echo "JBOSS: http://$local_ip:9990/"
USER_NAME=""
if [ "$HOST_CONFIG" == "master" ]; then
  USER_NAME="test"
else
  USER_NAME="$HOST_CONFIG"
fi
echo "JBOSS: User: $USER_NAME, Password: test1234"
success
echo
