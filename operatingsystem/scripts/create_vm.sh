#!/bin/bash

TEMPLATE_IMG=/home/vm/template/rhel63.qcow2.img
TEMPLATE_XML=/home/vm/template/template.xml
GUEST_ISO_FILE=/home/vm/iso/rhel-server-6.3-x86_64-dvd.iso
MAX_PORT=60001

template_vm_vnc=60001
template_vm_name=rhel63_$template_vm_vnc
template_vm_memory=1024
template_vm_cpu=4
template_vm_img_type=qcow2
template_vm_img=/home/vm/rhel63_$template_vm_vnc.img
template_vm_iso=$GUEST_ISO_FILE
template_vm_bridge=br0

vm_xml=/home/vm/$template_vm_name.xml

# copy template img to /home/vm
cp $TEMPLATE_IMG $template_vm_img

# copy template xml to /etc/libvirt/qemu
cp $TEMPLATE_XML $vm_xml

# setting template properties
sed -i "s@template_vm_vnc@$template_vm_vnc@"           $vm_xml
sed -i "s@template_vm_name@$template_vm_name@"         $vm_xml
sed -i "s@template_vm_memory@$template_vm_memory@"     $vm_xml
sed -i "s@template_vm_cpu@$template_vm_cpu@"           $vm_xml
sed -i "s@template_vm_img_type@$template_vm_img_type@" $vm_xml
sed -i "s@template_vm_img@$template_vm_img@"           $vm_xml
sed -i "s@template_vm_iso@$template_vm_iso@"           $vm_xml
sed -i "s@template_vm_bridge@$template_vm_bridge@"     $vm_xml

# register vm
/usr/bin/virsh create $vm_xml

#vm_xml="/home/vm/rhel63.xml"
# get uuid
UUID="`cat $vm_xml|grep '<uuid>'|/bin/sed 's@.*<uuid>@@'|/bin/sed 's@</uuid>.*@@'`"
echo $UUID

# get mac
MAC="`cat $vm_xml|grep 'mac address'|/bin/sed 's@.*=.@@'|/bin/sed 's@./>@@'`"
echo $MAC

getMaxSvnPort(){
  for file in $(ls /home/vm/*.img); do
    TEMP_PORT="`basename $file|sed 's@.*_@@'|sed 's@.img$@@'`"
    if [ $((MAX_PORT)) -lt $((TEMP_PORT)) ]; then
      MAX_PORT=$((TEMP_PORT+1))
    fi
  done
}
