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

