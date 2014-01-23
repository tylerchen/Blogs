#!/bin/bash

IPADDR=
NETMASK=
DEFAULT_IF=
GATEWAY=
for file in $(ls /etc/sysconfig/network-scripts/ifcfg-*); do
IF_NAME=`echo "$file"|sed "s@.*ifcfg-@@"`;
if [ "$IF_NAME" != "lo" ] && [ "$DEFAULT_IF" == "" ]; then
  if [ "`ifconfig $IF_NAME|grep -c RUNNING`" == "1" ]; then
    if [ "`cat $file|grep -c 'GATEWAY'`" == "0" ]; then
      IPADDR="`/sbin/ifconfig $IF_NAME|grep Bcast|awk '{print $2}'|sed 's@.*:@@'`"
      NETMASK="`/sbin/ifconfig $IF_NAME|grep Bcast|awk '{print $4}'|sed 's@.*:@@'`"
      DEFAULT_IF=$file
      echo "[`date +'%F %T'`] IPADDR=$IPADDR....";
      echo "[`date +'%F %T'`] NETMASK=$NETMASK....";
      echo "[`date +'%F %T'`] interface=$DEFAULT_IF....";
      if [ "$NETMASK" == "255.255.255.0" ]; then
        GATEWAY="`echo $IPADDR|/bin/sed 's@\.[0-9]*$@.1@g'`"
        echo "GATEWAY=$GATEWAY" >> $file
        if [ "`cat $file|grep -c 'DNS1'`" == "0" ]; then
          echo "DNS1=8.8.8.8" >> $file
        fi
        cat $file
      fi
    fi
  fi
fi

# restart network
if [ "$GATEWAY" != "" ]; then
  /sbin/service network restart &
fi
done

