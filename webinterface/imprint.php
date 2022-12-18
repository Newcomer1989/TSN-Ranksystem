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
		$cfg['stats_imprint_address'] = addslashes($_POST['stats_imprint_address']);
		$cfg['stats_imprint_address_url'] = addslashes($_POST['stats_imprint_address_url']);
		$cfg['stats_imprint_email'] = addslashes($_POST['stats_imprint_email']);
		$cfg['stats_imprint_phone'] = addslashes($_POST['stats_imprint_phone']);
		$cfg['stats_imprint_notes'] = addslashes($_POST['stats_imprint_notes']);
		$cfg['stats_imprint_privacypolicy'] = addslashes($_POST['stats_imprint_privacypolicy']);
		$cfg['stats_imprint_privacypolicy_url'] = addslashes($_POST['stats_imprint_privacypolicy_url']);
		if (isset($_POST['stats_imprint_switch'])) $cfg['stats_imprint_switch'] = 1; else $cfg['stats_imprint_switch'] = 0;
		if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_imprint_switch','{$cfg['stats_imprint_switch']}'),('stats_imprint_address','{$cfg['stats_imprint_address']}'),('stats_imprint_address_url','{$cfg['stats_imprint_address_url']}'),('stats_imprint_email','{$cfg['stats_imprint_email']}'),('stats_imprint_phone','{$cfg['stats_imprint_phone']}'),('stats_imprint_notes','{$cfg['stats_imprint_notes']}'),('stats_imprint_privacypolicy','{$cfg['stats_imprint_privacypolicy']}'),('stats_imprint_privacypolicy_url','{$cfg['stats_imprint_privacypolicy_url']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
			$err_msg = print_r($mysqlcon->errorInfo(), true);
			$err_lvl = 3;
		} else {
			$err_msg = $lang['wisvsuc'];
			$err_lvl = NULL;
		}
		$cfg['stats_imprint_address'] = $_POST['stats_imprint_address'];
		$cfg['stats_imprint_email'] = $_POST['stats_imprint_email'];
		$cfg['stats_imprint_phone'] = $_POST['stats_imprint_phone'];
		$cfg['stats_imprint_notes'] = $_POST['stats_imprint_notes'];
		$cfg['stats_imprint_privacypolicy'] = $_POST['stats_imprint_privacypolicy'];
	} elseif(isset($_POST['update'])) {
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
								<?php echo $lang['imprint'],' & ',$lang['privacy']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
						<div class="row">
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpswitchdesc"><?php echo $lang['wiimpswitch']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_imprint_switch'] == 1) {
													echo '<input id="switch-animate" type="checkbox" checked data-size="mini" name="stats_imprint_switch" value="',$cfg['stats_imprint_switch'],'">';
												} else {
													echo '<input id="switch-animate" type="checkbox" data-size="mini" name="stats_imprint_switch" value="',$cfg['stats_imprint_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpaddrurldesc"><?php echo $lang['wiimpaddrurl']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="url" name="stats_imprint_address_url" class="form-control" value='<?php echo $cfg["stats_imprint_address_url"]; ?>'>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpaddrdesc"><?php echo $lang['wiimpaddr']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<textarea class="form-control" rows="4" name="stats_imprint_address" maxlength="21588"><?php echo $cfg['stats_imprint_address']; ?></textarea>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpemaildesc"><?php echo $lang['wiimpnotes']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="email" name="stats_imprint_email" class="form-control" value='<?php echo $cfg["stats_imprint_email"]; ?>'>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpphonedesc"><?php echo $lang['wiimpphone']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="tel" name="stats_imprint_phone" class="form-control" value='<?php echo $cfg["stats_imprint_phone"]; ?>'>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpnotesdesc"><?php echo $lang['wiimpnotes']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<textarea class="form-control" rows="5" name="stats_imprint_notes" maxlength="21588"><?php echo $cfg['stats_imprint_notes']; ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpprivurldesc"><?php echo $lang['wiimpprivurl']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="url" name="stats_imprint_privacypolicy_url" class="form-control" value='<?php echo $cfg["stats_imprint_privacypolicy_url"]; ?>'>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpprivacydesc"><?php echo $lang['privacy']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<textarea class="form-control" rows="15" name="stats_imprint_privacypolicy" maxlength="21588"><?php echo $cfg['stats_imprint_privacypolicy']; ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i>&nbsp;<?php echo $lang['wisvconf']; ?></button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</form>
				</div>
			</div>
		</div>
		
	<div class="modal fade" id="wiimpswitchdesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo $lang['wiimpswitch']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpswitchdesc']; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wiimpaddrurldesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo $lang['wiimpaddrurl']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpaddrurldesc']; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wiimpaddrdesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo $lang['wiimpaddr']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpaddrdesc']; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wiimpemaildesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo $lang['wiimpnotes']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpemaildesc']; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wiimpphonedesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo $lang['wiimpphone']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpphonedesc']; ?>
				</div>
				<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wiimpnotesdesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo $lang['wiimpnotes']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpnotesdesc']; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wiimpprivurldesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo $lang['wiimpprivurl']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpprivurldesc']; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wiimpprivacydesc" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo $lang['privacy']; ?></h4>
				</div>
				<div class="modal-body">
					<?php echo $lang['wiimpprivacydesc']; ?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<script>
	$("[name='stats_imprint_switch']").bootstrapSwitch();
	</script>
	</body>
	</html>
	<?PHP
} catch(Throwable $ex) { }
?>