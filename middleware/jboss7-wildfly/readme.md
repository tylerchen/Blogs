JBoss AS7 & Wildfly Configuration
====

### Files List

Install Scripts: [scripts/install-env.sh](install-env.sh), [scripts/install-jdk.sh](install-jdk.sh), [scripts/install-jboss.sh](install-jboss.sh), [scripts/start-jboss.sh](start-jboss.sh), [scripts/stop-jboss.sh](stop-jboss.sh)

A Cluster Test Application: [scripts/test-application.war](test-application.war)

A JDBC Module (mysql): [scripts/com.msql.tar.gz](com.msql.tar.gz)

A Mod_cluster: [scripts/mod_cluster-1.2.0.Final-linux2-x64-ssl.tar.gz](mod_cluster-1.2.0.Final-linux2-x64-ssl.tar.gz)

### Howto Use the Install Scripts

1. Copy all files (Files List) to /opt directory and make shell scripts executable: "chmod a+x /opt/*.sh"
2. Download JDK7(gz distribute) to /opt directory and rename to "jdk-7u25-linux-x64.gz" or change the file name in "install-jdk.sh"
3. Download Wildfly to /opt directory and rename to "wildfly-8.0.0.Final.zip" or change the file name in "install-jboss.sh"
4. In /opt directory execute: "./install-jboss.sh", that will install 3 host in domain/configuration "cluster-host-master.xml, cluster-host-node1.xml, cluster-host-node2.xml"
5. There are 3 default users: test/test1234, node1/test1234, node2/test1234
6. Start a Domain Controller: "./start-jboss.sh"
7. Start a Host Controller: "./start-jboss.sh [node1|node2] masterip" (example: ./start-jboss.sh node1 192.168.1.1)