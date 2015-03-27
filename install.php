<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Installation</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
  <script type="text/javascript" src="jquerylib/jquery.js"></script>
  <script type="text/javascript">
	function showpwd()
	{
	$('#pass')[0].type = 'text';
	}
	
	function hidepwd()
	{
	$('#pass')[0].type = 'password';
	}
  </script>
</head>
<body>
<?php
$language='en';
if(isset($_GET['lang']))
{
	$language=$_GET['lang'];
}

require_once('other/dbconfig.php');
require_once('lang.php');

if(isset($_POST['confweb']))
{
	require_once('other/dbconfig.php');
	$user=$_POST['user'];
	$pass=$_POST['pass'];
	$dbserver = $db['type'].':host='.$db['host'].';dbname='.$db['dbname'];
	try {
		$mysqlcon = new PDO($dbserver, $db['user'], $db['pass']);
	} catch (PDOException $e) {
		$sqlconerr = 'SQL Connection failed: '.$e->getMessage();
		exit;
	}
	if($mysqlcon->exec("INSERT INTO config (webuser,webpass,tshost,tsquery,tsvoice,tsuser,language,queryname,queryname2,grouptime,resetbydbchange,msgtouser,upcheck,uniqueid,updateinfotime,currvers,exceptuuid,exceptgroup,dateformat,showexgrp,showexcld,showcolcld,showcoluuid,showcoldbid,showcolot,showcolit,showcolat,showcolnx,showcolsg,bgcolor,hdcolor,txcolor,hvcolor,ifcolor,wncolor,sccolor,showgen,showcolrg,showcolls) VALUES ('$user','$pass','localhost','10011','9987','serveradmin','en','http://ts-n.net/ranksystem.php','www.ts-n.net/ranksystem.php','31536000=>47,31536060=>50','1','1','1','xrTKhT/HDl4ea0WoFDQH2zOpmKg=,9odBYAU7z2E2feUz965sL0/MyBom=','7200','0.11-beta','xrTKhT/HDl4ea0WoFDQH2zOpmKg=','2,6','%a days, %h hours, %i mins, %s secs','1','1','1','1','1','1','1','1','1','1','#101010','#909090','#707070','#FFFFFF','#3366CC','#CC0000','#008000','1','1','1')") === false)
	{
		echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
	}
	else
	{
		echo'<span class="sccolor">'.$lang['isntwiusr'].'</span><br><br>';
		echo'<span class="wncolor">'.sprintf($lang['isntwidel'],"<a href=\"webinterface.php\">webinterface.php</a>").'</span>';
	}
}
elseif($db['host']!='hostname')
{
	echo'<span class="wncolor">'.sprintf($lang['isntwidel'],"<a href=\"webinterface.php\">webinterface.php</a>").'</span>';
}
else
{
	if(isset($_POST['installdb']))
	{
		$type=$_POST['type'];
		$host=$_POST['host'];
		$user=$_POST['user'];
		$pass=$_POST['pass'];
		$dbname=$_POST['dbname'];
		$dbserver  = $type.':host='.$host.';dbname='.$dbname;
		$dbserver2 = $type.':host='.$host;
		try {
			$mysqlcon = new PDO($dbserver, $user, $pass);
		} catch (PDOException $e) {
			try {
				$mysqlcon = new PDO($dbserver2, $user, $pass);
			} catch (PDOException $e) {
				$sqlconerr = 'SQL Connection failed: '.$e->getMessage();
			}
		}
		if(empty($host) || empty($user) || empty($pass) || empty($dbname) || isset($sqlconerr))
		{
			echo '<form name="form" method="post">
			<table class="tabledefault">
			<tr><td class="right" colspan="2">Language: <select name="lang" onchange="location.href=this.form.lang.options[this.form.lang.selectedIndex].value"><option>&nbsp;</option><option value="install.php?lang=en">english</option><option value="install.php?lang=de">deutsch</option><option value="install.php?lang=ru">русский</option></select></td></tr>
			<tr><td class="center" colspan="2"><span class="size1"><b>'.$lang['instdb'].'</span></td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr><td class="wncolor" colspan="2">'.$lang['isntwidberr'].'</td></tr>';
			if(isset($sqlconerr)) {
				echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="wncolor" colspan="2">'.$lang['isntwidbmsg'].$sqlconerr.'</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
			}
			echo '<tr><td class="tdred">'.$lang['isntwidbtype'].'</td>
			<td class="tdleft"><select name="type" style="width:35%">
			<option value="cubrid">cubrid - Cubrid</option>
			<option value="dblib">dblib - FreeTDS / Microsoft SQL Server / Sybase</option>
			<option value="firebird">firebird - Firebird/Interbase 6</option>
			<option value="ibm">ibm - IBM DB2</option>
			<option value="informix">informix - IBM Informix Dynamic Server</option>
			<option value="mysql" selected="selected">mysql - MySQL 3.x/4.x/5.x [recommend]</option>
			<option value="oci">oci - Oracle Call Interface</option>
			<option value="odbc">odbc - ODBC v3 (IBM DB2, unixODBC und win32 ODBC)</option>
			<option value="pgsql">pgsql - PostgreSQL</option>
			<option value="sqlite">sqlite - SQLite 3 und SQLite 2</option>
			<option value="sqlsrv">sqlsrv - Microsoft SQL Server / SQL Azure</option>
			<option value="4d">4d - 4D</option>
			</select></td></tr>';
			if(empty($host)) {
				echo '<tr><td class="tdred">'.$lang['isntwidbhost'].'</td><td class="tdleft"><input type="text" name="host" value="',$host,'" style="width:35%"></td></tr>';
			} else {
				echo '<tr><td class="tdright">'.$lang['isntwidbhost'].'</td><td class="tdleft"><input type="text" name="host" value="',$host,'" style="width:35%"></td></tr>';
			}
			if(empty($user)) {
				echo '<tr><td class="tdred">'.$lang['isntwidbusr'].'</td><td class="tdleft"><input type="text" name="user" value="',$user,'" style="width:35%;"></td></tr>';
			} else {
				echo '<tr><td class="tdright">'.$lang['isntwidbusr'].'</td><td class="tdleft"><input type="text" name="user" value="',$user,'" style="width:35%;"></td></tr>';
			}
			if(empty($pass)) {
				echo '<tr><td class="tdred">'.$lang['isntwidbpass'].'</td><td class="tdleft"><input type="password" name="pass" id="pass" value="',$pass,'" ondblclick="showpwd()" onblur="hidepwd()" style="width:35%;"></td></tr>';
			} else {
				echo '<tr><td class="tdright">'.$lang['isntwidbpass'].'</td><td class="tdleft"><input type="password" name="pass" id="pass" value="',$pass,'" ondblclick="showpwd()" onblur="hidepwd()" style="width:35%;"></td></tr>';
			}
			if(empty($dbname)) {
				echo '<tr><td class="tdred">'.$lang['isntwidbname'].'</td><td class="tdleft"><input type="text" name="dbname" value="',$dbname,'" style="width:35%"></td></tr>';
			} else {
				echo '<tr><td class="tdright">'.$lang['isntwidbname'].'</td><td class="tdleft"><input type="text" name="dbname" value="',$dbname,'" style="width:35%"></td></tr>';
			}
			echo '<tr><td>&nbsp;</td><td class="tdleft"><br><input type="submit" name="installdb" class="button" value="'.$lang['instdbsubm'].'" style="width:150px"></td></tr>
			</table></form>';

		}
		else
		{
			$newconfig='<?php
$db[\'type\']="'.$type.'";
$db[\'host\']="'.$host.'";
$db[\'user\']="'.$user.'";
$db[\'pass\']="'.$pass.'";
$db[\'dbname\']="'.$dbname.'";
?>';
			$handle=fopen('./other/dbconfig.php','w');
			if(!fwrite($handle,$newconfig))
			{
				echo $lang['isntwicfg'];
			}
			else
			{
				echo '<br><br>'.$lang['instdb'].'<br>';
				$mysqlcon->exec("DROP DATABASE $dbname");
				if($mysqlcon->exec("CREATE DATABASE $dbname") === false)
				{
					echo $lang['instdberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'</span>';
				}
				else
				{
					echo'<span class="sccolor">'.sprintf($lang['instdbsuc'],$dbname).'</span>';
					$count++;
				}
				echo '<br><br>'.$lang['insttb'].'<br>';
				if($mysqlcon->exec("CREATE TABLE $dbname.user (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,cldbid bigint(10) NOT NULL default '0',count bigint(11) NOT NULL default '0',ip bigint(10) NOT NULL default '0',name text CHARACTER SET utf8 COLLATE utf8_unicode_ci,lastseen bigint(11) NOT NULL default '0',grpid bigint(10) NOT NULL default '0',nextup bigint(11) NOT NULL default '0',idle bigint(11) NOT NULL default '0',cldgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci,online int(1) NOT NULL default '0')") === false)
				{
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				}
				else
				{
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'user').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.upcheck (timestamp bigint(11) NOT NULL default '0')") === false)
				{
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
				}
				else
				{
					if($mysqlcon->exec("INSERT INTO $dbname.upcheck SET timestamp='1'") === false) {
						echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
					} else {
						echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'upcheck').'</span><br>';
						$count++;
					}
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.lastscan (timestamp bigint(11) NOT NULL default '0')") === false)
				{
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
				}
				else
				{
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'lastscan').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.groups (sgid bigint(10) PRIMARY KEY,sgidname text CHARACTER SET utf8 COLLATE utf8_unicode_ci,iconid bigint(10) NOT NULL default '0')") === false)
				{
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				}
				else
				{
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'groups').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.config (webuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci,webpass text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tshost text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tsquery int(5) NOT NULL default '0',tsvoice int(5) NOT NULL default '0',tsuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tspass text CHARACTER SET utf8 COLLATE utf8_unicode_ci,language text CHARACTER SET utf8 COLLATE utf8_unicode_ci,queryname text CHARACTER SET utf8 COLLATE utf8_unicode_ci,queryname2 text CHARACTER SET utf8 COLLATE utf8_unicode_ci,grouptime text CHARACTER SET utf8 COLLATE utf8_unicode_ci,resetbydbchange int(1) NOT NULL default '0',msgtouser int(1) NOT NULL default '0',upcheck int(1) NOT NULL default '0',uniqueid text CHARACTER SET utf8 COLLATE utf8_unicode_ci,updateinfotime int(8) NOT NULL default '0',currvers text CHARACTER SET utf8 COLLATE utf8_unicode_ci,substridle int(1) NOT NULL default '0',exceptuuid text CHARACTER SET utf8 COLLATE utf8_unicode_ci,exceptgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci,dateformat text CHARACTER SET utf8 COLLATE utf8_unicode_ci,showexgrp int(1) NOT NULL default '0',showexcld int(1) NOT NULL default '0',showcolcld int(1) NOT NULL default '0',showcoluuid int(1) NOT NULL default '0',showcoldbid int(1) NOT NULL default '0',showcolot int(1) NOT NULL default '0',showcolit int(1) NOT NULL default '0',showcolat int(1) NOT NULL default '0',showcolnx int(1) NOT NULL default '0',showcolsg int(1) NOT NULL default '0',bgcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,hdcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,txcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,hvcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,ifcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,wncolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,sccolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,showgen int(1) NOT NULL default '0',showcolrg int(1) NOT NULL default '0',showcolls int(1) NOT NULL default '0',slowmode int(1) NOT NULL default '0')") === false)
				{
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
				}
				else
				{
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'config').'</span><br>';
					$count++;
				}
				if($count>1)
				{
					echo '<form name="form" method="post">
					<table class="tabledefault">
					<tr><td class="center" colspan="2"><span class="size1">'.$lang['isntwiusrh'].'</span></td></tr>
					<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
					<tr><td class="center" colspan="2">'.$lang['isntwiusrdesc'].'</td></tr>
					<tr><td class="tdright">'.$lang['user'].'</td><td class="tdleft"><input type="text" name="user" value="" style="width:35%"></td></tr>
					<tr><td class="tdright">'.$lang['pass'].'</td><td class="tdleft"><input type="password" name="pass" id="pass" value="" ondblclick="showpwd()" onblur="hidepwd()" style="width:35%;"></td></tr>
					<tr><td>&nbsp;</td><td class="tdleft"><br><input type="submit" name="confweb" class="button" value="'.$lang['isntwiusrcr'].'" style="width:150px"></td></tr>
					</table></form>';
				}
			}
			fclose($handle);
		}
	} elseif (!is_writable('./other/dbconfig.php')) {
		echo '<span class="wncolor">',$lang['isntwicfg'],'</span>';
	} else {
		echo '<form name="form" method="post">
		<table class="tabledefault">
		<tr><td class="right" colspan="2">Language: <select name="lang" onchange="location.href=this.form.lang.options[this.form.lang.selectedIndex].value"><option>&nbsp;</option><option value="install.php?lang=en">english</option><option value="install.php?lang=de">deutsch</option><option value="install.php?lang=ru">русский</option></select></td></tr>
		<tr><td class="center" colspan="2"><span class="size1">'.$lang['instdb'].'</span></td></tr>
		<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td class="center" colspan="2">'.$lang['isntwidb'].'</td></tr>
		<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td class="tdright">'.$lang['isntwidbtype'].'</td>
		<td class="tdleft"><select name="type" style="width:35%">
		<option value="cubrid">cubrid - Cubrid</option>
		<option value="dblib">dblib - FreeTDS / Microsoft SQL Server / Sybase</option>
		<option value="firebird">firebird - Firebird/Interbase 6</option>
		<option value="ibm">ibm - IBM DB2</option>
		<option value="informix">informix - IBM Informix Dynamic Server</option>
		<option value="mysql" selected="selected">mysql - MySQL 3.x/4.x/5.x [recommend]</option>
		<option value="oci">oci - Oracle Call Interface</option>
		<option value="odbc">odbc - ODBC v3 (IBM DB2, unixODBC und win32 ODBC)</option>
		<option value="pgsql">pgsql - PostgreSQL</option>
		<option value="sqlite">sqlite - SQLite 3 und SQLite 2</option>
		<option value="sqlsrv">sqlsrv - Microsoft SQL Server / SQL Azure</option>
		<option value="4d">4d - 4D</option>
		</select></td></tr>
		<tr><td class="tdright">'.$lang['isntwidbhost'].'</td><td class="tdleft"><input type="text" name="host" value="" style="width:35%"></td></tr>
		<tr><td class="tdright">'.$lang['isntwidbusr'].'</td><td class="tdleft"><input type="text" name="user" value="" style="width:35%;"></td></tr>
		<tr><td class="tdright">'.$lang['isntwidbpass'].'</td><td class="tdleft"><input type="password" name="pass" id="pass" value="" ondblclick="showpwd()" onblur="hidepwd()" style="width:35%;"></td></tr>
		<tr><td class="tdright">'.$lang['isntwidbname'].'</td><td class="tdleft"><input type="text" name="dbname" value="" style="width:35%"></td></tr>
		<tr><td>&nbsp;</td><td class="tdleft"><br><input type="submit" name="installdb" class="button" value="'.$lang['instdbsubm'].'" style="width:150px"></td></tr>
		</table></form>';
	}
}
?>
</body>
</html>