<?php
	session_start();

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");             // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");// always modified
	header("Cache-Control: no-cache, must-revalidate");           // HTTP/1.1
	header("Pragma: no-cache");                                   // HTTP/1.0
	
	$add_proxy_name = "";
	$add_proxy_url = "";
	$remove_proxy_name = "";
	$login_user_name="";
	$login_user_password="";
	
	if(isset($_POST['user_login'])){
		$login_user_name = trim($_POST['login_user_name']);
		$login_user_password  = trim($_POST['login_user_password']);
		error(user_login($login_user_name, $login_user_password));
		redirect();
	}else if(isset($_POST['add_proxy'])){
		$add_proxy_name = trim($_POST['add_proxy_name']);
		$add_proxy_url  = trim($_POST['add_proxy_url']);
		error(add_proxy($add_proxy_name, $add_proxy_url));
		redirect();
	}else if(isset($_GET['remove_proxy_name'])){
		$remove_proxy_name = trim($_GET['remove_proxy_name']);
		error(remove_proxy($remove_proxy_name));
		redirect();
	}else{
	}
	
	function redirect(){
		header('Location: '.request_uri());
		exit;
	}
	function request_uri(){
		if (isset($_SERVER['PHP_SELF'])){
			$uri = $_SERVER['PHP_SELF'];
		}
		return $uri;
	}
	function error($error_msg = ""){
		if(strlen($error_msg)>0){
			if(!array_key_exists("error_msg",$_SESSION)){
				$_SESSION["error_msg"]=array();
			}
			$_SESSION["error_msg"][]=$error_msg;
		}else{
			$msg=isset($_SESSION["error_msg"])?join(',',$_SESSION["error_msg"]):"";
			$_SESSION["error_msg"]=array();
			return $msg;
		}
	}
	function user_login($user_name="", $user_password=""){
		if( strlen($user_name)<1 ){
			return "[error]:The user name is required.";
		}
		if( strlen($user_password)<1 ){
			return "[error]:The password is required.";
		}
		$out = '1';
		$retval = 1;
		$command = "cat /etc/sysconfig/keeprunning/httpd.users 2>/dev/null|grep -c '$user_name=$user_password'";
		$last_out = exec($command, $out, $retval);
		if( strcmp("$last_out", "1") != 0 ){
			return "[error]:The user name or password not correct.$out";
		}else{
			$_SESSION["user_login"] = "$user_name=$user_password";
		}
	}
	function add_proxy($proxy_name="", $proxy_url=""){
		if( strlen($proxy_name)<1 ){
			return "[error]:The proxy name is required.";
		}
		if( strlen($proxy_url)<1 ){
			return "[error]:The proxy url is required.";
		}
		$out = '1';
		$retval = 1;
		$command = "cat /var/www/html/proxy_applications.list 2>/dev/null|grep -c '$proxy_name='";
		$last_out = exec($command, $out, $retval);
		if( strcmp("$last_out", "0") != 0 ){
			return "[error]:The proxy $proxy_name exsits.$out";
		}
		$command = "echo '$proxy_name=$proxy_url' >> /var/www/html/proxy_applications.list";
		exec($command, $out, $retval);
		if( $retval != 0 ){
			return "[error]:Can't add the proxy to file /var/www/html/proxy_applications.list, perhaps you should check the authoriation. $out";
		}
		// add proxy to apache
		// /etc/httpd/conf.d/app_proxy.conf
		// ProxyPass /redmine http://10.108.1.13:7700/redmine retry=0
		// ProxyPassReverse /redmine http://10.108.1.13:7700/redmine
		// remove exists proxy
		#$command = "/bin/sed -i '/ProxyPassReverse *\/$proxy_name /d' /etc/httpd/conf.d/app_proxy.conf";
		$command = "/bin/ed -s /etc/httpd/conf.d/app_proxy.conf <<< $'g/ProxyPassReverse *\/$proxy_name/d\nw'";
		exec($command, $out, $retval);
		if($retval != 0){
			return "[error]:Can't remove the proxy from file /etc/httpd/conf.d/app_proxy.conf, perhaps you should check the authoriation. $out";
		}
		#$command = "/bin/sed -i '/ProxyPass *\/$proxy_name /d' /etc/httpd/conf.d/app_proxy.conf";
		$command = "/bin/ed -s /etc/httpd/conf.d/app_proxy.conf <<< $'g/ProxyPass *\/$proxy_name/d\nw'";
		exec($command, $out, $retval);
		if($retval != 0){
			return "[error]:Can't remove the proxy from file /etc/httpd/conf.d/app_proxy.conf, perhaps you should check the authoriation. $out";
		}
		// write proxy
		$command = "echo 'ProxyPass /$proxy_name $proxy_url retry=0' >> /etc/httpd/conf.d/app_proxy.conf";
		exec($command, $out, $retval);
		if($retval != 0){
			return "[error]:Can't add the proxy to file /etc/httpd/conf.d/app_proxy.conf, perhaps you should check the authoriation. $out";
		}
		$command = "echo 'ProxyPassReverse /$proxy_name $proxy_url' >> /etc/httpd/conf.d/app_proxy.conf";
		exec($command, $out, $retval);
		if($retval != 0){
			return "[error]:Can't add the proxy to file /etc/httpd/conf.d/app_proxy.conf, perhaps you should check the authoriation. $out";
		}
		// restart httpd service
		$command = "echo 'reload' >> /etc/sysconfig/keeprunning/httpd.reload";
		exec($command, $out, $retval);
		if( $retval != 0 ){
			return "[error]:Can't reload the httpd, perhaps you should check the authoriation /etc/sysconfig/keeprunning/httpd.reload. $out";
		}
		return;
	}
	function remove_proxy($proxy_name=""){
		$out = '1';
		$retval = 1;
		#$command = "/bin/sed -i '/ProxyPass *\/$proxy_name /d' /etc/httpd/conf.d/app_proxy.conf";
		$command = "/bin/ed -s /etc/httpd/conf.d/app_proxy.conf <<< $'g/ProxyPass *\/$proxy_name/d\nw'";
		exec($command, $out, $retval);
		if( $retval != 0 ){
			return "[error]:Can't remove the proxy from file /etc/httpd/conf.d/app_proxy.conf, perhaps you should check the authoriation. $out";
		}
		#$command = "/bin/sed -i '/ProxyPassReverse *\/$proxy_name /d' /etc/httpd/conf.d/app_proxy.conf";
		$command = "/bin/ed -s /etc/httpd/conf.d/app_proxy.conf <<< $'g/ProxyPassReverse *\/$proxy_name/d\nw'";
		exec($command, $out, $retval);
		if( $retval != 0 ){
			return "[error]:Can't remove the proxy from file /etc/httpd/conf.d/app_proxy.conf, perhaps you should check the authoriation. $out";
		}
		// restart httpd service
		$command = "echo 'reload' >> /etc/sysconfig/keeprunning/httpd.reload";
		exec($command, $out, $retval);
		if( $retval != 0 ){
			return "[error]:Can't reload the httpd, perhaps you should check the authoriation /etc/sysconfig/keeprunning/httpd.reload. $out";
		}
		
		#$command = "/bin/sed -i '/$proxy_name=/d' /var/www/html/proxy_applications.list";
		$command = "/bin/ed -s /var/www/html/proxy_applications.list <<< $'g/$proxy_name=/d\nw'";
		error($command);
		exec($command, $out, $retval);
		if( $retval != 0 ){
			return "[error]:Can't remove the proxy from file /var/www/html/proxy_applications.list, perhaps you should check the authoriation. $out";
		}
	}
	function get_proxies(){
		$proxyArr=array();

		$fd = @fopen("/var/www/html/proxy_applications.list", "r");
		if ($fd == false) {
			echo "<H1>fail to open file $passwd_temp!<\H1>";
			exit;
		}
		rewind($fd); /* unnessecary but I'm paranoid */

		while (!feof($fd)) {
			$buffer = fgets($fd, 4096);
			/* all data is comprised of a name, an optional seperator, and a datum */

			/* oh wow!.. trim()!!! I could hug somebody! */
			$buffer = trim($buffer);
			if( strlen ($buffer) < 1 || $buffer[0] == "#"){
				continue;
			}
			{/* process proxy=url*/
				if(strpos($buffer,'=') > 0){
					$pos = strpos($buffer,'=');
					$proxyArr[trim(substr($buffer,0,$pos))] = trim(substr($buffer,$pos+1));
				}
			}
		}
		fclose($fd);
		return $proxyArr;
	}
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML Strict Level 3//EN">
<HTML>
<HEAD>
<TITLE>Application Proxy Management</TITLE>
<STYLE TYPE="text/css">
<!-- 
        
