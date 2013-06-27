JBOSS as7/eap6 相关
============

### 设置JVM启动系统属性

在<extensions></extensions>标签后添加系统属性，如下，添加SSL信任证书

    <system-properties>
        <property name="javax.net.ssl.trustStore" value="/path/to/localhost.jks"/>
        <property name="javax.net.ssl.trustStorePassword" value="abc123"/>
    </system-properties>


### 添加HTTPS支持

生成的证书alias=jboss，password=创建证书的密码，certificate-key-file=证书路径

    <connector name="http" protocol="HTTP/1.1" scheme="http" socket-binding="http"/>
    <connector name="https" protocol="HTTP/1.1" socket-binding="https" scheme="https" secure="true">
        <ssl name="https" password="changeit" certificate-key-file="../standalone/configuration/server.keystore"/>
    </connector>

### Create cluster in standalone mode

copy from [http://middlewaremagic.com/jboss/?p=1952](http://middlewaremagic.com/jboss/?p=1952)

1. Cluster on same box

        Steps:
        1) JBoss Cluster configuration
            
            === Unzip jboss-as-7.1.1.Final.zip
            
            === Copy two "standalone" and rename to "node1" and "node2", such as:
            /home/user/jboss-as-7.1.1.Final/node1
            /home/user/jboss-as-7.1.1.Final/node2
        
            === Start node1 and node2:
            ./standalone.sh -c standalone-ha.xml -b 0.0.0.0 -u 230.0.0.4 -Djboss.server.base.dir=../node1 -Djboss.node.name=node1 -Djboss.socket.binding.port-offset=100
            ./standalone.sh -c standalone-ha.xml -b 0.0.0.0 -u 230.0.0.4 -Djboss.server.base.dir=../node2 -Djboss.node.name=node2 -Djboss.socket.binding.port-offset=200
            
            === The cluster parameters:
            -c = is for server configuration file to be used
            -b = is for binding address
            -u = is for multicast address
            -Djboss.server.base.dir = is for the path from where node is present
            -Djboss.node.name = is for the name of the node
            -Djboss.socket.binding.port-offset = is for the port offset on which node would be running
            
            === Note: However we need to keep in mind the following things 
            Both the nodes should have same multicast address
            Both the nodes should have different node names
            Both the nodes should have different socket binding port-offsets
        
        2) Application Cluster configuration
        
            === Add <distributable/> tag to web.xml
            
            === Deploy ClusterWebApp.war into  /home/user/jboss-as-7.1.1.Final/node?/deployments
            
2. Cluster on different boxes

        Steps:
        1) JBoss Cluster configuration
            
            === Unzip jboss-as-7.1.1.Final.zip
            
            === Copy "standalone" and rename to "node1" and "node2" in two servers, such as:
            Server1: 10.10.10.10
            /home/user/jboss-as-7.1.1.Final/node1
            Server2: 20.20.20.20
            /home/user/jboss-as-7.1.1.Final/node2
            
            === Note: However we need to keep in mind the following things
            Both the nodes should have same multicast address
            Both the nodes should have different node names
            Both the nodes should be running on the IP_ADDRESS or HOST_NAME of the box

            === Start node1 and node2:
            ./standalone.sh -c standalone-ha.xml -b 10.10.10.10 -u 230.0.0.4 -Djboss.server.base.dir=../node1 -Djboss.node.name=node1
            ./standalone.sh -c standalone-ha.xml -b 20.20.20.20 -u 230.0.0.4 -Djboss.server.base.dir=../node2 -Djboss.node.name=node2

        2) Application Cluster configuration
        
            === Add <distributable/> tag to web.xml
            
            === Deploy ClusterWebApp.war into  /home/user/jboss-as-7.1.1.Final/node?/deployments
            
Download the cluster app file: [ClusterWebApp.war](ClusterWebApp.war)

### Start JBoss as Service

