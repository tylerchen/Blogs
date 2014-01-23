<?php
/**
 * read_authz(): read authz file.
 * read_passwd(): read passwd file.
 * write_authz(): write authz file to a temp file.
 * edit_group($group_name="",$group_users=""): edit a group and the users.
 * remove_group($group_name=""): remove a group.
 * get_groups(): return the groups of authz file.
 * get_group_users($group_name = ""): return the group users.
 * edit_repository($repo_name=""): edit(add) a repository.
 * remove_repository($repo_name=""): remove a repository.
 * editAuth_repository($repo_name="", $auth_rw=array()): edit authorization of the repository, $auth can be group or user.
 * removeAuth_repository($repo_name="", $auth=""): remove authorization of the repository, $auth can be group or user.
 * get_repositories(): return the repositories of the authz file.
 * get_repository_group_user($repository_name = ""): get the groups and users of the repository.
 * create_repository($repository_name = ''): create a repostory.
 * edit_user($user_name="", $password=""): edit or add the user with the password.
 * remove_user($user_name=""): remove the user.
 * copy_authz(): copy authz file to a temp file.
 * copy_passwd(): copy passwd file to a temp file.
 * get_users(): get the users of authz file.
 * active_authz(): active the authz configuration that you have changed.
 * active_passwd(): active the passwd configuration that you have changed.
 * restore_authz(): restore last authz file.
 * restore_passwd(): restore last passwd file.
 * copy_file($source="", $dest=""): private method to copy a file.
 **/
if(PATH_SEPARATOR == ':'){
	$backup_dir = "/opt/hdb/svnroot/conf/backup";/* Linux */
	$authz = "/opt/hdb/svnroot/conf/authz";/* Linux */
	$authz_temp = "/opt/hdb/svnroot/conf/.authz";/* Linux */
	$passwd = "/opt/hdb/svnroot/conf/passwd";/* Linux */
	$passwd_temp = "/opt/hdb/svnroot/conf/.passwd";/* Linux */
	$htpasswd = "/usr/bin/htpasswd"; /* Linux */
	$svn_repository_dir = '/opt/hdb/svnroot'; /* Linux */
	$svn_admin = '/usr/bin/svnadmin';
} else {
	$backup_dir = "D:\\ProgramFiles\\portable\\xampp\\htdocs\\web\backup";/* Windows */
	$authz = "D:\\ProgramFiles\\portable\\xampp\\htdocs\\web\\authz";/* Windows */
	$authz_temp = "D:\\ProgramFiles\\portable\\xampp\\htdocs\\web\\authz_temp";/* Windows */
	$passwd = "D:\\ProgramFiles\\portable\\xampp\\htdocs\\web\\passwd";/* Windows */
	$passwd_temp = "D:\\ProgramFiles\\portable\\xampp\\htdocs\\web\\passwd_temp";/* Windows */
	$htpasswd = "D:\\ProgramFiles\\portable\\xampp\\apache\\bin\\htpasswd.exe"; /* Windows */
	$svn_repository_dir = 'D:\\ProgramFiles\\portable\\xampp\\htdocs\\svnroot'; /* Windows */
	$svn_admin = 'E:\\Server\\svn\\svn-win32-1.6.6\\bin\\svnadmin.exe';
}

$debug = false;
$authzArr = array("[groups]" => array());
$passwdArr = array();

try{
} catch (Exception $e) {
	if($debug){
		echo '<H1>Caught exception: ',  $e->getMessage(), "<\H1>";
	}
}

