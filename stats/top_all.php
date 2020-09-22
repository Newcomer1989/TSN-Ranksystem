<?PHP
require_once('_preload.php');

$notinuuid = '';
if($cfg['rankup_excepted_unique_client_id_list'] != NULL) {
	foreach($cfg['rankup_excepted_unique_client_id_list'] as $uuid => $value) {
		$notinuuid .= "'".$uuid."',";
	}
	$notinuuid = substr($notinuuid, 0, -1);
} else {
	$notinuuid = "'0'";
}

$notingroup = '';
$andnotgroup = '';
if($cfg['rankup_excepted_group_id_list'] != NULL) {
	foreach($cfg['rankup_excepted_group_id_list'] as $group => $value) {
		$notingroup .= "'".$group."',";
		$andnotgroup .= " AND `u`.`cldgroup` NOT LIKE ('".$group.",%') AND `u`.`cldgroup` NOT LIKE ('%,".$group.",%')";
	}
	$notingroup = substr($notingroup, 0, -1);
} else {
	$notingroup = '0';
}

if ($cfg['rankup_time_assess_mode'] == 1) {
	$order = "(`count` - `idle`)";
	$texttime = $lang['sttw0013'];
} else {
	$order = "`count`";
	$texttime = $lang['sttw0003'];
}

$db_arr = $mysqlcon->query("SELECT `u`.`uuid`,`u`.`name`,`u`.`count`,`u`.`idle`,`u`.`cldgroup`,`u`.`online` FROM (SELECT `uuid`,`removed` FROM `$dbname`.`stats_user` WHERE `removed`!=1) `s` INNER JOIN `$dbname`.`user` `u` ON `u`.`uuid`=`s`.`uuid` WHERE `u`.`uuid` NOT IN ($notinuuid) AND `u`.`cldgroup` NOT IN ($notingroup) $andnotgroup ORDER BY $order DESC LIMIT 10")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

$count10 = 0;
$top10_sum = 0;
$top10_idle_sum = 0;

foreach ($db_arr as $uuid => $client) {
	if ($cfg['rankup_time_assess_mode'] == 1) {
		$hours = $client['count'] - $client['idle'];
	} else {
		$hours = $client['count'];
	}
	$top10_sum = round(($client['count']/3600)) + $top10_sum;
	$top10_idle_sum = round(($client['idle']/3600)) + $top10_idle_sum;
	$client_data[$count10] = array(
	'name'		=>	$client['name'],
	'count'		=>	$hours,
	'online'	=>	$client['online']
	);
	$count10++;
}

for($count10 = $count10; $count10 <= 10; $count10++) {
	$client_data[$count10] = array(
		'name'		=>	"<i>unkown</i>",
		'count'		=>	"0",
		'online'	=>	"0"
	);
}

$sum = $mysqlcon->query("SELECT SUM(`count`) AS `count`, SUM(`idle`) AS `idle`, COUNT(*) AS `user` FROM `$dbname`.`user`")->fetch();
$others_sum = round(($sum['count']/3600)) - $top10_sum;
$others_idle_sum = round(($sum['idle']/3600)) - $top10_idle_sum;
$sumentries = $sum['user'] - 10;

function get_percentage($max_value, $value) {
	return (round(($value/$max_value)*100));
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['sttw0001']; ?>
							<small><?PHP echo $lang['stta0001']; ?></small>
						</h1>
					</div>
				</div>
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
			</div>
		</div>
	</div>
	<?PHP require_once('_footer.php'); ?>
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