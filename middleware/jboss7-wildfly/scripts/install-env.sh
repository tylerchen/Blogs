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

