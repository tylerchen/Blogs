     
     
     
     
     
     
     
     
    JBOSS中间件安全控制方案
     
     
     
     
     
     
     
     
    版本
    日期
    修改历史
    作者
    0.1
    2013年4月11日
    文档创建
    陈照
     
     
     
     
     
     
     
     
    目 录
    1背景
    2应用服务器的安全配置
    2.1Linux防火墙配置
    2.2JBoss中间件的安全配置
    2.2.1方法一：通过Apache进行服务转发
    2.2.2方法二：JBoss的安全配置
    2.3安全配置脚本实例
    2.3.1防火墙规则设定的脚本
    2.3.2删除jboss控制台的脚本
    1背景
    本文档是针对明珠商城在测试环境下，为了只让信任的IP访问指定的服务，减少系统的运行风险，为此引入了iptables服务进行防护。Jboss本身的安全问题也要做些处理，比如对外屏蔽jboss其他（除8080）众多端口，把控制台等模块服务给移除等方式，把安全漏洞降到最低。
    2应用服务器的安全配置
    2.1Linux防火墙配置
    设置环境:
    服务器
    IP地址
           描述
    服务端
    192.168.145.128
       被保护的应用服务器
    客户端A
    192.168.145.129
       不被信任的客户端
    客户端B
    192.168.145.1
       信任的客户端
     
     示意图：
    
     
    设置防火墙步骤:
    #清除现有规则
    iptables –F 
      #设置默认策略,默认关闭进入应用服务器的所有数据包链路
    iptables -P INPUT DROP
    iptables -P OUTPUT ACCEPT
    iptables -P FORWARD ACCEPT
     
    #设置可信任IP或端口
    iptables -A INPUT  -p tcp --dport 22 -j ACCEPT
    iptables -A INPUT -s 192.168.145.1  -p tcp --dport 8080 -j ACCEPT
     
    #把设置保持至/etc/sysconfig/iptables，以后防火墙重启也不会失效
    /etc/rc.d/init.d/iptables save
     
     
    2.2JBoss中间件的安全配置
    Jboss默认安装时，为了管理配置方便，很多服务都给默认安装上了，比如Tomcat status (full) (XML)、JMX Console 、JBoss Web Console 等管理服务，这些服务给人带来调试、配置方便的同时，也给系统带来了严重的安全隐患。为了减少jboss服务被恶意攻击，除了做好iptables安全防护外，jboss本身的很多服务也要减少被攻击机会，通常有两种做法，一是引入apache服务，让所有请求通过apache转发，把jboss服务隐藏在后端，减少直接被攻击的风险；二是不用apache，而是直接把jboss的相关服务给卸载掉，把这些安全漏洞直接堵死。
    2.2.1方法一：通过Apache进行服务转发
     要实现apache转发jboss服务，需集成mod_jk或是mod_proxy其中一个模块，这两个模块都可以做负载均衡和代理服务。mod_jk性能好些但相对而言配置量大些，而mod_proxy配置简单但性能稍差，其版本不断更新，正在不断完善中。另外需注意的是如果要通过apache转发服务，隐藏jboss细节的话，前提条件是apache服务与jboss应用不能部署在同一台服务器上，这里以mod_jk集成为例进行配置介绍。
    2.2.1.1安装apache服务
    安装linux是自带，这里就不介绍
    2.2.1.2安装mod_jk模块
     1).源码安装
    tar –zxvf tomcat-connectors-1.2.30-src.tar.gz
    cd tomcat-connectors-1.2.23-src
    cd native
    ./configure --with-apxs=/usr/local/apache/bin/apxs
    make
    cp ./apache-2.0/mod_jk.so /etc/httpd/modules/
     
    2).修改/etc/httpd/conf/http.conf配置文件
       LoadModule jk_module modules/mod_jk.so 
       Include conf/extra/httpd-vhosts.conf
    Include conf/mod_jk.conf 
    #ServerName www.example.com:80改为ServerName 127.0.0.1:80
    添加默认首页：
    <IfModule dir_module> 
    DirectoryIndex index.html index.htm index.jsp  
    </IfModule> 
     
     3). 增加mod_jk配置文件
    在/etc/httpd/conf/下面建立两个配置文件mod_jk.conf和workers.properties
    vi /etc/httpd/conf/mod_jk.conf
    在/etc/httpd/conf/下面建立两个配置文件mod_jk.conf和workers.properties
    cd /etc/httpd/conf/
     
    vi mod_jk.conf
    JkWorkersFile conf/workers.properties 
    JkLogFile logs/mod_jk.log 
    JkLogLevel info 
    JkLogStampFormat "[%a %b %d %H:%M:%S %Y]" 
    JkOptions +ForwardKeySize +ForwardURICompat -ForwardDirectories 
    JkRequestLogFormat "%w %V %T"
     
    vim workers.properties
    #Defining a worker named worker1 and of type ajp13 
    worker.list=worker1
    #Set properties for worker1 
    worker.worker1.type=ajp13
    worker.worker1.host=localhost
    worker.worker1.port=8009
    worker.worker1.lbfactor=50
    worker.worker1.cachesize=10
    worker.worker1.cache_timeout=600
    worker.worker1.socket_keepalive=1 
    worker.worker1.socket_timeout=300
     
      4). 配置apache的vhost
          配置/usr/local/apache/conf/extra/httpd-vhosts.conf，增加mod_jk的配置
    vi /etc/httpd/conf/extra/httpd-vhosts.conf
    NameVirtualHost *:80 
    # VirtualHost example: 
    # Almost any Apache directive may go into a VirtualHost container. 
    # The first VirtualHost section is used for all requests that do not 
    # match a ServerName or ServerAlias in any <VirtualHost> block. 
    # 
    <VirtualHost *:80>
        ServerAdmin caozhiping@foreveross.com 
        DocumentRoot "/usr/local/jboss-4.2.3.GA/server/default/deploy" 
        ServerName 192.168.145.128 
        ServerAlias www.foreveross.com
        JkMount /*.jsp worker1 
        JkMount /jmx-console/* worker1           //这个工程能通过80端口来访问 
        JkMount /web-console/* worker1           //这个工程能通过80端口来访问,如果没有定义的工程，不能访问 
        #apache will serve the static picture 
        JkUnMount /*.jpg worker1 
        #JkUnMount /*.gif worker1 
        JkUnMount /*.swf worker1 
        JkUnMount /*.bmp worker1 
        JkUnMount /*.png worker1 
        ErrorLog "logs/dummy-host.example.com-error_log" 
        CustomLog "logs/dummy-host.example.com-access_log" common 
    </VirtualHost>
     
    2.2.1.3禁止jboss配置
        jboss默认的端口是8080，可以注视掉，通过8009交给apache来解析
    cd /usr/local/jboss-4.2.3.GA/server/default/deploy/jboss-web.deployer
    vim server.xml
    <!-- 
    <Connector port="8080" address="${jboss.bind.address}"     
         maxThreads="250" maxHttpHeaderSize="8192"
         emptySessionPath="true" protocol="HTTP/1.1"
         enableLookups="false" redirectPort="8443" acceptCount="100"
         connectionTimeout="20000" disableUploadTimeout="true" />
    -->
    这这一段注视掉
     
    2.2.2方法二：JBoss的安全配置
    这种方式是通过删除jboss本身的一些在开发环境下有利于开发人员工作需要的服务，以及关闭管理账户、限制IP等方式加强jboss安全
    2.2.2.1移除jmx-console控制台
    rm  -r /usr/local/jboss/server/default/deploy/ jmx-console.war
    rm  –r /usr/local/jboss/server/default/deploy/jbossws.sar/jbossws-context.war
    rm  –r  /usr/local/jboss/server/default/deploy/management/console-mgr.sar/web-console.war
    2.2.2.2移除web-console控制台
    rm  –r /usr/local/jboss/server/default/deploy/jboss-web.deployer/ROOT.war
    rm  –r  /usr/local/jboss/server/default/deploy/jboss-web.deployer/context.xml
     
    测试效果
    访问：http://192.168.145.8080/jmx-console
    
     
    访问：http://192.168.145.8080/web-console
    
    2.2.2.3禁止自动扫描
    修改文件：JBOSS_HOME/server/web/conf/jboss-service.xml
    修改内容：<attribute name="ScanEnabled">false</attribute>false：表示禁用。这样设置可以提高性能,同时JBoss应用被修改,也不会马上生效（必须重启），但是这样设置之后代表热部署不再支持。这里可以根据实际情况权衡利弊然后再进行设置。
     
    2.2.2.4限制访问IP
    JBOSS 4.2以上版本服务启动如果不加任何参数的话,只监听127.0.0.1,就是说只能用127.0.0.1或者localhost访问。可以通过参数-b ip地址 来绑定监听的地址。如：
    所有IP都能访问：-b 0.0.0.0
    所有同局域网机器都可以访问：-b 10.101.1.100 （10.101.1.100为Jboss服务器IP）
     
    2.2.2.5移除管理用户
    注释/usr/local/jboss/server/default/conf/props文件夹里面的配置文件所有用户与角色
    vi jbossws-roles.properties
    # A sample roles.properties file for use with the UsersRolesLoginModule
    #kermit=friend
     
    vi jbossws-users.properties
    # A sample users.properties file for use with the UsersRolesLoginModule
    #kermit=thefrog
     
    vi jmx-console-roles.properties
    # A sample roles.properties file for use with the UsersRolesLoginModule
    #admin=JBossAdmin,HttpInvoker
     
    vi jmx-console-users.properties
    # A sample users.properties file for use with the UsersRolesLoginModule
    #admin=admin
     
    2.3安全配置脚本实例
    2.3.1防火墙规则设定的脚本
    #!/bin/bash
    # Program:
    #       This program configure your iptables.
    # History:
    # 2013/04/11    caozhiping      First release
    PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
    export PATH
    echo -e "Execute Iptables configure! \a \n"
    #clean rule table
    iptables -F
    iptables -t nat -F
    #setting default rule
    iptables -P INPUT DROP
    iptables -P OUTPUT ACCEPT
    iptables -P FORWARD ACCEPT
    # permit ping command
    #iptables -A INPUT -p icmp -j ACCEPT
    # permit source address:192.168.145.1 into destination port 8080
    iptables -A INPUT  -p tcp --dport 22 -j ACCEPT
    iptables -A INPUT –s 192.168.145.1  -p tcp --dport 8080 -j ACCEPT
    #data packet of relative local computer can pass firewall
    iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
    #save this configure information into /etc/sysconfig/iptables file
    /etc/rc.d/init.d/iptables save
    exit 0
     
    2.3.2删除jboss控制台的脚本
    #!/bin/bash
    # Program:
    #       This program delete jboss console relative file
    # History:
    # 2013/04/11    caozhiping      First release
    #let user input jboss deploy home,for example:/usr/local/jboss/server/all
    # or usr/local/jboss/server/default
    read –p “Please input jboss deplay home:” JBOSS_DEPLAY_HOME
    rm –r  $ JBOSS_DEPLAY_HOME/deploy/jmx-console.war
    rm  –r $ JBOSS_DEPLAY_HOME/deploy/jbossws.sar/jbossws-context.war
    rm –r  $ JBOSS_DEPLAY_HOME/deploy/management/console-mgr.sar/web-console.war
    rm –r  $ JBOSS_DEPLAY_HOME/deploy/jboss-web.deployer/ROOT.war
    rm –r  $ JBOSS_DEPLAY_HOME/deploy/jboss-web.deployer/context.xml
    exit 0
    
    -A PREROUTING -d 10.109.111.30/32 -p tcp -m tcp --dport 80 -j REDIRECT --to-ports 8888
    -A PREROUTING -p tcp -m tcp --dport 80 -j REDIRECT --to-ports 8888
    
    
