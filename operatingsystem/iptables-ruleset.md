Iptables规则集合
=====

### 内网端口代理/转发内网端口

两台虚拟机模拟环境：

转发服务器：192.168.30.131  端口：2222

SFTP服务器：192.168.30.134 端口：22

转发服务器的路由如下：


    echo 1 >/proc/sys/net/ipv4/ip_forward
    iptables -t nat -A PREROUTING -p tcp -m tcp --dport 2222 -d 192.168.30.131 -j DNAT --to 192.168.30.134:22
    iptables -t nat -A POSTROUTING -p tcp -m tcp --dport 22 -j MASQUERADE

