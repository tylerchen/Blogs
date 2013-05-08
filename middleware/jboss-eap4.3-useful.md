JBOSS EAP 4.3 Usefule Things
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
