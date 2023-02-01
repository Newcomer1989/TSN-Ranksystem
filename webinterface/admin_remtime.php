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

	if(!isset($_POST['number']) || $_POST['number'] == "yes") {
		$_SESSION[$rspathhex.'showexcepted'] = "yes";
		$filter = " WHERE `except`='0'";
	} else {
		$_SESSION[$rspathhex.'showexcepted'] = "no";
		$filter = "";
	}

	if(($user_arr = $mysqlcon->query("SELECT `uuid`,`cldbid`,`name` FROM `$dbname`.`user` $filter ORDER BY `name` ASC")->fetchAll(PDO::FETCH_ASSOC)) === false) {
		$err_msg = "DB Error: ".print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
	}

	if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
		$setontime = 0;
		if($_POST['setontime_day']) { $setontime = $setontime + $_POST['setontime_day'] * 86400; }
		if($_POST['setontime_hour']) { $setontime = $setontime + $_POST['setontime_hour'] * 3600; }
		if($_POST['setontime_min']) { $setontime = $setontime + $_POST['setontime_min'] * 60; }
		if($_POST['setontime_sec']) { $setontime = $setontime + $_POST['setontime_sec']; }
		if($setontime == 0) {
			$err_msg = $lang['errseltime']; $err_lvl = 3;
		} elseif($_POST['user'] == NULL) {
			$err_msg = $lang['errselusr']; $err_lvl = 3;
		} else {
			$allinsertdata = '';
			$succmsg = '';
			$nowtime = time();
			$setontime = $setontime * -1;
			foreach($_POST['user'] as $uuid) {
				$allinsertdata .= "('".$uuid."', ".$nowtime.", ".$setontime."),";
				$succmsg .= sprintf($lang['sccupcount'],$setontime,$uuid)."<br>";
			}
			$allinsertdata = substr($allinsertdata, 0, -1);
			if($mysqlcon->exec("INSERT INTO `$dbname`.`admin_addtime` (`uuid`,`timestamp`,`timecount`) VALUES $allinsertdata;") === false) {
				$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			} elseif($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`=1 WHERE `job_name`='reload_trigger'; ") === false) {
				$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			} else {
				$err_msg = substr($succmsg,0,-4); $err_lvl = NULL;
			}
		}
	} elseif(isset($_POST['update'])) {
		echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
		rem_session_ts3();
		exit;
	}
	?>
			<div id="page-wrapper" class="webinterface_admin_remtime">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['wihladm2']; ?>
							</h1>
						</div>
					</div>
					<form name="post" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="form-horizontal">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-sm-12 pointer" data-toggle="modal" data-target="#setontimedesc2"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
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
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiadmhidedesc"><?php echo $lang['wiadmhide']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8 pull-right">
												<select class="selectpicker show-tick form-control" id="number" name="number" onchange="this.form.submit();">
												<?PHP
												echo '<option data-icon="fas fa-eye-slash" value="yes"'; if(!isset($_SESSION[$rspathhex.'showexcepted']) || $_SESSION[$rspathhex.'showexcepted'] == "yes") echo " selected=selected"; echo '><span class="item-margin">hide</span></option>';
												echo '<option data-icon="fas fa-eye" value="no"'; if(isset($_SESSION[$rspathhex.'showexcepted']) && $_SESSION[$rspathhex.'showexcepted'] == "no") echo " selected=selected"; echo '><span class="item-margin">show</span></option>';
												?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiselclddesc"><?php echo $lang['wiselcld']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control" data-actions-box="true" data-live-search="true" multiple name="user[]">
												<?PHP
												foreach ($user_arr as $user) {
													echo '<option value="',$user['uuid'],'" data-subtext="UUID: ',$user['uuid'],'; DBID: ',$user['cldbid'],'">',htmlspecialchars($user['name']),'</option>';
												}
												?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#setontimedesc2"><?php echo $lang['setontime2']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" name="setontime_day">
												<script>
												$("input[name='setontime_day']").TouchSpin({
													min: 0,
													max: 11574,
													verticalbuttons: true,
													prefix: '<?PHP echo $lang['time_day']; ?>'
												});
												</script>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#setontimedesc2"></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" name="setontime_hour">
												<script>
												$("input[name='setontime_hour']").TouchSpin({
													min: 0,
													max: 277777,
													verticalbuttons: true,
													prefix: '<?PHP echo $lang['time_hour']; ?>'
												});
												</script>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#setontimedesc2"></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" name="setontime_min">
												<script>
												$("input[name='setontime_min']").TouchSpin({
													min: 0,
													max: 16666666,
													verticalbuttons: true,
													prefix: '<?PHP echo $lang['time_min']; ?>'
												});
												</script>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#setontimedesc2"></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" name="setontime_sec">
												<script>
												$("input[name='setontime_sec']").TouchSpin({
													min: 0,
													max: 999999999,
													verticalbuttons: true,
													prefix: '<?PHP echo $lang['time_sec']; ?>'
												});
												</script>
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
		
	<div class="modal fade" id="wiselclddesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wiselcld']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wiselclddesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="setontimedesc2" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['setontime2']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['setontimedesc2']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wiadmhidedesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wiadmhide']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wiadmhidedesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>