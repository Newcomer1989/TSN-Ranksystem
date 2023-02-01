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

	if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
		$cfg['rankup_time_assess_mode'] = $_POST['rankup_time_assess_mode'];
		$cfg['rankup_ignore_idle_time']	= $_POST['rankup_ignore_idle_time'];

		if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_time_assess_mode','{$cfg['rankup_time_assess_mode']}'),('rankup_ignore_idle_time','{$cfg['rankup_ignore_idle_time']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
			$err_msg = print_r($mysqlcon->errorInfo(), true);
			$err_lvl = 3;
		} else {
			$err_msg = $lang['wisvsuc'];
			$err_lvl = NULL;
		}
	} elseif(isset($_POST['update'])) {
		echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
		rem_session_ts3();
		exit;
	}
	?>
			<div id="page-wrapper" class="webinterface_core">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['winav3'],' ',$lang['wihlset']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
						<div class="row">
							<div class="col-md-3"></div>
							<div class="col-md-6">
								<div class="row">&nbsp;</div>
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wisupidledesc"><?php echo $lang['wisupidle']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control basic" name="rankup_time_assess_mode">
												<?PHP
												echo '<option data-icon="fas fa-microphone-slash fa-fw" value="0"'; if($cfg['rankup_time_assess_mode']=="0") echo " selected=selected"; echo '><span class="item-margin">',$lang['wishcolot'],'</span></option>';
												echo '<option data-icon="fas fa-microphone fa-fw" value="1"'; if($cfg['rankup_time_assess_mode']=="1") echo " selected=selected"; echo '><span class="item-margin">',$lang['wishcolat'],'</span></option>';
												?>
												</select>
											</div>
										</div>
										<div class="row">&nbsp;</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiignidledesc"><?php echo $lang['wiignidle']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" name="rankup_ignore_idle_time" value="<?php echo $cfg['rankup_ignore_idle_time']; ?>">
												<script>
												$("input[name='rankup_ignore_idle_time']").TouchSpin({
													min: 0,
													max: 65535,
													verticalbuttons: true,
													prefix: 'Sec.:'
												});
												</script>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3"></div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i><span class="item-margin"><?php echo $lang['wisvconf']; ?></span></button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</form>
				</div>
			</div>
		</div>
		
	<div class="modal fade" id="wisupidledesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wisupidle']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wisupidledesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wiignidledesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wiignidle']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wiignidledesc']; ?>
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