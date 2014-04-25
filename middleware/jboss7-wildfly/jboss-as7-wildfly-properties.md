JBoss as7 & WildFly properties
======

### Standalone

	Property name	 Usage	 Default value
	java.ext.dirs	 Use to specify JDK extension directory paths	null
	jboss.home.dir	 The root directory of the JBoss AS 7 installation.	 Set by standalone.sh to $JBOSS_HOME
	jboss.server.base.dir	 The base directory for server content.	jboss.home.dir/standalone
	jboss.server.config.dir	 The base configuration directory.	jboss.server.base.dir/configuration
	jboss.server.data.dir	 The directory used for persistent data file storage.	jboss.server.base.dir/data
	jboss.server.log.dir	 The directory containing the server.log file.	jboss.server.base.dir/log
	jboss.server.temp.dir	 The directory used for temporary file storage.	jboss.server.base.dir/tmp
	jboss.server.deploy.dir	 The directory used to store deployed content	jboss.server.data.dir/content

### Managed Domain

	Property name	 Usage	 Default value
	jboss.home.dir	 The root directory of the JBoss AS 7 installation.	 Set by domain.sh to $JBOSS_HOME
	jboss.domain.base.dir	 The base directory for domain content.	jboss.home.dir/domain
	jboss.domain.config.dir	 The base configuration directory	jboss.domain.base.dir/configuration
	jboss.domain.data.dir	 The directory used for persistent data file storage.	jboss.domain.base.dir/data
	jboss.domain.log.dir	 The directory containing the host-controller.log and process-controller.log files	jboss.domain.base.dir/log
	jboss.domain.temp.dir	 The directory used for temporary file storage	jboss.domain.base.dir/tmp
	jboss.domain.deployment.dir	 The directory used to store deployed content	jboss.domain.base.dir/content
	jboss.domain.servers.dir	 The directory containing the output for the managed server instances	jboss.domain.base.dir/log

### Command Line Parameters

	The first acceptable format is
	
	--name=value
	For example:
	
	$JBOSS_HOME/bin/standalone.sh --server-config=standalone-ha.xml
	If the parameter name is a single character, it is prefixed by a single '-' instead of two:
	
	-x=value
	For example:
	
	$JBOSS_HOME/bin/standalone.sh -P=/some/location/jboss.properties
	The sections below describe the command line parameter names that are available in standalone and domain mode.
	
	Standalone
	Name	 Default if absent	 Value
	--server-config	jboss.server.config.dir/standalone.xml	 Either a relative path which is interpreted to be relative to jboss.server.config.dir or an absolute path.
	Managed Domain
	Name	 Default if absent	 Value
	--domain-config	jboss.domain.config.dir/domain.xml	 Either a relative path which is interpreted to be relative to jboss.domain.config.dir or an absolute path.
	--host-config	jboss.domain.config.dir/host.xml	 Either a relative path which is interpreted to be relative to jboss.domain.config.dir or an absolute path.
	The following parameters take no value and are only usable on slave host controllers (i.e. hosts configured to connect to a remote domain controller.)
	
	Name	 Function
	--backup	 Causes the slave host controller to create and maintains a local copy of the domain configuration file
	--cached-dc	 If the slave host controller is unable to contact the master domain controller to get its configuration on boot, boot from a local copy previously created using --backup. The slave host controller will not be able make any modifications to the domain configuration, but it will be able to launch servers.
	Common parameters
	These parameters are usable in either standalone or managed domain mode, and have no values. The following table explains what each of these does
	
	Name	 Function
	--version 
	-V	 Prints out the version of JBoss AS and exits the JVM.
	--help 
	-h	 Prints out a help message explaining the options and exits the JVM.