/* =========================== read authz file =========================== */
function read_authz() {
	global $debug;
	global $authzArr;
	global $authz_temp;

    $fd = @fopen($authz_temp, "r");
	if ($fd == false) {
		echo "<H1>fail to open file $authz_temp!<\H1>";
		exit;
	}
    rewind($fd); /* unnessecary but I'm paranoid */


	while (!feof($fd)) {
		$buffer = fgets($fd, 4096);
		if ($debug) {
			echo "Buffer = [$buffer]<BR>";
		}

		/* all data is comprised of a name, an optional seperator, and a datum */

		/* oh wow!.. trim()!!! I could hug somebody! */
		$buffer = trim($buffer);
		if( strlen ($buffer) < 1 ||  $buffer[0] == "#"){
			continue;
		}
		{/* process [] section*/
			if($buffer[0] == "["){
				$section = $buffer;
				if(!array_key_exists($section,$authzArr)){
					$authzArr[$section] = array();
				}
				if ($debug) {
					echo "currSection = [$section]<BR>";
				}
				continue;
			}
		}
		{/* process [groups] section*/
			if($section == "[groups]"){
				$pieces = explode("=", $buffer);
				$count = count($pieces);
				if($count == 0){
					continue;
				}else if($count == 1){
					$authzArr["[groups]"][trim($pieces[0])]=array();
					continue;
				}
				$authzArr["[groups]"][trim($pieces[0])]=explode(",", trim($pieces[1]));
				continue;
			}
		}
		{// process repository section
			$pieces = explode("=", $buffer);
			$count = count($pieces);
			if($count == 0){
				continue;
			}else if($count == 1){
				$authzArr[$section][trim($pieces[0])]=array();
				continue;
			}
			$authzArr[$section][trim($pieces[0])]=trim($pieces[1]);
			continue;
		}
	}
	if($debug){// print
		echo "<BR/>========== print authz ============<BR/>";
		foreach ($authzArr as $k => $v) {
			echo "<BR/>KEY=$k, value=";
			foreach ($v as $kk => $vv) {
				echo "<BR/>KEY=$kk, value=";
				print_r($vv);
				echo "<BR/>";
			}
			echo "<BR/>";
		}
		echo "<BR/>========== print authz end ============<BR/>";
	}
	fclose($fd);
	return;
}

function read_passwd() {
	global $debug;
	global $passwd_temp;
	global $passwdArr;

    $fd = @fopen($passwd_temp, "r");
	if ($fd == false) {
		echo "<H1>fail to open file $passwd_temp!<\H1>";
		exit;
	}
    rewind($fd); /* unnessecary but I'm paranoid */

	while (!feof($fd)) {
		$buffer = fgets($fd, 4096);
		if ($debug) {
			echo "Buffer = [$buffer]<BR>";
		}

		/* all data is comprised of a name, an optional seperator, and a datum */

		/* oh wow!.. trim()!!! I could hug somebody! */
		$buffer = trim($buffer);
		if( strlen ($buffer) < 1 || $buffer[0] == "#"){
			continue;
		}
		{/* process user:password*/
			if(strpos($buffer,':') > 0){
				$pos = strpos($buffer,':');
				$passwdArr[trim(substr($buffer,0,$pos))] = trim(substr($buffer,$pos+1));
			}
		}
	}
	if($debug){// print
		echo "<BR/>========== print passwd ============<BR/>";
		foreach ($passwdArr as $k => $v) {
			echo "<BR/>KEY=$k, value=$v<BR/>";
		}
		echo "<BR/>========== print passwd end ============<BR/>";
	}
	fclose($fd);
	return;
}

function write_authz(){
	global $debug;
	global $authzArr;
	global $authz_temp;

	echo "=============";
	
	$fd_temp = @fopen($authz_temp, 'w');
	rewind($fd_temp);
	foreach ($authzArr as $k => $v) {
		fputs($fd_temp,"\n".$k."\n");
		foreach ($v as $kk => $vv) {
			if(is_array($vv)){
				fputs($fd_temp,$kk." = ".join(',',$vv)."\n");
			}else{
				fputs($fd_temp,$kk." = ".$vv."\n");
			}
		}
	}
	fclose($fd_temp);
}
/* =========================== read authz file end =========================== */
/* =========================== GROUP =========================== */
function edit_group($group_name="",$group_users=""){
	global $debug;
	global $authzArr;

	{
		$group_name = trim($group_name);
		if( strlen($group_name) < 1 ){
			return "group name is required: $group_name!";
		}
		$group_users = trim($group_users);
		$pieces = strlen($group_users)>0 ? explode(",", $group_users) : array();
		foreach( $pieces as $k => $v ){
			if(strlen($v)<1){
				continue;
			}
			if( !preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/", $v) ){
				return "group user name is illegal(/^[a-zA-Z][a-zA-Z0-9_]*$/): $v!";
			}
		}
	}
	$authzArr["[groups]"][$group_name] = $pieces;
	write_authz();
	return;
}

