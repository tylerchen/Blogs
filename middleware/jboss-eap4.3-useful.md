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
