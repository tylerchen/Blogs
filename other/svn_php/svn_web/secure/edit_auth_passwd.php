<?php
	session_start();
	require('parse_auth_passwd.php');

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");             // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");// always modified
	header("Cache-Control: no-cache, must-revalidate");           // HTTP/1.1
	header("Pragma: no-cache");                                   // HTTP/1.0

	$view_group = "";
	$view_repository = "";
	$remove_group = "";
	$remove_repository = "";
	$remove_user = "";
	$add_group = "";
	$add_repository = "";
	$create_repository = "";
	$add_user = "";
	$edit_group_user = "";
	$edit_repository_group_user = "";
	$active_authz_passwd = "";
	$active_start = "";
	$rollback_authz = "";
	$rollback_passwd = "";
	$hide_add = "";
	if(!isset($_SESSION["last_view"])){
		$_SESSION["last_view"] = "";
	}
	if(!isset($_SESSION["hide_add"])){
		$_SESSION["hide_add"] = "0";
	}

	if( isset($_SESSION["COPY_FILE"]) ){
		$_SESSION["COPY_FILE"] = time();
		read_authz();
		read_passwd();
	}

	if(isset($_GET['view_group'])){
		$view_group = trim($_GET['view_group']);
		$_SESSION["last_view"]='view_group='.rawurlencode($view_group);
	}
	if(isset($_GET['view_repository'])){
		$view_repository = trim($_GET['view_repository']);
		$_SESSION["last_view"]='view_repository='.rawurlencode($view_repository);
	}
	if(isset($_GET['remove_group'])){
		$remove_group = trim($_GET['remove_group']);
		remove_group($remove_group);
		redirect();
	}else if(isset($_GET['remove_repository'])){
		$remove_repository = trim($_GET['remove_repository']);
		remove_repository($remove_repository);
		redirect();
	}else if(isset($_GET['remove_user'])){
		$remove_user = trim($_GET['remove_user']);
		remove_user($remove_user);
		redirect();
	}else if(isset($_GET['active_authz_passwd'])){
		$active_authz_passwd = trim($_GET['active_authz_passwd']);
		active_authz();
		active_passwd();
		copy_authz();
		copy_passwd();
		redirect();
	}else if(isset($_GET['rollback_authz'])){
		$rollback_authz = trim($_GET['rollback_authz']);
		restore_authz();
		copy_authz();
		copy_passwd();
		redirect();
	}else if(isset($_GET['rollback_passwd'])){
		$rollback_passwd = trim($_GET['rollback_passwd']);
		restore_passwd();
		copy_authz();
		copy_passwd();
		redirect();
	}else if(isset($_GET['active_start'])){
		$active_start = trim($_GET['active_start']);
		$_SESSION["COPY_FILE"] = time();
		copy_authz();
		copy_passwd();
		redirect();
	}else if(isset($_POST['add_group'])){
		$add_group = trim($_POST['add_group']);
		$group_name = trim($_POST['group_name']);
		error(edit_group($group_name));
		redirect();
	}else if(isset($_POST['add_repository'])){
		$add_repository = trim($_POST['add_repository']);
		$repository_name = trim($_POST['repository_name']);
		error(edit_repository($repository_name));
		redirect();
	}else if(isset($_POST['create_repository'])){
		$create_repository = trim($_POST['create_repository']);
		$repository_name = trim($_POST['repository_name']);
		error(create_repository($repository_name));
		redirect();
	}else if(isset($_POST['add_user'])){
		$add_user = trim($_POST['add_user']);
		$user_name = trim($_POST['user_name']);
		$user_password = trim($_POST['user_password']);
		$user_password_confirm = trim($_POST['user_password_confirm']);
		if($user_password != $user_password_confirm){
			error("User password has different input: $user_password and $user_password_confirm!");
		}else{
			error(edit_user($user_name,$user_password));
		}
		redirect();
	}else if(isset($_POST['edit_group_user'])){
		$edit_group_user = trim($_POST['edit_group_user']);
		$group_name = $view_group = trim($_POST['group_name']);
		$users = get_users();
		$group_users = array();
		foreach($users as $k){
			if(isset($_POST[$k])){
				$group_users[]=$k;
			}
		}
		error(edit_group($group_name,join(',',$group_users)));
		redirect();
	}else if(isset($_POST['edit_repository_group_user'])){
		$edit_repository_group_user = trim($_POST['edit_repository_group_user']);
		$repository_name = $view_repository = trim($_POST['repository_name']);
		$repository_groups_users = array();
		{
			$groups = get_groups();
			foreach($groups as $k){
				$rw = '';
				if(isset($_POST['@'.$k.'=r'])){
					$rw=$rw.'r';
				}
				if(isset($_POST['@'.$k.'=w'])){
					$rw=$rw.'w';
				}
				if(isset($_POST['@'.$k.'=n'])){
					$rw=' ';
				}
				if(strlen($rw)>0){
					$repository_groups_users['@'.$k]=$rw;
				}
			}
		}
		{
			$users = get_users();
			foreach($users as $k){
				$rw = '';
				if(isset($_POST[$k.'=r'])){
					$rw=$rw.'r';
				}
				if(isset($_POST[$k.'=w'])){
					$rw=$rw.'w';
				}
				if(isset($_POST[$k.'=n'])){
					$rw=' ';
				}
				if(strlen($rw)>0){
					$repository_groups_users[$k]=$rw;
				}
			}
		}
		{
			 $rw = '';
			if(isset($_POST['*=r'])){
				$rw=$rw.'r';
			}
			if(isset($_POST['*=w'])){
				$rw=$rw.'w';
			}
			if(strlen($rw)<1 && isset($_POST['*=n'])){
				$rw=' ';
			}
			if(strlen($rw)>0){
				$repository_groups_users['*']=$rw;
			}
		}
		error(editAuth_repository($repository_name,$repository_groups_users));
		redirect();
	}else if(isset($_GET['hide_add'])){
		$_SESSION["hide_add"] = $hide_add = trim($_GET['hide_add']);
		redirect();
	}
	
