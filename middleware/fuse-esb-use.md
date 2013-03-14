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
