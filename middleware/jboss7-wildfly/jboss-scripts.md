JBoss Scripts
====

### install-jboss.sh

	#!/bin/bash
	
	if [ "x$PATH_HOME" == "x" ]; then
	  . /opt/install-env.sh
	fi
	
	if test ! -d $PATH_HOME/$DIR_JDK ; then
	  . $PATH_HOME/install-jdk.sh
	fi
	
	echo "### UNZIP JBOSS $DIR_JBOSS & SETTING JDK ENVIRONMENT FOR $DIR_JBOSS ###"
	unzip wildfly-8.0.0.Final.zip 1>/dev/null 2>/dev/null
	mv wildfly-8.0.0.Final $DIR_JBOSS
	
	echo "### Add JAVA_HOME Environment ###"
	/bin/sed -i "31 i $JAVA_HOME_V" $PATH_HOME/$DIR_JBOSS/bin/domain.conf
	/bin/sed -i "32 i $PATH_V" $PATH_HOME/$DIR_JBOSS/bin/domain.conf
	/bin/sed -i "33 i $EXPORT_V" $PATH_HOME/$DIR_JBOSS/bin/domain.conf
	
	/bin/sed -i "31 i $JAVA_HOME_V" $PATH_HOME/$DIR_JBOSS/bin/standalone.conf
	/bin/sed -i "32 i $PATH_V" $PATH_HOME/$DIR_JBOSS/bin/standalone.conf
	/bin/sed -i "33 i $EXPORT_V" $PATH_HOME/$DIR_JBOSS/bin/standalone.conf
	
	/bin/sed -i "4  i $JAVA_HOME_V" $PATH_HOME/$DIR_JBOSS/bin/jboss-cli.sh
	/bin/sed -i "5  i $PATH_V" $PATH_HOME/$DIR_JBOSS/bin/jboss-cli.sh
	/bin/sed -i "6  i $EXPORT_V" $PATH_HOME/$DIR_JBOSS/bin/jboss-cli.sh
	
	/bin/sed -i "10  i $JAVA_HOME_V" $PATH_HOME/$DIR_JBOSS/bin/add-user.sh
	/bin/sed -i "11  i $PATH_V" $PATH_HOME/$DIR_JBOSS/bin/add-user.sh
	/bin/sed -i "12  i $EXPORT_V" $PATH_HOME/$DIR_JBOSS/bin/add-user.sh
	
	echo "### Add sticky session ###"
	/bin/sed -i 's@urn:jboss:domain:undertow:1.0"@urn:jboss:domain:undertow:1.0" instance-id="${jboss.node.name}"@' $PATH_HOME/$DIR_JBOSS/domain/configuration/domain.xml
	/bin/sed -i 's@urn:jboss:domain:undertow:1.0"@urn:jboss:domain:undertow:1.0" instance-id="${jboss.node.name}"@' $PATH_HOME/$DIR_JBOSS/standalone/configuration/standalone.xml
	
	echo "### Add default user[test/test1234, node1/test1234, node2/test1234] ###"
	echo '' >> $PATH_HOME/$DIR_JBOSS/domain/configuration/mgmt-users.properties
	echo 'test=f1407eeca1f554d2194b22eefe7c2763' >> $PATH_HOME/$DIR_JBOSS/domain/configuration/mgmt-users.properties
	echo 'node1=bb8bb6104783d09cb2295d4818f81bd6' >> $PATH_HOME/$DIR_JBOSS/domain/configuration/mgmt-users.properties
	echo 'node2=eded223a2fd9febda3eb1020ab6496ce' >> $PATH_HOME/$DIR_JBOSS/domain/configuration/mgmt-users.properties
	
	echo '' >> $PATH_HOME/$DIR_JBOSS/standalone/configuration/mgmt-users.properties
	echo 'test=f1407eeca1f554d2194b22eefe7c2763' >> $PATH_HOME/$DIR_JBOSS/standalone/configuration/mgmt-users.properties
	echo 'node1=bb8bb6104783d09cb2295d4818f81bd6' >> $PATH_HOME/$DIR_JBOSS/standalone/configuration/mgmt-users.properties
	echo 'node2=eded223a2fd9febda3eb1020ab6496ce' >> $PATH_HOME/$DIR_JBOSS/standalone/configuration/mgmt-users.properties
	
	echo "### Create Custom Node Configuration ###"
	cp $PATH_HOME/$DIR_JBOSS/domain/configuration/host.xml       $PATH_HOME/$DIR_JBOSS/domain/configuration/cluster-host-master.xml
	cp $PATH_HOME/$DIR_JBOSS/domain/configuration/host-slave.xml $PATH_HOME/$DIR_JBOSS/domain/configuration/cluster-host-node1.xml
	cp $PATH_HOME/$DIR_JBOSS/domain/configuration/host-slave.xml $PATH_HOME/$DIR_JBOSS/domain/configuration/cluster-host-node2.xml
	
	echo "### Setting Cluster Host Node1 & Node2 name and base64-password ###"
	sed -i 's@xmlns@name="node1" xmlns@' $PATH_HOME/$DIR_JBOSS/domain/configuration/cluster-host-node1.xml
	sed -i 's@c2xhdmVfdXNlcl9wYXNzd29yZA==@dGVzdDEyMzQ=@' $PATH_HOME/$DIR_JBOSS/domain/configuration/cluster-host-node1.xml
	sed -i 's@xmlns@name="node2" xmlns@' $PATH_HOME/$DIR_JBOSS/domain/configuration/cluster-host-node2.xml
	sed -i 's@c2xhdmVfdXNlcl9wYXNzd29yZA==@dGVzdDEyMzQ=@' $PATH_HOME/$DIR_JBOSS/domain/configuration/cluster-host-node2.xml
	
	success
	echo


