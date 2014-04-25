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

