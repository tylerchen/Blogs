Redhat Enterprise Linux Install
======

1. 选择安装

    ![](imsages/rhel-01.jpg)

2. 选择安装语言 English

    ![](imsages/rhel-02.jpg)

3. 选择存储空间

    ![](imsages/rhel-03.jpg)

4. 选择“YES”

    ![](imsages/rhel-04.jpg)

5. 修改“Hostname”，设置IP地址(eth0)

    ![](imsages/rhel-05.jpg)

6. 选择时区“Asia/ShangHai”

    ![](imsages/rhel-06.jpg)

7. 磁盘分区

    ![](imsages/rhel-07.jpg)

8. 选择安装软件，“Desktop”，“Load Balancer”，“Customize Now”

    ![](imsages/rhel-08.jpg)

9. 选择“Web Services”，勾选“PHP Support”，“Web Server”，把Apache安装上

    ![](imsages/rhel-09.jpg)

10. 选择“Development”，勾选“Development Tools”，把GCC安装上

    ![](imsages/rhel-10.jpg)

11. 选择“Load Balancer”，勾选“Load Balancer”，把LVS安装上

    ![](imsages/rhel-11.jpg)


12. 选择“Languages”，勾选“Chinese Support”，把中文支持安装上，然后直到安装完成，重启

    ![](imsages/rhel-12.jpg)

13. 配置用户

    ![](imsages/rhel-13.jpg)


14. 完成安装

    ![](imsages/rhel-14.jpg)

15. 禁止“NetworkManager”，不自动管理网络

    ![](imsages/rhel-15.jpg)
  
16. 禁止“SeLinux”，“SELINUX=disabled”，“setenforce 0”可以立即生效，省得麻烦

    ![](imsages/rhel-16.jpg) 
    
17. 网络配置，配置IP、掩码、网关等，“ONBOOT=yes”, “NM_CONTROLLED=no”

    ![](imsages/rhel-17.jpg) 

    