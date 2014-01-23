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

initNetwork(){
  echo "[`date +'%F %T'`] initNetwork....";
  # if has br0 then return
  if [ -f /etc/sysconfig/network-scripts/ifcfg-br0 ]; then
    echo "[`date +'%F %T'`] File /etc/sysconfig/network-scripts/ifcfg-br0 exists."
    return;
  fi

  # get default ip address
  IPADDR=
  NETMASK=
  DEFAULT_IF=
  for file in $(ls /etc/sysconfig/network-scripts/ifcfg-*); do
    IF_NAME=`echo "$file"|sed "s@.*ifcfg-@@"`;
    if [ "$IF_NAME" != "lo" ] && [ "$DEFAULT_IF" == "" ]; then
      if [ "`ifconfig $IF_NAME|grep -c RUNNING`" == "1" ]; then
        IPADDR="`/sbin/ifconfig $IF_NAME|grep Bcast|awk '{print $2}'|sed 's@.*:@@'`"
        NETMASK="`/sbin/ifconfig $IF_NAME|grep Bcast|awk '{print $4}'|sed 's@.*:@@'`"
        DEFAULT_IF=$file
        echo "[`date +'%F %T'`] IPADDR=$IPADDR....";
        echo "[`date +'%F %T'`] NETMASK=$NETMASK....";
        echo "[`date +'%F %T'`] interface=$DEFAULT_IF....";
      fi
    fi
  done

  # test ip and net mask exists
  if [ "$IPADDR" == "" ]; then
    echo "[`date +'%F %T'`] no ip address found."
    return;
  fi
  if [ "$NETMASK" == "" ]; then
    echo "[`date +'%F %T'`] no netmask found."
    return;
  fi

  # create ifcfg-br0
  IF_FILE_NAME=/etc/sysconfig/network-scripts/ifcfg-br0
  touch $IF_FILE_NAME
  echo "DEVICE=br0"            >> $IF_FILE_NAME
  echo "TYPE=bridge"           >> $IF_FILE_NAME
  echo "BOOTRPOTO=none"        >> $IF_FILE_NAME
  echo "IPADDR=$IPADDR"        >> $IF_FILE_NAME
  echo "NETMASK=$NETMASK"      >> $IF_FILE_NAME
  echo "ONBOOT=yes"            >> $IF_FILE_NAME 
  echo "DELAY=0"               >> $IF_FILE_NAME

  # comment default ip address
  /bin/sed -i "s@IPADDR@#IPADDR@g"   $DEFAULT_IF
  /bin/sed -i "s@NETMASK@#NETMASK@g" $DEFAULT_IF
  /bin/sed -i "s@NETWORK@#NETWORK@g" $DEFAULT_IF
  if [ "`cat $DEFAULT_IF|grep -c BRIDGE`" == "0" ]; then
    echo "BRIDGE=br0" >> $DEFAULT_IF
  fi

  # restart network service
  /sbin/service network restart
}

case "$1" in
  initNetwork)
      install
      initNetwork
      ;;
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

