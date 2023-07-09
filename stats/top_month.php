<?php
require_once '_preload.php';

try {
    $notinuuid = '';
    if ($cfg['rankup_excepted_unique_client_id_list'] != null) {
        foreach ($cfg['rankup_excepted_unique_client_id_list'] as $uuid => $value) {
            $notinuuid .= "'".$uuid."',";
        }
        $notinuuid = substr($notinuuid, 0, -1);
    } else {
        $notinuuid = "'0'";
    }

    $notingroup = '';
    $andnotgroup = '';
    if ($cfg['rankup_excepted_group_id_list'] != null) {
        foreach ($cfg['rankup_excepted_group_id_list'] as $group => $value) {
            $notingroup .= "'".$group."',";
            $andnotgroup .= " AND `u`.`cldgroup` NOT LIKE ('".$group.",%') AND `u`.`cldgroup` NOT LIKE ('%,".$group.",%') AND `u`.`cldgroup` NOT LIKE ('%,".$group."')";
        }
        $notingroup = substr($notingroup, 0, -1);
    } else {
        $notingroup = '0';
    }

    if ($cfg['rankup_time_assess_mode'] == 1) {
        $order = '(`s`.`count_month` - `s`.`idle_month`)';
        $texttime = $lang['sttw0013'];
    } else {
        $order = '`s`.`count_month`';
        $texttime = $lang['sttw0003'];
    }

    $timeago = time() - 2592000;
    $db_arr = $mysqlcon->query("SELECT `s`.`uuid`,`s`.`count_month`,`s`.`idle_month`,`u`.`name`,`u`.`online`,`u`.`cldgroup` FROM `$dbname`.`stats_user` `s`, `$dbname`.`user` `u` WHERE `u`.`uuid` = `s`.`uuid` AND `s`.`removed`!=1 AND `u`.`lastseen`>{$timeago} AND `u`.`uuid` NOT IN ({$notinuuid}) AND `u`.`cldgroup` NOT IN ({$notingroup}) {$andnotgroup} AND `s`.`idle_month`<`s`.`count_month` AND `s`.`count_month`>=0 AND `s`.`idle_month`>=0 ORDER BY $order DESC LIMIT 10")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $count_ids = $mysqlcon->query("SELECT COUNT(DISTINCT(`id`)) AS `count` from `$dbname`.`user_snapshot`")->fetch();

    $count10 = 0;
    $top10_sum = 0;
    $top10_idle_sum = 0;

    foreach ($db_arr as $uuid => $client) {
        if ($cfg['rankup_time_assess_mode'] == 1) {
            $hours = $client['count_month'] - $client['idle_month'];
        } else {
            $hours = $client['count_month'];
        }
        $top10_sum += $client['count_month'];
        $top10_idle_sum += $client['idle_month'];
        $client_data[$count10] = [
            'name'		=>	htmlspecialchars($client['name']),
            'title'		=>	htmlspecialchars($client['name']),
            'count'		=>	$hours,
            'online'	=>	$client['online'],
        ];
        $count10++;
    }

    for ($count10 = $count10; $count10 <= 10; $count10++) {
        $client_data[$count10] = [
            'name'		=>	'<i>'.$lang['unknown'].'</i>',
            'title'		=>	$lang['unknown'],
            'count'		=>	0,
            'online'	=>	0,
        ];
    }

    $sum = $mysqlcon->query("SELECT SUM(`s`.`count_month`) AS `count`, SUM(`s`.`idle_month`) AS `idle`, COUNT(*) AS `user` FROM `$dbname`.`stats_user` `s`, `$dbname`.`user` `u` WHERE `u`.`uuid` = `s`.`uuid` AND `s`.`removed`!=1 AND `u`.`lastseen`>{$timeago} AND `u`.`uuid` NOT IN ({$notinuuid}) AND `u`.`cldgroup` NOT IN ({$notingroup}) {$andnotgroup} AND `s`.`idle_month`<`s`.`count_month` AND `s`.`count_month`>=0 AND `s`.`idle_month`>=0;")->fetch();
    $top10_sum = round(($top10_sum / 3600));
    $top10_idle_sum = round(($top10_idle_sum / 3600));
    $others_sum = round(($sum['count'] / 3600)) - $top10_sum;
    $others_idle_sum = round(($sum['idle'] / 3600)) - $top10_idle_sum;
    $sumentries = $sum['user'] - 10;
    ?>
			<div id="page-wrapper" class="stats_top_month">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['sttw0001']; ?>
								<small><?php echo $lang['sttm0001']; ?></small>
							</h1>
						</div>
					</div>
					<?php if ($count_ids['count'] < 121) {
					    echo $lang['stix0048'],' (',$count_ids['count'],'/121)';
					} else { ?>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[0]['title'],'">',$client_data[0]['name']; ?></span></div>
											<div><?php if ($client_data[0]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[0]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[0]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[1]['title'],'">',$client_data[1]['name']; ?></span></div>
											<div><?php if ($client_data[1]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[1]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[1]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[2]['title'],'">',$client_data[2]['name']; ?></span></div>
											<div><?php if ($client_data[2]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[2]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[2]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[3]['title'],'">',$client_data[3]['name']; ?></span></div>
											<div><?php if ($client_data[3]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[3]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[3]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[4]['title'],'">',$client_data[4]['name']; ?></span></div>
											<div><?php if ($client_data[4]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[4]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[4]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[5]['title'],'">',$client_data[5]['name']; ?></span></div>
											<div><?php if ($client_data[5]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[5]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[5]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[6]['title'],'">',$client_data[6]['name']; ?></span></div>
											<div><?php if ($client_data[6]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[6]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[6]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[7]['title'],'">',$client_data[7]['name']; ?></span></div>
											<div><?php if ($client_data[7]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[7]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[7]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[8]['title'],'">',$client_data[8]['name']; ?></span></div>
											<div><?php if ($client_data[8]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[8]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[8]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
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
											<div class="tophuge"><span title=<?php echo '"',$client_data[9]['title'],'">',$client_data[9]['name']; ?></span></div>
											<div><?php if ($client_data[9]['count'] < 3600) {
											    echo sprintf($texttime, round(($client_data[9]['count'] / 60)), $lang['sttw0015']);
											} else {
											    echo sprintf($texttime, round(($client_data[9]['count'] / 3600)), $lang['sttw0014']);
											} ?></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2><?php echo $lang['sttw0004']; ?></h2>
							<h4>#1 <?php echo $client_data[0]['name'] ?><?php echo ($client_data[0]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-striped <?php echo ($client_data[0]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: 100%;"><?php echo round(($client_data[0]['count'] / 3600)).'&nbsp;'.$lang['sttw0005']?>
								</div>
							</div>
							<h4>#2 <?php echo $client_data[1]['name'] ?><?php echo ($client_data[1]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped <?php echo ($client_data[1]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[1]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[1]['count'])); ?>
								</div>
							</div>
							<h4>#3 <?php echo $client_data[2]['name'] ?><?php echo ($client_data[2]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-warning progress-bar-striped <?php echo ($client_data[2]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[2]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[2]['count'])); ?>
								</div>
							</div>
							<h4>#4 <?php echo $client_data[3]['name'] ?><?php echo ($client_data[3]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-danger progress-bar-striped <?php echo ($client_data[3]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[3]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[3]['count'])); ?>
								</div>
							</div>
							<h4>#5 <?php echo $client_data[4]['name'] ?><?php echo ($client_data[4]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-striped <?php echo ($client_data[4]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[4]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[4]['count'])); ?>
								</div>
							</div>
							<h4>#6 <?php echo $client_data[5]['name'] ?><?php echo ($client_data[5]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped <?php echo ($client_data[5]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[5]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[5]['count'])); ?>
								</div>
							</div>
							<h4>#7 <?php echo $client_data[6]['name'] ?><?php echo ($client_data[6]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-warning progress-bar-striped <?php echo ($client_data[6]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[6]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[6]['count'])); ?>
								</div>
							</div>
							<h4>#8 <?php echo $client_data[7]['name'] ?><?php echo ($client_data[7]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-danger progress-bar-striped <?php echo ($client_data[7]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[7]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[7]['count'])); ?>
								</div>
							</div>
							<h4>#9 <?php echo $client_data[8]['name'] ?><?php echo ($client_data[8]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-striped <?php echo ($client_data[8]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[8]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[8]['count'])); ?>
								</div>
							</div>
							<h4>#10 <?php echo $client_data[9]['name'] ?><?php echo ($client_data[9]['online'] == '1') ? ' (Status: <span class="text-success">'.$lang['stix0024'].'</span>)' : ' (Status: <span class="text-danger">'.$lang['stix0025'].'</span>)' ?></h4>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped <?php echo ($client_data[9]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?php echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?php echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>%"><?php echo sprintf($lang['sttw0006'], round(($client_data[9]['count'] / 3600)), get_percentage($client_data[0]['count'], $client_data[9]['count'])); ?>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2><?php echo $lang['sttw0007']; ?></h2>
							<div class="col-lg-3">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<h3 class="panel-title"><i class="fas fa-chart-bar"></i><span class="item-margin"><?php echo $lang['sttw0008']; ?></span></h3>
									</div>
									<div class="panel-body">
										<div id="top10vs_donut1"></div>
									</div>
								</div>
							</div>
							<div class="col-lg-3">

								<div class="panel panel-green">
									<div class="panel-heading">
										<h3 class="panel-title"><i class="fas fa-chart-bar"></i><span class="item-margin"><?php echo $lang['sttw0009']; ?></span></h3>
									</div>
									<div class="panel-body">
										<div id="top10vs_donut2"></div>
									</div>
								</div>
							</div>
							<div class="col-lg-3">

								<div class="panel panel-yellow">
									<div class="panel-heading">
										<h3 class="panel-title"><i class="fas fa-chart-bar"></i><span class="item-margin"><?php echo $lang['sttw0010']; ?></span></h3>
									</div>
									<div class="panel-body">
										<div id="top10vs_donut3"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php require_once '_footer.php'; ?>
<input type="hidden" id="donut_time_color_1" value="">
<input type="hidden" id="donut_time_color_2" value="">
<input type="hidden" id="donut_version_color_1" value="">
<input type="hidden" id="donut_version_color_2" value="">
<input type="hidden" id="donut_nation_color_1" value="">
<input type="hidden" id="donut_nation_color_2" value="">
		<script>
const donut_time_color_1 = window.getComputedStyle(document.getElementById('donut_time_color_1')).getPropertyValue('color');
const donut_time_color_2 = window.getComputedStyle(document.getElementById('donut_time_color_2')).getPropertyValue('color');
const donut_version_color_1 = window.getComputedStyle(document.getElementById('donut_version_color_1')).getPropertyValue('color');
const donut_version_color_2 = window.getComputedStyle(document.getElementById('donut_version_color_2')).getPropertyValue('color');
const donut_nation_color_1 = window.getComputedStyle(document.getElementById('donut_nation_color_1')).getPropertyValue('color');
const donut_nation_color_2 = window.getComputedStyle(document.getElementById('donut_nation_color_2')).getPropertyValue('color');

Morris.Donut({
  element: 'top10vs_donut1',
  data: [
	{label: <?php echo '"',$lang['sttw0011'],'"'; ?>, value: <?php echo $top10_sum ?>},
	{label: <?php echo '"'.sprintf($lang['sttw0012'].'"', $sumentries); ?>, value: <?php echo $others_sum ?>},
  ],
  colors: [donut_time_color_1, donut_time_color_2]
});
Morris.Donut({
  element: 'top10vs_donut2',
  data: [
	{label: <?php echo '"',$lang['sttw0011'],'"'; ?>, value: <?php echo $top10_sum - $top10_idle_sum ?>},
	{label: <?php echo '"'.sprintf($lang['sttw0012'].'"', $sumentries); ?>, value: <?php echo $others_sum - $others_idle_sum ?>},
  ],
  colors: [donut_version_color_1, donut_version_color_2]
});
Morris.Donut({
  element: 'top10vs_donut3',
  data: [
	{label: <?php echo '"',$lang['sttw0011'],'"'; ?>, value: <?php echo $top10_idle_sum ?>},
	{label: <?php echo '"'.sprintf($lang['sttw0012'].'"', $sumentries); ?>, value: <?php echo $others_idle_sum ?>},
  ],
  colors: [donut_nation_color_1, donut_nation_color_2]
});
		</script>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>