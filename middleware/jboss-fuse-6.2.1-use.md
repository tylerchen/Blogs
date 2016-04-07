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
