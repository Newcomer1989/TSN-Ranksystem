<!doctype html>
<html>
<head>
  <title>TS-N.NET ranksystem - Update 0.02</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
</head>  
<body>
<?php
require_once('other/config.php');
require_once('lang.php');
$dbname=$db['dbname'];

if($currvers=='0.02-alpha')
{
	echo'<wncolor>'.$lang['alrup'].'</wncolor>';
} else
{
	echo sprintf($lang['updb'],'0.02','0-02');

	if(!$mysqlcon->query("ALTER TABLE $dbname.config ADD (dateformat text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,showexgrp int(1) NOT NULL,showexcld int(1) NOT NULL,showcolcld int(1) NOT NULL,showcoluuid int(1) NOT NULL,showcoldbid int(1) NOT NULL,showcolot int(1) NOT NULL,showcolit int(1) NOT NULL,showcolat int(1) NOT NULL,showcolnx int(1) NOT NULL)"))
	{
		printf("Errormessage: %s\n", $mysqlcon->error);
	}
	if(!$mysqlcon->query("ALTER TABLE $dbname.user ADD (online int(1) NOT NULL)"))
	{
		printf("Errormessage: %s\n", $mysqlcon->error);
	}
	if(!$mysqlcon->query("UPDATE config set currvers='0.02-alpha',dateformat='%a days, %h hours, %i mins, %s secs',showexgrp='1',showexcld='1',showcolcld='1',showcoluuid='1',showcoldbid='1',showcolot='1',showcolit='1',showcolat='1',showcolnx='1'"))
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
