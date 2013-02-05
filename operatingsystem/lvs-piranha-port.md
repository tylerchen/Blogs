解决Piranah中"/sbin/ipvsadm command failed!"问题的过程
====

### LVS问题现象

在一次系统部署的过程中测试发现LVS运行过程中出现异常，当一台RS挂掉的时候整个LVS的路由都被清空了(操作系统为RHEL5.5，使用piranha套件)。经过改LVS的配置，改心跳脚本，换piranha的版本，问题依旧，而且无法定位问题的所在，备受打击，以前没有遇到这种情况。不过经过接下来几天的努力终止发现问题所在，并解决了该问题，以下是问题的分析和解决过程。 

### LVS配置


    OS ：RHEL5.5 
    VIP：192.168.142.100:80 
    RS1：192.168.142.158:8080 
    RS2：192.168.142.159:8080


配置如下，心跳脚本就不贴了：


    serial_no = 34
    primary = 192.168.142.160
    service = lvs
    backup_active = 0
    backup = 0.0.0.0
    heartbeat = 1
    heartbeat_port = 539
    keepalive = 30
    deadtime = 18
    network = direct
    nat_nmask = 255.255.255.0
    debug_level = NONE
    virtual diff {
         active = 1
         address = 192.168.142.100 eth0:1
         vip_nmask = 255.255.255.0
         port = 80
         expect = "OK"
         use_regex = 0
         send_program = "/etc/sysconfig/ha/lvs.sh %h"
         load_monitor = none
         scheduler = rr
         protocol = tcp
         timeout = 30
         reentry = 10
         quiesce_server = 0
         server 158 {
             address = 192.168.142.158
             active = 1
             weight = 1
             port = 8080
         }
         server 159 {
             address = 192.168.142.159
             active = 1
             weight = 1
         port = 8080
     }
}


### 系统日志的分析

系统日志(/var/log/messages)打印的信息如下： 

 
    Apr 29 08:34:03 RHEL55 pulse[10082]: STARTING PULSE AS MASTER 
    Apr 29 08:34:21 RHEL55 pulse[10082]: partner dead: activating lvs 
    Apr 29 08:34:21 RHEL55 lvs[10085]: starting virtual service diff active: 80 
    Apr 29 08:34:21 RHEL55 nanny[10089]: starting LVS client monitor for 192.168.142.100:80 -> 192.168.142.158:8080 
    Apr 29 08:34:22 RHEL55 lvs[10085]: create_monitor for diff/128 running as pid 10089 
    Apr 29 08:34:22 RHEL55 lvs[10085]: create_monitor for diff/129 running as pid 10093 
    Apr 29 08:34:22 RHEL55 nanny[10093]: starting LVS client monitor for 192.168.142.100:80 -> 192.168.142.159:8080 
    Apr 29 08:34:22 RHEL55 nanny[10089]: [ active ] making 192.168.142.158:8080 available 
    Apr 29 08:34:22 RHEL55 nanny[10093]: [ active ] making 192.168.142.159:8080 available 
    Apr 29 08:34:27 RHEL55 pulse[10094]: gratuitous lvs arps finished 
    Apr 29 08:35:52 RHEL55 nanny[10089]: Trouble. Received results are not what we expected from (192.168.142.158:8080) 
    Apr 29 08:35:52 RHEL55 nanny[10089]: [inactive] shutting down 192.168.142.158:8080 due to connection failure 
    Apr 29 08:35:52 RHEL55 nanny[10089]: /sbin/ipvsadm command failed! 
    Apr 29 08:35:52 RHEL55 lvs[10085]: nanny died! shutting down lvs 
    Apr 29 08:35:52 RHEL55 lvs[10085]: shutting down virtual service diff 
    Apr 29 08:35:52 RHEL55 nanny[10093]: Terminating due to signal 15 
    Apr 29 08:35:52 RHEL55 nanny[10093]: /sbin/ipvsadm command failed! 
    Apr 29 08:36:56 RHEL55 pulse[10082]: Terminating due to signal 15 


1、其中(见下面代码)的信息表示检测脚本返回的值不是OK时，nanny进程准备把对应的LVS路由(192.168.142.158)删除，是正常的。


    Apr 29 08:35:52 RHEL55 nanny[10089]: Trouble. Received results are not what we expected from (192.168.142.158:8080) 
    Apr 29 08:35:52 RHEL55 nanny[10089]: [inactive] shutting down 192.168.142.158:8080 due to connection failure 


2、其中(见下面代码)的信息表示nanny进程调用/sbin/ipvsadm进行删除LVS路由时出现异常，结果导致整个LVS服务都被shutdown了，这就不正常了。


    Apr 29 08:35:52 RHEL55 nanny[10089]: /sbin/ipvsadm command failed! 
    Apr 29 08:35:52 RHEL55 lvs[10085]: nanny died! shutting down lvs 
    Apr 29 08:35:52 RHEL55 lvs[10085]: shutting down virtual service diff


### 调试源代码

从系统日志只能看出nanny调用/sbin/ipvsadm出错了，但如何出错的，出错在哪里，为什么出错，却不能从该日志中获得信息。只有走最后一步了：调试源代码。 

1. 先下载源码： 


        piranha-0.8.4-16.el5.src.rpm