copy from [https://community.jboss.org/thread/176251](https://community.jboss.org/thread/176251)

You need to configure JAVA_HOME, JBOSS_HOME, JBOSS_USER, JBOSS_CONFIG, BINDING_IP, UDP_IP, NODE_NAME, JBOSS_INSTALCE.

        #!/bin/sh
        #
        # JBoss standalone control script
        #
        # chkconfig: - 80 20
        # description: JBoss AS Standalone
        # processname: standalone
        # pidfile: $JBOSS_HOME/$JBOSS_INSTALCE/$JBOSS_INSTALCE.pid
        # config: /etc/jboss-as/jboss-as.conf
         
        # Source function library.
        . /etc/init.d/functions
        
        JAVA_HOME=/usr/local/jdk1.6.0_37
        JBOSS_HOME=/usr/local/jboss-as-7.1.1.Final
        JBOSS_USER=root
        NODE_NAME=node53
        JBOSS_INSTALCE=node
        JBOSS_CONFIG=standalone-ha.xml
        #Please specify a real IP address (such as NIC address 192.168.1.1), otherwise, the cluster won't work!!! 
        BINDING_IP=0.0.0.0
        UDP_IP=230.2.3.5
        JBOSS_PIDFILE="$JBOSS_HOME/$JBOSS_INSTALCE/$JBOSS_INSTALCE.pid"
        JBOSS_CONSOLE_LOG="$JBOSS_HOME/$JBOSS_INSTALCE/log/console.log"
        BASE_DIR="$JBOSS_HOME/$JBOSS_INSTALCE"
        DEPLOY_STATUS_FILE="$JBOSS_HOME/$JBOSS_INSTALCE/deployments/*.war.*"
        
         
        # Load Java configuration.
        [ -r /etc/java/java.conf ] && . /etc/java/java.conf
        export JAVA_HOME
         
        # Load JBoss AS init.d configuration.
        if [ -z "$JBOSS_CONF" ]; then
          JBOSS_CONF="/etc/jboss-as/jboss-as.conf"
        fi
         
        [ -r "$JBOSS_CONF" ] && . "${JBOSS_CONF}"
         
        # Set defaults.
         
        if [ -z "$JBOSS_HOME" ]; then
          JBOSS_HOME=/usr/share/jboss-as
        fi
        export JBOSS_HOME
         
        if [ -z "$JBOSS_PIDFILE" ]; then
          JBOSS_PIDFILE=/var/run/jboss-as/jboss-as-standalone.pid
        fi
        export JBOSS_PIDFILE
         
        if [ -z "$JBOSS_CONSOLE_LOG" ]; then
          JBOSS_CONSOLE_LOG=/var/log/jboss-as/console.log
        fi
         
        if [ -z "$JBOSS_USER" ]; then
          JBOSS_USER=jboss
        fi
         
        if [ -z "$STARTUP_WAIT" ]; then
          STARTUP_WAIT=30
        fi
         
        if [ -z "$SHUTDOWN_WAIT" ]; then
          SHUTDOWN_WAIT=30
        fi
         
        if [ -z "$JBOSS_CONFIG" ]; then
          JBOSS_CONFIG=standalone.xml
        fi
         
        JBOSS_SCRIPT="$JBOSS_HOME/bin/standalone.sh -c $JBOSS_CONFIG -b $BINDING_IP -u $UDP_IP -Djboss.server.base.dir=$BASE_DIR -Djboss.node.name=$NODE_NAME"
         
        prog='jboss-as'
         
        CMD_PREFIX=''
         
        if [ ! -z "$JBOSS_USER" ]; then
          if [ -x /etc/rc.d/init.d/functions ]; then
            CMD_PREFIX="daemon --user $JBOSS_USER"
          else
            CMD_PREFIX="su - $JBOSS_USER -c"
          fi
        fi
         
        start() {
          echo -n "Starting $prog: "
          if [ -f $JBOSS_PIDFILE ]; then
            read ppid < $JBOSS_PIDFILE
            if [ `ps --pid $ppid 2> /dev/null | grep -c $ppid 2> /dev/null` -eq '1' ]; then
              echo -n "$prog is already running"
              failure
              echo
              return 1
            else
              rm -f $JBOSS_PIDFILE
            fi
          fi
          mkdir -p $(dirname $JBOSS_CONSOLE_LOG)
          cat /dev/null > $JBOSS_CONSOLE_LOG
         
          mkdir -p $(dirname $JBOSS_PIDFILE)
          chown $JBOSS_USER $(dirname $JBOSS_PIDFILE) || true
          #$CMD_PREFIX JBOSS_PIDFILE=$JBOSS_PIDFILE $JBOSS_SCRIPT 2>&1 > $JBOSS_CONSOLE_LOG &
          #$CMD_PREFIX JBOSS_PIDFILE=$JBOSS_PIDFILE $JBOSS_SCRIPT &
          
          #remove deploy status
          rm -rf $DEPLOY_STATUS_FILE
         
          if [ ! -z "$JBOSS_USER" ]; then
            if [ -x /etc/rc.d/init.d/functions ]; then
              daemon --user $JBOSS_USER LAUNCH_JBOSS_IN_BACKGROUND=1 JBOSS_PIDFILE=$JBOSS_PIDFILE $JBOSS_SCRIPT 2>&1 > $JBOSS_CONSOLE_LOG &
            else
              su - $JBOSS_USER -c "LAUNCH_JBOSS_IN_BACKGROUND=1 JBOSS_PIDFILE=$JBOSS_PIDFILE $JBOSS_SCRIPT" 2>&1 > $JBOSS_CONSOLE_LOG &
            fi
          fi
         
          count=0
          launched=false
         
          until [ $count -gt $STARTUP_WAIT ]
          do
            grep 'JBoss AS.*started in' $JBOSS_CONSOLE_LOG > /dev/null
            if [ $? -eq 0 ] ; then
              launched=true
              break
            fi
            sleep 1
            let count=$count+1;
          done
         
          success
          echo
          return 0
        }
         
        stop() {
          echo -n $"Stopping $prog: "
          count=0;
         
          if [ -f $JBOSS_PIDFILE ]; then
            read kpid < $JBOSS_PIDFILE
            let kwait=$SHUTDOWN_WAIT
         
            # Try issuing SIGTERM
         
            kill -15 $kpid
            until [ `ps --pid $kpid 2> /dev/null | grep -c $kpid 2> /dev/null` -eq '0' ] || [ $count -gt $kwait ]
            do
              sleep 1
              let count=$count+1;
            done
         
            if [ $count -gt $kwait ]; then
              kill -9 $kpid
            fi
          fi
          rm -f $JBOSS_PIDFILE
          success
          echo
        }
         
        status() {
          if [ -f $JBOSS_PIDFILE ]; then
            read ppid < $JBOSS_PIDFILE
            if [ `ps --pid $ppid 2> /dev/null | grep -c $ppid 2> /dev/null` -eq '1' ]; then
              echo "$prog is running (pid $ppid)"
              return 0
            fi
          fi
          echo "$prog is not running"
        }
         
        case "$1" in
          start)
              start
              ;;
          stop)
              stop
              ;;
          restart)
              $0 stop
              $0 start
              ;;
          status)
              status
              ;;
          *)
              ## If no parameters are given, print which are avaiable.
              echo "Usage: $0 {start|stop|status|restart|reload}"
              exit 1
              ;;
        esac