function redirect(){
	global $view_group;
	global $view_repository;
	if(isset($_SESSION["last_view"])&&strlen($_SESSION["last_view"])>0){
		header('Location: '.request_uri().'?'.$_SESSION["last_view"]);
	}else{
		header('Location: '.request_uri());
	}
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
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML Strict Level 3//EN">
<HTML>
<HEAD>
<TITLE>SVN Authorization Management</TITLE>
<STYLE TYPE="text/css">
<!-- 

TD      {
        font-family: helvetica, sans-serif;
        }
        
.logo   {
        color: #FFFFFF;
        }
        
A.logolink      {
        color: #FFFFFF;
        font-size: .8em;
        }
        
.taboff {
        color: #FFFFFF;
        }
        
.tabon  {
        color: #999999;
        }
        
.title  {
        font-size: .8em;
        font-weight: bold;
        color: #660000;
        }
        
.smtext {
        font-size: .8em;
        }
        
.green  {
        color: green;
		}
TD.checkbox  {
        width: 25px;
		}
TD.op  {
        width: 32px;
		}
DIV.scroll{
		height: 150px;
		overflow-y: scroll;
		overflow-x: hidden;
		padding-right: 18px\9;
		}
DIV.body{
		padding: 0px;
		margin: auto;
		width: 960px;
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

<BODY BGCOLOR="#660000">

<?php if(!isset($_SESSION["COPY_FILE"])){ ?>
<DIV CLASS="body">
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000"> <TD ALIGN=center CLASS="logo"> <B>SVN AUTHORIZATION CONFIGURATION TOOL</B> </TD></TR>
	<TR BGCOLOR="#CC0000"> <TD ALIGN=center CLASS="logo"> <A CLASS="logo" HREF="edit_auth_passwd.php?active_start=1">Start Configure</A> </TD></TR>
</TABLE>
</DIV>
</BODY>
</HTML>
<?php exit;} ?>
<DIV CLASS="body"><!-- BODY DIV START -->
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000"> <TD ALIGN=center CLASS="logo"> <B>SVN AUTHORIZATION CONFIGURATION TOOL</B> </TD></TR>
</TABLE>
<BR/>
<!-- =================================== ERROR MESSAGES =================================== -->
<?php $msg=error(); if(strlen($msg)>0){ ?>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000"> <TD ALIGN=left CLASS="logo"> <B>ERROR MESSAGE: </B><?php echo $msg; ?> </TD></TR>
</TABLE>
<BR/>
<?php } ?>
<!-- =================================== ERROR MESSAGES END =================================== -->
<!-- =================================== SVN AUTHORIZATION CONFIGURATION PANEL =================================== -->
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000">
		<TD CLASS="logo"> <B>SVN AUTHORIZATION</B> CONFIGURATION PANEL </TD>
		<TD CLASS="logo"></TD>
		<TD CLASS="logo" ALIGN=right>
			<A CLASS="logo" HREF="edit_auth_passwd.php?active_authz_passwd=1" onclick="return confirm('Write the changes to auth and passwd file?');">Active Profile</A> | 
			<A CLASS="logo" HREF="edit_auth_passwd.php?active_start=1" onclick="return confirm('Give up the current changes?');">Reset Configuration</A> | 
			<A CLASS="logo" HREF="edit_auth_passwd.php?rollback_authz=1" onclick="return confirm('Rollback the last auth file?');">Rollback Authz</A> | 
			<A CLASS="logo" HREF="edit_auth_passwd.php?rollback_passwd=1" onclick="return confirm('Rollback the last passwd file?');">Rollback Passwd</A>
		</TD>
	</TR>
</TABLE>
<BR/>

<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000">
		<TD CLASS="logo"> <B>SVN ADD</B> INFOMATION PANEL </TD>
		<TD ALIGN=right CLASS="logo">
			<?php if(isset($_SESSION["hide_add"])&&$_SESSION["hide_add"]=="0"){ ?>
			<A CLASS="logo" HREF="edit_auth_passwd.php?hide_add=1">Hide Add Panel</A>
			<?php } else{ ?>
			<A CLASS="logo" HREF="edit_auth_passwd.php?hide_add=0">Show Add Panel</A>
			<?php } ?>
		</TD>
	</TR>
</TABLE>
<BR/>
<?php if(isset($_SESSION["hide_add"])&&$_SESSION["hide_add"]!="1"){ ?>
<DIV>
	<DIV>
	<!-- === SUMIT GROUP === -->
		<DIV CLASS="floatl with48p">
			<FORM METHOD="POST" ENCTYPE="application/x-www-form-urlencoded" ACTION="edit_auth_passwd.php">
				<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
					<TR BGCOLOR="#CC0000"> <TD CLASS="logo" colspan="2"> <B>ADD GROUP</B> </TD></TR>
					<TR>
						<TD ALIGN=left CLASS="logo"> Group Name: </TD>
						<TD ALIGN=right CLASS="logo"> <INPUT TYPE="text" SIZE="32" MAXLENGTH="64" NAME="group_name"> </TD>
					</TR>
					<TR BGCOLOR="#CC0000"> <TD ALIGN=right CLASS="logo" colspan="2"> <INPUT TYPE="submit" NAME="add_group" value="ADD GROUP"> </TD></TR>
				</TABLE>
			</FORM>
		</DIV>
	<!-- === SUMIT GROUP END === -->
	<!-- === SUMIT REPOSITORY NAME === -->
		<DIV  CLASS="floatr with48p">
			<FORM METHOD="POST" ENCTYPE="application/x-www-form-urlencoded" ACTION="edit_auth_passwd.php">
				<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
					<TR BGCOLOR="#CC0000"> <TD CLASS="logo" colspan="2"> <B>ADD REPOSITORY</B> </TD></TR>
					<TR>
						<TD ALIGN=left CLASS="logo"> Repository Name: </TD>
						<TD ALIGN=right CLASS="logo"> <INPUT TYPE="text" SIZE="32" MAXLENGTH="128" NAME="repository_name"> </TD>
					</TR>
					<TR BGCOLOR="#CC0000"> <TD ALIGN=right CLASS="logo" colspan="2"> <INPUT TYPE="submit" NAME="create_repository" value="CREATE REPOSITORY" onclick="return confirm('Do you want to create repository?');"/>  <INPUT TYPE="submit" NAME="add_repository" value="ADD REPOSITORY"/> </TD></TR>
				</TABLE>
			</FORM>
		</DIV>
	<!-- === SUMIT REPOSITORY NAME END === -->
	<DIV CLASS="clear"></DIV>
	</DIV>
	<DIV>
		<!-- === SUMIT USER NAME === -->
		<FORM METHOD="POST" ENCTYPE="application/x-www-form-urlencoded" ACTION="edit_auth_passwd.php">
			<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
				<TR BGCOLOR="#CC0000"> <TD CLASS="logo" colspan="6"> <B>ADD USER</B> </TD></TR>
				<TR>
					<TD ALIGN=left CLASS="logo"> User Name: </TD>
					<TD ALIGN=right CLASS="logo"> <INPUT TYPE="text" SIZE="24" MAXLENGTH="64" NAME="user_name"> </TD>
					<TD ALIGN=left CLASS="logo"> User Password: </TD>
					<TD ALIGN=right CLASS="logo"> <INPUT TYPE="password" SIZE="24" MAXLENGTH="64" NAME="user_password"> </TD>
					<TD ALIGN=left CLASS="logo"> Password Confirm: </TD>
					<TD ALIGN=right CLASS="logo"> <INPUT TYPE="password" SIZE="24" MAXLENGTH="64" NAME="user_password_confirm"> </TD>
				</TR>
				<TR BGCOLOR="#CC0000"> <TD ALIGN=right CLASS="logo" colspan="6"> <INPUT TYPE="submit" NAME="add_user" value="ADD USER"> </TD></TR>
			</TABLE>
		</FORM>
		<!-- === SUMIT USER NAME END === -->
	</DIV>
<DIV CLASS="clear"></DIV>
</DIV>
<?php } ?>
<!-- =================================== SVN AUTHORIZATION CONFIGURATION PANEL END =================================== -->

<!-- =================================== SVN AUTHORIZATION INFOMATION PANEL =================================== -->
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000"> <TD CLASS="logo"> <B>SVN AUTHORIZATION</B> INFOMATION PANEL </TD></TR>
</TABLE>
<BR/>
<DIV>
<DIV CLASS="floatl" STYLE="width: 29%;">
	<DIV>
		<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
			<TR BGCOLOR="#CC0000"> <TD CLASS="logo" COLSPAN="3"> <B>GROUP LIST</B> </TD></TR>
		</TABLE>
	</DIV>
	<DIV CLASS="scroll" STYLE="background-color:#666666;">
		<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
			<?php  $groups = get_groups(); foreach($groups as $k){ $t = htmlentities($k); ?>
			<TR BGCOLOR="#666666">
				<TD ALIGN=right CLASS="logo"> <?php echo $t; ?> </TD>
				<TD ALIGN=right CLASS="logo op"> <A HREF="edit_auth_passwd.php?remove_group=<?php echo rawurlencode($k); ?>" CLASS="tabon" onclick="return confirm('Remove Group <?php echo $t; ?>?');">DELETE</a> </TD>
				<TD ALIGN=right CLASS="logo"> <A HREF="edit_auth_passwd.php?view_group=<?php echo rawurlencode($k); ?>" CLASS="tabon">VIEW</a> </TD>
			</TR>
			<?php } ?>
		</TABLE>
	</DIV>
</DIV>
<DIV CLASS="floatl" style="width: 44%; left: 10px; right: 10px;">
	<DIV>
		<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
			<TR BGCOLOR="#CC0000"> <TD CLASS="logo" COLSPAN="3"> <B>REPOSITORY LIST</B> </TD></TR>
		</TABLE>
	</DIV>
	<DIV CLASS="scroll" STYLE="background-color:#666666;">
		<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
			<?php  $repositories = get_repositories(); foreach($repositories as $k){ $t = htmlentities($k); ?>
			<TR BGCOLOR="#666666">
				<TD ALIGN=right CLASS="logo"> <?php echo $t; ?> </TD>
				<TD ALIGN=right CLASS="logo op"> <A HREF="edit_auth_passwd.php?remove_repository=<?php echo rawurlencode($k); ?>" CLASS="tabon" onclick="return confirm('Remove Repository <?php echo $t; ?>?');">DELETE</a> </TD>
				<TD ALIGN=right CLASS="logo"> <A HREF="edit_auth_passwd.php?view_repository=<?php echo rawurlencode($k); ?>" CLASS="tabon">VIEW</a> </TD>
			</TR>
			<?php } ?>
		</TABLE>
	</DIV>
</DIV>
<DIV CLASS="floatl" STYLE="width: 25%; left: 20px;">
	<DIV>
		<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
			<TR BGCOLOR="#CC0000"> <TD CLASS="logo" COLSPAN="2"> <B>USER LIST</B> </TD></TR>
		</TABLE>
	</DIV>
	<DIV CLASS="scroll" STYLE="background-color:#666666;">
		<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
			<?php  $users = get_users(); foreach($users as $k){ $t = htmlentities($k); ?>
			<TR BGCOLOR="#666666">
				<TD ALIGN=right CLASS="logo"> <?php echo $t; ?> </TD>
				<TD ALIGN=right CLASS="logo op"> <A HREF="edit_auth_passwd.php?remove_user=<?php echo rawurlencode($k); ?>" CLASS="tabon" onclick="return confirm('Remove User <?php echo $t; ?>?');">DELETE</a> </TD>
			</TR>
			<?php } ?>
		</TABLE>
	</DIV>
</DIV>
<DIV CLASS="clear"></DIV>
</DIV>
<!-- =================================== SVN AUTHORIZATION INFOMATION PANEL END =================================== -->

<!-- =================================== SVN GROUP [] CONFIGURATION PANEL =================================== -->
<?php if(strlen($view_group)>0){ ?>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000"> <TD CLASS="logo"> <B>SVN GROUP <?php echo $view_group; ?></B> CONFIGURATION PANEL (R:read W:write N:none)</TD></TR>
</TABLE>
<BR/>
<DIV>
<FORM METHOD="POST" ENCTYPE="application/x-www-form-urlencoded" ACTION="edit_auth_passwd.php">
	<INPUT TYPE=hidden NAME="group_name" VALUE="<?php echo htmlentities($view_group);?>"/>
	<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
		<TR BGCOLOR="#CC0000"> <TD CLASS="logo" COLSPAN="<?php echo $column=6;?>"> <B>GROUP USER LIST</B> </TD></TR>
		<TR BGCOLOR="#666666">
			<?php for($i=0;$i<$column;$i++){ /*start for*/?>
			<TD CLASS="logo" VALIGN="top" <?php if($i+1!=$column){?>STYLE="border-right:white 1px solid;"<?php } ?>>
				<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
					<?php $count=0; $users = get_users(); $groupUsers = get_group_users($view_group); foreach($users as $k){if($count++%$column!=$i){continue;} $t = htmlentities($k); $ake=array_key_exists($k,$groupUsers);/*start foreach1*/ ?>
					<TR BGCOLOR="<?php echo $ake?'#AA1122':'#666666';?>">
						<TD ALIGN=left CLASS="logo checkbox"> <INPUT TYPE="checkbox" NAME="<?php echo $t; ?>" value="1" <?php if($ake){ ?>checked="checked" <?php } ?> /> </TD>
						<TD ALIGN=right CLASS="logo"> <?php echo $t; ?> </TD>
					</TR>
					<?php }/*end foreach1*/ ?>
				</TABLE>
			</TD>
			<?php } /*end for*/?>
		</TR>
		<TR BGCOLOR="#CC0000"> <TD ALIGN=right CLASS="logo" COLSPAN="<?php echo $column;?>"> <INPUT TYPE="SUBMIT" NAME="edit_group_user" value="Edit Group User"/> </TD></TR>
	</TABLE>
</FORM>
</DIV>
<?php } ?>
<!-- =================================== SVN GROUP [] CONFIGURATION PANEL END =================================== -->

<!-- =================================== SVN REPOSITORY [] CONFIGURATION PANEL =================================== -->
<?php if(strlen($view_repository)>0){ $groups = get_groups(); $users = get_users(); $repoGroupUsers = get_repository_group_user($view_repository); ?>
<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
	<TR BGCOLOR="#CC0000"> <TD CLASS="logo"> <B>SVN REPOSITORY <?php echo $view_repository; ?></B> CONFIGURATION PANEL (R:read W:write N:none)</TD></TR>
</TABLE>
<BR/>
<DIV>
<FORM METHOD="POST" ENCTYPE="application/x-www-form-urlencoded" ACTION="edit_auth_passwd.php">
<INPUT TYPE=hidden NAME="repository_name" VALUE="<?php echo htmlentities($view_repository);?>"/>
<DIV>
	<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
		<TR BGCOLOR="#CC0000"> <TD CLASS="logo" COLSPAN="<?php echo $column=3;?>"> <B>REPOSITORY GROUP LIST</B> </TD></TR>
		<TR BGCOLOR="#666666">
			<?php for($i=0;$i<$column;$i++){ /*start for*/?>
			<TD CLASS="logo" VALIGN="top" <?php if($i+1!=$column){?>STYLE="border-right:white 1px solid;"<?php } ?>>
				<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
					<?php $count=0; foreach($groups as $k){if($count++%$column!=$i){continue;} $t = htmlentities($k); $ake=array_key_exists("@".$k,$repoGroupUsers["group"]);/*start foreach1*/ ?>
					<TR BGCOLOR="<?php echo $ake?'#AA1122':'#666666';?>">
						<TD ALIGN=left CLASS="logo"> 
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities('@'.$t.'=r'); ?>" value="r" <?php if($ake && strpos($repoGroupUsers["group"]["@".$k],"r")>-1){ ?>checked="checked"<?php } ?>/>R&nbsp;
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities('@'.$t.'=w'); ?>" value="w" <?php if($ake && strpos($repoGroupUsers["group"]["@".$k],"w")>-1){ ?>checked="checked"<?php } ?>/>W&nbsp;
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities('@'.$t.'=n'); ?>" value="n" <?php if($ake && $repoGroupUsers["group"]["@".$k]==' '){ ?>checked="checked"<?php } ?>/>N&nbsp;
						</TD>
						<TD ALIGN=right CLASS="logo"> <?php echo '@'.$t; ?> </TD>
					</TR>
					<?php }/*end foreach1*/ ?>
				</TABLE>
			</TD>
			<?php } /*end for*/?>
		</TR>
		<TR BGCOLOR="#CC0000"> <TD ALIGN=right CLASS="logo" COLSPAN="<?php echo $column;?>"> <INPUT TYPE="SUBMIT" NAME="edit_repository_group_user" value="Edit Repostitory Group & User"/> </TD></TR>
	</TABLE>
</DIV>
<BR/>
<DIV>
	<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
		<TR BGCOLOR="#CC0000"> <TD CLASS="logo" COLSPAN="<?php echo $column=3;?>"> <B>REPOSITORY USER LIST</B> </TD></TR>
		<TR BGCOLOR="#666666">
			<?php for($i=0;$i<$column;$i++){ /*start for*/?>
			<TD CLASS="logo" VALIGN="top" <?php if($i+1!=$column){?>STYLE="border-right:white 1px solid;"<?php } ?>>
				<TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="5">
					<?php $count=0; foreach($users as $k){if($count++%$column!=$i){continue;} $t = htmlentities($k); $ake=array_key_exists($k,$repoGroupUsers["user"]);/*start foreach1*/ ?>
					<?php if($count==$i+1){$ake1=0;if($count==1){$ake1=array_key_exists('*',$repoGroupUsers['*']);}/*start if3*/ ?>
					<TR BGCOLOR="<?php echo $ake1?'#AA1122':'#666666';?>">
						<TD ALIGN=left CLASS="logo">
							<?php if($count==1){ ?>
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities('*=r'); ?>" value="r" <?php if($ake1 && strpos($repoGroupUsers['*']['*'],"r")>-1){ ?>checked="checked"<?php } ?>/>R&nbsp;
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities('*=w'); ?>" value="w" <?php if($ake1 && strpos($repoGroupUsers['*']['*'],"w")>-1){ ?>checked="checked"<?php } ?>/>W&nbsp;
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities('*=n'); ?>" value="n" <?php if($ake1 && $repoGroupUsers['*']['*']==' '){ ?>checked="checked"<?php } ?>/>N&nbsp;
							<?php } ?>
						</TD>
						<TD ALIGN=right CLASS="logo"> <?php echo $count==1?'*':'&nbsp;'; ?> </TD>
					</TR>
					<?php }/*end if3*/ ?>
					<TR BGCOLOR="<?php echo $ake?'#AA1122':'#666666';?>">
						<TD ALIGN=left CLASS="logo"> 
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities($t.'=r'); ?>" value="r" <?php if($ake && strpos($repoGroupUsers["user"][$k],"r")>-1){ ?>checked="checked"<?php } ?>/>R&nbsp;
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities($t.'=w'); ?>" value="w" <?php if($ake && strpos($repoGroupUsers["user"][$k],"w")>-1){ ?>checked="checked"<?php } ?>/>W&nbsp;
							<INPUT TYPE="checkbox" NAME="<?php echo htmlentities($t.'=n'); ?>" value="n" <?php if($ake && $repoGroupUsers["user"][$k]==' '){ ?>checked="checked"<?php } ?>/>N&nbsp;
						</TD>
						<TD ALIGN=right CLASS="logo"> <?php echo $t; ?> </TD>
					</TR>
					<?php }/*end foreach1*/ ?>
				</TABLE>
			</TD>
			<?php } /*end for*/?>
		</TR>
		<TR BGCOLOR="#CC0000"> <TD ALIGN=right CLASS="logo" COLSPAN="<?php echo $column;?>"> <INPUT TYPE="SUBMIT" NAME="edit_repository_group_user" value="Edit Repostitory Group & User"/> </TD></TR>
	</TABLE>
</DIV>
<DIV CLASS="clear"></DIV>
</FORM>
</DIV>
<?php } ?>
<!-- =================================== SVN REPOSITORY [] CONFIGURATION PANEL END =================================== -->
</DIV><!-- BODY DIV END -->
</BODY>
</HTML>
