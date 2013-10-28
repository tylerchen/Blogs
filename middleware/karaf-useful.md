Karaf Useful Thinks
======

1. How to create a custom distribute (create a offline distribute)

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
  3. Specify the features to download
  4. Specify the feature repositories
  5. Specify the Karaf system repository
  6. Specify the remote repositories
  7. Generate the offline repository
  8. Install the offline repository
