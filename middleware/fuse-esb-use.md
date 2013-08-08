Fuse ESB EAP 7 使用总结
======

#### 修改端口

1. 修改控制台的默认端口8181

    修改/etc/jetty.xml中的default端口，如：

        <Property name="jetty.port" default="3131"/>
        <Set name="confidentialPort">8443</Set>

    修改/etc/system.properties中的配置，如：

        org.osgi.service.http.port=3131

2. 修改ActiveMQ的端口

    修改/etc/system.properties中的配置，如：

        activemq.port = 41616

3. 修改jmx的端口

    修改/etc/org.apache.karaf.management.cfg中的配置，如：

        rmiRegistryPort = 3099
        rmiServerPort = 24444

    修改/etc/system.properties中的配置，如：

        activemq.jmx.url=service:jmx:rmi:///jndi/rmi://localhost:3099/karaf-${karaf.name}

#### 去除ActiveMQ登录验证

1. 如果默认没有配置ActiveMQ的登录验证访问MQ的时候会抛出异常

        javax.jms.JMSException: User name [null] or password is invalid.
        
2. 修改/etc/activemq.xml，注释Jass验证

        <!jaasAuthenticationPlugin configuration="karaf" />
        
#### ActiveMQ集群

1. 添加静态的集群IP

    修改/etc/activemq.xml，对于每个Fuse ESB都添加networkConnectors，可以指定多个IP：
    
        <networkConnectors>
            <networkConnector uri="static://(tcp://localhost:51616,tcp://localhost:41616)" />
    	</networkConnectors>
        
2. 添加广播地址

    修改/etc/activemq.xml，对于每个Fuse ESB都discoveryUri：

        <transportConnectors>
            <transportConnector name="openwire" uri="tcp://0.0.0.0:0?maximumConnections=1000" discoveryUri="multicast://default"/>
        </transportConnectors>

#### 解决启动异常：java.rmi.UnknownHostException: Unknown host: 0.0.0.0

1. 异常信息

        Exception in thread "JMX Connector Thread [service:jmx:rmi://0.0.0.0:44444/jndi/rmi://0.0.0.0:1099/karaf-root]" java.lang.RuntimeException: Could not start JMX connector server
                at org.apache.karaf.management.ConnectorServerFactory$1.run(ConnectorServerFactory.java:252)
        Caused by: java.io.IOException: Cannot bind to URL [rmi://0.0.0.0:1099/karaf-root]: javax.naming.ConfigurationException [Root exception is java.rmi.UnknownHostException: Unknown host: 0.0.0.0; nested exception is: 
                java.net.UnknownHostException: CustomerManagement: CustomerManagement: Name or service not known]
                at javax.management.remote.rmi.RMIConnectorServer.newIOException(RMIConnectorServer.java:826)
                at javax.management.remote.rmi.RMIConnectorServer.start(RMIConnectorServer.java:431)
                at org.apache.karaf.management.ConnectorServerFactory$1.run(ConnectorServerFactory.java:239)
        Caused by: javax.naming.ConfigurationException [Root exception is java.rmi.UnknownHostException: Unknown host: 0.0.0.0; nested exception is: 
                java.net.UnknownHostException: CustomerManagement: CustomerManagement: Name or service not known]
                at com.sun.jndi.rmi.registry.RegistryContext.bind(RegistryContext.java:143)
                at com.sun.jndi.toolkit.url.GenericURLContext.bind(GenericURLContext.java:226)
                at javax.naming.InitialContext.bind(InitialContext.java:419)
                at javax.management.remote.rmi.RMIConnectorServer.bind(RMIConnectorServer.java:643)
                at javax.management.remote.rmi.RMIConnectorServer.start(RMIConnectorServer.java:426)
                ... 1 more

2. 解决方法

    修改以下文件，把0.0.0.0替换为127.0.0.1：
    
        activemq.xml
        org.apache.karaf.management.cfg
        org.apache.karaf.shell.cfg
        
