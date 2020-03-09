<?PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if(in_array('sha512', hash_algos())) {
	ini_set('session.hash_function', 'sha512');
}
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	ini_set('session.cookie_secure', 1);
	if(!headers_sent()) {
		header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload;");
	}
}
session_start();

require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);

if(!isset($_SESSION[$rspathhex.'tsuid'])) {
	set_session_ts3($mysqlcon,$cfg,$lang,$dbname);
}

if ($cfg['rankup_time_assess_mode'] == 1) {
	$db_arr = $mysqlcon->query("SELECT `s`.`uuid`,`s`.`count_month`,`s`.`idle_month`,`u`.`name`,`u`.`online`,`u`.`cldgroup` FROM `$dbname`.`stats_user` AS `s` INNER JOIN `$dbname`.`user` AS `u` ON `s`.`uuid`=`u`.`uuid` WHERE `s`.`removed`='0' ORDER BY (`s`.`count_month` - `s`.`idle_month`) DESC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	$texttime = $lang['sttw0013'];
} else {
	$db_arr = $mysqlcon->query("SELECT `s`.`uuid`,`s`.`count_month`,`s`.`idle_month`,`u`.`name`,`u`.`online`,`u`.`cldgroup` FROM `$dbname`.`stats_user` AS `s` INNER JOIN `$dbname`.`user` AS `u` ON `s`.`uuid`=`u`.`uuid` WHERE `s`.`removed`='0' ORDER BY `s`.`count_month` DESC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	$texttime = $lang['sttw0003'];
}

$count_timestamps = $mysqlcon->query("SELECT COUNT(DISTINCT(`timestamp`)) AS `count` from `$dbname`.`user_snapshot`")->fetch();

$sumentries = count($db_arr) - 50;
$count10 = 0;
$top10_sum = 0;
$top10_idle_sum = 0;

foreach ($db_arr as $uuid => $client) {
	$sgroups = array_flip(explode(",", $client['cldgroup']));
	if (!isset($cfg['rankup_excepted_unique_client_id_list'][$uuid]) && (!isset($cfg['rankup_excepted_group_id_list']) || !array_intersect_key($sgroups, $cfg['rankup_excepted_group_id_list']))) {
		if ($count10 == 50) break;
		if ($cfg['rankup_time_assess_mode'] == 1) {
			$hours = $client['count_month'] - $client['idle_month'];
		} else {
			$hours = $client['count_month'];
		}
		$top10_sum = round(($client['count_month']/3600)) + $top10_sum;
		$top10_idle_sum = round(($client['idle_month']/3600)) + $top10_idle_sum;
		$client_data[$count10] = array(
		'name'		=>	$client['name'],
		'user_id'   =>  $client['uuid'],
		'count'		=>	$hours,
		'online'	=>	$client['online']
		);
		$count10++;
	}
}

for($count10 = $count10; $count10 <= 50; $count10++) {
	$client_data[$count10] = array(
		'name'		=>	"<i>unkown</i>",
		'user_id'   =>  "0",
		'count'		=>	"0",
		'online'	=>	"0"
	);
}

$sum = $mysqlcon->query("SELECT SUM(`count_month`) AS `count`, SUM(`idle_month`) AS `idle` FROM `$dbname`.`stats_user`")->fetch();
$others_sum = round(($sum['count']/3600)) - $top10_sum;
$others_idle_sum = round(($sum['idle']/3600)) - $top10_idle_sum;

function get_percentage($max_value, $value) {
	return (round(($value/$max_value)*100));
}
require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['sttw0001']; ?>
							<small><?PHP echo $lang['sttm0001']; ?></small>
						</h1>
					</div>
				</div>
				<?PHP if($count_timestamps['count'] < 120) {  echo $lang['stix0048'],' (',$count_timestamps['count'],'/120)'; } else { ?>
				<div class="row">
					<div class="col-lg-4 col-lg-offset-4">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<p class="text-center"><i>#1st</i></p>
										<p class="text-center"><i class="fa fa-trophy fa-5x"></i></p>
									</div>
									<div class="col-xs-9 text-right">
										<div>&nbsp;</div>
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[0]['name']),'">',htmlspecialchars($client_data[0]['name']); ?></span></div>
										<div><?PHP if($client_data[0]['count']<3600) { echo sprintf($texttime, round(($client_data[0]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[0]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4 col-lg-offset-2">
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<p class="text-center"><i>#2nd</i></p>
										<p class="text-center"><i class="fa fa-trophy fa-5x"></i></p>
									</div>
									<div class="col-xs-9 text-right">
										<div>&nbsp;</div>
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[1]['name']),'">',htmlspecialchars($client_data[1]['name']); ?></span></div>
										<div><?PHP if($client_data[1]['count']<3600) { echo sprintf($texttime, round(($client_data[1]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[1]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<p class="text-center"><i>#3rd</i></p>
										<p class="text-center"><i class="fa fa-trophy fa-5x"></i></p>
									</div>
									<div class="col-xs-9 text-right">
										<div>&nbsp;</div>
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[2]['name']),'">',htmlspecialchars($client_data[2]['name']); ?></span></div>
										<div><?PHP if($client_data[2]['count']<3600) { echo sprintf($texttime, round(($client_data[2]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[2]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa-2x">#4th</i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[3]['name']),'">',htmlspecialchars($client_data[3]['name']); ?></span></div>
										<div><?PHP if($client_data[3]['count']<3600) { echo sprintf($texttime, round(($client_data[3]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[3]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa-2x">#5th</i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[4]['name']),'">',htmlspecialchars($client_data[4]['name']); ?></span></div>
										<div><?PHP if($client_data[4]['count']<3600) { echo sprintf($texttime, round(($client_data[4]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[4]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa-2x">#6th</i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[5]['name']),'">',htmlspecialchars($client_data[5]['name']); ?></span></div>
										<div><?PHP if($client_data[5]['count']<3600) { echo sprintf($texttime, round(($client_data[5]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[5]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<div style="line-height:90%;">
											<br>
										</div>
										<i class="fa-2x">#7th</i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[6]['name']),'">',htmlspecialchars($client_data[6]['name']); ?></span></div>
										<div><?PHP if($client_data[6]['count']<3600) { echo sprintf($texttime, round(($client_data[6]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[6]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<div style="line-height:90%;">
											<br>
										</div>
										<i class="fa-2x">#8th</i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[7]['name']),'">',htmlspecialchars($client_data[7]['name']); ?></span></div>
										<div><?PHP if($client_data[7]['count']<3600) { echo sprintf($texttime, round(($client_data[7]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[7]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<div style="line-height:90%;">
											<br>
										</div>
										<i class="fa-2x">#9th</i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[8]['name']),'">',htmlspecialchars($client_data[8]['name']); ?></span></div>
										<div><?PHP if($client_data[8]['count']<3600) { echo sprintf($texttime, round(($client_data[8]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[8]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<div style="line-height:90%;">
											<br>
										</div>
										<i class="fa-2x">#10th</i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="tophuge"><span title=<?PHP echo '"',htmlspecialchars($client_data[9]['name']),'">',htmlspecialchars($client_data[9]['name']); ?></span></div>
										<div><?PHP if($client_data[9]['count']<3600) { echo sprintf($texttime, round(($client_data[9]['count']/60)), $lang['sttw0015']); } else { echo sprintf($texttime, round(($client_data[9]['count']/3600)), $lang['sttw0014']); } ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h2><?PHP echo $lang['sttw0004']; ?></h2>
						<h4>#1 <?PHP echo htmlspecialchars($client_data[0]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[0]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[0]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: 100%;"><?PHP echo round(($client_data[0]['count']/3600)) .'&nbsp;'.$lang['sttw0005']?>
							</div>
						</div>
						<h4>#2 <?PHP echo htmlspecialchars($client_data[1]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[1]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[1]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[1]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[1]['count'])); ?>
							</div>
						</div>
						<h4>#3 <?PHP echo htmlspecialchars($client_data[2]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[2]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[2]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[2]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[2]['count'])); ?>
							</div>
						</div>
						<h4>#4 <?PHP echo htmlspecialchars($client_data[3]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[3]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[3]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[3]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[3]['count'])); ?>
							</div>
						</div>
						<h4>#5 <?PHP echo htmlspecialchars($client_data[4]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[4]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[4]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[4]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[4]['count'])); ?>
							</div>
						</div>
						<h4>#6 <?PHP echo htmlspecialchars($client_data[5]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[5]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[5]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[5]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[5]['count'])); ?>
							</div>
						</div>
						<h4>#7 <?PHP echo htmlspecialchars($client_data[6]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[6]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[6]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[6]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[6]['count'])); ?>
							</div>
						</div>
						<h4>#8 <?PHP echo htmlspecialchars($client_data[7]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[7]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[7]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[7]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[7]['count'])); ?>
							</div>
						</div>
						<h4>#9 <?PHP echo htmlspecialchars($client_data[8]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[8]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[8]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[8]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[8]['count'])); ?>
							</div>
						</div>
						<h4>#10 <?PHP echo htmlspecialchars($client_data[9]['name'],' ( ',$client_data[9]['user_id'],')') ?><?PHP echo ($client_data[9]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[9]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[9]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[9]['count'])); ?>
							</div>
						</div>
						<h4>#11 <?PHP echo htmlspecialchars($client_data[10]['name']) ?><?PHP echo ($client_data[10]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[10]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[10]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[10]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[10]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[10]['count'])); ?>
							</div>
						</div>
						<h4>#12 <?PHP echo htmlspecialchars($client_data[11]['name']) ?><?PHP echo ($client_data[11]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[11]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[11]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[11]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[11]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[11]['count'])); ?>
							</div>
						</div>
						<h4>#13 <?PHP echo htmlspecialchars($client_data[12]['name']) ?><?PHP echo ($client_data[12]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[12]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[12]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[12]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[12]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[12]['count'])); ?>
							</div>
						</div>
						<h4>#14 <?PHP echo htmlspecialchars($client_data[13]['name']) ?><?PHP echo ($client_data[13]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[13]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[13]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[13]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[13]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[13]['count'])); ?>
							</div>
						</div>
						<h4>#15 <?PHP echo htmlspecialchars($client_data[14]['name']) ?><?PHP echo ($client_data[14]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[14]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[14]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[14]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[14]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[14]['count'])); ?>
							</div>
						</div>
						<h4>#16 <?PHP echo htmlspecialchars($client_data[15]['name']) ?><?PHP echo ($client_data[15]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[15]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[15]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[15]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[15]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[15]['count'])); ?>
							</div>
						</div>
						<h4>#17 <?PHP echo htmlspecialchars($client_data[16]['name']) ?><?PHP echo ($client_data[16]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[16]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[16]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[16]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[16]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[16]['count'])); ?>
							</div>
						</div>
						<h4>#18 <?PHP echo htmlspecialchars($client_data[17]['name']) ?><?PHP echo ($client_data[17]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[17]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[17]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[17]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[17]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[17]['count'])); ?>
							</div>
						</div>
						<h4>#19 <?PHP echo htmlspecialchars($client_data[18]['name']) ?><?PHP echo ($client_data[18]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[18]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[18]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[18]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[18]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[18]['count'])); ?>
							</div>
						</div>
						<h4>#20 <?PHP echo htmlspecialchars($client_data[19]['name']) ?><?PHP echo ($client_data[19]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[19]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[19]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[19]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[19]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[19]['count'])); ?>
							</div>
						</div>
						<h4>#21 <?PHP echo htmlspecialchars($client_data[20]['name']) ?><?PHP echo ($client_data[20]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[20]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[20]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[20]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[20]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[20]['count'])); ?>
							</div>
						</div>
						<h4>#22 <?PHP echo htmlspecialchars($client_data[21]['name']) ?><?PHP echo ($client_data[21]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[21]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[21]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[21]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[21]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[21]['count'])); ?>
							</div>
						</div>
						<h4>#23 <?PHP echo htmlspecialchars($client_data[22]['name']) ?><?PHP echo ($client_data[22]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[22]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[22]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[22]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[22]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[22]['count'])); ?>
							</div>
						</div>
						<h4>#24 <?PHP echo htmlspecialchars($client_data[23]['name']) ?><?PHP echo ($client_data[23]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[23]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[23]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[23]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[23]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[23]['count'])); ?>
							</div>
						</div>
						<h4>#25 <?PHP echo htmlspecialchars($client_data[24]['name']) ?><?PHP echo ($client_data[24]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[24]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[24]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[24]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[24]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[24]['count'])); ?>
							</div>
						</div>
						<h4>#26 <?PHP echo htmlspecialchars($client_data[25]['name']) ?><?PHP echo ($client_data[25]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[25]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[25]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[25]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[25]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[25]['count'])); ?>
							</div>
						</div>
						<h4>#27 <?PHP echo htmlspecialchars($client_data[26]['name']) ?><?PHP echo ($client_data[26]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[26]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[26]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[26]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[26]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[26]['count'])); ?>
							</div>
						</div>
						<h4>#28 <?PHP echo htmlspecialchars($client_data[27]['name']) ?><?PHP echo ($client_data[27]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[27]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[27]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[27]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[27]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[27]['count'])); ?>
							</div>
						</div>
						<h4>#29 <?PHP echo htmlspecialchars($client_data[28]['name']) ?><?PHP echo ($client_data[28]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[28]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[28]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[28]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[28]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[28]['count'])); ?>
							</div>
						</div>
						<h4>#30 <?PHP echo htmlspecialchars($client_data[29]['name']) ?><?PHP echo ($client_data[29]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[29]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[29]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[29]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[29]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[29]['count'])); ?>
							</div>
						</div>
						<h4>#31 <?PHP echo htmlspecialchars($client_data[]['name']) ?><?PHP echo ($client_data[30]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[30]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[30]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[30]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[30]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[30]['count'])); ?>
							</div>
						</div>
						<h4>#32 <?PHP echo htmlspecialchars($client_data[31]['name']) ?><?PHP echo ($client_data[31]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[31]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[31]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[31]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[31]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[31]['count'])); ?>
							</div>
						</div>
						<h4>#33 <?PHP echo htmlspecialchars($client_data[32]['name']) ?><?PHP echo ($client_data[32]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[32]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[32]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[32]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[32]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[32]['count'])); ?>
							</div>
						</div>
						<h4>#34 <?PHP echo htmlspecialchars($client_data[33]['name']) ?><?PHP echo ($client_data[33]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[33]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[33]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[33]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[33]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[33]['count'])); ?>
							</div>
						</div>
						<h4>#35 <?PHP echo htmlspecialchars($client_data[34]['name']) ?><?PHP echo ($client_data[34]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[34]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[34]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[34]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[34]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[34]['count'])); ?>
							</div>
						</div>
						<h4>#36 <?PHP echo htmlspecialchars($client_data[35]['name']) ?><?PHP echo ($client_data[35]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[35]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[35]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[35]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[35]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[35]['count'])); ?>
							</div>
						</div>
						<h4>#37 <?PHP echo htmlspecialchars($client_data[36]['name']) ?><?PHP echo ($client_data[36]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[36]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[36]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[36]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[36]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[36]['count'])); ?>
							</div>
						</div>
						<h4>#38 <?PHP echo htmlspecialchars($client_data[37]['name']) ?><?PHP echo ($client_data[37]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[37]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[37]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[37]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[37]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[37]['count'])); ?>
							</div>
						</div>
						<h4>#39 <?PHP echo htmlspecialchars($client_data[38]['name']) ?><?PHP echo ($client_data[38]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[38]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[38]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[38]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[38]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[38]['count'])); ?>
							</div>
						</div>
						<h4>#40 <?PHP echo htmlspecialchars($client_data[39]['name']) ?><?PHP echo ($client_data[39]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[39]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[39]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[39]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[39]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[39]['count'])); ?>
							</div>
						</div>
						<h4>#41 <?PHP echo htmlspecialchars($client_data[40]['name']) ?><?PHP echo ($client_data[40]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[40]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[40]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[40]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[40]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[40]['count'])); ?>
							</div>
						</div>
						<h4>#42 <?PHP echo htmlspecialchars($client_data[41]['name']) ?><?PHP echo ($client_data[41]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[41]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[41]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[41]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[41]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[41]['count'])); ?>
							</div>
						</div>
						<h4>#43 <?PHP echo htmlspecialchars($client_data[42]['name']) ?><?PHP echo ($client_data[42]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[42]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[42]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[42]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[42]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[42]['count'])); ?>
							</div>
						</div>
						<h4>#44 <?PHP echo htmlspecialchars($client_data[43]['name']) ?><?PHP echo ($client_data[43]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[43]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[43]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[43]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[43]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[43]['count'])); ?>
							</div>
						</div>
						<h4>#45 <?PHP echo htmlspecialchars($client_data[44]['name']) ?><?PHP echo ($client_data[44]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[44]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[44]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[44]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[44]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[44]['count'])); ?>
							</div>
						</div>
						<h4>#46 <?PHP echo htmlspecialchars($client_data[45]['name']) ?><?PHP echo ($client_data[45]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[45]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[45]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[45]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[45]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[45]['count'])); ?>
							</div>
						</div>
						<h4>#47 <?PHP echo htmlspecialchars($client_data[46]['name']) ?><?PHP echo ($client_data[46]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[46]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[46]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[46]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[46]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[46]['count'])); ?>
							</div>
						</div>
						<h4>#48 <?PHP echo htmlspecialchars($client_data[47]['name']) ?><?PHP echo ($client_data[47]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[47]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[47]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[47]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[47]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[47]['count'])); ?>
							</div>
						</div>
						<h4>#49 <?PHP echo htmlspecialchars($client_data[48]['name']) ?><?PHP echo ($client_data[48]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[48]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[48]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[48]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[48]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[48]['count'])); ?>
							</div>
						</div>
						<h4>#50 <?PHP echo htmlspecialchars($client_data[49]['name']) ?><?PHP echo ($client_data[49]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[49]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[49]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[49]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[49]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[49]['count'])); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h2><?PHP echo $lang['sttw0007']; ?></h2>
						<div class="col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading">
									<h3 class="panel-title"><i class="fas fa-chart-bar"></i>&nbsp;<?PHP echo $lang['sttw0008']; ?></h3>
								</div>
								<div class="panel-body">
									<div id="top10vs_donut1"></div>
								</div>
							</div>
						</div>
						<div class="col-lg-3">

							<div class="panel panel-green">
								<div class="panel-heading">
									<h3 class="panel-title"><i class="fas fa-chart-bar"></i>&nbsp;<?PHP echo $lang['sttw0009']; ?></h3>
								</div>
								<div class="panel-body">
									<div id="top10vs_donut2"></div>
								</div>
							</div>
						</div>
						<div class="col-lg-3">

							<div class="panel panel-yellow">
								<div class="panel-heading">
									<h3 class="panel-title"><i class="fas fa-chart-bar"></i>&nbsp;<?PHP echo $lang['sttw0010']; ?></h3>
								</div>
								<div class="panel-body">
									<div id="top10vs_donut3"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?PHP } ?>
			</div>
		</div>
	</div>
	<script>
		Morris.Donut({
		  element: 'top10vs_donut1',
		  data: [
			{label: <?PHP echo '"',$lang['sttw0011'],'"'; ?>, value: <?PHP echo $top10_sum ?>},
			{label: <?PHP echo '"'.sprintf($lang['sttw0012'].'"', $sumentries); ?>, value: <?PHP echo $others_sum ?>},
		  ]
		});
		Morris.Donut({
		  element: 'top10vs_donut2',
		  data: [
			{label: <?PHP echo '"',$lang['sttw0011'],'"'; ?>, value: <?PHP echo $top10_sum - $top10_idle_sum ?>},
			{label: <?PHP echo '"'.sprintf($lang['sttw0012'].'"', $sumentries); ?>, value: <?PHP echo $others_sum - $others_idle_sum ?>},
		  ],
			colors: [
			'#5cb85c',
			'#80ce80'
		]
		});
		Morris.Donut({
		  element: 'top10vs_donut3',
		  data: [
			{label: <?PHP echo '"',$lang['sttw0011'],'"'; ?>, value: <?PHP echo $top10_idle_sum ?>},
			{label: <?PHP echo '"'.sprintf($lang['sttw0012'].'"', $sumentries); ?>, value: <?PHP echo $others_idle_sum ?>},
		  ],
		  colors: [
			'#f0ad4e',
			'#ffc675'
		]
		});
	</script>
</body>
</html>
