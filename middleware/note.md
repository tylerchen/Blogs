JBoass AS 7 Domain
=========
    <interfaces>
            <interface name="management">
                <inet-address value="${jboss.bind.address.management:127.0.0.1}"/>
            </interface>
            <interface name="public">
               <inet-address value="${jboss.bind.address:127.0.0.1}"/>
            </interface>
            <interface name="unsecure">
                <!-- Used for IIOP sockets in the standard configuration.
                     To secure JacORB you need to setup SSL -->
                <inet-address value="${jboss.bind.address.unsecure:127.0.0.1}"/>
            </interface>
    </interfaces>
    <management-interfaces>
        <native-interface security-realm="ManagementRealm">
            <socket interface="management" port="${jboss.management.native.port:9999}"/>
        </native-interface>
        <http-interface security-realm="ManagementRealm">
            <socket interface="management" port="${jboss.management.http.port:9990}"/>
        </http-interface>
    </management-interfaces>
		

<remote host="${jboss.domain.master.address}" port="${jboss.domain.master.port:9999}"/>

./domain.sh -Djboss.bind.address.management=10.108.1.227 -Djboss.bind.address=10.108.1.227

./domain.sh -Djboss.bind.address.management=10.108.1.224 -Djboss.bind.address=10.108.1.224 -Djboss.domain.master.address=10.108.1.227
