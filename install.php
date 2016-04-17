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
if(!isset($language) || $language == "en") {
	require_once('languages/core_en.php');
} elseif($language == "de") {
	require_once('languages/core_de.php');
} elseif($language == "ru") {
	require_once('languages/core_ru.php');
}

require_once('other/dbconfig.php');

if(isset($_POST['confweb'])) {
	require_once('other/dbconfig.php');
	$user=$_POST['user'];
	$pass=$_POST['pass'];
	$dbserver = $db['type'].':host='.$db['host'].';dbname='.$db['dbname'];
	$logpath = addslashes(__DIR__."/logs/");
	try {
		$mysqlcon = new PDO($dbserver, $db['user'], $db['pass']);
	} catch (PDOException $e) {
		$sqlconerr = 'SQL Connection failed: '.$e->getMessage();
		exit;
	}
	if($mysqlcon->exec("INSERT INTO config (webuser,webpass,tshost,tsquery,tsvoice,tsuser,language,queryname,queryname2,grouptime,resetbydbchange,msgtouser,upcheck,uniqueid,updateinfotime,currvers,exceptuuid,exceptgroup,dateformat,showexgrp,showexcld,showcolcld,showcoluuid,showcoldbid,showcolot,showcolit,showcolat,showcolnx,showcolsg,bgcolor,hdcolor,txcolor,hvcolor,ifcolor,wncolor,sccolor,showgen,showcolrg,showcolls,slowmode,cleanclients,cleanperiod,showhighest,boost,showcolas,defchid,timezone,logpath) VALUES ('$user','$pass','localhost','10011','9987','serveradmin','en','http://ts-n.net/ranksystem.php','www.ts-n.net/ranksystem.php','31536000=>47,31536060=>50','1','1','1','xrTKhT/HDl4ea0WoFDQH2zOpmKg=,9odBYAU7z2E2feUz965sL0/MyBom=','7200','1.00','xrTKhT/HDl4ea0WoFDQH2zOpmKg=','2,6','%a days, %h hours, %i mins, %s secs','1','1','1','1','1','1','1','1','1','1','#101010','#909090','#707070','#FFFFFF','#3366CC','#CC0000','#008000','1','1','1','0','1','86400','1','','1','','Europe/Berlin','$logpath')") === false)	{
		echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
	} else {
		echo'<span class="sccolor">'.$lang['isntwiusr'].'</span><br><br>';
		echo'<span class="wncolor">'.sprintf($lang['isntwidel'],"<a href=\"webinterface.php\">webinterface.php</a>").'</span>';
	}
} else {
	if(isset($_POST['installdb'])) {
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
		if(empty($host) || empty($user) || empty($pass) || empty($dbname) || isset($sqlconerr))	{
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
		} else {
			$newconfig='<?php
$db[\'type\']="'.$type.'";
$db[\'host\']="'.$host.'";
$db[\'user\']="'.$user.'";
$db[\'pass\']="'.$pass.'";
$db[\'dbname\']="'.$dbname.'";
?>';
			$handle=fopen('./other/dbconfig.php','w');
			if(!fwrite($handle,$newconfig)) {
				echo $lang['isntwicfg'];
			} else {
				$count = 1;
				echo '<br><br>'.$lang['instdb'].'<br>';
				$mysqlcon->exec("DROP DATABASE $dbname");
				if($mysqlcon->exec("CREATE DATABASE $dbname") === false) {
					echo $lang['instdberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'</span>';
				} else {
					echo'<span class="sccolor">'.sprintf($lang['instdbsuc'],$dbname).'</span>';
					$count++;
				}
				echo '<br><br>'.$lang['insttb'].'<br>';
				if($mysqlcon->exec("CREATE TABLE $dbname.user (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,cldbid bigint(10) NOT NULL default '0',count bigint(11) NOT NULL default '0',ip bigint(10) NOT NULL default '0',name text CHARACTER SET utf8 COLLATE utf8_unicode_ci,lastseen bigint(11) NOT NULL default '0',grpid bigint(10) NOT NULL default '0',nextup bigint(11) NOT NULL default '0',idle bigint(11) NOT NULL default '0',cldgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci,online int(1) NOT NULL default '0',boosttime bigint(11) NOT NULL default '0', rank bigint(11) NOT NULL default '0', platform text default NULL, nation text default NULL, version text default NULL, firstcon bigint(11) NOT NULL default '0', except int(1) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'user').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.upcheck (timestamp bigint(11) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
				} else {
					if($mysqlcon->exec("INSERT INTO $dbname.upcheck SET timestamp='1'") === false) {
						echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
					} else {
						echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'upcheck').'</span><br>';
						$count++;
					}
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.lastscan (timestamp bigint(11) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'lastscan').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.cleanclients (timestamp bigint(11) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
					$count++;
				}
				if($mysqlcon->exec("INSERT INTO $dbname.cleanclients SET timestamp='1'") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.groups (sgid bigint(10) PRIMARY KEY,sgidname text CHARACTER SET utf8 COLLATE utf8_unicode_ci,iconid bigint(10) NOT NULL default '0',icondate bigint(11) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'groups').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.config (webuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci,webpass text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tshost text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tsquery int(5) NOT NULL default '0',tsvoice int(5) NOT NULL default '0',tsuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tspass text CHARACTER SET utf8 COLLATE utf8_unicode_ci,language text CHARACTER SET utf8 COLLATE utf8_unicode_ci,queryname text CHARACTER SET utf8 COLLATE utf8_unicode_ci,queryname2 text CHARACTER SET utf8 COLLATE utf8_unicode_ci,grouptime text CHARACTER SET utf8 COLLATE utf8_unicode_ci,resetbydbchange int(1) NOT NULL default '0',msgtouser int(1) NOT NULL default '0',upcheck int(1) NOT NULL default '0',uniqueid text CHARACTER SET utf8 COLLATE utf8_unicode_ci,updateinfotime int(8) NOT NULL default '0',currvers text CHARACTER SET utf8 COLLATE utf8_unicode_ci,substridle int(1) NOT NULL default '0',exceptuuid text CHARACTER SET utf8 COLLATE utf8_unicode_ci,exceptgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci,dateformat text CHARACTER SET utf8 COLLATE utf8_unicode_ci,showexgrp int(1) NOT NULL default '0',showexcld int(1) NOT NULL default '0',showcolcld int(1) NOT NULL default '0',showcoluuid int(1) NOT NULL default '0',showcoldbid int(1) NOT NULL default '0',showcolot int(1) NOT NULL default '0',showcolit int(1) NOT NULL default '0',showcolat int(1) NOT NULL default '0',showcolnx int(1) NOT NULL default '0',showcolsg int(1) NOT NULL default '0',bgcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,hdcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,txcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,hvcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,ifcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,wncolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,sccolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci,showgen int(1) NOT NULL default '0',showcolrg int(1) NOT NULL default '0',showcolls int(1) NOT NULL default '0',slowmode bigint(11) NOT NULL default '0',cleanclients int(1) NOT NULL default '0',cleanperiod bigint(11) NOT NULL default '0',showhighest int(1) NOT NULL default '0',boost text default NULL,showcolas int(1) NOT NULL default '0',defchid bigint(11) NOT NULL default '0',timezone varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci,logpath varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'config').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.server_usage (timestamp bigint(11) NOT NULL default '0', clients bigint(11) NOT NULL default '0', channel bigint(11) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'server_usage').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.user_snapshot (timestamp bigint(11) NOT NULL default '0', uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci, count bigint(11) NOT NULL default '0', idle bigint(11) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
					if($mysqlcon->exec("CREATE INDEX snapshot_timestamp ON $dbname.user_snapshot (timestamp)") === false) {
						echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
						$count++;
					}
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'user_snapshot').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.stats_server (total_user bigint(11) NOT NULL default '0', total_online_time bigint(13) NOT NULL default '0', total_online_month bigint(11) NOT NULL default '0', total_online_week bigint(11) NOT NULL default '0', total_active_time bigint(11) NOT NULL default '0', total_inactive_time bigint(11) NOT NULL default '0', country_nation_name_1 varchar(3) NOT NULL default '0', country_nation_name_2 varchar(3) NOT NULL default '0', country_nation_name_3 varchar(3) NOT NULL default '0', country_nation_name_4 varchar(3) NOT NULL default '0', country_nation_name_5 varchar(3) NOT NULL default '0', country_nation_1 bigint(11) NOT NULL default '0', country_nation_2 bigint(11) NOT NULL default '0', country_nation_3 bigint(11) NOT NULL default '0', country_nation_4 bigint(11) NOT NULL default '0', country_nation_5 bigint(11) NOT NULL default '0', country_nation_other bigint(11) NOT NULL default '0', platform_1 bigint(11) NOT NULL default '0', platform_2 bigint(11) NOT NULL default '0', platform_3 bigint(11) NOT NULL default '0', platform_4 bigint(11) NOT NULL default '0', platform_5 bigint(11) NOT NULL default '0', platform_other bigint(11) NOT NULL default '0', version_name_1 varchar(35) NOT NULL default '0', version_name_2 varchar(35) NOT NULL default '0', version_name_3 varchar(35) NOT NULL default '0', version_name_4 varchar(35) NOT NULL default '0', version_name_5 varchar(35) NOT NULL default '0', version_1 bigint(11) NOT NULL default '0', version_2 bigint(11) NOT NULL default '0', version_3 bigint(11) NOT NULL default '0', version_4 bigint(11) NOT NULL default '0', version_5 bigint(11) NOT NULL default '0', version_other bigint(11) NOT NULL default '0', server_status int(1) NOT NULL default '0', server_free_slots bigint(11) NOT NULL default '0', server_used_slots bigint(11) NOT NULL default '0', server_channel_amount bigint(11) NOT NULL default '0', server_ping bigint(11) NOT NULL default '0', server_packet_loss float (4,4), server_bytes_down bigint(11) NOT NULL default '0', server_bytes_up bigint(11) NOT NULL default '0', server_uptime bigint(11) NOT NULL default '0', server_id bigint(11) NOT NULL default '0', server_name text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_pass int(1) NOT NULL default '0', server_creation_date bigint(11) NOT NULL default '0', server_platform text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_weblist text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_version text CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'stats_server').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.stats_user (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, removed int(1) NOT NULL default '0', rank bigint(11) NOT NULL default '0', total_connections bigint(11) NOT NULL default '0', count_week bigint(11) NOT NULL default '0', count_month bigint(11) NOT NULL default '0', idle_week bigint(11) NOT NULL default '0', idle_month bigint(11) NOT NULL default '0', achiev_count bigint(11) NOT NULL default '0', achiev_time bigint(11) NOT NULL default '0', achiev_connects bigint(11) NOT NULL default '0', achiev_battles bigint(11) NOT NULL default '0', achiev_time_perc int(3) NOT NULL default '0', achiev_connects_perc int(3) NOT NULL default '0', achiev_battles_perc int(3) NOT NULL default '0', battles_total bigint(11) NOT NULL default '0', battles_won bigint(11) NOT NULL default '0', battles_lost bigint(11) NOT NULL default '0', client_description text CHARACTER SET utf8 COLLATE utf8_unicode_ci, base64hash varchar(58) CHARACTER SET utf8 COLLATE utf8_unicode_ci, client_total_up bigint(15) NOT NULL default '0', client_total_down bigint(15) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'stats_user').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("INSERT INTO $dbname.stats_server SET total_user='9999'") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.job_check (job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, timestamp bigint(11) NOT NULL default '0')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'job_check').'</span><br>';
					$count++;
				}
				if($mysqlcon->exec("INSERT INTO $dbname.job_check (job_name) VALUES ('calc_user_limit'),('calc_user_lastscan'),('check_update'),('check_clean')") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
					$count++;
				}
				if($mysqlcon->exec("CREATE TABLE $dbname.job_log (id bigint(11) AUTO_INCREMENT PRIMARY KEY, timestamp bigint(11) NOT NULL default '0', job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci, status int(1) NOT NULL default '0', err_msg text CHARACTER SET utf8 COLLATE utf8_unicode_ci, runtime float (4,4))") === false) {
					echo $lang['insttberr'].'<span class="wncolor">'.$mysqlcon->errorCode().'.</span><br>';
				} else {
					echo '<span class="sccolor">'.sprintf($lang['insttbsuc'],'job_log').'</span><br>';
					$count++;
				}
				if($count>1) {
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
	} elseif (!is_writable('./other/dbconfig.php') || substr(sprintf('%o', fileperms('./icons/')), -4)!='0777' || substr(sprintf('%o', fileperms('./avatars/')), -4)!='0777') {
		echo '<span class="wncolor">',$lang['isntwichm'],'</span>';
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