RHEL(5/6) About
====

### Console/X Window Switch 


    # /etc/inittab  
    # Console:  id:3:initdefault:  
    # X Window: id:5:initdefault:  
    # Default runlevel. The runlevels used by RHS are:  
    #   0 - halt (Do NOT set initdefault to this)  
    #   1 - Single user mode  
    #   2 - Multiuser, without NFS (The same as 3, if you do not have networking)  
    #   3 - Full multiuser mode  
    #   4 - unused  
    #   5 - X11  
    #   6 - reboot (Do NOT set initdefault to this)  
    #  
    id:3:initdefault:  


### Check / List Running Services 

#### service command - list running services 


    service --status-all  
    service --status-all | grep ntpd  
    service --status-all | less  


#### Print the status of any service 


    service httpd status  


#### List all known services (configured via SysV) 


    chkconfig --list  


#### List service and their open ports 


    netstat -tulpn  


#### Turn on / off service 


    ntsysv  
    chkconfig service off  
    chkconfig service on  
    chkconfig httpd off  
    chkconfig ntpd on  

#### Other 


    /sbin/service [name] start  
    starts the background service  
    /sbin/service [name] stop  
    stops the background service  
    /sbin/service [name] restart  
    restarts the background service  
    /sbin/service [name] status  
    displays the background service status  
    /sbin/chkconfig ––list  
    displays all the available services  
    /sbin/chkconfig ––level 345 [name] on  
    automatically runs the background service on the next startup  
    /sbin/chkconfig ––level 345 [name] off  
    removes the background service from the startup list  
    /sbin/chkconfig [name] on  
    enables the on-demand service  
    /sbin/chkconfig [name] off  
    disables the on-demand service  


#### 系统时间与硬件时间同步 


    vi /etc/crontab  
    #*/3 * * * * root ntpdate time.windows.com  
    * */1 * * * root /sbin/hwclock -s --localtime  


### Linux Find 

#### find 命令的基本结构： 


    find   start_directory  test  options   criteria_to_match  
    action_to_perform_on_results  


#### 在以下命令中，find 将开始在目录中查找文件： 


    find . -name  "*.java"  
    find /home/bluher -name \*.java  
    find /usr /home  /tmp -name "*.jar"  
    find /usr /home  /tmp -name "*.jar" 2&gt;/dev/null  
    find downloads  -iname "*.gif"  


### Ethernet Interfaces 


    /etc/sysconfig/network-scripts/ifcfg-eth0  


#### Static IP 


    DEVICE=eth0  
    BOOTPROTO=none  
    ONBOOT=yes  
    NETWORK=10.0.1.0  
    NETMASK=255.255.255.0  
    IPADDR=10.0.1.27  
    USERCTL=no  


#### DHCP 


    DEVICE=eth0  
    BOOTPROTO=dhcp  
    ONBOOT=yes  

### 将YUM指定为光盘 

#### 将光盘mount起来


    #mount -o loop rhel-5-server-dvd.iso /media/rhel  
    #mount -t iso9660 /dev/cdrom /media/cdrom  


#### 或者启动系统默认挂载光盘 


    #vim /etc/fstab  
    /dev/cdrom     /mdeia/rhel        iso9660   defaults    0 0  


#### 创建repo文件 


    #vim /etc/yum.repos.d/rhel-local.repo  

    [Cluster]  
    name=Red Hat Enterprise Linux $releasever - $basearch - Cluster  
    baseurl=file:///media/rhel/Cluster  
    enabled=1  
    gpgcheck=1  
    gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-redhat-release  
      
    [ClusterStorage]  
    name=Red Hat Enterprise Linux $releasever - $basearch - ClusterStorage  
    baseurl=file:///media/rhel/ClusterStorage  
    enabled=1  
    gpgcheck=1  
    gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-redhat-release  
      
    [Server]  
    name=Red Hat Enterprise Linux $releasever - $basearch - Server  
    baseurl=file:///media/rhel/Server  
    enabled=1  
    gpgcheck=1  
    gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-redhat-release  
      
    [VT]  
    name=Red Hat Enterprise Linux $releasever - $basearch - VT  
    baseurl=file:///media/rhel/VT  
    enabled=1  
    gpgcheck=1  
    gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-redhat-release  