### start-jboss.sh

	#!/bin/bash
	
	if [ "x$PATH_HOME" == "x" ]; then
	  . /opt/install-env.sh
	fi
	
	echo "### $DIR_JBOSS STARTING... ###"
	local_ip="`ifconfig|grep Mask|grep Bcast|awk '{print $2}'|sed 's/addr://'`";
	
	### Get Host config name from argument, defualt is master ###
	HOST_CONFIG="$1"
	if [ "x$HOST_CONFIG" == "x" ]; then
	  HOST_CONFIG="master"
	fi
	
	if [ "$HOST_CONFIG" != "master" ] && [ "x$2" == "x" ]; then
	  echo "[ERROR] Missing argument of jboss.domain.master.address"
	  echo "[Example] $0 $1 127.0.0.1"
	  exit 1
	fi
	
	### Start JBoss ###
	START_JBOSS="$PATH_HOME/$DIR_JBOSS/bin/domain.sh -Djboss.bind.address.management=$local_ip -Djboss.bind.address=$local_ip --host-config=cluster-host-$HOST_CONFIG.xml"
	if [ "$HOST_CONFIG" != "master" ]; then
	  START_JBOSS="$PATH_HOME/$DIR_JBOSS/bin/domain.sh -Djboss.bind.address.management=$local_ip -Djboss.bind.address=$local_ip -Djboss.domain.master.address=$2 --host-config=cluster-host-$HOST_CONFIG.xml"
	fi
	
	echo "[Example] $0 [master|node1|node2]"
	echo "[RUN] $START_JBOSS"
	$START_JBOSS 2>/dev/null 1>/dev/null &
	
	### Print url and user name and password ###
	echo "JBOSS: http://$local_ip:9990/"
	USER_NAME=""
	if [ "$HOST_CONFIG" == "master" ]; then
	  USER_NAME="test"
	else
	  USER_NAME="$HOST_CONFIG"
	fi
	echo "JBOSS: User: $USER_NAME, Password: test1234"
	success
	echo


### stop-jboss.sh

	#!/bin/bash
	
	if [ "x$PATH_HOME" == "x" ]; then
	  . /opt/install-env.sh
	fi
	
	echo "### Stopping $DIR_JBOSS ###"
	local_ip="`ifconfig|grep Mask|grep Bcast|awk '{print $2}'|sed 's/addr://'`";
	
	### Get Host config name from argument, defualt is master ###
	HOST_CONFIG="$1"
	if [ "x$HOST_CONFIG" == "x" ]; then
	  HOST_CONFIG="master"
	fi
	
	### Shutdown JBoss ###
	SHUTDOWN_JBOSS="$PATH_HOME/$DIR_JBOSS/bin/jboss-cli.sh --connect --controller=$local_ip:9999 /host=$HOST_CONFIG:shutdown"
	echo "[Example] $0 [master|node1|node2]"
	echo "[RUN] $SHUTDOWN_JBOSS"
	$SHUTDOWN_JBOSS 2>/dev/null 1>/dev/null &
	
	### Force Shutdown JBoss ###
	count=0
	until [ "`ps -ef|grep jboss|grep 'Process Controller'|wc -l`" == "0" ] || [ $count -gt 5 ]
	do
	  sleep 1;
	  $SHUTDOWN_JBOSS 2>/dev/null 1>/dev/null;
	  let count=$count+1;
	done
	
	if [ "`ps -ef|grep jboss|grep 'Process Controller'|wc -l`" != "0" ]; then
	  echo "[WARNING] Force Shutdown $DIR_JBOSS!"
	  ps -ef|grep jboss|grep 'Process Controller'|awk '{print $2}'|xargs kill -9 2>/dev/null 1>/dev/null
	fi
	
	success
	echo
