JBOSS EAP 4.3 About
===========

### Remove JBoss Messaging

For default instance:

1. remove jms deployer: conf/jboss-service.xml

        change: <attribute name="JMSService">jboss.messaging:service=ServerPeer</attribute>
        to    : <!--attribute name="JMSService">jboss.messaging:service=ServerPeer</attribute-->

2. remove deployment files

        deploy/jboss-messaging.sar
        deploy/jms-ds.xml
        deploy/jms-ra.rar

3. remove jms recover: conf/jbossjta-properties.xml

        change: <property name="com.arjuna.ats.jta.recovery.XAResourceRecovery.JBMESSAGING1"
                  value="org.jboss.jms.server.recovery.MessagingXAResourceRecovery;java:/DefaultJMSProvider"/>
        to    : <!--property name="com.arjuna.ats.jta.recovery.XAResourceRecovery.JBMESSAGING1"
                  value="org.jboss.jms.server.recovery.MessagingXAResourceRecovery;java:/DefaultJMSProvider"/ -->

### Remove Hibernate Lib

Sometimes the jboss hibernate lib conflict will cause the some unknown exceptions...

Just remove those libs will solve the problem.

	ejb3-persistence.jar
	hibernate3.jar
	hibernate-annotations.jar
	hibernate-commons-annotations.jar
	hibernate-entitymanager.jar
	hibernate-validator.jar

### org.jboss.invocation.pooled.server.PooledInvoker

PooledInvoker - RMI/Socket

The org.jboss.invocation.pooled.server.PooledInvoker is an MBean service that provides RMI over a custom socket transport implementation of the Invoker interface. The PooledInvoker exports itself as an RMI server so that when it is used as the Invoker in a remote client, the PooledInvoker stub is sent to the client instead and invocations use the custom socket protocol. 

The PooledInvoker MBean supports a number of attribute to configure the socket transport layer. Its configurable attributes are:

	NumAcceptThreads: The number of threads that exist for accepting client connections. The default is 1.
	MaxPoolSize: The number of server threads for processing client. The default is 300.
	SocketTimeout: The socket timeout value passed to the Socket.setSoTimeout() method. The default is 60000.
	ServerBindPort: The port used for the server socket. A value of 0 indicates that an anonymous port should be chosen.
	ClientConnectAddress: The address that the client passes to the Socket(addr, port) constructor. This defaults to the server InetAddress.getLocalHost() value.
	ClientConnectPort: The port that the client passes to the Socket(addr, port) constructor. The default is the port of the server listening socket.
	ClientMaxPoolSize: The client side maximum number of threads. The default is 300.
	Backlog: The backlog associated with the server accept socket. The default is 200.
	EnableTcpNoDelay: A boolean flag indicating if client sockets will enable the TcpNoDelay flag on the socket. The default is false.
	ServerBindAddress: The address on which the server binds its listening socket. The default is an empty value which indicates the server should be bound on all interfaces.
	TransactionManagerService: The JMX ObjectName of the JTA transaction manager service.
	ClientSocketFactoryName: the javax.net.SocketFactory implementation class name to use on the client
	ServerSocketFactoryName: the javax.net.ServerSocketFactory implementation class name to use on the server. See ServerSocketFactory as well.
	ServerSocketFactory : an instantiated javax.net.ServerSocketFactory implementation to use on the server. See the Example SSL Config below for a usage.
	
	<mbean code="org.jboss.invocation.pooled.server.PooledInvoker"
	      name="jboss:service=invoker,type=pooled">
	      <attribute name="NumAcceptThreads">1</attribute>
	      <attribute name="MaxPoolSize">300</attribute>
	      <attribute name="ClientMaxPoolSize">300</attribute>
	      <attribute name="SocketTimeout">60000</attribute>
	      <attribute name="ServerBindAddress">${jboss.bind.address}</attribute>
	      <attribute name="ServerBindPort">4445</attribute>
	      <attribute name="ClientConnectAddress">${jboss.bind.address}</attribute>
	      <attribute name="ClientConnectPort">0</attribute>
	      <attribute name="EnableTcpNoDelay">false</attribute>
	      <depends optional-attribute-name="TransactionManagerService">jboss:service=TransactionManager</depends>
	</mbean>


### Add RHEL Service

1. Write a service script, and copy into /etc/init.d/ director, such as jboss

		vi /etc/init.d/jboss
		chmod +x /etc/init.d/jboss

2. Add to startup

		chkconfig --add jboss
		chkconfig jboss on

3. Modify "jboss-eap-4.3/jboss-as/server/default/conf/props/jmx-console-users.properties" file to enable username and password

		admin=admin

