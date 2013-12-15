KVM Useful
==========

### Specify the Keyboard

1. Edit vm profile

        virsh edit 'your vm name'

2. find this

        <graphics type='vnc' port='-1'/>

3. change to this

        <graphics type='vnc' port='-1' keymap='en-us'/>

4. save and reload

        virsh create /etc/libvirt/qemu/'your vm name'.xml

5. by user interface virt-manager

        open-->Details-->Display VNC-->keymap-->en-us

### Enable virsh console

1. add ttyS0

        echo ttyS0 >> /etc/securetty

2. add console ttyS0 to grub[/etc/grub.conf] or [/boot/grub/grub.conf]

        console=ttyS0
        such as:
        kernel /vmlinuz-2.6.32-220.23.1.el6.x86_64 ro root=/dev/mapper/vg_main-root rd_NO_LUKS LANG=en_US.UTF-8 rd_LVM_LV=vg_main/root rd_NO_MD quiet SYSFONT=latarcyrheb-sun16 rhgb crashkernel=auto rd_LVM_LV=vg_main/swap  KEYBOARDTYPE=pc KEYTABLE=us rd_NO_DM console=ttyS0

3. add inittab config

        echo "S0:12345:respawn:/sbin/agetty ttyS0 115200" >> /etc/inittab

4. reboot guest OS

### Configure bridge network

1. show bridge network

        brctl show
        [without vm running ouput]
        [root@localhost ~]# brctl show
        bridge name     bridge id               STP enabled     interfaces
        virbr0          8000.000000000000       yes             
        
        [one vm running output]
        [root@localhost ~]# brctl show
        bridge name     bridge id               STP enabled     interfaces
        virbr0          8000.000000000000       yes             virbr0-nic

2. check the "virbr0" default configure [/var/lib/libvirt/network/default.xml]

        [root@localhost ~]# cat /var/lib/libvirt/network/default.xml
        <network>
          <name>default</name>
          <uuid>cb7de0e0-9096-4101-b822-e42f8f8e8a00</uuid>
          <forward mode='nat'/>
          <bridge name='virbr0' stp='on' delay='0' />
          <mac address='52:54:00:3B:56:0F'/>
          <ip address='192.168.122.1' netmask='255.255.255.0'>
            <dhcp>
              <range start='192.168.122.2' end='192.168.122.254' />
            </dhcp>
          </ip>
        </network>

3. create bridge "br0" by create file [/etc/sysconfig/network-scripts/ifcfg-br0]

        [root@localhost ~]# vi /etc/sysconfig/network-scripts/ifcfg-br0
        DEVICE=br0
        TYPE=bridge
        BOOTRPOTO=none
        # your new ip address or copy from eth0, eth0 ip address will not work
        IPADDR=10.108.1.230
        NETMASK=255.255.255.0
        NETWORK=10.108.1.0
        ONBOOT=yes

4. connect the network with bridge [some nic should be ifcfg-eth0]

        [root@localhost ~]# vi /etc/sysconfig/network-scripts/ifcfg-p4p1
        DEVICE="p4p1"
        BOOTPROTO="none"
        HWADDR="D0:67:E5:22:30:86"
        NM_CONTROLLED="no"
        ONBOOT="yes"
        TYPE="Ethernet"
        UUID="954a5967-d627-460e-a57d-e51372286f5b"
        # disable ip configure
        #IPADDR=10.108.1.230
        #NETWORK=10.108.1.0
        #NETMASK=255.255.255.0
        BRIDGE=br0

5. restart network

        service network restart
        
        [maybe you could want to use a script, br0 may lazy init]
        [root@localhost ~]# vi rsnetwork.sh
        service network restart
        sleep 3
        ifup br0
        
        [root@localhost ~]# chmod +x rsnetwork.sh
        [root@localhost ~]# ./rsnetwork.sh &

6. reconnect the system using "br0" ip address

7. check bridge network

        [root@localhost ~]# brctl show
        bridge name     bridge id               STP enabled     interfaces
        br0             8000.5254003b560f       no              p4p1
        virbr0          8000.000000000000       yes             
        
8. connection virtual network "virbr0" to bridge "br0"

        [root@localhost ~]# brctl delif virbr0 virbr0-nic
        [root@localhost ~]# brctl addif br0  virbr0-nic
        [root@localhost ~]# brctl show
        [root@localhost ~]# brctl show
        bridge name     bridge id               STP enabled     interfaces
        br0             8000.5254003b560f       no              p4p1
                                                                virbr0-nic
        virbr0          8000.000000000000       yes

9. start guest OS and configure network [if br0 use dhcp the guest OS will assigned a new ip address]

### virsh command list

1. virsh commands

        virsh <command> <domain-id> [OPTIONS]
        virsh list                        #list all running vm
        virsh start vmname                #start vm by name
        virsh shutdown vmname             #shutdown vm by name
        virsh console vmname              #open console to vm
        virsh suspend vmname              #suspend vm by name, still in memory
        virsh resume vmname               #resume a suspend vm
        virsh vncdisplay vmname           #display vnc display port
        virsh snapshot-create-as --domain rhel63 --name rhel63-20131213 --description "pure rhel63"
        
        qemu-img create -f raw /home/vm/rhel63.img 20G
        qemu-img create -f qcow2 rhel63min.img 10G
        qemu-img convert -O qcow2 rhel63.img rhel63min.img
        
         rm /etc/udev/rules.d/70-persistent-net.rules

