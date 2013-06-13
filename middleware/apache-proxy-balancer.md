Apache proxy balancer
=======

1. Install Apache with mod_proxy (apache/2.2.15 (Unix) DAV/2, I use RHEL6.2 apache)

2. Make sure mod_proxy modules load in /etc/httpd/conf/httpd.conf file.

3. Create a new conf file in /etc/httpd/conf.d/ directory, file name reverse_proxy.conf, httpd.conf will auto load the files in this directory

4. File content of reverse_proxy.conf

        # 
        # This configuration file enables the default "Welcome"
        # page if there is no default index page present for
        # the root URL.  To disable the Welcome page, comment
        # out all the lines below.
        
        ProxyRequests Off
        
        Header add Set-Cookie "ROUTEID=.%{BALANCER_WORKER_ROUTE}e; path=/" env=BALANCER_ROUTE_CHANGED
        ProxyPass /ClusterWebApp balancer://mycluster/ClusterWebApp
        ProxyPassReverse / balancer://mycluster/ 
        <Proxy balancer://mycluster>
        BalancerMember http://10.12.102.63:8180 route=node1
        BalancerMember http://10.12.102.63:8280 route=node2
        ProxySet stickysession=ROUTEID
        </Proxy>
        
        <Location /balancer-manager>
        SetHandler balancer-manager
        Order Deny,Allow
        Allow from all
        </Location>

5. If you want to the same session send to the same background server, you need to do 3 configurations: 

        1) Add the "Header add..." configuration to bind the route id
        2) Add the "route=..." configuration to specify the route id
        3) Add the "stickysession=..." configuration to specify the stickysession model

6. For the Java Servlet stickysession you can configure, Note this configure will not sticky the same session to the same server

        ProxySet stickysession=JSESSIONID|jsessionid lbmethod=byrequests nofailover=Off



