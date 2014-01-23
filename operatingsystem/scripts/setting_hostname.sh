#!/bin/bash

HOST_NAME=$1

# change host name /etc/sysconfig/network
/bin/sed -i "s@HOSTNAME.*@HOSTNAME=$HOST_NAME@" /etc/sysconfig/network

# change host name if /etc/hosts
/bin/sed -i  "s@ localhost .*@ localhost $HOST_NAME@g" /etc/hosts

# use active hostname
/bin/hostname $HOST_NAME
