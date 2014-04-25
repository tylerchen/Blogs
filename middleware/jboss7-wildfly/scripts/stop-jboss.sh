#!/bin/bash

if [ "x$PATH_HOME" == "x" ]; then
  . /opt/install-env.sh
fi

echo "### Stopping $DIR_JBOSS ###"
local_ip="`ifconfig|grep Mask|grep Bcast|awk '{print $2}'|sed 's/addr://'`";

### Get Host config name from argument, defualt is master ###
HOST_CONFIG="$1"
if [ "x$HOST_CONFIG" == "x" ]; then
  HOST_CONFIG="master"
fi

### Shutdown JBoss ###
SHUTDOWN_JBOSS="$PATH_HOME/$DIR_JBOSS/bin/jboss-cli.sh --connect --controller=$local_ip:9999 /host=$HOST_CONFIG:shutdown"
echo "[Example] $0 [master|node1|node2]"
echo "[RUN] $SHUTDOWN_JBOSS"
$SHUTDOWN_JBOSS 2>/dev/null 1>/dev/null &

### Force Shutdown JBoss ###
count=0
until [ "`ps -ef|grep jboss|grep 'Process Controller'|wc -l`" == "0" ] || [ $count -gt 5 ]
do
  sleep 1;
  $SHUTDOWN_JBOSS 2>/dev/null 1>/dev/null;
  let count=$count+1;
done

if [ "`ps -ef|grep jboss|grep 'Process Controller'|wc -l`" != "0" ]; then
  echo "[WARNING] Force Shutdown $DIR_JBOSS!"
  ps -ef|grep jboss|grep 'Process Controller'|awk '{print $2}'|xargs kill -9 2>/dev/null 1>/dev/null
fi

success
echo
