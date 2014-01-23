#!/bin/bash

if [ "`cat /etc/sysconfig/iptables|grep -c "60000:61000"`" == "0" ]; then
  line_num="`/bin/sed -n '/dport 22/=' /etc/sysconfig/iptables`"
  line_num=$((line_num+1))
  svn_rule="-A INPUT -p tcp --dport 60000:61000 -j ACCEPT"
  /bin/sed -i "$line_num i $svn_rule" /etc/sysconfig/iptables
  /sbin/service iptables restart
fi