function remove_group($group_name=""){
	global $debug;
	global $authzArr;

	{
		$group_name = trim($group_name);
		if( strlen($group_name) < 1 ){
			return "group name is required: $group_name!";
		}
		if( !array_key_exists($group_name, $authzArr["[groups]"]) ){
			return;
		}
	}
	unset( $authzArr["[groups]"][$group_name] );
	write_authz();
	return;
}

function get_groups(){
	global $debug;
	global $authzArr;

	$groupArr = array();
	foreach ($authzArr["[groups]"] as $k => $v) {
		$groupArr[$k] = $k;
	}
	ksort($groupArr);
	if( $debug ){
		echo "Group: ".join(",",$groupArr)."\n";
	}
	return $groupArr;
}

function get_group_users($group_name = ""){
	global $debug;
	global $authzArr;

	{
		$group_name = trim($group_name);
		if( strlen($group_name) < 1 ){
			return array();
		}
	}

	$groupUserArr = array();
	foreach ($authzArr["[groups]"] as $k => $v) {
		if( $k != $group_name ){
			continue;
		}
		foreach ($v as $kk => $vv) {
			$vv = trim($vv);
			if( strlen($vv) > 0 ){
				$groupUserArr[$vv] = $vv;
			}
		}
	}
	ksort($groupUserArr);
	if( $debug ){
		echo "Group [$group_name] users: ".join(",",$groupUserArr)."\n";
	}
	return $groupUserArr;
}
/* =========================== GROUP END =========================== */

/* =========================== Repository =========================== */
function edit_repository($repo_name=""){
	global $debug;
	global $authzArr;

	{
		$repo_name = trim($repo_name);
		if( strlen($repo_name) < 1 ){
			return "repository name is required: $repo_name!";
		}
		if( array_key_exists($repo_name, $authzArr) ){
			return;
		}
		if( !preg_match("/^\\[[a-zA-Z0-9_\\-\\.:\/]*\\]$/", $repo_name) ){
			return "repository name is illegal(/^\\[[a-zA-Z0-9_\\-\\.:\/]*\\]$/): $repo_name!";
		}
	}
	$authzArr[$repo_name]=array();
	write_authz();
	return;
}

function remove_repository($repo_name=""){
	global $debug;
	global $authzArr;
	{
		$repo_name = trim($repo_name);
		if( strlen($repo_name) < 1 ){
			return "repository name is required: $repo_name!";
		}
		if( !array_key_exists($repo_name, $authzArr) ){
			return;
		}
	}
	unset( $authzArr[$repo_name] );
	write_authz();
	return;
}

function editAuth_repository($repo_name="", $auth_rw=array()){
	global $debug;
	global $authzArr;
	{
		$repo_name = trim($repo_name);
		if( strlen($repo_name) < 1 ){
			return "repository name is required: $repo_name!";
		}
		if( !array_key_exists($repo_name, $authzArr) ){
			return "repository name not found: $repo_name!";
		}
		$authzArr[$repo_name] = array();
		foreach($auth_rw as $k => $v){
			$auth = trim($k);
			$rw = trim($v);
			if( strlen($auth) < 1 ){
				return "group name or user name is required: $auth!";
			}
			$rw = (strpos($rw, "r") > -1 ? "r" : "").(strpos($rw, "w") > -1 ? "w" : "");
			if( strlen($rw) < 1 ){
				$rw = " ";
			}
			if( $auth[0] == "@" && !array_key_exists(substr($auth,1), $authzArr["[groups]"]) ){
				return "group name not found: $auth!";
			}
			$authzArr[$repo_name][$auth] = $rw;
		}
	}
	write_authz();
	return;
}

function removeAuth_repository($repo_name="", $auth=""){
	global $debug;
	global $authzArr;
	{
		$repo_name = trim($repo_name);
		if( strlen($repo_name) < 1 ){
			return "repository name is required: $repo_name!";
		}
		$auth = trim($auth);
		if( strlen($auth) < 1 ){
			return "group name or user name is required: $auth!";
		}
		if( !array_key_exists($repo_name, $authzArr) ){
			return "repository name not found: $repo_name!";
		}
		if( !array_key_exists($auth, $authzArr[$repo_name]) ){
			return;
		}
	}
	unset( $authzArr[$repo_name][$auth] );
	write_authz();
	return;
}

