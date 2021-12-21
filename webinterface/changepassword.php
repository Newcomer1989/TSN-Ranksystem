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

	if (isset($_POST['changepw']) && isset($db_csrf[$_POST['csrf_token']])) {
		if (!password_verify($_POST['oldpwd'], $cfg['webinterface_pass'])) {
			$err_msg = $lang['wichpw1']; $err_lvl = 3;
		} else {
			$cfg['webinterface_pass'] = password_hash($_POST['newpwd1'], PASSWORD_DEFAULT);
			if ($_POST['newpwd1'] !== $_POST['newpwd2'] || $_POST['newpwd1'] == NULL) {
				$err_msg = $lang['wichpw2']; $err_lvl = 3;
			} elseif($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_pass','{$cfg['webinterface_pass']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) {
				$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			} else {
				enter_logfile($cfg,3,sprintf($lang['wichpw3'],getclientip()));
				$err_msg = $lang['wisvsuc']; $err_lvl = NULL;
			}
		}
	} elseif(isset($_POST['changepw'])) {
		echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
		rem_session_ts3();
		exit;
	}
	?>
			<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div id="login-overlay" class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
							  <h4 class="modal-title" id="myModalLabel"><?PHP echo $lang['wichpw4'].' - '.$lang['wi']; ?></h4>
							</div>
							<div class="modal-body">
								<div class="row">
									<div class="col-xs-12">
										<form id="resetForm" method="POST">
										<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
											<div class="form-group">
												<label for="password" class="control-label"><?PHP echo $lang['pass3']; ?>:</label>
												<div class="input-group-justified">
													<input type="password" class="form-control" name="oldpwd" data-toggle="password" data-placement="before" placeholder="<?PHP echo $lang['pass3']; ?>">
												</div>
											</div>
											<p>&nbsp;</p>
											<div class="form-group">
												<label for="password" class="control-label"><?PHP echo $lang['pass4']; ?>:</label>
												<div class="input-group-justified">
													<input type="password" class="form-control" name="newpwd1" data-toggle="password" data-placement="before" placeholder="<?PHP echo $lang['pass4']; ?>">
												</div>
											</div>
											<div class="form-group">
												<label for="password" class="control-label"><?PHP echo $lang['pass4']; ?> (<?PHP echo $lang['repeat']; ?>):</label>
												<div class="input-group-justified">
													<input type="password" class="form-control" name="newpwd2" data-toggle="password" data-placement="before" placeholder="<?PHP echo $lang['pass4']; ?> (<?PHP echo $lang['repeat']; ?>)">
												</div>
											</div>
											<br>
											<p>
												<button type="submit" class="btn btn-success btn-block" name="changepw"><i class="fas fa-save"></i>&nbsp;<?PHP echo $lang['wichpw4']; ?></button>
											</p>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>