JBoss Fuse 6.2.1 Usage
=====

### 1. Maven Environment Setting

#### 1.1. Setting setting.xml

    <profile>
        <id>redhat-techpreview-all-repository</id>
        <repositories>
          <repository>
            <id>redhat-techpreview-all-repository</id>
            <name>Red Hat Tech Preview repository (all)</name>
            <url>http://maven.repository.redhat.com/techpreview/all/</url>
            <releases>
              <enabled>true</enabled>
              <updatePolicy>never</updatePolicy>
            </releases>
            <snapshots>
              <enabled>false</enabled>
              <updatePolicy>daily</updatePolicy>
            </snapshots>
          </repository>
        </repositories>
        <pluginRepositories>
          <pluginRepository>
            <id>redhat-techpreview-all-repository</id>
            <name>Red Hat Tech Preview repository (all)</name>
            <url>http://maven.repository.redhat.com/techpreview/all/</url>
            <releases>
              <enabled>true</enabled>
              <updatePolicy>never</updatePolicy>
            </releases>
            <snapshots>
              <enabled>false</enabled>
              <updatePolicy>daily</updatePolicy>
            </snapshots>
          </pluginRepository>
        </pluginRepositories>
      </profile>
      <profile>
          <id>jboss-ea-repository</id>
          <repositories>
            <repository>
              <id>jboss-ea-repository</id>
              <name>JBoss ea repository (all)</name>
              <url>https://repository.jboss.org/nexus/content/groups/ea/</url>
              <releases>
                <enabled>true</enabled>
                <updatePolicy>never</updatePolicy>
              </releases>
              <snapshots>
                <enabled>false</enabled>
                <updatePolicy>daily</updatePolicy>
              </snapshots>
            </repository>
          </repositories>
          <pluginRepositories>
            <pluginRepository>
              <id>jboss-ea-repository</id>
              <name>JBoss ea repository (all)</name>
              <url>https://repository.jboss.org/nexus/content/groups/ea/</url>
              <releases>
                <enabled>true</enabled>
                <updatePolicy>never</updatePolicy>
              </releases>
              <snapshots>
                <enabled>false</enabled>
                <updatePolicy>daily</updatePolicy>
              </snapshots>
            </pluginRepository>
          </pluginRepositories>
        </profile>
        
        
        <activeProfiles>
          <activeProfile>redhat-techpreview-all-repository</activeProfile>
      	  <activeProfile>jboss-ea-repository</activeProfile>
        </activeProfiles>
        
#### 1.2. Repository configure in you pom.xml

	<repository>
		<releases>
			<enabled>true</enabled>
		</releases>
		<snapshots>
			<enabled>false</enabled>
		</snapshots>
		<id>release.fusesource.org</id>
		<name>FuseSource Release Repository</name>
		<url>http://repo.fusesource.com/nexus/content/repositories/releases</url>
	</repository>
	<repository>
		<releases>
			<enabled>true</enabled>
		</releases>
		<snapshots>
			<enabled>false</enabled>
		</snapshots>
		<id>ea.fusesource.org</id>
		<name>FuseSource Community Early Access Release Repository</name>
		<url>http://repo.fusesource.com/nexus/content/groups/ea</url>
	</repository>


### 2. Examples

#### 2.1. Plugin introduce "maven-bundle-plugin"

	<plugin>
		<groupId>org.apache.felix</groupId>
		<artifactId>maven-bundle-plugin</artifactId>
		<version>2.3.7</version>
		<extensions>true</extensions>
		<configuration>
			<instructions>
				<Bundle-SymbolicName>camel-cxf-code-first-blueprint</Bundle-SymbolicName>
				<Private-Package>com.mycompany.camel.cxf.code.first.blueprint.*</Private-Package>
				<Import-Package>*</Import-Package>
				<Embed-Dependency>gson;scope=compile|runtime|system</Embed-Dependency>
			</instructions>
		</configuration>
	</plugin>
	
	
	Bundle-SymbolicName: bundle symbolic name
	Private-Package: The project you created package, such as your groupid
	Import-Package: If you don't know what package you need in the project, use "*"
	Embed-Dependency: Some jars not in fuse system or not osgi bundle, specify the artifactid here, multi jars use "," to join the artifactids


#### 2.2. Plugin introduce "maven-bundle-plugin"

	<plugin>
		<groupId>org.apache.camel</groupId>
		<artifactId>camel-maven-plugin</artifactId>
		<version>2.15.1.redhat-621084</version>
		<configuration>
			<useBlueprint>true</useBlueprint>
		</configuration>
	</plugin>

