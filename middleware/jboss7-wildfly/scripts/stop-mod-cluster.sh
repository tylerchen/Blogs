#!/bin/bash

if [ "x$PATH_HOME" == "x" ]; then
  . /opt/install-env.sh
fi

echo "### $DIR_MODCLUSTER STOPPING... ###"
STOP_MOD_CLUSTER="$PATH_HOME/$DIR_MODCLUSTER/httpd/sbin/apachectl stop"
$STOP_MOD_CLUSTER

success
echo
