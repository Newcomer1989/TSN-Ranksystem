<?PHP
require_once('_preload.php');

try {
	require_once('_nav.php');

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
		$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if(($job_check = $mysqlcon->query("SELECT * FROM `$dbname`.`job_check`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
	}

	function reset_status($lang, $job_check, $check = NULL) {
		$err_msg = "<b>".$lang['wihladm31']."</b>: ";
		
		switch([$job_check['reset_user_time']['timestamp'],$job_check['reset_user_delete']['timestamp']]) {
			case [0,1]:
				if($check == 1) {
					$err_msg .= $lang['wihladmrs16']." (".$lang['wisupidle'].": ".$lang['wihladm312'].")<br>"; break;
				} else {
					$err_msg .= $lang['wihladmrs1']." (".$lang['wisupidle'].": ".$lang['wihladm312'].")<br>"; break;
				}
			case [0,2]:
				$err_msg .= "<span class=\"alert-info\">".$lang['wihladmrs2']."</span> (".$lang['wisupidle'].": ".$lang['wihladm312'].")<br>"; break;
			case [0,3]:
				$err_msg .= "<span class=\"alert-danger\">".$lang['wihladmrs3']."</span> (".$lang['wisupidle'].": ".$lang['wihladm312'].")<br>"; break;
			case [0,4]:
				$err_msg .= "<span class=\"alert-success\">".$lang['wihladmrs4']."</span> (".$lang['wisupidle'].": ".$lang['wihladm312'].")<br>"; break;
			case [1,0]:
				if($check == 1) {
					$err_msg .= $lang['wihladmrs16']." (".$lang['wisupidle'].": ".$lang['wihladm311'].")<br>"; break;
				} else {
					$err_msg .= $lang['wihladmrs1']." (".$lang['wisupidle'].": ".$lang['wihladm311'].")<br>"; break;
				}
			case [2,0]:
				$err_msg .= "<span class=\"alert-info\">".$lang['wihladmrs2']."</span> (".$lang['wisupidle'].": ".$lang['wihladm311'].")<br>"; break;
			case [3,0]:
				$err_msg .= "<span class=\"alert-danger\">".$lang['wihladmrs3']."</span> (".$lang['wisupidle'].": ".$lang['wihladm311'].")<br>"; break;
			case [4,0]:
				$err_msg .= "<span class=\"alert-success\">".$lang['wihladmrs4']."</span> (".$lang['wisupidle'].": ".$lang['wihladm311'].")<br>"; break;
			default:
				$err_msg .= "<span class=\"alert-secondary\"><i>".$lang['wihladmrs0']."</i></span><br>";
		}

		$err_msg .= "<b>".$lang['wihladm32']."</b>: ";
		switch($job_check['reset_group_withdraw']['timestamp']) {
			case 1:
				if($check == 1) {
					$err_msg .= $lang['wihladmrs16']."<br>"; break;
				} else {
					$err_msg .= $lang['wihladmrs1']."<br>"; break;
				}
			case 2:
				$err_msg .= "<span class=\"alert-info\">".$lang['wihladmrs2']."</span><br>"; break;
			case 3:
				$err_msg .= "<span class=\"alert-danger\">".$lang['wihladmrs3']."</span><br>"; break;
			case 4:
				$err_msg .= "<span class=\"alert-success\">".$lang['wihladmrs4']."</span><br>"; break;
			default:
				$err_msg .= "<span class=\"alert-secondary\"><i>".$lang['wihladmrs0']."</i></span><br>";
		}

		$err_msg .= "<b>".$lang['wihladm33']."</b>: ";
		switch($job_check['reset_webspace_cache']['timestamp']) {
			case 1:
				if($check == 1) {
					$err_msg .= $lang['wihladmrs16']."<br>"; break;
				} else {
					$err_msg .= $lang['wihladmrs1']."<br>"; break;
				}
			case 2:
				$err_msg .= "<span class=\"alert-info\">".$lang['wihladmrs2']."</span><br>"; break;
			case 3:
				$err_msg .= "<span class=\"alert-danger\">".$lang['wihladmrs3']."</span><br>"; break;
			case 4:
				$err_msg .= "<span class=\"alert-success\">".$lang['wihladmrs4']."</span><br>"; break;
			default:
				$err_msg .= "<span class=\"alert-secondary\"><i>".$lang['wihladmrs0']."</i></span><br>";
		}

		$err_msg .= "<b>".$lang['wihladm34']."</b>: ";
		switch($job_check['reset_usage_graph']['timestamp']) {
			case 1:
				if($check == 1) {
					$err_msg .= $lang['wihladmrs16']."<br>"; break;
				} else {
					$err_msg .= $lang['wihladmrs1']."<br>"; break;
				}
			case 2:
				$err_msg .= "<span class=\"alert-info\">".$lang['wihladmrs2']."</span><br>"; break;
			case 3:
				$err_msg .= "<span class=\"alert-danger\">".$lang['wihladmrs3']."</span><br>"; break;
			case 4:
				$err_msg .= "<span class=\"alert-success\">".$lang['wihladmrs4']."</span><br>"; break;
			default:
				$err_msg .= "<span class=\"alert-secondary\"><i>".$lang['wihladmrs0']."</i></span><br>";
		}
		
		$err_msg .= "<br><br><b>".$lang['wihladm36']."</b>: ";
		switch($job_check['reset_stop_after']['timestamp']) {
			case 1:
				$err_msg .= $lang['wihladmrs16']."<br>"; break;
			default:
				$err_msg .= "<span class=\"alert-secondary\"><i>".$lang['wihladmrs0']."</i></span><br>";
		}

		return $err_msg;
	}


	if($job_check['reset_user_time']['timestamp'] != 0 || $job_check['reset_user_delete']['timestamp'] != 0 || $job_check['reset_group_withdraw']['timestamp'] != 0 || $job_check['reset_webspace_cache']['timestamp'] != 0 || $job_check['reset_usage_graph']['timestamp'] != 0) {
		$err_msg = '<b>'.$lang['wihladmrs'].":</b><br><br><pre>"; $err_lvl = 2;
		$err_msg .= reset_status($lang, $job_check);

		if(in_array($job_check['reset_user_time']['timestamp'], ["0","4"], true) && in_array($job_check['reset_user_delete']['timestamp'], ["0","4"], true) && in_array($job_check['reset_group_withdraw']['timestamp'], ["0","4"], true) && in_array($job_check['reset_webspace_cache']['timestamp'], ["0","4"], true) && in_array($job_check['reset_usage_graph']['timestamp'], ["0","4"], true)) {
			$err_msg .= '</pre><br><br><br>'.sprintf($lang['wihladmrs9'], '<form class="btn-group" name="confirm" action="reset.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-success btn-sm" name="confirm"><i class="fas fa-check"></i>&nbsp;', '</button></form>');
		} else {
			$err_msg .= '</pre><br>'.sprintf($lang['wihladmrs7'], '<form class="btn-group" name="refresh" action="reset.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary btn-sm" name="refresh"><i class="fas fa-sync"></i>&nbsp;', '</button></form>').'<br><br>'.$lang['wihladmrs8'];
		}
	}

	if (isset($_POST['confirm']) && isset($db_csrf[$_POST['csrf_token']])) {
		if(in_array($job_check['reset_user_time']['timestamp'], ["0","4"], true) && in_array($job_check['reset_user_delete']['timestamp'], ["0","4"], true) && in_array($job_check['reset_group_withdraw']['timestamp'], ["0","4"], true) && in_array($job_check['reset_webspace_cache']['timestamp'], ["0","4"], true) && in_array($job_check['reset_usage_graph']['timestamp'], ["0","4"], true)) {
			if ($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('reset_user_time','0'),('reset_user_delete','0'),('reset_group_withdraw','0'),('reset_webspace_cache','0'),('reset_usage_graph','0'),('reset_stop_after','0') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
				$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
				$err_lvl = 3;
			} else {
				$err_msg = $lang['wihladmrs10'];
				$err_lvl = NULL;
			}
		} else {
			$err_msg = $lang['errukwn']; $err_lvl = 3;
		}
	} elseif (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
		if($job_check['reset_user_time']['timestamp'] != 0 || $job_check['reset_user_delete']['timestamp'] != 0 || $job_check['reset_group_withdraw']['timestamp'] != 0 || $job_check['reset_webspace_cache']['timestamp'] != 0 || $job_check['reset_usage_graph']['timestamp'] != 0) {
			$err_msg = '<b>'.$lang['wihladmrs6'].'</b><br><br>'.sprintf($lang['wihladmrs7'], '<form class="btn-group" name="refresh" action="reset.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary btn-sm" name="refresh"><i class="fas fa-sync"></i>&nbsp;', '</button></form>').'<br><br>'.$lang['wihladmrs8'];
			$err_lvl = 3;
		} elseif($_POST['reset_user_time'] == 0 && !isset($_POST['reset_group_withdraw']) && !isset($_POST['reset_webspace_cache']) && !isset($_POST['reset_usage_graph'])) {
			$err_msg = $lang['wihladmrs15']; $err_lvl = 3;
		} else {
			if(($stats_server = $mysqlcon->query("SELECT * FROM `$dbname`.`stats_server`")->fetch()) === false) {
				$err_msg .= print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			}
			if(($groups = $mysqlcon->query("SELECT COUNT(*) AS `count` from `$dbname`.`groups`")->fetch()) === false) {
				$err_msg .= print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			}

			if (isset($_POST['reset_user_time']) && $_POST['reset_user_time'] == 1) {
				$job_check['reset_user_time']['timestamp'] = 1;
			} elseif (isset($_POST['reset_user_time']) && $_POST['reset_user_time'] == 2) {
				$job_check['reset_user_delete']['timestamp'] = 1;
			}
			if (isset($_POST['reset_group_withdraw'])) $_POST['reset_group_withdraw'] = $job_check['reset_group_withdraw']['timestamp'] = 1; else $_POST['reset_group_withdraw'] = $job_check['reset_group_withdraw']['timestamp'] = 0;
			if (isset($_POST['reset_webspace_cache'])) $_POST['reset_webspace_cache'] = $job_check['reset_webspace_cache']['timestamp'] = 1; else $_POST['reset_webspace_cache'] = $job_check['reset_webspace_cache']['timestamp'] = 0;
			if (isset($_POST['reset_usage_graph'])) $_POST['reset_usage_graph'] = $job_check['reset_usage_graph']['timestamp'] = 1; else $_POST['reset_usage_graph'] = $job_check['reset_usage_graph']['timestamp'] = 0;
			if (isset($_POST['reset_stop_after'])) $_POST['reset_stop_after'] = $job_check['reset_stop_after']['timestamp'] = 1; else $_POST['reset_stop_after'] = $job_check['reset_stop_after']['timestamp'] = 0;
			
			if ($_POST['reset_group_withdraw'] == 0) $delay = 0; else $delay = ($cfg['teamspeak_query_command_delay'] / 1000000) + 0.05;
			if ($_POST['reset_webspace_cache'] == 0) $cache_needed_time = 0; else $cache_needed_time = $stats_server['total_user'] / 10 * 0.005;
			$time_to_begin = 5 * $cfg['teamspeak_query_command_delay'] / 1000000;
			$est_time = round($delay * ($stats_server['total_user'] + $groups['count']) + $time_to_begin + $cache_needed_time);
			$dtF = new \DateTime('@0');
			$dtT = new \DateTime("@$est_time");
			$est_time = $dtF->diff($dtT)->format($cfg['default_date_format']);

			$err_msg = $lang['wihladmrs11'].': '.$est_time.'.<br>'.$lang['wihladmrs12'].'<br><br><pre>';
			$err_msg .= reset_status($lang, $job_check, $check = 1);
			$err_msg .= '</pre><br><br><form class="btn-group" name="startjobs" action="reset.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><input type="hidden" name="reset_user_time" value="'.$_POST['reset_user_time'].'"><input type="hidden" name="reset_group_withdraw" value="'.$_POST['reset_group_withdraw'].'"><input type="hidden" name="reset_webspace_cache" value="'.$_POST['reset_webspace_cache'].'"><input type="hidden" name="reset_usage_graph" value="'.$_POST['reset_usage_graph'].'"><input type="hidden" name="reset_stop_after" value="'.$_POST['reset_stop_after'].'"><button type="submit" class="btn btn-success btn-sm" name="startjobs"><i class="fas fa-check"></i>&nbsp;'.$lang['wihladmrs13'].'</button></form>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<form class="btn-group" name="cancel" action="reset.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-danger btn-sm" name="cancel"><i class="fas fa-times"></i>&nbsp;'.$lang['wihladmrs14'].'</button></form>';
			$err_lvl = 1;
		}		
	} elseif(isset($_POST['startjobs']) && isset($db_csrf[$_POST['csrf_token']])) {
		if($_POST['reset_user_time'] == 1) {
			$reset_user_time = 1;
			$reset_user_delete = 0;
		} elseif($_POST['reset_user_time'] == 2) {
			$reset_user_delete = 1;
			$reset_user_time = 0;
		} else {
			$reset_user_time = 0;
			$reset_user_delete = 0;
		}

		if ($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('reset_user_time','{$reset_user_time}'),('reset_user_delete','{$reset_user_delete}'),('reset_group_withdraw','{$_POST['reset_group_withdraw']}'),('reset_webspace_cache','{$_POST['reset_webspace_cache']}'),('reset_usage_graph','{$_POST['reset_usage_graph']}'),('reset_stop_after','{$_POST['reset_stop_after']}') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
			$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
			$err_lvl = 3;
		} else {
			$err_msg = '<b>'.$lang['wihladmrs5'].'</b><br><br>'.sprintf($lang['wihladmrs7'], '<form class="btn-group" name="refresh" action="reset.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary btn-sm" name="refresh"><i class="fas fa-sync"></i>&nbsp;', '</button></form>').'<br><br>'.$lang['wihladmrs8'];
			$err_lvl = NULL;
		}
	} elseif(isset($_POST['update']) || isset($_POST['confirm'])) {
		echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
		rem_session_ts3();
		exit;
	}
	?>
			<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['wihladm3']; ?>
							</h1>
						</div>
					</div>
					<form name="post" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="form-horizontal">
						<div class="row">
						<div class="col-md-12">
							<div class="form-group">
									<label class="col-sm-12 pointer" data-toggle="modal" data-target="#wihladm0desc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="panel-body">
									</div>
								</div>
							</div>
							<div class="col-md-3">
							</div>
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm31desc"><?php echo $lang['wihladm31']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
													<select class="selectpicker show-tick form-control basic" name="reset_user_time">
														<option data-icon="fas fa-ban" selected="selected" value="0">&nbsp;<?PHP echo $lang['winxmode1']; ?></option>
														<option data-divider="true">&nbsp;</option>
														<option data-icon="fas fa-history" value="1">&nbsp;<?PHP echo $lang['wihladm311']; ?></option>
														<option data-icon="fas fa-user-slash" value="2">&nbsp;<?PHP echo $lang['wihladm312']; ?></option>
													</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm32desc"><?php echo $lang['wihladm32']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input class="switch-animate" type="checkbox" data-size="mini" name="reset_group_withdraw" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm33desc"><?php echo $lang['wihladm33']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input class="switch-animate" type="checkbox" data-size="mini" name="reset_webspace_cache" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm34desc"><?php echo $lang['wihladm34']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input class="switch-animate" type="checkbox" data-size="mini" name="reset_usage_graph" value="0">
											</div>
										</div>
										<div class="row">&nbsp;</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm36desc"><?php echo $lang['wihladm36']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input class="switch-animate" type="checkbox" data-size="mini" name="reset_stop_after" value="0">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="update"><?php echo $lang['wihladm35']; ?></button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</div>
					</form>
				</div>
			</div>
		</div>
		
	<div class="modal fade" id="wihladm0desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm0']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm0desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm31desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm31']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm31desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm32desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm32']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm32desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm33desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm33']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm33desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm34desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm34']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm34desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm36desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm36']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm36desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<script>
	$("[name='reset_group_withdraw']").bootstrapSwitch();
	$("[name='reset_webspace_cache']").bootstrapSwitch();
	$("[name='reset_usage_graph']").bootstrapSwitch();
	$("[name='reset_stop_after']").bootstrapSwitch();
	</script>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>