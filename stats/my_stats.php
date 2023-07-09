<?php
require_once '_preload.php';

try {
    if (isset($_SESSION[$rspathhex.'multiple']) && count($_SESSION[$rspathhex.'multiple']) > 1 && ! isset($_SESSION[$rspathhex.'uuid_verified'])) {
        $err_msg = sprintf($lang['stag0006'], '<a href="verify.php">', '</a>');
        $err_lvl = 3;
    } elseif (isset($_SESSION[$rspathhex.'connected']) && $_SESSION[$rspathhex.'connected'] == 0 || ! isset($_SESSION[$rspathhex.'connected'])) {
        $err_msg = sprintf($lang['stag0015'], '<a href="verify.php">', '</a>');
        $err_lvl = 3;
    } else {
        $dbdata_fetched = $mysqlcon->query("SELECT * FROM `$dbname`.`user` WHERE `uuid` LIKE '%".$_SESSION[$rspathhex.'tsuid']."%'")->fetch();
        $count_hours = round($dbdata_fetched['count'] / 3600);
        $idle_hours = round($dbdata_fetched['idle'] / 3600);
        $dbdata_fetched['count'] = round($dbdata_fetched['count']);
        $dbdata_fetched['idle'] = round($dbdata_fetched['idle']);

        if ($cfg['rankup_time_assess_mode'] == 1) {
            $activetime = $dbdata_fetched['count'] - $dbdata_fetched['idle'];
        } else {
            $activetime = $dbdata_fetched['count'];
        }
        $active_count = $dbdata_fetched['count'] - $dbdata_fetched['idle'];

        krsort($cfg['rankup_definition']);
        $nextgrp = '';

        foreach ($cfg['rankup_definition'] as $rank) {
            $actualgrp = $rank['time'];
            if ($activetime > $rank['time']) {
                break;
            } else {
                $nextgrp = $rank['time'];
            }
        }
        if ($actualgrp == $nextgrp) {
            $actualgrp = 0;
        }
        if ($activetime > $nextgrp) {
            $percentage_rankup = 100;
        } else {
            $takedtime = $activetime - $actualgrp;
            $neededtime = $nextgrp - $actualgrp;
            $percentage_rankup = round($takedtime / $neededtime * 100, 2);
        }

        $stats_user = $mysqlcon->query("SELECT `count_week`,`active_week`,`count_month`,`active_month`,`last_calculated` FROM `$dbname`.`stats_user` WHERE `uuid`='".$_SESSION[$rspathhex.'tsuid']."'")->fetch();

        if (isset($stats_user['count_week'])) {
            $count_week = $stats_user['count_week'];
        } else {
            $count_week = 0;
        }
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$count_week");
        $count_week = $dtF->diff($dtT)->format($cfg['default_date_format']);
        if (isset($stats_user['active_week'])) {
            $active_week = $stats_user['active_week'];
        } else {
            $active_week = 0;
        }
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$active_week");
        $active_week = $dtF->diff($dtT)->format($cfg['default_date_format']);
        if (isset($stats_user['count_month'])) {
            $count_month = $stats_user['count_month'];
        } else {
            $count_month = 0;
        }
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$count_month");
        $count_month = $dtF->diff($dtT)->format($cfg['default_date_format']);
        if (isset($stats_user['active_month'])) {
            $active_month = $stats_user['active_month'];
        } else {
            $active_month = 0;
        }
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$active_month");
        $active_month = $dtF->diff($dtT)->format($cfg['default_date_format']);
        if (isset($dbdata_fetched['count'])) {
            $count_total = $dbdata_fetched['count'];
        } else {
            $count_total = 0;
        }
        $dtF = new DateTime('@0');
        $dtT = new DateTime('@'.round($count_total));
        $count_total = $dtF->diff($dtT)->format($cfg['default_date_format']);
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$active_count");
        $active_count = $dtF->diff($dtT)->format($cfg['default_date_format']);

        $achievements_done = 0;

        if ($count_hours >= $cfg['stats_time_legend']) {
            $achievements_done = $achievements_done + 4;
        } elseif ($count_hours >= $cfg['stats_time_gold']) {
            $achievements_done = $achievements_done + 3;
        } elseif ($count_hours >= $cfg['stats_time_silver']) {
            $achievements_done = $achievements_done + 2;
        } else {
            $achievements_done = $achievements_done + 1;
        }
        if ($_SESSION[$rspathhex.'tsconnections'] >= $cfg['stats_connects_legend']) {
            $achievements_done = $achievements_done + 4;
        } elseif ($_SESSION[$rspathhex.'tsconnections'] >= $cfg['stats_connects_gold']) {
            $achievements_done = $achievements_done + 3;
        } elseif ($_SESSION[$rspathhex.'tsconnections'] >= $cfg['stats_connects_silver']) {
            $achievements_done = $achievements_done + 2;
        } else {
            $achievements_done = $achievements_done + 1;
        }
    }
    ?>
			<div id="page-wrapper" class="stats_my_stats">
			<?php if (isset($err_msg)) {
			    error_handling($err_msg, $err_lvl);
			}
            if (isset($_SESSION[$rspathhex.'multiple']) && count($_SESSION[$rspathhex.'multiple']) > 1 || isset($_SESSION[$rspathhex.'connected']) && $_SESSION[$rspathhex.'connected'] == 0 || ! isset($_SESSION[$rspathhex.'connected'])) {
                echo '</div></div></body></html>';
                exit;
            } ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['stmy0001']; ?>
								<a href="#infoModal" data-toggle="modal" class="btn btn-primary">
									<span class="fas fa-info-circle" aria-hidden="true"></span>
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
											<div class="huge"><?php echo $_SESSION[$rspathhex.'tsname'] ?></div>
											<div><?php if ($dbdata_fetched['except'] == 0 || $dbdata_fetched['except'] == 1) {
											    echo $lang['stmy0002'],' #',$dbdata_fetched['rank'];
											} ?></div>
										</div>
										<div class="col-xs-3">
											<?php
											    if (isset($_SESSION[$rspathhex.'tsavatar']) && $_SESSION[$rspathhex.'tsavatar'] != 'none') {
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
										<p><strong><?php echo $lang['stmy0003']; ?></strong></p>
										<p><strong><?php echo $lang['stmy0004']; ?></strong></p>
										<p><strong><?php echo $lang['stmy0005']; ?></strong></p>
										<p><strong><?php echo $lang['stmy0006']; ?></strong></p>
										<p><strong><?php echo $lang['stmy0007']; ?></strong></p>
										<p><strong><?php echo $lang['stmy0031']; ?></strong></p>
										<p><strong><?php echo $lang['stmy0010']; ?></strong></p>
									</div>
									<div class="pull-right">
										<p class="text-right"><?php echo $dbdata_fetched['cldbid']; ?></p>
										<p class="text-right"><?php echo $dbdata_fetched['uuid']; ?></p>
										<p class="text-right"><?php echo $_SESSION[$rspathhex.'tsconnections']; ?></p>
										<p class="text-right"><?php echo $_SESSION[$rspathhex.'tscreated']; ?></p>
										<p class="text-right" title=<?php echo '"',$dbdata_fetched['count'],' sec.">',$count_total; ?></p>
										<p class="text-right" title=<?php echo '"',($dbdata_fetched['count'] - $dbdata_fetched['idle']),' sec.">',$active_count; ?></p>
										<p class="text-right"><?php echo $achievements_done.' / 8'; ?></p>
									</div>
									<div class="clearfix"></div>
								</div>
								<div class="panel-footer">
									<div class="pull-left">
										<p><strong><?php echo $lang['stmy0032']; ?></strong></p>
										<p><strong><?php echo sprintf($lang['stmy0008'], '7'); ?></strong></p>
										<p><strong><?php echo sprintf($lang['stmy0009'], '7'); ?></strong></p>
										<p><strong><?php echo sprintf($lang['stmy0008'], '30'); ?></strong></p>
										<p><strong><?php echo sprintf($lang['stmy0009'], '30'); ?></strong></p>
									</div>
									<div class="pull-right">
										<p class="text-right"><?php echo date('Y-m-d H:i:s', $stats_user['last_calculated']); ?></p>
										<p class="text-right"><?php echo $count_week; ?></p>
										<p class="text-right"><?php echo $active_week; ?></p>
										<p class="text-right"><?php echo $count_month; ?></p>
										<p class="text-right"><?php echo $active_month; ?></p>
									</div>
									<div class="clearfix"></div>
								</div>
							</div>
						</div>
						<?php if ($dbdata_fetched['except'] == 0 || $dbdata_fetched['except'] == 1) { ?>
						<div class="col-lg-6">
							<h3><?php echo $lang['stmy0030']; ?></h3>
							<div class="progress">
								<div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo $percentage_rankup; ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: <?php echo $percentage_rankup; ?>%;">
									<?php echo $percentage_rankup,' %'; ?>
								</div>
							</div>
						</div>
						<?php } ?>
						<div class="col-lg-6">
							<h3><?php echo $lang['stmy0011']; ?></h3>
							<?php if ($count_hours >= $cfg['stats_time_legend']) { ?>
							<div class="panel panel-green">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge">
												<small><?php echo $lang['stmy0012']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0013'], $count_hours); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
									<?php echo $lang['stmy0014']; ?>
								</div>
							</div>
							<?php } elseif ($count_hours >= $cfg['stats_time_gold']) { ?>
							<div class="panel panel-green">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge">
												<small><?php echo $lang['stmy0015']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0013'], $count_hours); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo  get_percentage($cfg['stats_time_legend'], $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?php echo get_percentage($cfg['stats_time_legend'], $count_hours); ?>%;">
									<?php echo get_percentage($cfg['stats_time_legend'], $count_hours), $lang['stmy0016']; ?>
								</div>
							</div>
							<?php } elseif ($count_hours >= $cfg['stats_time_silver']) { ?>
							<div class="panel panel-green">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge">
												<small><?php echo $lang['stmy0017']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0013'], $count_hours); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo get_percentage($cfg['stats_time_gold'], $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?php echo get_percentage($cfg['stats_time_gold'], $count_hours); ?>%;">
									<?php echo get_percentage($cfg['stats_time_gold'], $count_hours), $lang['stmy0018']; ?>
								</div>
							</div>
							<?php } elseif ($count_hours >= $cfg['stats_time_bronze']) { ?>
							<div class="panel panel-green">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge">
												<small><?php echo $lang['stmy0019']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0013'], $count_hours); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo get_percentage($cfg['stats_time_silver'], $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?php echo get_percentage($cfg['stats_time_silver'], $count_hours); ?>%;">
									<?php echo get_percentage($cfg['stats_time_silver'], $count_hours), $lang['stmy0020']; ?>
								</div>
							</div>
							<?php } else { ?>
							<div class="panel panel-green">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge">
												<small><?php echo $lang['stmy0021']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0013'], $count_hours); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo get_percentage($cfg['stats_time_bronze'], $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: <?php echo get_percentage($cfg['stats_time_bronze'], $count_hours); ?>%;">
									<?php echo get_percentage($cfg['stats_time_bronze'], $count_hours), $lang['stmy0022']; ?>
								</div>
							</div>
							<?php } ?>
						</div>
						<div class="col-lg-6">
							<h3><?php echo $lang['stmy0023']; ?></h3>
							<?php if ($_SESSION[$rspathhex.'tsconnections'] >= $cfg['stats_connects_legend']) { ?>
							<div class="panel panel-yellow">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge"><small><?php echo $lang['stmy0024']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
									<?php echo $lang['stmy0014']; ?>
								</div>
							</div>
							<?php } elseif ($_SESSION[$rspathhex.'tsconnections'] >= $cfg['stats_connects_gold']) { ?>
							<div class="panel panel-yellow">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge"><small><?php echo $lang['stmy0026']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo get_percentage($cfg['stats_connects_legend'], $_SESSION[$rspathhex.'tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?php echo get_percentage($cfg['stats_connects_legend'], $_SESSION[$rspathhex.'tsconnections']); ?>%;">
									<?php echo get_percentage($cfg['stats_connects_legend'], $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0016']; ?>
								</div>
							</div>
							<?php } elseif ($_SESSION[$rspathhex.'tsconnections'] >= $cfg['stats_connects_silver']) { ?>
							<div class="panel panel-yellow">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge"><small><?php echo $lang['stmy0027']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo get_percentage($cfg['stats_connects_gold'], $_SESSION[$rspathhex.'tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?php echo get_percentage($cfg['stats_connects_gold'], $_SESSION[$rspathhex.'tsconnections']); ?>%;">
									<?php echo get_percentage($cfg['stats_connects_gold'], $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0018']; ?>
								</div>
							</div>
							<?php } elseif ($_SESSION[$rspathhex.'tsconnections'] >= $cfg['stats_connects_bronze']) { ?>				
							<div class="panel panel-yellow">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge"><small><?php echo $lang['stmy0028']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo get_percentage($cfg['stats_connects_silver'], $_SESSION[$rspathhex.'tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?php echo get_percentage($cfg['stats_connects_silver'], $_SESSION[$rspathhex.'tsconnections']); ?>%;">
									<?php echo get_percentage($cfg['stats_connects_silver'], $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0020']; ?>
								</div>
							</div>
							<?php } else { ?>
							<div class="panel panel-yellow">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-12 text-right">
											<div class="huge"><small><?php echo $lang['stmy0029']; ?></small>
											</div>
											<div><?php echo sprintf($lang['stmy0025'], $_SESSION[$rspathhex.'tsconnections']); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="progress">
								<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width:<?php echo get_percentage($cfg['stats_connects_bronze'], $_SESSION[$rspathhex.'tsconnections']); ?>%;">
									<?php echo get_percentage($cfg['stats_connects_bronze'], $_SESSION[$rspathhex.'tsconnections']),$lang['stmy0022']; ?>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php require_once '_footer.php'; ?>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>