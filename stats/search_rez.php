<?PHP
session_start();
include 'db.php';

require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if(!isset($_SESSION['tsuid']) || isset($_SESSION['uuid_verified'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}

$getstring = $_POST['nume'];
$searchmysql = 'WHERE name LIKE \'%'.$getstring.'%\'';

$dbdata = $mysqlcon->query("SELECT * FROM $dbname.user $searchmysql");
$dbdata_fetched = $dbdata->fetchAll();
$count_hours = round($dbdata_fetched[0]['count']/3600);
$idle_hours = round($dbdata_fetched[0]['idle']/3600);
$except = $dbdata_fetched[0]['except'];

if ($substridle == 1) {
	$activetime = $dbdata_fetched[0]['count'] - $dbdata_fetched[0]['idle'];
} else {
	$activetime = $dbdata_fetched[0]['count'];
}
$active_count = $dbdata_fetched[0]['count'] - $dbdata_fetched[0]['idle'];

krsort($grouptime);
$grpcount = 0;
$nextgrp = '';

foreach ($grouptime as $time => $groupid) {
	$grpcount++;
	$actualgrp = $time;
	if ($activetime > $time) {
		break;
	} else {
		$nextup = $time - $activetime;
		$nextgrp = $time;
	}
}
if($actualgrp==$nextgrp) {
	$actualgrp = 0;
}
if($activetime>$nextgrp) {
	$percentage_rankup = 100;
} else {
	$takedtime = $activetime - $actualgrp;
	$neededtime = $nextgrp - $actualgrp;
	$percentage_rankup = round($takedtime/$neededtime*100);
}

$stats_user = $mysqlcon->query("SELECT * FROM $dbname.stats_user WHERE uuid='$getstring'");
$stats_user = $stats_user->fetchAll();

if (isset($stats_user[0]['count_week'])) $count_week = $stats_user[0]['count_week']; else $count_week = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_week"); $count_week = $dtF->diff($dtT)->format($timeformat);
if (isset($stats_user[0]['active_week'])) $active_week = $stats_user[0]['active_week']; else $active_week = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$active_week"); $active_week = $dtF->diff($dtT)->format($timeformat);
if (isset($stats_user[0]['count_month'])) $count_month = $stats_user[0]['count_month']; else $count_month = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_month"); $count_month = $dtF->diff($dtT)->format($timeformat);
if (isset($stats_user[0]['active_month'])) $active_month = $stats_user[0]['active_month']; else $active_month = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$active_month"); $active_month = $dtF->diff($dtT)->format($timeformat);
if (isset($dbdata_fetched[0]['count'])) $count_total = $dbdata_fetched[0]['count']; else $count_total = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_total"); $count_total = $dtF->diff($dtT)->format($timeformat);
$dtF = new DateTime("@0"); $dtT = new DateTime("@$active_count"); $active_count = $dtF->diff($dtT)->format($timeformat);

$time_for_bronze = 50;
$time_for_silver = 100;
$time_for_gold = 250;
$time_for_legendary = 500;

$connects_for_bronze = 50;
$connects_for_silver = 100;
$connects_for_gold = 250;
$connects_for_legendary = 500;

$achievements_done = 0;

if($count_hours >= $time_for_legendary) {
	$achievements_done = $achievements_done + 4; 
} elseif($count_hours >= $time_for_gold) {
	$achievements_done = $achievements_done + 3;
} elseif($count_hours >= $time_for_silver) {
	$achievements_done = $achievements_done + 2;
} else {
	$achievements_done = $achievements_done + 1;
}
if($_SESSION['tsconnections'] >= $connects_for_legendary) {
	$achievements_done = $achievements_done + 4;
} elseif($_SESSION['tsconnections'] >= $connects_for_gold) {
	$achievements_done = $achievements_done + 3;
} elseif($_SESSION['tsconnections'] >= $connects_for_silver) {
	$achievements_done = $achievements_done + 2;
} else {
	$achievements_done = $achievements_done + 1;
}

function get_percentage($max_value, $value) {
	return (round(($value/$max_value)*100));
}
	

require_once('nav.php');
?>
		
<div id="page-wrapper">
		
			<div class="container-fluid">

				<!-- Page Heading -->
				<div class="row">
					<div class="col-lg-12">
 
<table class="table table-bordered table-condensed">
<tbody><tr class="info">
<td>Nume</td>
<td>UUID</td>
<td>Online</td>
<td>View Profile</td>

</tr>
<?php 
$nume=$_POST['nume'];

$query = mysql_query("SELECT cldbid,name,uuid,online,lastseen from user WHERE name LIKE '%$nume%'"); 

	  while($dnn=mysql_fetch_array($query)) { ?>
<tr>


<td>
<?php echo htmlentities($dnn['name']); ?>
</td>
<td>
<?php echo htmlentities($dnn['uuid']); ?>
</td>
<td>
<?PHP echo ($dnn['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?>
</td>
<td>
<form name="form1" method="post" action="profil.php?id=<?php echo htmlentities($dnn['cldbid']); ?>">
						
						<button class="btn btn-primary" type="submit" name="nume" value=""><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
					</form>
</td>
<?php } ?>
</tr></tbody></table>

 

 

<!-- do not affect this divs, or your design will fuck up -->

</div>
</div>
</div>
</div>
</body>
</html>