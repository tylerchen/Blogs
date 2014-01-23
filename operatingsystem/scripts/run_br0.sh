#!/bin/bash


DIR_DEFAULT=/etc/sysconfig/keeprunning
FILE_NAME=run_br0.sh

running() {
  echo "[`date +'%F %T'`] run...";
  if [ "`/sbin/ifconfig br0 2>/dev/null|grep -c Mask`" -eq "0" ]; then  /sbin/ifup br0; fi
}

install() {
  echo "[`date +'%F %T'`] install...";
  mkdir -p $DIR_DEFAULT;
  cp -rf $0 $DIR_DEFAULT/$FILE_NAME;
  chmod a+x $DIR_DEFAULT/$FILE_NAME;
  if [ "`cat /etc/crontab | grep -c $FILE_NAME`" -eq "0" ]; then
    echo "*/1 * * * * root $DIR_DEFAULT/$FILE_NAME >> $DIR_DEFAULT/$FILE_NAME.log 2>&1" >> /etc/crontab;
    /sbin/service crond restart;
  fi
}


remove() {
  echo "[`date +'%F %T'`] remove....";
  sed_str=$(echo "$DIR_DEFAULT/$FILE_NAME"|sed 's@\/@\\\/@g'|sed 's@\.@\\\.@g')
  echo $sed_str
  /bin/sed -i "/$sed_str/d" /etc/crontab;
  rm -rf $DIR_DEFAULT/$FILE_NAME;
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
