copy index.php                         to /var/www/html
copy proxy_applications_management.php to /var/www/html
touch     /var/www/html/proxy_applications.list
chmod 666 /var/www/html/proxy_applications.list

su root to run this
./run_httpd.sh install

default user
tyler
manager

access URL: http://localhost