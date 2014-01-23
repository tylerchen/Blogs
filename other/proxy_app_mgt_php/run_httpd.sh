#!/bin/bash

DIR_DEFAULT=/etc/sysconfig/keeprunning
FILE_NAME=run_httpd.sh

running() {
  echo "[`date +'%F %T'`] run...";
  if [ "`/sbin/service httpd status|grep -c 'running'`" == "0" ]; then
    /sbin/service httpd restart
  else
    if [ ! -f $DIR_DEFAULT/httpd.reload ]; then
      echo "[`date +'%F %T'`] File $DIR_DEFAULT/httpd.reload does not exist."
      touch $DIR_DEFAULT/httpd.reload
      chmod 666 $DIR_DEFAULT/httpd.reload
    fi
    if [ "`cat $DIR_DEFAULT/httpd.reload|grep -c 'reload'`" != "0" ]; then
      echo "" > $DIR_DEFAULT/httpd.reload
      /sbin/service httpd restart
    fi
  fi
}

install() {
  echo "[`date +'%F %T'`] install...";
  mkdir -p $DIR_DEFAULT;
  cp -rf $0 $DIR_DEFAULT/$FILE_NAME;
  chmod a+x $DIR_DEFAULT/$FILE_NAME;
  if [ ! -f $DIR_DEFAULT/httpd.reload ]; then
    echo "[`date +'%F %T'`] File $DIR_DEFAULT/httpd.reload does not exist."
    touch $DIR_DEFAULT/httpd.reload
    chmod 666 $DIR_DEFAULT/httpd.reload
  fi
  # add default user to use httpd reload
  echo "tyler=tyler123"   >  $DIR_DEFAULT/httpd.users
  echo "manager=manager1" >> $DIR_DEFAULT/httpd.users

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