4. The service script file, you need to modify the JBOSS_HOME, JBOSSSH, JBOSS_SHUTDOWN, JBOSS_START_USER

		#! /bin/sh
		#
		# chkconfig: - 85 15
		# description: JBoss for application
		#
		
		#define where jboss is - this is the directory containing directories log, bin, conf etc
		JBOSS_HOME=${JBOSS_HOME:-"/usr/local/jboss-eap-4.3/jboss-as"}
		
		#define the script to use to start jboss
		JBOSSSH=${JBOSSSH:-"$JBOSS_HOME/bin/run.sh -c default -b 0.0.0.0"}
		
		#define what will be done with the console log
		#JBOSS_CONSOLE=${JBOSS_CONSOLE:-"$JBOSS_HOME/bin/nohup.out"}
		JBOSS_CONSOLE=${JBOSS_CONSOLE:-"/dev/null"}
		
		#define shutdown scripts
		JBOSS_SHUTDOWN=${JBOSS_SHUTDOWN:-"-s jnp://localhost:1099 -u admin -p admin"}
		
		#define the startup user
		JBOSS_START_USER=${JBOSS_START_USER:-"root"}
		
		
		start(){
		        echo "Starting jboss.."
		# If using an SELinux system such as RHEL 4, use the command below
		        # instead of the "su":
		        # eval "runuser - jboss -c '/opt/jboss/current/bin/run.sh > /dev/null 2> /dev/null &'
		        # if the 'su -l ...' command fails (the -l flag is not recognized by my su cmd) try:
		        #   sudo -u jboss /opt/jboss/bin/run.sh > /dev/null 2> /dev/null &
		        # sleep 5
		        # mount -a
			if [ `whoami` == ${JBOSS_START_USER} ]; then
				${JBOSSSH} > ${JBOSS_CONSOLE} 2> ${JBOSS_CONSOLE} &	
			else 
		        	su -l ${JBOSS_START_USER} -c "${JBOSSSH} > ${JBOSS_CONSOLE} 2> ${JBOSS_CONSOLE} &"
			fi
		}
		
		stop(){
		        echo "Stopping jboss.."
		
		        # If using an SELinux system such as RHEL 4, use the command below
		        # instead of the "su":
		        # eval "runuser - jboss -c '/opt/jboss/current/bin/shutdown.sh -S &'
		        # if the 'su -l ...' command fails try:
		        #   sudo -u root /opt/jboss/bin/shutdown.sh -S &
			if [ `whoami` == ${JBOSS_START_USER} ]; then
				${JBOSS_HOME}/bin/shutdown.sh ${JBOSS_SHUTDOWN} -S &
			else
				su -l ${JBOSS_START_USER} -c "${JBOSS_HOME}/bin/shutdown.sh ${JBOSS_SHUTDOWN} -S &"
			fi
		        sleep 60
		        forceStop
		}
		
		
		# not use again
		restart(){
		        stop
		# give stuff some time to stop before we restart
		        #sleep 60
		# force stop jboss if it still alive
			 #forceStop
		# protect against any services that can't stop before we restart (warning this kills all Java instances running as 'jboss' user)
		        # su -l root -c 'killall java'
		# if the 'su -l ...' command fails try:
		        #   sudo -u root killall java
		        start
		}
		
		function procrunning() {
		   procid=0
		   JBOSSSCRIPT=$(echo $JBOSSSH | awk '{print $1}' | sed 's/\//\\\//g')
		   for procid in `/sbin/pidof -x "$JBOSSSCRIPT"`; do
		       ps -fp $procid | grep "${JBOSSSH% *}" > /dev/null && pid=$procid
		   done
		}
		
		function forceStop() {
		    pid=0
		    procrunning
		    if [ $pid = '0' ]; then
		        echo -n -e "\nNo JBossas is currently running\n"
		        exit 1
		    fi
		
		    RETVAL=1
		
		    # If process is still running
		
		    # First, try to kill it nicely
		    for id in `ps --ppid $pid | awk '{print $1}' | grep -v "^PID$"`; do
		       if [ -z "$SUBIT" ]; then
		           kill -15 $id
		       else
		           $SUBIT "kill -15 $id"
		       fi
		    done
		
		    sleep=0
		    while [ $sleep -lt 120 -a $RETVAL -eq 1 ]; do
		        echo -n -e "\nwaiting for processes to stop";
		        sleep 10
		        sleep=`expr $sleep + 10`
		        pid=0
		        procrunning
		        if [ $pid == '0' ]; then
		            RETVAL=0
		        fi
		    done
		
		    # Still not dead... kill it
		
		    count=0
		    pid=0
		    procrunning
		
		    if [ $RETVAL != 0 ] ; then
		        echo -e "\nTimeout: Shutdown command was sent, but process is still running with PID $pid"
		        exit 1
		    fi
		
		    echo
		    exit 0
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
		  *)
		        echo "Usage: gsmsweb {start|stop|restart}"
		        exit 1
		esac
		
		exit 0


