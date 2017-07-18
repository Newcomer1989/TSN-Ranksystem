<?PHP
session_start();
include 'db.php'; //simple method for mysql_connect
require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if(!isset($_SESSION['tsuid']) || isset($_SESSION['uuid_verified'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}
$username = (isset($_GET['id']) ? (int)$_GET['id'] : NULL);
//$searchmysql = 'WHERE uuid LIKE \'%'.$username.'%\'';
$searchmysql = "WHERE cldbid='$username'";
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
	//avatar
	function ts3_base16($str) {
        $convert = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p');

        $ret = '';
        for ($i = 0; $i < 20; $i++) {
                $ch = ord(substr($str, $i, 1));

                $ret .= $convert[($ch & 0xF0) >> 4];
                $ret .= $convert[$ch & 0x0F];
        }
        return $ret;
}

require_once('nav.php');
?>
		<div id="page-wrapper">
		<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
			<div class="container-fluid">

				<!-- Page Heading -->
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['stmy0001']; ?>
							<a href="#infoModal" data-toggle="modal" class="btn btn-primary">
								<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
							</a>
						</h1>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6 col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-9 text-left">
									



										<div class="huge"><?PHP echo $dbdata_fetched[0]['name'] ?></div>
										<div><?PHP if ($except == 0 || $except == 1) {
											echo $lang['stmy0002'],' #',$dbdata_fetched[0]['rank'];
										} ?></div>
										<?php //Server asigned on server and offer by bot
										$uuid=$dbdata_fetched[0]['uuid'];
										$query = mysql_query("SELECT cldgroup,grpid from user WHERE uuid='$uuid'"); 

										$dnn=mysql_fetch_array($query) ;
										
										$cldg = explode(",", $dnn['cldgroup']);
										$grpid = explode(",", $dnn['grpid']);
										if($cldg[0]!==NULL){
										echo'<div>'.$lang['grad_server'].' <img src="../icons/'.$cldg[0].'.png" alt="groupicon" title="">';
										}
										if($cldg[1]!==NULL){
										echo'<img src="../icons/'.$cldg[1].'.png" alt="groupicon" title="">';
										}
										if($cldg[2]!==NULL){
										echo'<img src="../icons/'.$cldg[2].'.png" alt="groupicon" title="">';
										}
										if($cldg[3]!==NULL){
										echo'<img src="../icons/'.$cldg[3].'.png" alt="groupicon" title="">';
										}
										if($grpid[0]!==NULL && $grpid[0]!=0 ){
										echo'<br>'.$lang['grad_system'].' <img src="../icons/'.$grpid[0].'.png" alt="groupicon" title="">';
										}
										 
										
										
										echo '</div>'; //final server and bot group
										 ?>
									
									
										</div>	
									<div class="col-xs-3">
										
									<?php
									// avatar functie by bit
$uid = (isset($dbdata_fetched[0]['uuid']) && !empty($dbdata_fetched[0]['uuid'])) ? $dbdata_fetched[0]['uuid'] : '';
if (!empty($uid)) {
        $var=ts3_base16(base64_decode($uid));
} 

//final avatar
						if($var!=NULL){ ?>
						<img src="../avatars/<?php echo $var ?>.png" class="img-rounded pull-right" alt="avatar" height="100px">
						<?php
						} else {
							    echo '<span class="fa fa-user fa-5x"></span>';
							}


											
										
											
										?>
									</div>
								</div>
							</div>
							<div class="panel-footer">
								<div class="pull-left">
									<p><strong><?PHP echo $lang['stmy0003']; ?></strong></p>
									<p><strong><?PHP echo $lang['stmy0004']; ?></strong></p>
								
									<p><strong><?PHP echo $lang['stmy0007']; ?></strong></p>
									<p><strong><?PHP echo $lang['stmy0031']; ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0008'], '7'); ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0009'], '7'); ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0008'], '30'); ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0009'], '30'); ?></strong></p>
									<p><strong><?PHP echo $lang['stmy0010']; ?></strong></p>
								</div>
								<div class="pull-right">
									<p class="text-right"><?PHP echo $dbdata_fetched[0]['cldbid']; ?></p>
									<p class="text-right"><?PHP echo $dbdata_fetched[0]['uuid']; ?></p>
									
									<p class="text-right"><?PHP echo $count_total; ?></p>
									<p class="text-right"><?PHP echo $active_count; ?></p>
									<p class="text-right"><?PHP echo $count_week; ?></p>
									<p class="text-right"><?PHP echo $active_week; ?></p>
									<p class="text-right"><?PHP echo $count_month; ?></p>
									<p class="text-right"><?PHP echo $active_month; ?></p>
									<p class="text-right"><?PHP echo $achievements_done .' / 8'; ?></p>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>
					<?PHP if($except == 0 || $except == 1) { ?>
					<div class="col-lg-6">
						<h3><?PHP echo $lang['stmy0030']; ?></h3>
						<div class="progress">
							<div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo $percentage_rankup; ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: <?PHP echo $percentage_rankup; ?>%;">
								<?PHP echo $percentage_rankup," %"; ?>
							</div>
						</div>
					</div>
					<?PHP } ?>
					<div class="col-lg-6">
						<h3><?PHP echo $lang['stmy0011']; ?></h3>
						<?PHP if($count_hours >= $time_for_legendary) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small><?PHP echo $lang['stmy0012']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0013'], $count_hours); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
								<?PHP echo $lang['stmy0014']; ?>
							</div>
						</div>
						<?PHP } elseif($count_hours >= $time_for_gold) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small><?PHP echo $lang['stmy0015']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0013'], $count_hours);; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo  get_percentage($time_for_legendary, $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?PHP echo get_percentage($time_for_legendary, $count_hours); ?>%;">
								<?PHP echo get_percentage($time_for_legendary, $count_hours), $lang['stmy0016']; ?>
							</div>
						</div>
						<?PHP } elseif($count_hours >= $time_for_silver) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small><?PHP echo $lang['stmy0017']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0013'], $count_hours); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($time_for_gold, $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?PHP echo get_percentage($time_for_gold, $count_hours); ?>%;">
								<?PHP echo get_percentage($time_for_gold, $count_hours), $lang['stmy0018']; ?>
							</div>
						</div>
						<?PHP } elseif($count_hours >= $time_for_bronze) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small><?PHP echo $lang['stmy0019']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0013'], $count_hours); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($time_for_silver, $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?PHP echo get_percentage($time_for_silver, $count_hours); ?>%;">
								<?PHP echo get_percentage($time_for_silver, $count_hours), $lang['stmy0020']; ?>
							</div>
						</div>
						<?PHP } else { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small><?PHP echo $lang['stmy0021']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0013'], $count_hours); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($time_for_bronze, $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?PHP echo get_percentage($time_for_bronze, $count_hours); ?>%;">
								<?PHP echo get_percentage($time_for_bronze, $count_hours), $lang['stmy0022']; ?>
							</div>
						</div>
						<?PHP } ?>
					</div>
					<div class="col-lg-6">
						<h3><?PHP echo $lang['stmy0023']; ?></h3>
						<?PHP if($_SESSION['tsconnections'] >= $connects_for_legendary) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0024']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION['tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
								<?PHP echo $lang['stmy0014']; ?>
							</div>
						</div>
						<?PHP } elseif($_SESSION['tsconnections'] >= $connects_for_gold) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0026']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION['tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($connects_for_legendary, $_SESSION['tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_legendary, $_SESSION['tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_legendary, $_SESSION['tsconnections']),$lang['stmy0016']; ?>
							</div>
						</div>
						<?PHP } elseif($_SESSION['tsconnections'] >= $connects_for_silver) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0027']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION['tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($connects_for_gold, $_SESSION['tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_gold, $_SESSION['tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_gold, $_SESSION['tsconnections']),$lang['stmy0018']; ?>
							</div>
						</div>
						<?PHP } elseif($_SESSION['tsconnections'] >= $connects_for_bronze) { ?>				
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0028']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION['tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($connects_for_silver, $_SESSION['tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_silver, $_SESSION['tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_silver, $_SESSION['tsconnections']),$lang['stmy0020']; ?>
							</div>
						</div>
						<?PHP } else { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0029']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION['tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_bronze, $_SESSION['tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_bronze, $_SESSION['tsconnections']),$lang['stmy0022']; ?>
							</div>
						</div>
						<?PHP } ?>
					</div>
				</div>
			</div>
			<!-- /.container-fluid -->

		</div>
		<!-- /#page-wrapper -->

	</div>
	<!-- /#wrapper -->
</body>
</html>