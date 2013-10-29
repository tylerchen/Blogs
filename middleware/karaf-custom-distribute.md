Karaf Custom Distribute
======

How to create a custom distribute (create a offline distribute)

[Generating a Custom Offline Repository](https://access.redhat.com/site/documentation/en-US/Fuse_ESB_Enterprise/7.1/html/Deploying_into_the_Container/files/Locate-CustomRepo.html)

1. create a maven project with a POM file

	    <?xml version="1.0" encoding="UTF-8"?>
	    <project xmlns="http://maven.apache.org/POM/4.0.0"
	        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	        xsi:schemaLocation="http://maven.apache.org/POM/4.0.0 http://maven.apache.org/xsd/maven-4.0.0.xsd">
	        <modelVersion>4.0.0</modelVersion>
	        <groupId>org.acme.offline-repo</groupId>
	        <artifactId>custom-repo</artifactId>
	        <version>1.0.0</version>
	        <name>Generate offline features repository</name>
	    </project>
      
2. Add the features-maven-plugin

		<project ...>
		  ...
		  <build>
			  <plugins>
				<plugin>
				  <groupId>org.apache.karaf.tooling</groupId>
				  <artifactId>features-maven-plugin</artifactId>
				  <version>2.2.1</version>

				  <executions>
					<execution>
					  <id>add-features-to-repo</id>
					  <phase>generate-resources</phase>
					  <goals>
						<goal>add-features-to-repo</goal>
					  </goals>
					  <configuration>
						<descriptors>
						  <!-- List the URLs of required feature repositories here -->
						</descriptors>
						<features>
						  <!-- List features you want in the offline repo here -->
						</features>
						<repository>target/features-repo</repository>
					  </configuration>
					</execution>
				  </executions>
				</plugin>
			  </plugins>
		  </build>
		</project>

3. Specify the features to download

		<features>
			<feature>camel-jms</feature>
			<feature>camel-quartz</feature>
		</features>

4. Specify the feature repositories

  To see the full list of standard feature repositories used by your installation of Fuse ESB Enterprise, open the etc/org.apache.karaf.features.cfg configuration file and look at the featuresRepository setting, which is a comma-separated list of feature repositories, like the following:
    
          ...
          #
          # Comma separated list of feature repositories to register by default
          #
          featuresRepositories=mvn:org.apache.karaf/apache-karaf/2.1.3-fuse-00-00/xml/features,
          mvn:org.apache.servicemix.nmr/apache-servicemix-nmr/1.4.0-fuse-00-00/xml/features,mvn
          :org.apache.servicemix/apache-servicemix/4.3.1-fuse-00-00/xml/features,mvn:org.apache
          .camel.karaf/apache-camel/2.6.0-fuse-00-00/xml/features,mvn:org.apache.servicemix/ode
          -jbi-karaf/1.3.4/xml/features,mvn:org.apache.activemq/activemq-karaf/5.4.2-fuse-01-00
          /xml/features
          ...
          
    Now, add the listed feature repositories to the configuration of the features-maven-plugin in your POM file. Open the project's pom.xml file and add a descriptor element (as a child of the descriptors element) for each of the standard feature repositories. For example, given the preceding value of the featuresRepositories list, you would define the features-maven-plugin descriptors list in pom.xml as follows:
    
          <descriptors>
            <!-- List taken from featuresRepositories in etc/org.apache.karaf.features.cfg -->
            <descriptor>mvn:org.apache.karaf/apache-karaf/2.1.3-fuse-00-00/xml/features</descriptor>
            <descriptor>mvn:org.apache.servicemix.nmr/apache-servicemix-nmr/1.4.0-fuse-00-00/xml/features</descriptor>
            <descriptor>mvn:org.apache.servicemix/apache-servicemix/4.3.1-fuse-00-00/xml/features</descriptor>
            <descriptor>mvn:org.apache.camel.karaf/apache-camel/2.6.0-fuse-00-00/xml/features</descriptor>
            <descriptor>mvn:org.apache.servicemix/ode-jbi-karaf/1.3.4/xml/features</descriptor>
            <descriptor>mvn:org.apache.activemq/activemq-karaf/5.4.2-fuse-01-00/xml/features</descriptor>
          </descriptors>

5. Specify the Karaf system repository

          <project ...>
              ...
              <repositories>
                <repository>
                  <id>esb.system.repo</id>
                  <name>Fuse ESB internal system repo</name>
                  <url>file:///opt/karaf/apache-karaf-2.3.3/system</url>
                  <snapshots>
                    <enabled>false</enabled>
                  </snapshots>
                  <releases>
                    <enabled>true</enabled>
                  </releases>
                </repository>
              </repositories>
              ...
          </project>

6. Specify the remote repositories

  Generally, the project requires access to all of the standard Fuse ESB Enterprise remote repositories. To see the full list of standard remote repositories, open the etc/org.ops4j.pax.url.mvn.cfg configuration file and look at the org.ops4j.pax.url.mvn.repositories setting, which is a comma-separated list of URLs like the following:

          org.ops4j.pax.url.mvn.repositories= \
              http://repo1.maven.org/maven2, \
              http://repo.fusesource.com/maven2, \
              http://repo.fusesource.com/maven2-snapshot@snapshots@noreleases, \
              http://repo.fusesource.com/nexus/content/repositories/releases, \
              http://repo.fusesource.com/nexus/content/repositories/snapshots@snapshots@noreleases, \
              http://repository.apache.org/content/groups/snapshots-group@snapshots@noreleases, \
              http://repository.ops4j.org/maven2, \
              http://svn.apache.org/repos/asf/servicemix/m2-repo, \
              http://repository.springsource.com/maven/bundles/release, \
              http://repository.springsource.com/maven/bundles/external

  RepoURL
  
  The value of the repository URL, RepoURL, is inserted directly into the url child element of the repository element. For example, the http://repo1.maven.org/maven2 repository URL translates to the following repository element:

        <repository>
          <!-- 'id' can be whatever you like -->
          <id>repo1.maven.org</id>
          <!-- 'name' can be whatever you like -->
          <name>Maven central</name>
          <url>http://repo1.maven.org/maven2</url>
          <snapshots>
            <enabled>false</enabled>
          </snapshots>
          <releases>
            <enabled>true</enabled>
          </releases>
        </repository>

7. Generate the offline repository

  To generate the custom offline repository, open a new command prompt, change directory to ProjectDir/custom-repo, and enter the following Maven command:

        mvn generate-resources

  Assuming that the Maven build completes successfully, the custom offline repository should now be available in the following location:

        ProjectDir/custom-repo/target/features-repo

8. Install the offline repository

  To install the custom offline repository in the Fuse ESB Enterprise container, edit the etc/org.ops4j.pax.url.mvn.cfg file and append the offline repository directory to the list of default repositories, as follows:

        org.ops4j.pax.url.mvn.defaultRepositories=file:${karaf.home}/${karaf.default.repository}@snapshots,ProjectDir/custom-repo/target/features-repo@snapshots

  The @snapshots suffix can be added to the offline repository URL, if there is a possibility that some of the artifacts in it are snapshot versions.

9. My sample pom.xml

            <project>
            	<modelVersion>4.0.0</modelVersion>
            	<groupId>org.iff</groupId>
            	<artifactId>test-karaf</artifactId>
            	<version>1.0.0</version>
            	<repositories>
            		<repository>
            			<id>esb.system.repo</id>
            			<name>Fuse ESB internal system repo</name>
            			<url>file:///opt/karaf/apache-karaf-2.3.3/system</url>
            			<snapshots>
            				<enabled>false</enabled>
            			</snapshots>
            			<releases>
            				<enabled>true</enabled>
            			</releases>
            		</repository>
            		<repository>
            			<id>com.springsource.repository.bundles.release</id>
            			<name>SpringSource Enterprise Bundle Repository - SpringSource Bundle
            				Releases</name>
            			<url>http://repository.springsource.com/maven/bundles/release</url>
            			<releases>
            				<enabled>true</enabled>
            				<updatePolicy>daily</updatePolicy>
            				<checksumPolicy>warn</checksumPolicy>
            			</releases>
            		</repository>
            	</repositories>
            	<dependencies>
            	</dependencies>
            	<build>
            		<plugins>
            			<plugin>
            				<groupId>org.apache.karaf.tooling</groupId>
            				<artifactId>features-maven-plugin</artifactId>
            				<version>2.3.2</version>
            				<executions>
            					<execution>
            						<id>add-features-to-repo</id>
            						<phase>generate-resources</phase>
            						<goals>
            							<goal>add-features-to-repo</goal>
            						</goals>
            						<configuration>
            							<descriptors>
            								<descriptor>mvn:org.apache.camel.karaf/apache-camel/2.12.0/xml/features
            								</descriptor>
            							</descriptors>
            							<features>
            								<feature>war</feature>
            								<feature>webconsole</feature>
            								<feature>camel</feature>
            							</features>
            							<repository>${project.build.directory}/features-repo</repository>
            						</configuration>
            					</execution>
            				</executions>
            			</plugin>
            		</plugins>
            	</build>
            </project>

10. Other resources
  
  Spring repository:

          <repository>
              <id>com.springsource.repository.bundles.release
              </id>
              <name>SpringSource Enterprise Bundle Repository -
                  SpringSource Bundle Releases</name>
              <url>http://repository.springsource.com/maven/bundles/release
              </url>
              <releases>
                  <enabled>true</enabled>
                  <updatePolicy>daily</updatePolicy>
                  <checksumPolicy>warn</checksumPolicy>
              </releases>
          </repository>
          <repository>
              <id>com.springsource.repository.bundles.external
              </id>
              <name>SpringSource Enterprise Bundle Repository -
                  External Bundle Releases</name>
              <url>http://repository.springsource.com/maven/bundles/external
              </url>
              <releases>
                  <enabled>true</enabled>
                  <updatePolicy>daily</updatePolicy>
                  <checksumPolicy>warn</checksumPolicy>
              </releases>
          </repository>
          <repository>
              <id>com.springsource.repository.libraries.release
              </id>
              <name>SpringSource Enterprise Bundle Repository -
                  SpringSource Library Releases</name>
              <url>http://repository.springsource.com/maven/libraries/release
              </url>
              <releases>
                  <enabled>true</enabled>
                  <updatePolicy>daily</updatePolicy>
                  <checksumPolicy>warn</checksumPolicy>
              </releases>
          </repository>
          <repository>
              <id>com.springsource.repository.libraries.external
              </id>
              <name>SpringSource Enterprise Bundle Repository -
                  External Library Releases</name>
              <url>http://repository.springsource.com/maven/libraries/external
              </url>
              <releases>
                  <enabled>true</enabled>
                  <updatePolicy>daily</updatePolicy>
                  <checksumPolicy>warn</checksumPolicy>
              </releases>
          </repository>