function get_repositories(){
	global $debug;
	global $authzArr;

	$repositoryArr = array();
	foreach ($authzArr as $k => $v) {
		if($k == "[groups]"){
			continue;
		}
		$repositoryArr[$k] = $k;
	}
	ksort($repositoryArr);
	if( $debug ){
		echo "Repository: ".join(",",$repositoryArr)."\n";
	}
	return $repositoryArr;
}

function get_repository_group_user($repository_name = ""){
	global $debug;
	global $authzArr;

	{
		$repository_name = trim($repository_name);
		if( strlen($repository_name) < 1 ){
			return array("group" => array(), "user" => array(), "*" => array());
		}
	}
	$repositoryGroupUserArr = array("group" => array(), "user" => array(), "*" => array());
	foreach ($authzArr as $k => $v) {
		if($k == "[groups]" || $k != $repository_name ){
			continue;
		}
		foreach ($v as $kk => $vv) {
			$kk = trim($kk);
			$vv = strtolower(trim($vv));
			$vv = strlen($vv) < 1 ? ' ' : $vv;
			if( $kk[0] == "@" ){
				$repositoryGroupUserArr["group"][$kk] = $vv;
			} else if( $kk == "*" ){
				$repositoryGroupUserArr["*"][$kk] = $vv;
			} else {
				$repositoryGroupUserArr["user"][$kk] = $vv;
			}
		}
	}
	if( $debug ){
		echo "Repository $repository_name: ";
		print_r($repositoryGroupUserArr);
	}
	return $repositoryGroupUserArr;
}

function create_repository($repository_name = ''){
	global $svn_repository_dir;
	global $svn_admin;

	{
		$repository_name = trim($repository_name);
		if( strlen($repository_name) < 1 ){
			return "repository name is required!";
		}
		if( !preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/", $repository_name) ){
			return "repository name is illegal (/^[a-zA-Z][a-zA-Z0-9_]*$/): $repository_name!";
		}
		{
			$handle = opendir($svn_repository_dir);
			$count = 0;
			if($handle) {
				while(false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..') {
						if( $file == $repository_name ){
							return "repository name is existing: $repository_name!";
						}
						$filename = $svn_repository_dir.DIRECTORY_SEPARATOR.$file;
						if( is_dir($filename) ){
							$count = $count + 1;
						}
					}
				}   //  end while
				closedir($handle);
			}
			if( $count > 19 ){
				return "Can't make any more svn repository, repository number is over 20: $repository_name!";
			}
		}
	}
	{
		if( !mkdir($svn_repository_dir.DIRECTORY_SEPARATOR.$repository_name) ){
			return "Can't make repository dir: $repository_name!";
		}
	}
	$out = '';
	$retval = 1;
	exec($svn_admin.' create '.$svn_repository_dir.DIRECTORY_SEPARATOR.$repository_name, $out, $retval);
	if( $retval == 0 ){
		return edit_repository("[$repository_name:/]");;
	} else {
		return "exec svn_admin error: $repository_name : $out !";
	}
}
/* =========================== Repository end =========================== */

/* =========================== User =========================== */
function edit_user($user_name="", $password=""){
	global $htpasswd;
	global $passwd_temp;

	{
		$user_name = trim($user_name);
		if( strlen($user_name) < 1 ){
			return "user name is required: $user_name!";
		}
		if( !preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/", $user_name) ){
				return "user name is illegal(/^[a-zA-Z][a-zA-Z0-9_]*$/): $user_name!";
		}
		$password = trim($password);
		if( strlen($password) < 6 ){
			return "password is required (length > 5): $password!";
		}
		if( strpos($password, " ") >-1 ){
			return "password can't contain the blank character!";
		}
	}
	$out = "";
	$retval = 1;
	exec($htpasswd." -bm ".$passwd_temp." ".$user_name." ".$password, $out, $retval);
	if( $retval == 0 ){
		return;
	} else {
		return "exec htpasswd error: $out, $user_name, $password!";
	}
}
function remove_user($user_name=""){
	global $debug;
	global $authzArr;
	global $passwd_temp;
	global $htpasswd;

	{
		$user_name = trim($user_name);
		if( strlen($user_name) < 1 ){
			return "user name is required: $user_name!";
		}
	}

	$out = "";
	$retval = 1;
	exec($htpasswd." -D ".$passwd_temp." ".$user_name, $out, $retval);
	if( $retval == 1 ){
		return "exec htpasswd error: $out";
	}
	foreach ($authzArr as $k => $v) {
		foreach ($v as $kk => $vv) {
			if($k == '[groups]'){
				foreach ($vv as $kkk => $vvv) {
					if( $vvv == $user_name){
						unset($authzArr[$k][$kk][$kkk]);
					}
				}
			} else if( $kk == $user_name ){
				unset($authzArr[$k][$kk]);
			}
		}
	}
	write_authz();
	return;
}

