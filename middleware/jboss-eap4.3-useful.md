JBOSS EAP 4.3 Usefule Things
===========

### Remove JBoss Messaging

For default instance:

1. remove jms deployer: conf/jboss-service.xml

    change: <attribute name="JMSService">jboss.messaging:service=ServerPeer</attribute>
    to    : <!--attribute name="JMSService">jboss.messaging:service=ServerPeer</attribute-->

2. remove from directory: deploy/jboss-messaging.sar, deploy/jms-ds.xml, deploy/jms-ra.rar
