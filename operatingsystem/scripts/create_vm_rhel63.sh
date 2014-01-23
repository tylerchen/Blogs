#!/bin/bash

TEMPLATE_IMG=/home/vm/template/rhel63.qcow2.img
TEMPLATE_XML=/home/vm/template/template.xml
GUEST_ISO_FILE=/home/vm/iso/rhel-server-6.3-x86_64-dvd.iso
MAX_PORT=60001

# getting max svn port
for file in $(ls /home/vm/*.img); do
  TEMP_PORT="`basename $file|sed 's@.*_@@'|sed 's@.img$@@'`"
  echo $TEMP_PORT
  if [ $((MAX_PORT)) -le $((TEMP_PORT)) ]; then
    MAX_PORT=$((TEMP_PORT+1))
  fi
done

template_vm_vnc=$MAX_PORT
template_vm_name=rhel63_$template_vm_vnc
template_vm_memory=1024
template_vm_cpuset=0,1
template_vm_vcpu=2
template_vm_img_type=qcow2
template_vm_img=/home/vm/rhel63_$template_vm_vnc.img
template_vm_iso=$GUEST_ISO_FILE
template_vm_bridge=br0

vm_xml=/etc/libvirt/qemu/$template_vm_name.xml

echo "$template_vm_name"

# copy template img to /home/vm
cp $TEMPLATE_IMG $template_vm_img

# copy template xml to /etc/libvirt/qemu
cp $TEMPLATE_XML $vm_xml

# setting template properties
sed -i "s@template_vm_vnc@$template_vm_vnc@g"           $vm_xml
sed -i "s@template_vm_name@$template_vm_name@g"         $vm_xml
sed -i "s@template_vm_memory@$template_vm_memory@g"     $vm_xml
sed -i "s@template_vm_cpuset@$template_vm_cpuset@g"     $vm_xml
sed -i "s@template_vm_vcpu@$template_vm_vcpu@g"         $vm_xml
sed -i "s@template_vm_img_type@$template_vm_img_type@g" $vm_xml
sed -i "s@template_vm_img@$template_vm_img@g"           $vm_xml
sed -i "s@template_vm_iso@$template_vm_iso@g"           $vm_xml
sed -i "s@template_vm_bridge@$template_vm_bridge@g"     $vm_xml

# register vm
/usr/bin/virsh define $vm_xml
/usr/bin/virsh create $vm_xml

# get uuid
UUID="`cat $vm_xml|grep '<uuid>'|/bin/sed 's@.*<uuid>@@'|/bin/sed 's@</uuid>.*@@'`"
echo $UUID

# get mac
MAC="`cat $vm_xml|grep 'mac address'|/bin/sed 's@.*=.@@'|/bin/sed 's@./>@@'`"
echo $MAC

