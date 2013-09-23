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

