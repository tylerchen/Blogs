Hornetq 与 JBoss4集成
====

* 下载Hornetq，URL：[http://www.jboss.org/hornetq/downloads](http://www.jboss.org/hornetq/downloads)这里使用2.2.5.Final 

* 解压Hornetq 

* 指定需要安装Hornetq的JBOSS4目录 

    修改hornetq-2.2.5.Final\config\jboss-as-4\build.xml手动设置JBOSS_HOME，把 


        <property environment="ENV"/>  


    改为： 


        <property environment="ENV1"/>  
        <property name="ENV.JBOSS_HOME" value="E:/Server/jboss-4.2.2.GA"/>  


* 修正Hornetq的错误配置 

    修改 


        hornetq-2.2.5.Final\config\jboss-as-4\clustered\jms-ds.xml  


    中的配置，删除</mbean>前的那个多余的</attribute>标签。 

    修改 


        hornetq-2.2.5.Final\config\jboss-as-4\clustered\ra.xml  
        hornetq-2.2.5.Final\config\jboss-as-4\non-clustered\ra.xml  


    中的配置，因为这个配置写错了，把 


        <config-property>  
          <description>The class that will locate the transactionmanager</description>  
          <config-property-name>TransactionManagerLocatorMethod</config-property-name>  
          <config-property-type>java.lang.String</config-property-type>  
          <config-property-value>org.hornetq.integration.jboss.tm.JBoss4TransactionManagerLocator</config-property-value>  
        </config-property>  


    改为： 


        <config-property>  
          <description>The class that will locate the transactionmanager</description>  
          <config-property-name>TransactionManagerLocatorClass</config-property-name>  
          <config-property-type>java.lang.String</config-property-type>  
          <config-property-value>org.hornetq.integration.jboss.tm.JBoss4TransactionManagerLocator</config-property-value>  
        </config-property>  


* 运行安装 

    运行hornetq-2.2.5.Final\config\jboss-as-4\build.bat，前提是要安装JDK和ANT，安装成功后会默认安装两个JBoss实例all-with-hornetq和default-with-hornetq，这两实例是基于原来all和default实例配置，直接复制原来实例配置然后再修改的。 

* 添加启动脚本 

    到jboss-4.2.2.GA\server目录下，添加一个批处理文件，文件名为JBOSS实例的名称，下面的脚本会自动运行与bat文件名称相同的JBoss实例，如：default-with-hornetq.bat 

    内容如下： 
 

        %~dp0..\bin\run.bat -c %~n0 -b 0.0.0.0  


* 运行错误处理 

    如果运行过程中出现以下错误，那说明你在复制JBOSS的实例的时候把data目录的hornetq数据也复制了，解决办法是删除data下面的所有数据。 


        WARN  [org.hornetq.core.cluster.impl.DiscoveryGroupImpl]  There are more than one servers on the network broadcasting the same  node id. You will see this message exactly once (per node) if a node is  restarted, in which case it can be safely ignored. But if it is logged  continuously it means you really do have more than one node on the same  network active concurrently with the same node id. This could occur if  you have a backup node active at the same time as its live node.  


