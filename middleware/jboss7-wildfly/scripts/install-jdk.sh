#!/bin/bash

if [ "x$PATH_HOME" == "x" ]; then
  . /opt/install-env.sh
fi

echo -n "=========UNTAR JDK=========="
tar -xf jdk-7u25-linux-x64.gz
mv jdk1.7.0_25 $DIR_JDK
success
echo