#### 2.3. pom base setting

	<packaging>bundle</packaging>
	<name>Camel Blueprint Route</name>
	<properties>
		<project.reporting.outputEncoding>UTF-8</project.reporting.outputEncoding>
		<project.build.sourceEncoding>UTF-8</project.build.sourceEncoding>
	</properties>

#### 2.4. sample01-dynamic-route

	from queue foo:
		when header.test='1' then send to queue a
		when header.test='2' then send to queue b
		otherwise send to queue c
	
	project create by jboss fuse ide:
		1) Fuse Intergration Project
		2) Project Name = sample01-dynamic-route, Next
		3) Select camel-archetype-blueprint, change artifactid to sample01-dynamic-route, Finish
		4) Remove src/main/resource/OSGI-INF folder, (just use spring)
		5) Create src/main/resource/META-INF/spring
		6) Right Click the folder you created -> New -> Camel XML File, camelContext.xml
	
	add content to camelContext.xml:
		1) add mq configure
			<bean id="activemq" class="org.apache.camel.component.jms.JmsComponent">
				<property name="connectionFactory">
					<bean class="org.apache.activemq.ActiveMQConnectionFactory">
						<property name="brokerURL" value="tcp://localhost:61616" />
						<property name="userName" value="admin" />
						<property name="password" value="admin" />
					</bean>
				</property>
			</bean>
		2) add route
			<camelContext trace="false" xmlns="http://camel.apache.org/schema/spring">
				<route>
					<from uri="activemq:queue:foo" />
					<choice>
						<when>
							<simple>${header.test} == '1'</simple>
							<to uri="activemq:queue:a" />
						</when>
						<when>
							<simple>${header.test}  == '2'</simple>
							<to uri="activemq:queue:b" />
						</when>
						<otherwise>
							<to uri="activemq:queue:c" />
						</otherwise>
					</choice>
				</route>
			</camelContext>
	
	compile and deploy:
		1) compile and package bundle
			mvn clean package -Dmaven.test.skip=true
		2) deploy to fuse
			cp sample01-dynamic-route-*.jar $JBOSS_FUSE/deploy/
	
	testing:
		1) create a queue foo from http://localhost:8181
		2) add message, remember to set the header "test"
		3) when test=1, test=2, test=other, to see then queue a, b, c content

#### 2.5. sample02-camel-activemq

	use a timer to produce message, and consume this message:
		a timer
		produce message
		consume message
	
	project create by jboss fuse ide:
		1) Fuse Intergration Project
		2) Project Name = sample02-camel-activemq, Next
		3) Select camel-archetype-blueprint, change artifactid to sample02-camel-activemq, Finish
		4) Remove src/main/resource/OSGI-INF folder, (just use spring)
		5) Create src/main/resource/META-INF/spring
		6) Right Click the folder you created -> New -> Camel XML File, sender.xml
		7) Right Click the folder you created -> New -> Camel XML File, reciever.xml
	
	add content to sender.xml:
		1) add mq configure
			<bean id="activemq" class="org.apache.camel.component.jms.JmsComponent">
				<property name="connectionFactory">
					<bean class="org.apache.activemq.ActiveMQConnectionFactory">
						<property name="brokerURL" value="tcp://localhost:61616" />
						<property name="userName" value="admin" />
						<property name="password" value="admin" />
					</bean>
				</property>
			</bean>
		2) add route
			<camelContext trace="false" xmlns="http://camel.apache.org/schema/spring">
				<route>
					<from uri="timer:timerName" />
					<setBody>
						<constant>Hello world.</constant>
					</setBody>
					<log message="Send: ${body}" />
					<to uri="activemq:queue:timer" />
				</route>
			</camelContext>

	add content to reciever.xml:
		1) add route
			<camelContext trace="false" xmlns="http://camel.apache.org/schema/spring">
				<route>
					<from uri="activemq:queue:timer" />
					<log message="Recieve: ${body}" />
				</route>
			</camelContext>

	compile and deploy:
		1) compile and package bundle
			mvn clean package -Dmaven.test.skip=true
		2) deploy to fuse
			cp sample02-camel-activemq-*.jar $JBOSS_FUSE/deploy/
	
	testing:
		1) to see the log out put, to input "log:tail" in fuse console 