.logo   {
        color: #666666;
        }
        
A.logolink      {
        color: #FFFFFF;
        font-size: .8em;
        }
        
.taboff {
        color: #FFFFFF;
        }
        
.tabon  {
        color: #666666;
        }

TD.checkbox  {
        width: 25px;
		}
TD.op  {
        width: 32px;
		}
DIV.scroll{
		height: 300px;
		overflow-y: scroll;
		overflow-x: hidden;
		padding-right: 18px\9;
		}
DIV.body{
		padding: 0px;
		margin: auto;
		width: 780px;
		}
DIV.floatl{
		position: relative;
		float: left;
		}
DIV.floatr{
		position: relative;
		float: right;
		}
DIV.with48p{
		width: 48%;
		}
DIV.clear{
		clear: both;
		height: 18px\9;
		}
// -->
</STYLE>

</HEAD>

<BODY BGCOLOR="#FFFFFF">
<DIV CLASS="body"><!-- BODY DIV START -->
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CCCCCC"> <TD ALIGN=center CLASS="logo"> <B>Proxy Configeration Tool</B> </TD></TR>
</TABLE>
<P></P>
<!-- =================================== ERROR MESSAGES =================================== -->
<?php $msg=error(); if(strlen($msg)>0){ ?>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CCCCCC"> <TD ALIGN=left CLASS="logo"> <B>Error Message: </B><?php echo $msg; ?> </TD></TR>
</TABLE>
<P></P>
<?php } ?>
<!-- =================================== ERROR MESSAGES END =================================== -->

