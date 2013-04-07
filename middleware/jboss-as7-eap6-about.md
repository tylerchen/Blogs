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

