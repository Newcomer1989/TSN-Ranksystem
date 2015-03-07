<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Installation</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
</head>
<body>
<?php
if(isset($_GET['lang']))
{
	$language=$_GET['lang'];
}

require_once('other/dbconfig.php');
require_once('lang.php');

echo $test;
if(isset($_POST['confweb']))
{
	require_once('other/dbconfig.php');
	$user=$_POST['user'];
	$pass=$_POST['pass'];
	$mysqlcon=mysqli_connect($db['host'], $db['user'], $db['pass'], $db['dbname']);
	if(!$mysqlcon->query("INSERT INTO config (webuser,webpass,tshost,tsquery,tsvoice,tsuser,tspass,language,queryname,queryname2,grouptime,resetbydbchange,msgtouser,upcheck,uniqueid,updateinfotime,currvers,exceptuuid,exceptgroup,dateformat,showexgrp,showexcld,showcolcld,showcoluuid,showcoldbid,showcolot,showcolit,showcolat,showcolnx,showcolsg,bgcolor,hdcolor,txcolor,hvcolor,ifcolor,wncolor,sccolor,showgen) VALUES ('$user','$pass','localhost','10011','9987','serveradmin','querypass','en','http://ts-n.net/ranksystem.php','http://www.ts-n.net/ranksystem.php','31536000=>47,31536060=>50','1','1','1','xrTKhT/HDl4ea0WoFDQH2zOpmKg=,9odBYAU7z2E2feUz965sL0/MyBom=','7200','0.10-beta','xrTKhT/HDl4ea0WoFDQH2zOpmKg=','2,6','%a days, %h hours, %i mins, %s secs','1','1','1','1','1','1','1','1','1','1','#101010','#909090','#707070','#FFFFFF','#3366CC','#CC0000','#008000','1')"))
	{
		echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor>';
	}
	else
	{
		echo'<sccolor>'.$lang['isntwiusr'].'</sccolor><br><br>';
		echo'<wncolor>'.sprintf($lang['isntwidel'],"<a href=\"webinterface.php\">webinterface.php</a>").'</wncolor>';
	}
}
elseif($db['host']!='hostname')
{
	echo'<wncolor>'.sprintf($lang['isntwidel'],"<a href=\"webinterface.php\">webinterface.php</a>").'</wncolor>';
}
else
{
	if(isset($_POST['installdb']))
	{
		$host=$_POST['host'];
		$user=$_POST['user'];
		$pass=$_POST['pass'];
		$dbname=$_POST['dbname'];
		$mysqlcon=mysqli_connect($host, $user, $pass);

		if(empty($host) or empty($user) or empty($pass) or empty($dbname) or mysqli_connect_errno())
		{
			echo '<form name="form" method="post">
			<table class="tabledefault">
			<tr><td class="center" colspan="2"><b><h1>'.$lang['instdb'].'</h1></b></td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr><td class="red" colspan="2">'.$lang['isntwidberr'].'</td></tr>';
			if(mysqli_connect_errno())
			{ echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="red" colspan="2">'.$lang['isntwidbmsg'].mysqli_connect_error().'</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr>'; }
			if(empty($host))
			{ echo '<tr><td class="tdred">'.$lang['isntwidbhost'].'</td><td class="tdleft"><input type="text" name="host" value="',$host,'" style="width:35%"></td></tr>'; } else
			{ echo '<tr><td class="tdright">'.$lang['isntwidbhost'].'</td><td class="tdleft"><input type="text" name="host" value="',$host,'" style="width:35%"></td></tr>'; }
			if(empty($user))
			{ echo '<tr><td class="tdred">'.$lang['isntwidbusr'].'</td><td class="tdleft"><input type="text" name="user" value="',$user,'" style="width:35%;"></td></tr>'; } else
			{ echo '<tr><td class="tdright">'.$lang['isntwidbusr'].'</td><td class="tdleft"><input type="text" name="user" value="',$user,'" style="width:35%;"></td></tr>'; }
			if(empty($pass))
			{ echo '<tr><td class="tdred">'.$lang['isntwidbpass'].'</td><td class="tdleft"><input type="text" name="pass" value="',$pass,'" style="width:35%;"></td></tr>'; } else
			{ echo '<tr><td class="tdright">'.$lang['isntwidbpass'].'</td><td class="tdleft"><input type="text" name="pass" value="',$pass,'" style="width:35%;"></td></tr>'; }
			if(empty($dbname))
			{ echo '<tr><td class="tdred">'.$lang['isntwidbname'].'</td><td class="tdleft"><input type="text" name="dbname" value="',$dbname,'" style="width:35%"></td></tr>'; } else
			{ echo '<tr><td class="tdright">'.$lang['isntwidbname'].'</td><td class="tdleft"><input type="text" name="dbname" value="',$dbname,'" style="width:35%"></td></tr>'; }
			echo '<tr><td>&nbsp;</td><td class="tdleft"><br><input type="submit" name="installdb" class="button" value="'.$lang['instdbsubm'].'" style="width:150px"></td></tr>
			</table></form>';

		}
		else
		{
			$newconfig='<?php
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
				$mysqlcon->query("DROP DATABASE $dbname");
				if(!$mysqlcon->query("CREATE DATABASE $dbname"))
				{
					echo $lang['instdberr'].'<wncolor>'.$mysqlcon->error.'</wncolor>';
				}
				else
				{
					echo'<sccolor>'.sprintf($lang['instdbsuc'],$dbname).'</sccolor>';
					$count++;
				}
				echo '<br><br>'.$lang['insttb'].'<br>';
				if(!$mysqlcon->query("CREATE TABLE $dbname.user (uuid text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,cldbid int(10) NOT NULL,count int(11) NOT NULL,ip int(10) NOT NULL,name text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,lastseen int(10) NOT NULL,grpid int(10) NOT NULL,nextup int(11) NOT NULL,idle int(11) NOT NULL,cldgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,online int(1) NOT NULL)"))
				{
					echo $lang['insttberr'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
				}
				else
				{
					echo '<sccolor>'.sprintf($lang['insttbsuc'],'user').'</sccolor><br>';
					$count++;
				}
				if(!$mysqlcon->query("CREATE TABLE $dbname.upcheck (timestamp int(10) NOT NULL)"))
				{
					echo $lang['insttberr'].'<wncolor>'.$mysqlcon->error.'.</wncolor>';
					$mysqlcon->query("INSERT INTO $dbname.upcheck (timestamp) VALUES ('1')");
				}
				else
				{
					echo '<sccolor>'.sprintf($lang['insttbsuc'],'upcheck').'</sccolor><br>';
					$count++;
				}
				if(!$mysqlcon->query("CREATE TABLE $dbname.groups (sgid int(10) NOT NULL,sgidname text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL)"))
				{
					echo $lang['insttberr'].'<span class="red">'.$mysqlcon->error.'.</span><br>';
				}
				else
				{
					echo '<sccolor>'.sprintf($lang['insttbsuc'],'groups').'</sccolor><br>';
					$count++;
				}
				if(!$mysqlcon->query("CREATE TABLE $dbname.config (webuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,webpass text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,tshost text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,tsquery int(5) NOT NULL,tsvoice int(5) NOT NULL,tsuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,tspass text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,language text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,queryname text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,queryname2 text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,grouptime text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,resetbydbchange int(1) NOT NULL,msgtouser int(1) NOT NULL,upcheck int(1) NOT NULL,uniqueid text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,updateinfotime int(11) NOT NULL,currvers text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,substridle int(1) NOT NULL,exceptuuid text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,exceptgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,dateformat text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,showexgrp int(1) NOT NULL,showexcld int(1) NOT NULL,showcolcld int(1) NOT NULL,showcoluuid int(1) NOT NULL,showcoldbid int(1) NOT NULL,showcolot int(1) NOT NULL,showcolit int(1) NOT NULL,showcolat int(1) NOT NULL,showcolnx int(1) NOT NULL,showcolsg int(1) NOT NULL,bgcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,hdcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,txcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,hvcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,ifcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,wncolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,sccolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,showgen int(1) NOT NULL)"))
				{
					echo $lang['insttberr'].'<wncolor>'.$mysqlcon->error.'.</wncolor>';
				}
				else
				{
					echo '<sccolor>'.sprintf($lang['insttbsuc'],'config').'</sccolor><br>';
					$count++;
				}
				if($count>1)
				{
					echo '<form name="form" method="post">
					<table class="tabledefault">
					<tr><td class="center" colspan="2"><b><h1>'.$lang['isntwiusrh'].'</h1></b></td></tr>
					<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
					<tr><td class="center" colspan="2">'.$lang['isntwiusrdesc'].'</td></tr>
					<tr><td class="tdright">'.$lang['user'].'</td><td class="tdleft"><input type="text" name="user" value="" style="width:35%"></td></tr>
					<tr><td class="tdright">'.$lang['pass'].'</td><td class="tdleft"><input type="text" name="pass" value="" style="width:35%;"></td></tr>
					<tr><td>&nbsp;</td><td class="tdleft"><br><input type="submit" name="confweb" class="button" value="'.$lang['isntwiusrcr'].'" style="width:150px"></td></tr>
					</table></form>';
				}
			}
			fclose($handle);
		}
	}
	else
	{
		echo '<form name="form" method="post">
		<table class="tabledefault">
		<tr><td class="right" colspan="2">Language: <select name="lang" onchange="location.href=this.form.lang.options[this.form.lang.selectedIndex].value"><option></option><option value="install.php?lang=en">english</option><option value="install.php?lang=de">deutsch</option><option value="install.php?lang=ru">русский</option></select></td></tr>
		<tr><td class="center" colspan="2"><b><h1>'.$lang['insttb'].'</h1></b></td></tr>
		<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td class="center" colspan="2">'.$lang['isntwidb'].'</td></tr>
		<tr><td class="tdright">'.$lang['isntwidbhost'].'</td><td class="tdleft"><input type="text" name="host" value="" style="width:35%"></td></tr>
		<tr><td class="tdright">'.$lang['isntwidbusr'].'</td><td class="tdleft"><input type="text" name="user" value="" style="width:35%;"></td></tr>
		<tr><td class="tdright">'.$lang['isntwidbpass'].'</td><td class="tdleft"><input type="text" name="pass" value="" style="width:35%;"></td></tr>
		<tr><td class="tdright">'.$lang['isntwidbname'].'</td><td class="tdleft"><input type="text" name="dbname" value="" style="width:35%"></td></tr>
		<tr><td>&nbsp;</td><td class="tdleft"><br><input type="submit" name="installdb" class="button" value="'.$lang['instdbsubm'].'" style="width:150px"></td></tr>
		</table></form>';
	}
}
?>
</body>
</html>