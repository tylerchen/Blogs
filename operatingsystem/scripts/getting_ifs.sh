#!/bin/bash

DIR_DEFAULT=/etc/sysconfig/keeprunning
FILE_NAME=run_vm.sh

running() {
  echo "[`date +'%F %T'`] run...";

  while read line; do
    if [ "`echo $line|grep -c '#'`" == "0" ] && [ "`echo $line|grep -c 'SUBSYSTEM'`" != "0" ]; then
      echo "[`date +'%F %T'`] $line";
      IF_NAME="`echo $line| sed 's@.*NAME=.@@'| sed 's@"$@@'`"
      echo $IF_NAME
      IF_MAC="`echo $line |sed 's@.*ATTR{address}==.@@'| sed 's@".*@@'`"
      echo $IF_MAC
    fi
  done < /etc/udev/rules.d/70-persistent-net.rules
}

install() {
  echo "[`date +'%F %T'`] install...";
}


remove() {
  echo "[`date +'%F %T'`] remove....";
}

case "$1" in
  install)
      install
      ;;
  remove)
      remove
      ;;
  run)
      running
      ;;
  *)
      ## If no parameters are given, print which are avaiable.
      running
      exit 0
      ;;
esac
