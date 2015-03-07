<!doctype html>
<html>
<head>
  <title>TS-N.NET ranksystem - Update 0.10</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
</head>  
<body>
<?php
require_once('other/config.php');
require_once('lang.php');
$dbname=$db['dbname'];

if(is_file('dbconfig.php'))
{
	if(is_file('other/dbconfig.php'))
	{
		unlink('other/dbconfig.php');
	}
	rename('dbconfig.php','other/dbconfig.php');
}
if(is_file('install.php') or is_file('config.php') or is_file('mysql_connect.php') or is_file('webinterface_list.php') or is_file('update_0-02.php') or is_file('style.css'))
{
	unlink('install.php');
	unlink('config.php');
	unlink('mysql_connect.php');
	unlink('webinterface_list.php');
	unlink('update_0-02.php');
	unlink('style.css');
}

if(is_file('dbconfig.php'))
{
	echo '<wncolor>'.sprintf($lang['upmov'],'dbconfig.php','other').'</wncolor><br><br>';
	echo '<wncolor>'.sprintf($lang['updel'],'config.php<br>install.php<br>mysql_connect.php<br>style.css<br>update_0-02.php<br>webinterface_list.php').'</wncolor>';
}
elseif(is_file('install.php') or is_file('config.php') or is_file('mysql_connect.php') or is_file('webinterface_list.php') or is_file('update_0-02.php') or is_file('style.css'))
{
	echo '<wncolor>'.sprintf($lang['updel'],'config.php<br>install.php<br>mysql_connect.php<br>style.css<br>update_0-02.php<br>webinterface_list.php').'</wncolor>';
}
elseif($currvers=='0.10-beta')
{
	echo'<wncolor>'.$lang['alrup'].'</wncolor><br>';
}
else
{
echo sprintf($lang['updb'],'0.10','0-10');
echo '<form name="updateranksystem" method="post"><input type="submit" name="updateranksystem" value="update"></form>';
}

if(isset($_POST['updateranksystem']))
{
	if(!$mysqlcon->query("ALTER TABLE $dbname.config ADD (showcolsg int(1) NOT NULL,bgcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,hdcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,txcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,hvcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,ifcolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,wncolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,sccolor text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,showgen int(1) NOT NULL)"))
	{
		printf("Errormessage: %s\n", $mysqlcon->error);
	}
	echo '<br><br>'.$lang['insttb'].'<br>';
	if(!$mysqlcon->query("CREATE TABLE $dbname.groups (sgid int(10) NOT NULL,sgidname text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL)"))
	{
		echo $lang['insttberr'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
	}
	if(!$mysqlcon->query("UPDATE config set currvers='0.10-beta',showcolsg='1',bgcolor='#101010',hdcolor='#909090',txcolor='#707070',hvcolor='#FFFFFF',ifcolor='#3366CC',wncolor='#CC0000',sccolor='#008000',showgen='1'"))
	{
		printf("Errormessage: %s\n", $mysqlcon->error);
	}
	else
	{
		echo'<sccolor>'.$lang['upsucc'].'</sccolor><br><br>';
	}
}
?>
</body>
</html>