2. 然后在RHEL系统中安装该源码： 


        rpm -ivh piranha-0.8.4-16.el5.src.rpm


3. 去到打补丁目录，运行命令进行打补丁： 


        cd /usr/src/redhat/SPECS
        rpmbuild -bp piranha.spec


4. 然后去到打完补丁的目录


        cd /usr/src/redhat/BUILD/piranha


经过一翻的查找，找到ipvs_exec.c文件中的runCommand和shutdownDev这两个函数，其中shutdownDev就是RS服务器挂掉(心跳脚本执行不通过)时要执行的函数，我在runCommand函数的开头写了些调试信息，把命令和参数都打印出来，代码如下：


runCommand (char *cmd, int flags, char **argv, int log_flag)
{
  int i=0;
  for(i=0; argv[i] != NULL; i++){
    piranha_log (flags, (char *) "tyler println:%s", argv[i]);
  }
//......


然后执行make，把编译出来的nanny程序替换/usr/sbin/nanny，启动pulse，查看系统日志，当RS服务器挂掉时日志如下(可能与原来的有点差别)： 


    May  5 13:02:28 RHEL5 nanny[8378]: Trouble. Received results are not what we expected from (192.168.142.158:8080) 
    May  5 13:02:28 RHEL5 nanny[8378]: [inactive] shutting down 192.168.142.158:8080 due to connection failure 
    May  5 13:02:28 RHEL5 nanny[8378]: tyler println:/sbin/ipvsadm 
    May  5 13:02:28 RHEL5 nanny[8378]: tyler println:-d 
    May  5 13:02:28 RHEL5 nanny[8378]: tyler println:-t 
    May  5 13:02:28 RHEL5 nanny[8378]: tyler println:192.168.142.100:80:80 
    May  5 13:02:28 RHEL5 nanny[8378]: tyler println:-r 
    May  5 13:02:28 RHEL5 nanny[8378]: tyler println:192.168.142.158:8080 
    May  5 13:02:28 RHEL5 nanny[8378]: /sbin/ipvsadm command failed! 
    May  5 13:02:28 RHEL5 lvs[8370]: nanny died! shutting down lvs 
    May  5 13:02:28 RHEL5 lvs[8370]: shutting down virtual service lvs 


把命令重组一下： 


    /sbin/ipvsadm -d -t 192.168.142.100:80:80 -r 192.168.142.158:8080


拿到控制台中执行，发现执行出错，怪不得nanny都要自杀了。 

其实正确的命令应该如下，RS的IP是不需要带端口的(如果一定要指定端口就指定VIP的端口吧)：


    /sbin/ipvsadm -d -t 192.168.142.100:80:80 -r 192.168.142.158


或


    /sbin/ipvsadm -d -t 192.168.142.100:80:80 -r 192.168.142.158:80


### 给piranha的补丁打上补丁

nanny执行ipvsadm命令出错的原因已经找到了，程序中多加了RS服务器的端口号，知道这个原因就容易多了，下面是对shutdownDev函数的修改：


    //sprintf (remoteName, "%s:%d", inet_ntoa (*remoteAddr), rport);
    sprintf (remoteName, "%s", inet_ntoa (*remoteAddr));


注释了添加端口的方法，改为不添加RS服务器的端口。去掉runCommand函数中的注释，然后执行make，把编译出来的nanny程序替换/usr/sbin/nanny，启动pulse。 

经过测试发现一切正常。

### 总结一下 

经过这次对piranha的调试，可以发现当VIP暴露的端口与RS的端口一致时一般都不会出现该问题，但一但端口对不上的时候问题就可能出现了，这还取决于你的运气好不好，因为一般RS运行都是很稳定的，长期都不会挂机，也就不会出现任何问题，一但出现挂机情况，问题都浮现了。 

修改过的源代码可以打成RPM包，或者是直接就拷程序也可以，涉及的程序有lvsd，pulse，nanny，直接覆盖原程序就OK了，如果要打成RPM包，下面摘录了打RPM包的过程： 


    有些软件包是以.src.rpm结尾的，这类软件包是包含了源代码的rpm包，在安装时需要进行编译。这类软件包有两种安装方法， 
    方法一： 
    1.执行rpm -i your-package.src.rpm 
    2. cd /usr/src/redhat/SPECS 
    3. rpmbuild -bp your-package.specs 一个和你的软件包同名的specs文件 
    4. cd /usr/src/redhat/BUILD/your-package/ 一个和你的软件包同名的目录 
    5. ./configure 这一步和编译普通的源码软件一样，可以加上参数
    也可以具体看该目录下的INSTALL文件，按照指导进行安装
    6. make 
    7. make install
    方法二: 
    1.执行rpm -i you-package.src.rpm 
    2. cd /usr/src/redhat/SPECS 
    前两步和方法一相同 
    3. rpmbuild -bb your-package.specs 一个和你的软件包同名的specs文件 
    这时，在/usr/src/redhat/RPM/i386/ （根据具体包的不同，也可能是i686,noarch等等) 
    在这个目录下，有一个新的rpm包，这个是编译好的二进制文件。 
    执行rpm -i new-package.rpm即可安装完成。


### 相关下载

* 修改过的piranha

* 源码piranha-0.8.4-16.el5.src.zip



