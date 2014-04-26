Base Scripts
====

### install-env.sh

	#!/bin/bash
	
	. /etc/init.d/functions
	
	PATH_HOME=$(dirname $0)
	PATH_HOME=${PATH_HOME/\./$(pwd)}
	export PATH_HOME
	cd $PATH_HOME
	
	DIR_JDK=jdk7
	DIR_JBOSS=wildfly8
	
	JAVA_HOME_V="JAVA_HOME=$PATH_HOME/$DIR_JDK"
	PATH_V='PATH=$PATH:$JAVA_HOME/bin'
	EXPORT_V='export JAVA_HOME PATH'

### install-jdk.sh

	#!/bin/bash
	
	if [ "x$PATH_HOME" == "x" ]; then
	  . /opt/install-env.sh
	fi
	
	echo -n "=========UNTAR JDK=========="
	tar -xf jdk-7u25-linux-x64.gz
	mv jdk1.7.0_25 $DIR_JDK
	success
	echo
