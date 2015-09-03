<!doctype html>
<html>
<head>
  <title>TS-N.NET ranksystem - Update 0.13</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
</head>  
<body>
<?php
require_once('other/config.php');
require_once('lang.php');
$dbname=$db['dbname'];


if($currvers=='0.13-beta') {
	echo'<span class="wncolor">'.$lang['alrup'].'</span><br>';
	if(is_file('install.php') or is_file('update_0-02.php') or is_file('update_0-10.php') or is_file('update_0-11.php')) {
		unlink('install.php');
		unlink('update_0-02.php');
		unlink('update_0-10.php');
		unlink('update_0-11.php');
		unlink('update_0-12.php');
		echo '<span class="wncolor">'.sprintf($lang['updel'],'install.php<br>update_0-02.php<br>update_0-10.php<br>update_0-11.php<br>update_0-12.php<br>update_0-13.php').'</span>';
	}
} elseif (!is_writable('./other/dbconfig.php') || substr(sprintf('%o', fileperms('./icons/')), -4)!='0777') {
	echo '<span class="wncolor">',$lang['isntwichm'],'</span>';
} elseif($currvers=='0.12-beta') {
	echo sprintf($lang['updb'],'0.13','0-13');
	echo '<form name="updateranksystem13" method="post"><input type="submit" name="updateranksystem13" value="update"></form>';
} else {
	echo sprintf($lang['updb'],'0.13','0-13');
	echo '<form name="updateranksystem12u13" method="post"><input type="submit" name="updateranksystem12u13" value="update"></form>';
}

if(isset($_POST['updateranksystem13'])) {
	$errcount = 1;
	if($mysqlcon->exec("INSERT INTO $dbname.cleanclients SET timestamp='1'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (showhighest int(1) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("UPDATE $dbname.config SET showhighest='1'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if ($errcount == 1) {
		if($mysqlcon->exec("UPDATE $dbname.config set currvers='0.13-beta'") === false) {
			echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$errcount++;
		}
		if ($errcount == 1) {
			echo'<span class="sccolor"">'.$lang['upsucc'].'</span><br><br>';
			if(is_file('install.php') or is_file('update_0-02.php') or is_file('update_0-10.php')) {
				unlink('install.php');
				unlink('update_0-02.php');
				unlink('update_0-10.php');
				unlink('update_0-11.php');
				unlink('update_0-12.php');
				echo '<span class="wncolor">'.sprintf($lang['updel'],'install.php<br>update_0-02.php<br>update_0-10.php<br>update_0-11.php<br>update_0-12.php<br>update_0-13.php').'</span>';
			}
		}
	}
	if ($errcount > 1) {
		echo "<span class=\"wncolor\">Error by Updating the Database for the Ranksystem. Please run the following SQL Statements yourself and be sure all works correctly:</span><br><br>
		INSERT INTO $dbname.cleanclients SET timestamp='1'
		ALTER TABLE $dbname.config ADD (showhighest int(1) NOT NULL default '0')<br>
		UPDATE $dbname.config SET showhighest='1'<br>
		UPDATE $dbname.config set currvers='0.13-beta';<br>
		";
	}
}
if(isset($_POST['updateranksystem12u13'])) {
	$errcount = 1;
	if($mysqlcon->exec("CREATE TABLE $dbname.cleanclients (timestamp bigint(11) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("INSERT INTO $dbname.cleanclients SET timestamp='1'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (cleanclients int(1) NOT NULL default '0',cleanperiod bigint(11) NOT NULL default '0',showhighest int(1) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("UPDATE $dbname.config SET cleanclients='1', cleanperiod='86400', showhighest='1'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if ($errcount == 1) {
		if($mysqlcon->exec("UPDATE $dbname.config set currvers='0.13-beta'") === false) {
			echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$errcount++;
		}
		if ($errcount == 1) {
			echo'<span class="sccolor"">'.$lang['upsucc'].'</span><br><br>';
			if(is_file('install.php') or is_file('update_0-02.php') or is_file('update_0-10.php')) {
				unlink('install.php');
				unlink('update_0-02.php');
				unlink('update_0-10.php');
				unlink('update_0-11.php');
				unlink('update_0-12.php');
				echo '<span class="wncolor">'.sprintf($lang['updel'],'install.php<br>update_0-02.php<br>update_0-10.php<br>update_0-11.php<br>update_0-12.php<br>update_0-13.php').'</span>';
			}
		}
	}
	if ($errcount > 1) {
		echo "<span class=\"wncolor\">Error by Updating the Database for the Ranksystem. Please run the following SQL Statements yourself and be sure all works correctly:</span><br><br>
		CREATE TABLE $dbname.cleanclients (timestamp bigint(11) NOT NULL default '0')<br>
		INSERT INTO $dbname.cleanclients SET timestamp='1'
		ALTER TABLE $dbname.config ADD (cleanclients int(1) NOT NULL default '0',cleanperiod bigint(11) NOT NULL default '0',showhighest int(1) NOT NULL default '0')<br>
		UPDATE $dbname.config SET cleanclients='1', cleanperiod='86400', showhighest='1'<br>
		UPDATE $dbname.config set currvers='0.13-beta';<br>
		";
	}
}
?>
</body>
</html>