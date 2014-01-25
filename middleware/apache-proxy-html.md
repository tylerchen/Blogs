Apache proxy html
=======

1. Before write howto do, I have to say open source software help a lot but hard to use, waste so mutch time to try to google and very very not environmental.

2. Here is my situation:

		What i want ) incoming request  --->  www.out2internetserver.com/redmine ---> www.myproxyserver.com/redmine ---> www.redmineservice.com:7700/redmine
		What i do   ) incoming request  --->  port map to myproxyserver          ---> using apache proxy to reverse ---> www.redmineservice.com:7700/redmine
		The problem ) some resource expect such as www.redmineservice.com/redmine/javascript/jquery.js but actually I got www.redmineservice.com:7700/redmine/javascript/jquery.js
		The solution) using mod_proxy_html to rewrite the url in page www.redmineservice.com:7700/redmine/javascript/jquery.js ---> www.redmineservice.com/redmine/javascript/jquery.js

3. Install Apache mod_proxy_html (apache/2.2.15 (Unix) DAV/2, I use RHEL6.2 apache)

		in file(nothing to change): /etc/httpd/conf.d/proxy_html.conf
		LoadModule      proxy_html_module       modules/mod_proxy_html.so
		LoadModule      xml2enc_module          modules/mod_xml2enc.so

4. Add conf file: /etc/httpd/conf.d/app_proxy.conf

		ProxyRequests Off
		ProxyPreserveHost On
		
		ProxyPass /redmine http://www.redmineservice.com:7700/redmine retry=0
		ProxyPassReverse /redmine http://www.redmineservice.com:7700/redmine
		<Location /redmine>
		  ProxyPassReverse /
		  ProxyHTMLEnable On
		  ProxyHTMLExtended On
		  ProxyHTMLURLMap http://www.out2internetserver.com:7700/ /
		  ProxyHTMLURLMap / /
		  RequestHeader unset Accept-Encoding
		</Location>

5. DO NOT add the "SetOutputFilter" configure.