Autorun Commands for RaspberryPi
======
Auto Setting ip address for RaspberryPi:

1. Add a job to /etc/crontab

        */2 * * * * root sh /boot/auto-config.sh

2. Add a script file:

        #!/bin/bash
        
        CONFIG_FILE=/var/run/auto-conf
        LOG_FILE=/boot/mylog
        touch $LOG_FILE
        echo `date`>>$LOG_FILE
        
        WIFI_FILE=/etc/wpa_supplicant/wpa.conf
        touch $WIFI_FILE
        
        if [ -f $CONFIG_FILE ]; then echo; else touch $CONFIG_FILE; fi
        
        . $CONFIG_FILE
        
        new_resetwifi='dsafsadf'
        new_resetnetwork='adsf'
        new_datetime='09/17/13 15:10:00'
        new_lanip='192.168.10.197'
        new_lanmask='255.255.255.0'
        new_langateway='192.168.1.1'
        new_wifissid='SSID'
        new_wifipsk='password'
        new_wlanip='198'
        
        update_mark=0
        
        # variable
        has_eth0_dev=`ip addr|grep eth0|grep "state UP"|wc -l`
        has_wlan0_dev=`ip addr|grep wlan0|wc -l`
        if_info=`ifconfig`
        eth0_info=`ifconfig eth0 2>/dev/null`
        eth0_1_info=`ifconfig eth0:1 2>/dev/null`
        wlan0_info=`ifconfig wlan0 2>/dev/null`
        wlan0_1_info=`ifconfig wlan0:1 2>/dev/null`
        has_eth0=`echo $eth0_info|grep netmask|wc -l`
        has_eth0_1=`echo $eth0_1_info|grep netmask|wc -l`
        has_wlan0=`echo $wlan0_info|grep netmask|wc -l`
        has_wlan0_1=`echo $wlan0_1_info|grep netmask|wc -l`
        
        # reset network
        if [ "$resetnetwork" != "$new_resetnetwork" ]; then
          service NetworkManager stop;
          chkconfig NetworkManager off;
          chkconfig network on;
          systemctl enable crond.service;
          systemctl isolate multi-user.target;
          update_mark=1;
          echo "reset network `date`">>$LOG_FILE;
        fi
        
        # setting datetime
        if [ "$datetime" != "$new_datetime" ]; then
          date -s "$new_datetime";
          update_mark=1;
          echo "setting datetime `date`">>$LOG_FILE;
        fi
        
        # reset wifi
        if [ "$resetwifi" != "$new_resetwifi" ]; then
          wpa_passphrase "$new_wifissid" $new_wifipsk > $WIFI_FILE;
          update_mark=1;
          echo "reset wifi `date`">>$LOG_FILE;
        fi
        
        # setting wifi
        if [[ "$has_wlan0_dev" != "0" && "$has_wlan0" == "0" ]]; then
            if [ `cat $WIFI_FILE|grep "$new_wifissid"|wc -l` == 0 ]; then
              wpa_passphrase "$new_wifissid" $new_wifipsk > $WIFI_FILE;
            fi
            systemctl disable wpa_supplicant.service
            systemctl stop wpa_supplicant.service
            wpa_supplicant -D wext -i wlan0 -c $WIFI_FILE &
            ps -ef | grep "dhclient-wlan0.pid" | awk '{print $2}' | xargs kill -9
            /sbin/dhclient -1 -q -lf /var/lib/dhclient/dhclient--wlan0.lease -pf /var/run/dhclient-wlan0.pid wlan0
            echo "setting wifi `date`">>$LOG_FILE;
        fi
        
        # setting eth0
        if [[ "$has_eth0_dev" != "0" && "$has_eth0" == "0" ]]; then
            ps -ef | grep "dhclient-eth0.pid" | awk '{print $2}' | xargs kill -9
            /sbin/dhclient -1 -q -lf /var/lib/dhclient/dhclient--eth0.lease -pf /var/run/dhclient-eth0.pid eth0
            echo "setting eth0 `date`">>$LOG_FILE;
        fi
        
        # setting static ip
        if [[ "$has_eth0_dev" != "0" && "$has_eth0_1" == "0" ]]; then 
          ifconfig eth0:1 $new_lanip netmask $new_lanmask up; 
          echo "ifconfig eth0:1 `date`">>$LOG_FILE;
        fi
        
        if [[ "$has_wlan0_dev" != "0" && "$has_wlan0" == "1" && "$has_wlan0_1" == "0" ]]; then
          new_wlanip_addr=`ifconfig wlan0|grep "inet "|awk '{print $2}'|sed "s/\\./\ /g"|awk '{print $1"."$2"."$3".""'$new_wlanip'"}'`
          ifconfig wlan0:1 $new_wlanip_addr netmask $new_lanmask up;
          echo "ifconfig wlan0:1 `date`">>$LOG_FILE;
        fi
        
        if [ "$update_mark" == "1" ]; then
          echo "#auto-config">$CONFIG_FILE;
          echo "resetwifi='$new_resetwifi'">>$CONFIG_FILE;
          echo "resetnetwork='$new_resetnetwork'">>$CONFIG_FILE;
          echo "datetime='$new_datetime'">>$CONFIG_FILE;
          echo "writing $CONFIG_FILE `date`">>$LOG_FILE;
        fi


