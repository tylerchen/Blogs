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

1. Cluster on same box

        Steps:
        1) Unzip jboss-as-7.1.1.Final.zip
        2) Copy two "standalone" and rename to "node1" and "node2", such as:
            /home/user/jboss-as-7.1.1.Final/node1
            /home/user/jboss-as-7.1.1.Final/node2
        3) Start node1 and node2:
            ./standalone.sh -c standalone-ha.xml -b 0.0.0.0 -u 230.0.0.4 -Djboss.server.base.dir=../node1 -Djboss.node.name=node1 -Djboss.socket.binding.port-offset=100
            ./standalone.sh -c standalone-ha.xml -b 0.0.0.0 -u 230.0.0.4 -Djboss.server.base.dir=../node2 -Djboss.node.name=node2 -Djboss.socket.binding.port-offset=200
        4) The cluster parameters:
            -c = is for server configuration file to be used
            -b = is for binding address
            -u = is for multicast address
            -Djboss.server.base.dir = is for the path from where node is present
            -Djboss.node.name = is for the name of the node
            -Djboss.socket.binding.port-offset = is for the port offset on which node would be running - See more at: http://middlewaremagic.com/jboss/?p=1952#sthash.fn4TsMpv.dpuf
