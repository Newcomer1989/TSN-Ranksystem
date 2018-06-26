<?PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if(in_array('sha512', hash_algos())) {
	ini_set('session.hash_function', 'sha512');
}
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	ini_set('session.cookie_secure', 1);
}
session_start();

require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if(!isset($_SESSION[$rspathhex.'tsuid'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}

$multiple_uuid = explode(',', substr($_SESSION[$rspathhex.'multiple'], 0, -1));

if(count($multiple_uuid) > 1 && !isset($_SESSION[$rspathhex.'uuid_verified'])) {
	$err_msg = sprintf($lang['stag0006'], '<a href="verify.php">', '</a>'); $err_lvl = 3;
} elseif ($_SESSION[$rspathhex.'connected'] == 0) {
	$err_msg = sprintf("Du konntest nicht auf dem TeamSpeak gefunden werden. Bitte %sklicke hier%s um dich zun&auml;chst zu verifizieren.", '<a href="verify.php">', '</a>'); $err_lvl = 3;
} else {
	$dbdata_fetched = $mysqlcon->query("SELECT * FROM `$dbname`.`user` WHERE `uuid` LIKE '%".$_SESSION[$rspathhex.'tsuid']."%'")->fetch();
	$count_hours = round($dbdata_fetched['count']/3600);
	$idle_hours = round($dbdata_fetched['idle']/3600);

	if ($substridle == 1) {
		$activetime = $dbdata_fetched['count'] - $dbdata_fetched['idle'];
	} else {
		$activetime = $dbdata_fetched['count'];
	}
	$active_count = $dbdata_fetched['count'] - $dbdata_fetched['idle'];

	krsort($grouptime);
	$nextgrp = '';

	foreach ($grouptime as $time => $groupid) {
		$actualgrp = $time;
		if ($activetime > $time) {
			break;
		} else {
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

	$stats_user = $mysqlcon->query("SELECT `count_week`,`active_week`,`count_month`,`active_month` FROM `$dbname`.`stats_user` WHERE `uuid`='".$_SESSION[$rspathhex.'tsuid']."'")->fetch();

	if (isset($stats_user['count_week'])) $count_week = $stats_user['count_week']; else $count_week = 0;
	$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_week"); $count_week = $dtF->diff($dtT)->format($timeformat);
	if (isset($stats_user['active_week'])) $active_week = $stats_user['active_week']; else $active_week = 0;
	$dtF = new DateTime("@0"); $dtT = new DateTime("@$active_week"); $active_week = $dtF->diff($dtT)->format($timeformat);
	if (isset($stats_user['count_month'])) $count_month = $stats_user['count_month']; else $count_month = 0;
	$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_month"); $count_month = $dtF->diff($dtT)->format($timeformat);
	if (isset($stats_user['active_month'])) $active_month = $stats_user['active_month']; else $active_month = 0;
	$dtF = new DateTime("@0"); $dtT = new DateTime("@$active_month"); $active_month = $dtF->diff($dtT)->format($timeformat);
	if (isset($dbdata_fetched['count'])) $count_total = $dbdata_fetched['count']; else $count_total = 0;
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
	if($_SESSION[$rspathhex.'tsconnections'] >= $connects_for_legendary) {
		$achievements_done = $achievements_done + 4;
	} elseif($_SESSION[$rspathhex.'tsconnections'] >= $connects_for_gold) {
		$achievements_done = $achievements_done + 3;
	} elseif($_SESSION[$rspathhex.'tsconnections'] >= $connects_for_silver) {
		$achievements_done = $achievements_done + 2;
	} else {
		$achievements_done = $achievements_done + 1;
	}
}

function get_percentage($max_value, $value) {
	return (round(($value/$max_value)*100));
}
require_once('nav.php');
?>
		<div id="page-wrapper">
		<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); 
		if(count($multiple_uuid) > 1 || $_SESSION[$rspathhex.'connected'] == 0) { echo "</div></div></body></html>"; exit; } ?>
			<div class="container-fluid">
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
										<div class="huge"><?PHP echo $_SESSION[$rspathhex.'tsname'] ?></div>
										<div><?PHP if ($dbdata_fetched['except'] == 0 || $dbdata_fetched['except'] == 1) {
											echo $lang['stmy0002'],' #',$dbdata_fetched['rank'];
										} ?></div>
									</div>
									<div class="col-xs-3">
										<?PHP
											if(isset($_SESSION[$rspathhex.'tsavatar']) && $_SESSION[$rspathhex.'tsavatar'] != "none") {
												echo '<img src="../avatars/'.$_SESSION[$rspathhex.'tsavatar'].'" class="img-rounded pull-right" alt="avatar" height="70">';
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
									<p><strong><?PHP echo $lang['stmy0005']; ?></strong></p>
									<p><strong><?PHP echo $lang['stmy0006']; ?></strong></p>
									<p><strong><?PHP echo $lang['stmy0007']; ?></strong></p>
									<p><strong><?PHP echo $lang['stmy0031']; ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0008'], '7'); ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0009'], '7'); ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0008'], '30'); ?></strong></p>
									<p><strong><?PHP echo sprintf($lang['stmy0009'], '30'); ?></strong></p>
									<p><strong><?PHP echo $lang['stmy0010']; ?></strong></p>
								</div>
								<div class="pull-right">
									<p class="text-right"><?PHP echo $dbdata_fetched['cldbid']; ?></p>
									<p class="text-right"><?PHP echo $dbdata_fetched['uuid']; ?></p>
									<p class="text-right"><?PHP echo $_SESSION[$rspathhex.'tsconnections']; ?></p>
									<p class="text-right"><?PHP echo $_SESSION[$rspathhex.'tscreated']; ?></p>
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
					<?PHP if($dbdata_fetched['except'] == 0 || $dbdata_fetched['except'] == 1) { ?>
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
						<?PHP if($_SESSION[$rspathhex.'tsconnections'] >= $connects_for_legendary) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0024']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
								<?PHP echo $lang['stmy0014']; ?>
							</div>
						</div>
						<?PHP } elseif($_SESSION[$rspathhex.'tsconnections'] >= $connects_for_gold) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0026']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($connects_for_legendary, $_SESSION[$rspathhex.'tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_legendary, $_SESSION[$rspathhex.'tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_legendary, $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0016']; ?>
							</div>
						</div>
						<?PHP } elseif($_SESSION[$rspathhex.'tsconnections'] >= $connects_for_silver) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0027']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($connects_for_gold, $_SESSION[$rspathhex.'tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_gold, $_SESSION[$rspathhex.'tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_gold, $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0018']; ?>
							</div>
						</div>
						<?PHP } elseif($_SESSION[$rspathhex.'tsconnections'] >= $connects_for_bronze) { ?>				
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0028']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($connects_for_silver, $_SESSION[$rspathhex.'tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_silver, $_SESSION[$rspathhex.'tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_silver, $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0020']; ?>
							</div>
						</div>
						<?PHP } else { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small><?PHP echo $lang['stmy0029']; ?></small>
										</div>
										<div><?PHP echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?PHP echo get_percentage($connects_for_bronze, $_SESSION[$rspathhex.'tsconnections']); ?>%;">
								<?PHP echo get_percentage($connects_for_bronze, $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0022']; ?>
							</div>
						</div>
						<?PHP } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>