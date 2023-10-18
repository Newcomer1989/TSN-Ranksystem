<?PHP
require_once('_preload.php');

try {
	require_once('_nav.php');

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	$lastseentime = time() - 2592000;
	if(isset($_POST['showlastseen']) && $_POST['showlastseen'] != NULL) {
		$_SESSION[$rspathhex.'showlastseen'] = strtotime($_POST['showlastseen']);
		$lastseentime = strtotime($_POST['showlastseen']);
	}
	$filter = " WHERE `lastseen`>='{$lastseentime}'";

	if(($user_arr = $mysqlcon->query("SELECT `uuid`,`cldbid`,`name`,`lastseen`,`count`,`idle` FROM `$dbname`.`user` {$filter} ORDER BY `name` ASC")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
		$err_msg = "DB Error1: ".print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
	}

	if (isset($_POST['confirm']) && isset($db_csrf[$_POST['csrf_token']])) {
		$timestamp = time();
		if($mysqlcon->exec("INSERT INTO `$dbname`.`admin_mrgclient` (`uuid_source`,`uuid_target`,`timestamp`) VALUES ('{$_POST['user_source']}','{$_POST['user_target']}','{$timestamp}');") === false) {
			$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
		} elseif($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`=1 WHERE `job_name`='reload_trigger'; ") === false) {
			$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
		} elseif(isset($_POST['delete_user']) && $mysqlcon->exec("INSERT INTO `$dbname`.`admin_addtime` (`uuid`,`timestamp`,`timecount`) VALUES ('{$_POST['user_source']}', '4273093200', '0');") === false) {
			$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
		} else {
			$err_msg = sprintf($lang['wihladm506'], $user_arr[$_POST['user_source']]['name'], $_POST['user_source'], $user_arr[$_POST['user_source']]['cldbid'], $user_arr[$_POST['user_target']]['name'], $_POST['user_target'], $user_arr[$_POST['user_target']]['cldbid']);
			$err_lvl = NULL;
		}
	} elseif(isset($_POST['update']) && $_POST['user_source'] == NULL && $_POST['user_target'] == NULL && isset($db_csrf[$_POST['csrf_token']])) {
		$err_msg = $lang['errselusr']; $err_lvl = 3;
	} elseif(isset($_POST['update']) && $_POST['user_source'] == $_POST['user_target'] && isset($db_csrf[$_POST['csrf_token']])) {
		$err_msg = "Please choose two different user!"; $err_lvl = 3;
	} elseif(isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
		$err_msg = '<b>'.$lang['wihladm505'].'</b><br><br>';

		$online_time = (new DateTime("@0"))->diff(new DateTime("@".round($user_arr[$_POST['user_source']]['count'])))->format($cfg['default_date_format']);
		$idle_time = (new DateTime("@0"))->diff(new DateTime("@".round($user_arr[$_POST['user_source']]['idle'])))->format($cfg['default_date_format']);
		$active_time = (new DateTime("@0"))->diff(new DateTime("@".(round($user_arr[$_POST['user_source']]['count'])-round($user_arr[$_POST['user_source']]['idle']))))->format($cfg['default_date_format']);

		if(isset($_POST['delete_user'])) {
			$del = '<span class="text-danger">'.$lang['yes'].'</span>';
		} else {
			$del = $lang['no'];
		}
		
		$err_msg .= '<b>'.$lang['wihladm502'].':</b><br>'.sprintf("%s (UUID: %s; DBID: %s)",$user_arr[$_POST['user_source']]['name'],$_POST['user_source'],$user_arr[$_POST['user_source']]['cldbid']).' - ';
		$err_msg .= sprintf('<span class="text-warning">'.$lang['delmark'].': %s</span>',$del);
		$err_msg .= '<br>- '.$lang['listseen'].' '.date('Y-m-d H:i:s',$user_arr[$_POST['user_source']]['lastseen']).'<br>- '.$lang['listsumo'].': '.$online_time.'<br>- '.$lang['listsumi'].': '.$idle_time.'<br>- '.$lang['listsuma'].': '.$active_time;

		$err_msg .= '<br><br><span class="arrow-down">↓</span>'.$lang['wihladm504'].'<span class="arrow-down">↓</span><br><br>';
		$online_time = (new DateTime("@0"))->diff(new DateTime("@".round($user_arr[$_POST['user_target']]['count'])))->format($cfg['default_date_format']);
		$idle_time = (new DateTime("@0"))->diff(new DateTime("@".round($user_arr[$_POST['user_target']]['idle'])))->format($cfg['default_date_format']);
		$active_time = (new DateTime("@0"))->diff(new DateTime("@".(round($user_arr[$_POST['user_target']]['count'])-round($user_arr[$_POST['user_target']]['idle']))))->format($cfg['default_date_format']);
		$err_msg .= '<b>'.$lang['wihladm503'].':</b><br>'.sprintf("%s (UUID: %s; DBID: %s)",$user_arr[$_POST['user_target']]['name'],$_POST['user_target'],$user_arr[$_POST['user_target']]['cldbid']).'<br>- '.$lang['listseen'].' '.date('Y-m-d H:i:s',$user_arr[$_POST['user_target']]['lastseen']).'<br>- '.$lang['listsumo'].': '.$online_time.'<br>- '.$lang['listsumi'].': '.$idle_time.'<br>- '.$lang['listsuma'].': '.$active_time;

		$err_msg .= '<br><br><form class="btn-group" name="confirm" action="admin_mrgclient.php" method="POST">
		<input type="hidden" name="csrf_token" value="'.$csrf_token.'">
		<input type="hidden" name="user_source" value="'.$_POST['user_source'].'">
		<input type="hidden" name="user_target" value="'.$_POST['user_target'].'">
		<input type="hidden" name="showlastseen" value="'.$_POST['showlastseen'].'">';
		if(isset($_POST['delete_user'])) $err_msg .= '<input type="hidden" name="delete_user" value="'.$_POST['delete_user'].'">';
		$err_msg .= '<button type="submit" class="btn btn-success btn-sm" name="confirm"><i class="fas fa-check"></i><span class="item-margin">'."Yes, merge it".'</span></button></form><span class="item-margin"><form class="btn-group" name="cancel" action="admin_mrgclient.php" method="POST">
		<input type="hidden" name="csrf_token" value="'.$csrf_token.'">
		<button type="submit" class="btn btn-danger btn-sm" name="cancel"><i class="fas fa-times"></i><span class="item-margin">'.$lang['wihladmrs14'].'</span></button></form></span>';
		$err_lvl = 1;	
	} elseif(isset($_POST['update'])) {
		echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
		rem_session_ts3();
		exit;
	}
	?>
			<div id="page-wrapper" class="webinterface_admin_mrgclient">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['wihladm500']; ?>
							</h1>
						</div>
					</div>
					<form name="post" data-toggle="validator" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="form-horizontal">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-sm-12 pointer" data-toggle="modal" data-target="#wihladm500desc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
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
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm507desc"><?php echo $lang['wihladm507']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
										<div class="input-group date">
											<input type="text" class="form-control" id="showlastseen" name="showlastseen" value="<?php echo date('Y-m-d', $lastseentime); ?>" onchange="this.form.submit();">
											<div class="input-group-addon">
												<span class="fas fa-calendar-days"></span>
											</div>
										</div>
										</div>
									</div>
								</div>
								</div>
								<div class="row">&nbsp;</div>
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm502desc"><?php echo $lang['wihladm501'].' - '.$lang['wihladm502']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control" data-live-search="true" name="user_source" multiple data-max-options="1">
												<?PHP
												foreach ($user_arr as $uuid => $user) {
													echo '<option value="',$uuid,'" data-subtext="UUID: ',$uuid,'; DBID: ',$user['cldbid'],'">',htmlspecialchars($user['name']),'</option>';
												}
												?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm508desc"><?php echo $lang['wihladm4']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input class="switch-animate" type="checkbox" data-size="mini" id="delete_user" name="delete_user" value="0">
											</div>
										</div>
									</div>
									<div class="row">&nbsp;</div>
									<div class="panel-body text-center"><span class="arrow-down">↓</span><?PHP echo $lang['wihladm504']; ?><span class="arrow-down">↓</span></div>
									<div class="row">&nbsp;</div>
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wihladm503desc"><?php echo $lang['wihladm501'].' - '.$lang['wihladm503']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control" data-live-search="true" name="user_target" multiple data-max-options="1">
												<?PHP
												foreach ($user_arr as $uuid => $user) {
													echo '<option value="',$uuid,'" data-subtext="UUID: ',$uuid,'; DBID: ',$user['cldbid'],'">',htmlspecialchars($user['name']),'</option>';
												}
												?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i><span class="item-margin"><?php echo $lang['wisvconf']; ?></span></button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</div>
					</form>
				</div>
			</div>
		</div>
	<div class="modal fade" id="wihladm500desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm500']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm500desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm507desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm507']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm507desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>	
	<div class="modal fade" id="wihladm502desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm502']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm502desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm508desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm4']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm508desc'],"<br><br>",$lang['wihladm40desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wihladm503desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm503']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladm503desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<script>
	$('#showlastseen').datepicker({
		todayBtn: "linked",
		autoclose: true,
		format: 'yyyy-mm-dd',
		todayHighlight: true,
		container: '.date'
	});
	$("[id='delete_user']").bootstrapSwitch();
	</script>
	</script>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>