#### RHEL6的YUM配置


    [root@rhel6 ~]# cat /etc/yum.repos.d/rhel-source.repo   
    [rhel-source]  
    name=Red Hat Enterprise Linux $releasever - $basearch - Source  
    #baseurl=ftp://ftp.redhat.com/pub/redhat/linux/enterprise/$releasever/en/os/SRPMS/  
    baseurl=file:///media/cdrom/  
    enabled=1  
    gpgcheck=1  
    #gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-redhat-release  
    gpgkey=file:///media/cdrom/RPM-GPG-KEY-redhat-release  
      
    [rhel-source-beta]  
    name=Red Hat Enterprise Linux $releasever Beta - $basearch - Source  
    baseurl=ftp://ftp.redhat.com/pub/redhat/linux/beta/$releasever/en/os/SRPMS/  
    enabled=0  
    gpgcheck=1  
    gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-redhat-beta,file:///etc/pki/rpm-gpg/RPM-GPG-KEY-redhat-release  


#### 创建YUM仓库目录结构


    #mkdir -p /var/rhel/{Cluster,ClusterStorage,Server,VT}  


#### 重建个repomd.xml

生成rpm依赖关系及组信息，在RHEL 5中每个目录下的repodata目录下都有一个repomd.xml， 
该文件中就记录了rpm包的依赖关系，还有一个comps-rhel5-*.xml文件，这个文件主要记录分组情况，建立yum仓库时，需要先重建该文件。当然，如果你系统还没有createrepo 命令，你需要安装createrepo 软件包: 


    
    # cd /media/rhel/Server  
    # rpm -ivh createrepo-0.4.11-3.el5.noarch.rpm  
      
    #createrepo -o /var/rhel/Cluster -g /media/rhel/Cluster/repodata/comps-rhel5-cluster.xml /media/rhel/Cluster  
    #createrepo -o /var/rhel/ClusterStorage -g /media/rhel/ClusterStorage/repodata/comps-rhel5-cluster-st.xml /media/rhel/ClusterStorage  
    #createrepo -o /var/rhel/Server -g /media/rhel/Server/repodata/comps-rhel5-server-core.xml /media/rhel/Server  
    #createrepo -o /var/rhel/VT -g /media/rhel/VT/repodata/comps-rhel5-vt.xml /media/rhel/VT  


#### 挂载仓库目录


    #mount --bind /var/rhel/Cluster/repodata /media/rhel/Cluster/repodata  
    #mount --bind /var/rhel/ClusterStorage/repodata /media/rhel/ClusterStorage/repodata  
    #mount --bind /var/rhel/Server/repodata /media/rhel/Server/repodata  
    #mount --bind /var/rhel/VT/repodata /media/rhel/VT/repodata  


#### 清除yum缓存 


    #yum clean all  


#### yum的使用技巧： 


    # yum install [-y ]package  


-y：不提示用户确认直接安装 


    # yum localinstall rpmfile  

install与localinstall的区别：install直接通过yum服务器端安装指定包及所有依赖关系，而localinstall是本地已有rpm文件，只要到yum服务器上安装依赖关系。 


    # yum grouplist  



显示所有yum服务器定义的组 


    # yum groupinstall packagegroup  


一次性安装yum服务器上定义的一组包 


    # yum remove package  
    # yum groupremove packagegroup  
    # yum search searcherm  


查找yum服务器上所有符合searcherm关键字的内容 


    # yum list [all]  


列出yum服务器所有可用的包 


    # yum info package  
    # yum groupinfo grouppackgroup  
    # yum whatprovides filename  


### 关闭服务器的高级电源管理 

服务器的高级电源管理可能会造成网络异常，如防火墙模块被卸载等问题： 

kernel: Removing netfilter NETLINK layer. 


    service acpid stop  
    chkconfig acpid off  


