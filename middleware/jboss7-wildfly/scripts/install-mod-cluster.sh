#!/bin/bash

if [ "x$PATH_HOME" == "x" ]; then
  . /opt/install-env.sh
fi

echo "### UNTAR MOD_CLUSTER ###"
tar -xf mod_cluster-1.2.0.Final-linux2-x64-ssl.tar.gz
mv $PATH_HOME/opt/jboss $PATH_HOME/$DIR_MODCLUSTER
rm -rf $PATH_HOME/opt $PATH_HOME/licenses

### Modify Mod_cluster Httpd.conf ###
local_ip="`ifconfig|grep Mask|grep Bcast|awk '{print $2}'|sed 's/addr://'`";
MOD_CLUSTER_CFG=$PATH_HOME/$DIR_MODCLUSTER/httpd/httpd/conf/httpd.conf

/bin/sed -i "168 i ServerName $local_ip:80" $MOD_CLUSTER_CFG
/bin/sed -i "s@127.0.0.1:@$local_ip:@g" $MOD_CLUSTER_CFG
/bin/sed -i 's@#ServerAdvertise on .*@ServerAdvertise on@' $MOD_CLUSTER_CFG
/bin/sed -i 's@127.0.0@all@g' $MOD_CLUSTER_CFG

success
echo

