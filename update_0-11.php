<!doctype html>
<html>
<head>
  <title>TS-N.NET ranksystem - Update 0.11</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
</head>  
<body>
<?php
require_once('other/config.php');
require_once('lang.php');
$dbname=$db['dbname'];


if($currvers=='0.11-beta') {
	echo'<span class="wncolor">'.$lang['alrup'].'</span><br>';
	if(is_file('install.php') or is_file('update_0-02.php') or is_file('update_0-10.php')) {
		unlink('install.php');
		unlink('update_0-02.php');
		unlink('update_0-10.php');
		echo '<span class="wncolor">'.sprintf($lang['updel'],'install.php<br>update_0-02.php<br>update_0-10.php<br>update_0-11.php').'</span>';
	}
} else {
	echo sprintf($lang['updb'],'0.11','0-11');
	echo '<form name="updateranksystem" method="post"><input type="submit" name="updateranksystem" value="update"></form>';
}

if(isset($_POST['updateranksystem'])) {
	$errcount = 1;
	if($mysqlcon->exec("ALTER TABLE $dbname.config ALTER COLUMN tsquery SET default '0', ALTER COLUMN tsvoice SET default '0', ALTER COLUMN resetbydbchange SET default '0',  ALTER COLUMN msgtouser SET default '0', ALTER COLUMN upcheck SET default '0', CHANGE updateinfotime updateinfotime int(8) default '0', ALTER COLUMN substridle SET default '0', ALTER COLUMN showexgrp SET default '0', ALTER COLUMN showexcld SET default '0', ALTER COLUMN showcolcld SET default '0', ALTER COLUMN showcoluuid SET default '0', ALTER COLUMN showcoldbid SET default '0', ALTER COLUMN showcolot SET default '0', ALTER COLUMN showcolit SET default '0', ALTER COLUMN showcolat SET default '0', ALTER COLUMN showcolnx SET default '0', ALTER COLUMN showcolsg SET default '0', ALTER COLUMN showgen SET default '0'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r(print_r($mysqlcon->errorInfo())).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.groups CHANGE sgid sgid bigint(10) default '0' PRIMARY KEY") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.upcheck CHANGE timestamp timestamp bigint(11) default '0'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.user CHANGE uuid uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,CHANGE cldbid cldbid bigint(10) default '0', CHANGE count count bigint(11) default '0', CHANGE ip ip bigint(10) default '0', CHANGE lastseen lastseen bigint(11) default '0', CHANGE grpid grpid bigint(10)  default '0', CHANGE nextup nextup bigint(11) default '0', CHANGE idle idle bigint(11) default '0', ALTER COLUMN online SET default '0'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (showcolrg int(1) NOT NULL default '0',showcolls int(1) NOT NULL default '0',slowmode int(1) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.groups ADD (iconid bigint(10) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE TABLE $dbname.lastscan (timestamp bigint(11) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($lastscantime = $mysqlcon->query("SELECT lastseen FROM $dbname.user WHERE uuid='lastscantime' LIMIT 1")) {
		$lastscantime = $lastscantime->fetch();
		$time = $lastscantime['lastseen'];
		if($mysqlcon->exec("INSERT INTO $dbname.lastscan SET timestamp='$time'") === false) {
			echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$errcount++;
		} else {
			if($mysqlcon->exec("DELETE FROM $dbname.user WHERE uuid='lastscantime'") === false) {
				echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
				$errcount++;
			}
		}
	} else {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($updatetime = $mysqlcon->query("SELECT updateinfotime from $dbname.config LIMIT 1")) {
		$time = $updatetime->fetch();
		if( $time['updateinfotime'] < 1800) {
			$mysqlcon->exec("UPDATE $dbname.config set updateinfotime='1800'");
		}
	}
	if ($errcount == 1) {
		if($mysqlcon->exec("UPDATE $dbname.config set currvers='0.11-beta'") === false) {
			echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$errcount++;
		}
		if ($errcount == 1) {
			echo'<span class="sccolor"">'.$lang['upsucc'].'</span><br><br>';
			if(is_file('install.php') or is_file('update_0-02.php') or is_file('update_0-10.php')) {
				unlink('install.php');
				unlink('update_0-02.php');
				unlink('update_0-10.php');
				echo '<span class="wncolor">'.sprintf($lang['updel'],'install.php<br>update_0-02.php<br>update_0-10.php<br>update_0-11.php').'</span>';
			}
		}
	}
	if ($errcount > 1) {
		echo "<span class=\"wncolor\">Error by Updating the Database for the Ranksystem. Please run the following SQL Statements yourself and be sure all works correctly:</span><br><br>
		ALTER TABLE $dbname.config ALTER COLUMN tsquery SET default '0', ALTER COLUMN tsvoice SET default '0', ALTER COLUMN resetbydbchange SET default '0',  ALTER COLUMN msgtouser SET default '0', ALTER COLUMN upcheck SET default '0', CHANGE updateinfotime updateinfotime int(8) default '0', ALTER COLUMN substridle SET default '0', ALTER COLUMN showexgrp SET default '0', ALTER COLUMN showexcld SET default '0', ALTER COLUMN showcolcld SET default '0', ALTER COLUMN showcoluuid SET default '0', ALTER COLUMN showcoldbid SET default '0', ALTER COLUMN showcolot SET default '0', ALTER COLUMN showcolit SET default '0', ALTER COLUMN showcolat SET default '0', ALTER COLUMN showcolnx SET default '0', ALTER COLUMN showcolsg SET default '0', ALTER COLUMN showgen SET default '0';<br>
		ALTER TABLE $dbname.groups CHANGE sgid sgid bigint(10) default '0' PRIMARY KEY;<br>
		ALTER TABLE $dbname.upcheck CHANGE timestamp timestamp bigint(11) default '0';<br>
		ALTER TABLE $dbname.user CHANGE uuid uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,CHANGE cldbid cldbid bigint(10) default '0', CHANGE count count bigint(11) default '0', CHANGE ip ip bigint(10) default '0', CHANGE lastseen lastseen bigint(11) default '0', CHANGE grpid grpid bigint(10)  default '0', CHANGE nextup nextup bigint(11) default '0', CHANGE idle idle bigint(11) default '0', ALTER COLUMN online SET default '0';<br>
		ALTER TABLE $dbname.config ADD (showcolrg int(1) NOT NULL default '0',showcolls int(1) NOT NULL default '0',slowmode int(1) NOT NULL default '0')<br>
		ALTER TABLE $dbname.groups ADD (iconid bigint(10) NOT NULL default '0');<br>
		CREATE TABLE $dbname.lastscan (timestamp bigint(11) NOT NULL default '0');<br>
		SELECT lastseen FROM $dbname.user WHERE uuid='lastscantime' LIMIT 1;   -- take this value and input in next SQL instead of ###VALUE###<br>
		INSERT INTO $dbname.lastscan SET timestamp='###VALUE###';<br>
		DELETE FROM $dbname.user WHERE uuid='lastscantime';<br>
		UPDATE $dbname.config set currvers='0.11-beta';<br>
		";
	}
}
?>
</body>
</html>