3. How to expand the rootfs size [copy from http://www.raspberrypi.org/phpBB3/viewtopic.php?f=51&t=45265](http://www.raspberrypi.org/phpBB3/viewtopic.php?f=51&t=45265)

       following steps:
       - backup your system in case of a misstake!
       - use "fdisk /dev/mmcblk0" to view your partitions.
       - use "parted" to delete the partition and then recreate it but with a larger size. (don't worry, the data will remain)
       - reboot to activate the partition changes.
       - use "resize2fs /dev/mmclk0p2" to enlarge the root file system.
       - use e2fsck -f /dev/mmcblk0p2 to perform a file system check.
       - use "df -h" to check results.

       Before you extend your root partition and filesystem you should know how big your rootfs is and how much space is available:

              [root@raspi ~]# df -h
              Filesystem      Size  Used Avail Use% Mounted on
              /dev/root       1.6G  1.5G   53M  97% /
              /dev/mmcblk0p1   50M   18M   33M  35% /boot
              [root@raspi ~]#

       Determine the storage devices:

              [root@raspi ~]# ll /dev/mm*
              brw-rw---- 1 root disk 179, 0 Jun  3 13:22 /dev/mmcblk0
              brw-rw---- 1 root disk 179, 1 Jun  3 13:21 /dev/mmcblk0p1
              brw-rw---- 1 root disk 179, 2 Jun  3 13:21 /dev/mmcblk0p2
              [root@raspi ~]


       Check the partition table:


              [root@raspi ~] fdisk /dev/mmcblk0
              Welcome to fdisk (util-linux 2.22.1).
              
              Changes will remain in memory only, until you decide to write them.
              Be careful before using the write command.
              
              
              Command (m for help): p
              
              Disk /dev/mmcblk0: 16.0 GB, 16012804096 bytes, 31275008 sectors
              Units = sectors of 1 * 512 = 512 bytes
              Sector size (logical/physical): 512 bytes / 512 bytes
              I/O size (minimum/optimal): 512 bytes / 512 bytes
              Disk identifier: 0x000622ba
              
                      Device Boot      Start         End      Blocks   Id  System
              /dev/mmcblk0p1   *        2048      104447       51200    c  W95 FAT32 (LBA)
              /dev/mmcblk0p2          104448     3494304     1694928+  83  Linux
              
              Command (m for help): q
              
              [root@raspi ~]#


       So the SD card has 31275008 (16GB) sectors and the last one in use is 3494304 (1.6GB). 
       Print the partition table with "parted":


              [root@raspi ~]# parted /dev/mmcblk0
              GNU Parted 3.1
              Using /dev/mmcblk0
              Welcome to GNU Parted! Type 'help' to view a list of commands.
              (parted) unit chs                                                         
              (parted) print                                                            
              Model: SD  (sd/mmc)
              Disk /dev/mmcblk0: 1946,198,43
              Sector size (logical/physical): 512B/512B
              BIOS cylinder,head,sector geometry: 1946,255,63.  Each cylinder is 8225kB.
              Partition Table: msdos
              Disk Flags: 
              
              Number  Start     End        Type     File system  Flags
               1      0,32,32   6,127,56   primary  fat16        boot, lba
               2      6,127,57  217,130,9  primary  ext4
              (parted)


       So the disk ends at 1946,198,43 cylinder,head,sector and the current root partition ends at 217,130,9.

       Note: "fdisk" displays the partition info in 512 bytes blocks and "parted" displays the cylinder,head,sector geometry. Each cylinder is 8225kB.
       
       Now remove the second partition and recreate it larger. 
       
       Note: If you have a third swap or other partition that you don't need any longer, you can remove that one too and use the disk space to extend you.
       
       Removing the partition will only change the partition table and not the data. Creating a new partition will write a new start and end point in the partition table.
       
       Be careful: If you make a misstake, you lose you root partition data:
       (Ignore the warning.)


              (parted) rm 2                                                             
              Error: Partition(s) 2 on /dev/mmcblk0 have been written, but we have been unable to inform the kernel of the change, probably because it/they are in use.  As a result, the old partition(s) will
              remain in use.  You should reboot now before making further changes.
              Ignore/Cancel? i                                                          
              (parted)


       And check whether the partition was removed:


              (parted) print                                                            
              Model: SD  (sd/mmc)
              Disk /dev/mmcblk0: 1946,198,43
              Sector size (logical/physical): 512B/512B
              BIOS cylinder,head,sector geometry: 1946,255,63.  Each cylinder is 8225kB.
              Partition Table: msdos
              Disk Flags: 
              
              Number  Start    End       Type     File system  Flags
               1      0,32,32  6,127,56  primary  fat16        boot, lba
              
              (parted)
              

       Now the second partition is removed. Do not reboot your system before you have created the new partition! Other wise you lose your root file system.

       The new partition must start at the same position where the old root partition did start and it ends where you like. It must have at least the same size as current partition and it may not exceed the end of the disk (in my case 1946,198,43).
       (Ignore the warning.)

              
              (parted) mkpart primary 6,127,57  1946,198,43
              Error: Partition(s) 2 on /dev/mmcblk0 have been written, but we have been unable to inform the kernel of the change, probably because it/they are in use.  As a result, the old partition(s) will
              remain in use.  You should reboot now before making further changes.
              Ignore/Cancel? i                                                          
              (parted)
              
              
              And check whether the partition was created:
              (parted) print                                                            
              Model: SD  (sd/mmc)
              Disk /dev/mmcblk0: 1946,198,43
              Sector size (logical/physical): 512B/512B
              BIOS cylinder,head,sector geometry: 1946,255,63.  Each cylinder is 8225kB.
              Partition Table: msdos
              Disk Flags: 
              
              Number  Start     End          Type     File system  Flags
               1      0,32,32   6,127,56     primary  fat16        boot, lba
               2      6,127,57  1946,198,43  primary  ext4
              
              (parted) quit                                                             
              Information: You may need to update /etc/fstab.

              [root@raspi ~]#

       Be carefull: The kernel is not aware yet of the new partition size. You must reboot your system before you do any thing else.

              [root@raspi ~]# reboot

       Check the new partition size after the reboot:
              
              [root@raspi ~]# fdisk /dev/mmcblk0
              Welcome to fdisk (util-linux 2.22.1).
              
              Changes will remain in memory only, until you decide to write them.
              Be careful before using the write command.
              
              
              Command (m for help): p
              
              Disk /dev/mmcblk0: 16.0 GB, 16012804096 bytes, 31275008 sectors
              Units = sectors of 1 * 512 = 512 bytes
              Sector size (logical/physical): 512 bytes / 512 bytes
              I/O size (minimum/optimal): 512 bytes / 512 bytes
              Disk identifier: 0x000622ba
              
                      Device Boot      Start         End      Blocks   Id  System
              /dev/mmcblk0p1   *        2048      104447       51200    c  W95 FAT32 (LBA)
              /dev/mmcblk0p2          104448    31275007    15585280   83  Linux
              
              Command (m for help): quit
              [root@raspi ~]#
              
       Now the partition is larger, but the root file system has still the old size. Re-size the root filesystem:
       
              [root@raspi ~]# resize2fs /dev/mmcblk0p2 
              resize2fs 1.42.3 (14-May-2012)
              Filesystem at /dev/mmcblk0p2 is mounted on /; on-line resizing required
              old_desc_blocks = 1, new_desc_blocks = 1
              The filesystem on /dev/mmcblk0p2 is now 3896320 blocks long.
              
              [root@raspi ~]

       The root file system is now extended. 
       Then check the file system for errors:

              [root@raspi ~]# e2fsck -f /dev/mmcblk0p2
              e2fsck 1.42.3 (14-May-2012)
              /dev/mmcblk0p2 is mounted.  
              
              
              WARNING!!!  The filesystem is mounted.   If you continue you ***WILL***
              cause ***SEVERE*** filesystem damage.
              
              
              Do you really want to continue<n>? yes
              rootfs: recovering journal
              Pass 1: Checking inodes, blocks, and sizes
              Pass 2: Checking directory structure
              Pass 3: Checking directory connectivity
              Pass 4: Checking reference counts
              Pass 5: Checking group summary information
              Free blocks count wrong (3453563, counted=3453559).
              Fix<y>? yes
              
              rootfs: ***** FILE SYSTEM WAS MODIFIED *****
              rootfs: ***** REBOOT LINUX *****
              rootfs: 63775/952000 files (0.1% non-contiguous), 442761/3896320 blocks
              [root@raspi ~]#
              
       The file system is free of errors.
       Finaly check the file systems size and the available space:
       
              [root@raspi ~]# df -h
              Filesystem      Size  Used Avail Use% Mounted on
              /dev/root        15G  1.5G   13G  11% /
              /dev/mmcblk0p1   50M   18M   33M  35% /boot
              [root@raspi ~]#
              
       It has lots of free space available and it is ready to use.
       
       I hope this helps you.