function get_users(){
	global $debug;
	global $authzArr;
	global $passwdArr;

	$userArr = array();
	foreach ($authzArr as $k => $v) {
		foreach ($v as $kk => $vv) {
			if($k == "[groups]"){
				if( !is_array($vv) ){
					continue;
				}
				foreach ($vv as $kkk => $vvv) {
					$vvv = trim($vvv);
					if( strlen($vvv) > 0 ){
						$userArr[$vvv] = $vvv;
					}
				}
			} else if($kk[0] != "@" ){
				$kk = trim($kk);
				if( strlen($kk) > 0 ){
					$userArr[$kk] = $kk;
				}
			}
		}
	}

	foreach ($passwdArr as $k => $v) {
		if( !array_key_exists($k, $userArr) ){
			$userArr[$k] = $k;
		}
	}

	if( array_key_exists("*", $userArr) ){
		unset($userArr["*"]);
	}
	ksort($userArr);
	if( $debug ){
		echo "User: ".join(",",$userArr)."\n";
	}
	return $userArr;
}

/* =========================== User end =========================== */

function copy_authz(){
	global $authz;
	global $authz_temp;
	copy_file($authz,$authz_temp);
}

function copy_passwd(){
	global $passwd;
	global $passwd_temp;
	copy_file($passwd,$passwd_temp);
}

function active_authz(){
	global $authz;
	global $authz_temp;
	global $backup_dir;
	copy_file($authz,$backup_dir.DIRECTORY_SEPARATOR.'authz'.time());
	copy_file($authz_temp, $authz);
}

function active_passwd(){
	global $passwd;
	global $passwd_temp;
	global $backup_dir;
	copy_file($passwd,$backup_dir.DIRECTORY_SEPARATOR.'passwd'.time());
	copy_file($passwd_temp, $passwd);
}

function restore_authz(){
	global $authz;
	global $backup_dir;

	$last_file = '';
	$handle = opendir($backup_dir);
    if($handle) {
        while(false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && strpos($file,'authz')>-1) {
                $filename = $backup_dir.DIRECTORY_SEPARATOR.$file;
                if(is_file($filename)&&(strlen($last_file)==0 || filectime($last_file)<filectime($filename))) {
                   $last_file = $filename;
                }
            }
        }   //  end while
        closedir($handle);
    }
	if(strlen($last_file)>0){
		copy_file($last_file,$authz);
	}
}

function restore_passwd(){
	global $passwd;
	global $backup_dir;

	$last_file = '';
	$handle = opendir($backup_dir);
    if($handle) {
        while(false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && strpos($file,'passwd')>-1) {
                $filename = $backup_dir.DIRECTORY_SEPARATOR.$file;
                if(is_file($filename)&&(strlen($last_file)==0 || filectime($last_file)<filectime($filename))) {
                   $last_file = $filename;
                }
            }
        }   //  end while
        closedir($handle);
    }
	if(strlen($last_file)>0){
		copy_file($last_file,$passwd);
	}
}

function copy_file($source="", $dest=""){
	global $debug;
	
	{
		$source = trim($source);
		if( strlen($source) < 1 ){
			exit;
		}
		$dest = trim($dest);
		if( strlen($dest) < 1 ){
			exit;
		}
	}
	$fd = false;
	$fd_temp = false;
	try{
		$fd = @fopen($source, "r");
		rewind($fd);
		$fd_temp = @fopen($dest, 'w');
		rewind($fd_temp);
		while (!feof($fd)) {
			$buffer = fgets($fd, 4096);
			if ($debug) {
				echo "Buffer = [$buffer]<BR>";
			}
			fputs($fd_temp, $buffer);
		}
	} catch(Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		try{fclose($fd_temp);}catch(Exception $ee){}
		try{fclose($fd);}catch(Exception $ee){}
		exit;
	}
	try{fclose($fd_temp);}catch(Exception $ee){}
	try{fclose($fd);}catch(Exception $ee){}
}

?>