<!-- =================================== LOGIN =================================== -->
<?php if(!isset($_SESSION["user_login"])){ ?>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CCCCCC">
		<TD ALIGN=center CLASS="logo">
			<!-- === Add Proxy === -->
			<FORM METHOD="POST" ENCTYPE="application/x-www-form-urlencoded" ACTION="proxy_applications_management.php" AUTOCOMPLETE="off">
				<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
					<TR BGCOLOR="#CCCCCC"> <TD CLASS="logo" colspan="5"> <B>User Login</B> </TD></TR>
					<TR>
						<TD ALIGN=right CLASS="logo"> User Name: </TD>
						<TD ALIGN=left  CLASS="logo"> <INPUT TYPE="text" SIZE="24" MAXLENGTH="64" NAME="login_user_name"> </TD>
						<TD ALIGN=right CLASS="logo"> Password: </TD>
						<TD ALIGN=left  CLASS="logo"> <INPUT TYPE="password" SIZE="24" MAXLENGTH="99" NAME="login_user_password"> </TD>
						<TD ALIGN=right CLASS="logo"> <INPUT TYPE="submit" NAME="user_login" value="Login"> </TD>
					</TR>
				</TABLE>
			</FORM>
			<!-- === Add Proxy END === -->
		</TD>
	</TR>
</TABLE>
<?php exit;} ?>
<!-- =================================== LOGIN END =================================== -->
<!-- =================================== Proxy Configuration Pannel =================================== -->
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CCCCCC">
		<TD ALIGN=center CLASS="logo">
			<!-- === Add Proxy === -->
			<FORM METHOD="POST" ENCTYPE="application/x-www-form-urlencoded" ACTION="proxy_applications_management.php" AUTOCOMPLETE="off">
				<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
					<TR BGCOLOR="#CCCCCC"> <TD CLASS="logo" colspan="5"> <B>Add Proxy</B> </TD></TR>
					<TR>
						<TD ALIGN=right CLASS="logo"> Proxy Name: </TD>
						<TD ALIGN=left  CLASS="logo"> <INPUT TYPE="text" SIZE="24" MAXLENGTH="64" NAME="add_proxy_name"> </TD>
						<TD ALIGN=right CLASS="logo"> Proxy URL: </TD>
						<TD ALIGN=left  CLASS="logo"> <INPUT TYPE="text" SIZE="24" MAXLENGTH="99" NAME="add_proxy_url"> </TD>
						<TD ALIGN=right CLASS="logo"> <INPUT TYPE="submit" NAME="add_proxy" value="Add Proxy"> </TD>
					</TR>
				</TABLE>
			</FORM>
			<!-- === Add Proxy END === -->
		</TD>
	</TR>
</TABLE>
<BR/>
<DIV>
	<DIV>
		<DIV>
			<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
				<TR BGCOLOR="#CCCCCC"> <TD CLASS="logo" COLSPAN="2"> <B>Proxy List</B> </TD></TR>
			</TABLE>
		</DIV>
		<DIV CLASS="scroll" STYLE="background-color:#CCCCCC;">
			<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
				<?php  $proxies = get_proxies(); foreach ($proxies as $k => $v) { ?>
				<TR BGCOLOR="#CCCCCC">
					<TD ALIGN=right CLASS="logo"> <?php echo $k; ?> </TD>
					<TD ALIGN=left CLASS="logo"> <?php echo $v; ?> </TD>
					<TD ALIGN=right CLASS="logo op"> <A HREF="proxy_applications_management.php?remove_proxy_name=<?php echo rawurlencode($k); ?>" CLASS="tabon" onclick="return confirm('Remove Proxy <?php echo $k; ?>?');">DELETE</a> </TD>
				</TR>
				<?php } ?>
			</TABLE>
		</DIV>
	</DIV>
	<DIV CLASS="clear"></DIV>
</DIV>
<!-- =================================== Proxy Configuration Pannel END =================================== -->
</DIV><!-- BODY DIV END -->
</BODY>
</HTML>
