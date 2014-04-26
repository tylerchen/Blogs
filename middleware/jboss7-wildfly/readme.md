JBoss AS7 & Wildfly Configuration
====

### Files List

1. JBoss7/WildFly Scripts: [install-env.sh](jboss7-wildfly/scripts/install-env.sh), [install-jdk.sh](jboss7-wildfly/scripts/install-jdk.sh), [install-jboss.sh](jboss7-wildfly/scripts/install-jboss.sh), [start-jboss.sh](jboss7-wildfly/scripts/start-jboss.sh), [stop-jboss.sh](jboss7-wildfly/scripts/stop-jboss.sh)
2. Mod_cluster Scripts: [install-mod-cluster.sh](jboss7-wildfly/scripts/install-mod-cluster.sh), [start-mod-cluster.sh](jboss7-wildfly/scripts/start-mod-cluster.sh), [stop-mod-cluster.sh](jboss7-wildfly/scripts/stop-mod-cluster.sh)
3. A Cluster Test Application: [test-application.war](jboss7-wildfly/scripts/test-application.war)
4. A JDBC Module (mysql): [com.msql.tar.gz](jboss7-wildfly/scripts/com.msql.tar.gz)
5. A Mod_cluster: [mod_cluster-1.2.0.Final-linux2-x64-ssl.tar.gz](jboss7-wildfly/scripts/mod_cluster-1.2.0.Final-linux2-x64-ssl.tar.gz)

### Howto Install JBoss7/Wildfly

1. Copy all files (Files List) to /opt directory and make shell scripts executable: "chmod a+x /opt/*.sh"
2. Download JDK7(gz distribute) to /opt directory and rename to "jdk-7u25-linux-x64.gz" or change the file name in "install-jdk.sh"
3. Download Wildfly to /opt directory and rename to "wildfly-8.0.0.Final.zip" or change the file name in "install-jboss.sh"
4. Install JBoss7/Wildfly by into /opt directory and execute: "./install-jboss.sh", that will install 3 host in domain/configuration "cluster-host-master.xml, cluster-host-node1.xml, cluster-host-node2.xml"
5. There are 3 default users: test/test1234, node1/test1234, node2/test1234
6. Start a Domain Controller: "./start-jboss.sh"
7. Start a Host Controller: "./start-jboss.sh [node1|node2] masterip" (example: ./start-jboss.sh node1 192.168.1.1)
8. Stop a Domain Controller: "./stop-jboss.sh"
9. Stop a Host Controller: "./stop-jboss.sh [node1|node2]"

### Howto Install mod_cluster

1. Copy all files (Files List) to /opt directory and make shell scripts executable: "chmod a+x /opt/*.sh"
2. Install mod_cluster by into /opt directory and execute: "./install-mod-cluster.sh", the mod_cluster will install in directory /opt/jboss/httpd
3. Start mod_cluster: "./start-mod-cluster.sh"
4. Stop mod_cluster: "./stop-mod-cluster.sh"

### Script Contents

1. [Base Scripts(Environment, JDK)](?md=jboss7-wildfly/base-scripts.md)
2. [JBoss Scripts](?md=jboss7-wildfly/jboss-scripts.md)
3. [Mod Cluster Scripts](?md=jboss7-wildfly/mod-cluster-scripts.md)