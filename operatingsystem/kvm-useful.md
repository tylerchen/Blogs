KVM Useful
==========

### Specify the Keyboard
1. virsh edit 'your vm name'
2. find this: <graphics type='vnc' port='-1'/>
3. change to this: <graphics type='vnc' port='-1' keymap='en-us'/>
4. save and reload: virsh create /etc/libvirt/qemu/'your vm name'.xml
5. by user interface virt-manager: open-->Details-->Display VNC-->keymap-->en-us
