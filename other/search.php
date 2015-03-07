<?php

$q = strtolower($_GET["q"]);
if (!$q) return;
require_once('config.php');
$dbuserlist=$mysqlcon->query("SELECT * FROM user ORDER BY online DESC");
$items=array();
while($userlist=$dbuserlist->fetch_assoc())
{
	$items[$userlist['name']]=$userlist['uuid'];
}

foreach ($items as $key=>$value) {
	if (strpos(strtolower($key), $q) !== false) {
		echo "$key|$value\n";
	}
}
?>