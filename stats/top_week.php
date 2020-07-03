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
	$db_arr = $mysqlcon->query("SELECT `s`.`uuid`,`s`.`count_week`,`s`.`idle_week`,`u`.`name`,`u`.`online`,`u`.`cldgroup` FROM `$dbname`.`stats_user` AS `s` INNER JOIN `$dbname`.`user` AS `u` ON `s`.`uuid`=`u`.`uuid` WHERE `s`.`removed`='0' ORDER BY (`s`.`count_week` - `s`.`idle_week`) DESC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	$texttime = $lang['sttw0013'];
} else {
	$db_arr = $mysqlcon->query("SELECT `s`.`uuid`,`s`.`count_week`,`s`.`idle_week`,`u`.`name`,`u`.`online`,`u`.`cldgroup` FROM `$dbname`.`stats_user` AS `s` INNER JOIN `$dbname`.`user` AS `u` ON `s`.`uuid`=`u`.`uuid` WHERE `s`.`removed`='0' ORDER BY `s`.`count_week` DESC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	$texttime = $lang['sttw0003'];
}

$count_ids = $mysqlcon->query("SELECT COUNT(DISTINCT(`id`)) AS `count` from `$dbname`.`user_snapshot`")->fetch();

$sumentries = count($db_arr) - 10;
$count10 = 0;
$top10_sum = 0;
$top10_idle_sum = 0;

foreach ($db_arr as $uuid => $client) {
	$sgroups = array_flip(explode(",", $client['cldgroup']));
	if (!isset($cfg['rankup_excepted_unique_client_id_list'][$uuid]) && (!isset($cfg['rankup_excepted_group_id_list']) || !array_intersect_key($sgroups, $cfg['rankup_excepted_group_id_list']))) {
		if ($count10 == 10) break;
		if ($cfg['rankup_time_assess_mode'] == 1) {
			$hours = $client['count_week'] - $client['idle_week'];
		} else {
			$hours = $client['count_week'];
		}
		$top10_sum = round(($client['count_week']/3600)) + $top10_sum;
		$top10_idle_sum = round(($client['idle_week']/3600)) + $top10_idle_sum;
		$client_data[$count10] = array(
			'name'		=>	$client['name'],
			'count'		=>	$hours,
			'online'	=>	$client['online']
		);
		$count10++;
	}
}

for($count = $count10; $count10 < 10; $count10++) {
	$client_data[$count] = array(
		'name'		=>	"<i>unkown</i>",
		'count'		=>	"0",
		'online'	=>	"0"
	);
}

$sum = $mysqlcon->query("SELECT SUM(`count_week`) AS `count`, SUM(`idle_week`) AS `idle` FROM `$dbname`.`stats_user`")->fetch();
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
							<small><?PHP echo $lang['sttw0002']; ?></small>
						</h1>
					</div>
				</div>
				<?PHP if($count_ids['count'] < 29) {  echo $lang['stix0048'],' (',$count_ids['count'],'/29)'; } else { ?>
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
						<h4>#1 <?PHP echo htmlspecialchars($client_data[0]['name']) ?><?PHP echo ($client_data[0]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[0]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: 100%;"><?PHP echo round(($client_data[0]['count']/3600)) .'&nbsp;'.$lang['sttw0005']?>
							</div>
						</div>
						<h4>#2 <?PHP echo htmlspecialchars($client_data[1]['name']) ?><?PHP echo ($client_data[1]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[1]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[1]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[1]['count'])); ?>
							</div>
						</div>
						<h4>#3 <?PHP echo htmlspecialchars($client_data[2]['name']) ?><?PHP echo ($client_data[2]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[2]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[2]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[2]['count'])); ?>
							</div>
						</div>
						<h4>#4 <?PHP echo htmlspecialchars($client_data[3]['name']) ?><?PHP echo ($client_data[3]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[3]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[3]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[3]['count'])); ?>
							</div>
						</div>
						<h4>#5 <?PHP echo htmlspecialchars($client_data[4]['name']) ?><?PHP echo ($client_data[4]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[4]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[4]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[4]['count'])); ?>
							</div>
						</div>
						<h4>#6 <?PHP echo htmlspecialchars($client_data[5]['name']) ?><?PHP echo ($client_data[5]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[5]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[5]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[5]['count'])); ?>
							</div>
						</div>
						<h4>#7 <?PHP echo htmlspecialchars($client_data[6]['name']) ?><?PHP echo ($client_data[6]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[6]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[6]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[6]['count'])); ?>
							</div>
						</div>
						<h4>#8 <?PHP echo htmlspecialchars($client_data[7]['name']) ?><?PHP echo ($client_data[7]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[7]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[7]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[7]['count'])); ?>
							</div>
						</div>
						<h4>#9 <?PHP echo htmlspecialchars($client_data[8]['name']) ?><?PHP echo ($client_data[8]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[8]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[8]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[8]['count'])); ?>
							</div>
						</div>
						<h4>#10 <?PHP echo htmlspecialchars($client_data[9]['name']) ?><?PHP echo ($client_data[9]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[9]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>%"><?PHP echo sprintf($lang['sttw0006'], round(($client_data[9]['count']/3600)), get_percentage($client_data[0]['count'], $client_data[9]['count'])); ?>
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