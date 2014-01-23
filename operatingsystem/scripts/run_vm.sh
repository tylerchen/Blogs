#!/bin/bash

DIR_DEFAULT=/etc/sysconfig/keeprunning
FILE_NAME=run_vm.sh

running() {
  echo "[`date +'%F %T'`] run...";
  if [ ! -f $DIR_DEFAULT/vm.list ]; then
    echo "[`date +'%F %T'`] File $DIR_DEFAULT/vm.list does not exist."
    touch $DIR_DEFAULT/vm.list
  fi

  while read line; do
    if [ "`/usr/bin/virsh list | grep -c $line`" -eq "0" ]; then
      echo "[`date +'%F %T'`] $line not running";
      echo "[`date +'%F %T'`] $line is starting...";
      /usr/bin/virsh start $line;
    fi
  done < $DIR_DEFAULT/vm.list
}

install() {
  echo "[`date +'%F %T'`] install...";
  mkdir -p $DIR_DEFAULT;
  cp -rf $0 $DIR_DEFAULT/$FILE_NAME;
  chmod a+x $DIR_DEFAULT/$FILE_NAME;
  if [ "`cat /etc/crontab | grep -c $FILE_NAME`" -eq "0" ]; then
    echo "*/5 * * * * root $DIR_DEFAULT/$FILE_NAME >> $DIR_DEFAULT/$FILE_NAME.log 2>&1" >> /etc/crontab;
    /sbin/service crond restart;
  fi
}


remove() {
  echo "[`date +'%F %T'`] remove....";
  sed_str=$(echo "$DIR_DEFAULT/$FILE_NAME"|sed 's@\/@\\\/@g'|sed 's@\.@\\\.@g')
  echo $sed_str
  /bin/sed -i "/$sed_str/d" /etc/crontab;
  rm -rf $DIR_DEFAULT/$FILE_NAME;
  rm -rf $DIR_DEFAULT/vm.list;
  /sbin/service crond restart;
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
