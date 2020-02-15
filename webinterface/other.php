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

function getclientip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP']))
		return $_SERVER['HTTP_CLIENT_IP'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED']))
		return $_SERVER['HTTP_X_FORWARDED'];
	elseif(!empty($_SERVER['HTTP_FORWARDED_FOR']))
		return $_SERVER['HTTP_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_FORWARDED']))
		return $_SERVER['HTTP_FORWARDED'];
	elseif(!empty($_SERVER['REMOTE_ADDR']))
		return $_SERVER['REMOTE_ADDR'];
	else
		return false;
}

if (isset($_POST['logout'])) {
    rem_session_ts3($rspathhex);
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

if (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $cfg['webinterface_user'] || $_SESSION[$rspathhex.'password'] != $cfg['webinterface_pass'] || $_SESSION[$rspathhex.'clientip'] != getclientip()) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

require_once('nav.php');
$csrf_token = bin2hex(openssl_random_pseudo_bytes(32));

if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
	if ($_POST['rankup_hash_ip_addresses_mode'] != $cfg['rankup_hash_ip_addresses_mode']) {
		$err_msg2 = $lang['wisvinfo1'];
		$err_lvl2 = 2;
	}
	$cfg['rankup_hash_ip_addresses_mode'] = $_POST['rankup_hash_ip_addresses_mode'];
	$cfg['logs_timezone'] = $_POST['logs_timezone'];
	$cfg['default_date_format'] = $_POST['default_date_format'];
	$cfg['logs_path'] = addslashes($_POST['logs_path']);
	$cfg['logs_debug_level'] = $_POST['logs_debug_level'];
	$cfg['logs_rotation_size'] = $_POST['logs_rotation_size'];
	$cfg['default_language'] = $_SESSION[$rspathhex.'language'] = $_POST['default_language'];
	unset($lang); $lang = set_language($cfg['default_language']);
	$cfg['version_update_channel'] = $_POST['version_update_channel'];
	if (isset($_POST['rankup_client_database_id_change_switch'])) $cfg['rankup_client_database_id_change_switch'] = 1; else $cfg['rankup_client_database_id_change_switch'] = 0;
	if (isset($_POST['rankup_clean_clients_switch'])) $cfg['rankup_clean_clients_switch'] = 1; else $cfg['rankup_clean_clients_switch'] = 0;
	$cfg['rankup_clean_clients_period'] = $_POST['rankup_clean_clients_period'];

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('logs_timezone','{$cfg['logs_timezone']}'),('default_date_format','{$cfg['default_date_format']}'),('logs_path','{$cfg['logs_path']}'),('logs_debug_level','{$cfg['logs_debug_level']}'),('logs_rotation_size','{$cfg['logs_rotation_size']}'),('default_language','{$cfg['default_language']}'),('version_update_channel','{$cfg['version_update_channel']}'),('rankup_hash_ip_addresses_mode','{$cfg['rankup_hash_ip_addresses_mode']}'),('rankup_client_database_id_change_switch','{$cfg['rankup_client_database_id_change_switch']}'),('rankup_clean_clients_switch','{$cfg['rankup_clean_clients_switch']}'),('rankup_clean_clients_period','{$cfg['rankup_clean_clients_period']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button
		type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
		$err_lvl = NULL;
    }
	$cfg['logs_path'] = $_POST['logs_path'];
	
	if(isset($cfg['default_language']) && is_dir(substr(__DIR__,0,-12).'languages/')) {
		foreach(scandir(substr(__DIR__,0,-12).'languages/') as $file) {
			if ('.' === $file || '..' === $file || is_dir($file)) continue;
			$sep_lang = preg_split("/[._]/", $file);
			if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
				if(strtolower($cfg['default_language']) == strtolower($sep_lang[1])) {
					require_once('../languages/core_'.$sep_lang[1].'_'.$sep_lang[2].'_'.$sep_lang[3].'.'.$sep_lang[4]);
					$required_lang = 1;
					break;
				}
			}
		}
	}
	if(!isset($required_lang)) {
		require_once('../languages/core_en_english_gb.php');
	}
} elseif(isset($_POST['update'])) {
	echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
	rem_session_ts3($rspathhex);
	exit;
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
<?PHP if(isset($err_msg2)) error_handling($err_msg2, $err_lvl2); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['winav4'],' ',$lang['wihlset']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" data-toggle="validator" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wivlangdesc"><?php echo $lang['wivlang']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control" name="default_language">
									<?PHP
									if(is_dir(substr(__DIR__,0,-12).'languages/')) {
										foreach(scandir(substr(__DIR__,0,-12).'languages/') as $file) {
											if ('.' === $file || '..' === $file || is_dir($file)) continue;
											$sep_lang = preg_split("/[._]/", $file);
											if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
												echo '<option data-icon="flag-icon flag-icon-'.$sep_lang[3].'" data-subtext="'.$sep_lang[2].'" value="'.$sep_lang[1].'"'.($cfg['default_language'] === $sep_lang[1] ? ' selected="selected"' : '').'>&nbsp;'.strtoupper($sep_lang[1]).'</option>';
											}
										}
									}
									?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#witimedesc"><?php echo $lang['witime']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control" data-live-search="true" name="logs_timezone">
									<?PHP
									$timezonearr = DateTimeZone::listIdentifiers();
									foreach ($timezonearr as $timez) {
										if ($timez == $cfg['logs_timezone']) {
											echo '<option value="'.$cfg['logs_timezone'],'" selected=selected>',$cfg['logs_timezone'],'</option>';
										} else {
											echo '<option value="',$timez,'">',$timez,'</option>';
										}
									}
									?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#widaformdesc"><?php echo $lang['widaform']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="default_date_format" value="<?php echo $cfg['default_date_format']; ?>">
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wilogdesc"><?php echo $lang['wilog']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control required" data-pattern=".*(\/|\\)$" data-error="The Logpath must end with / or \" name="logs_path" value="<?php echo $cfg['logs_path']; ?>" required>
											<div class="help-block with-errors"></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#widbgdesc"><?php echo $lang['widbg']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<select class="selectpicker show-tick form-control basic" name="logs_debug_level">
											<?PHP
											echo '<option value="1"'.($cfg['logs_debug_level'] === '1' ? ' selected="selected"' : '').'>1 - Critical</option>';
											echo '<option value="2"'.($cfg['logs_debug_level'] === '2' ? ' selected="selected"' : '').'>2 - Error</option>';
											echo '<option value="3"'.($cfg['logs_debug_level'] === '3' ? ' selected="selected"' : '').'>3 - Warning</option>';
											echo '<option value="4"'.($cfg['logs_debug_level'] === '4' ? ' selected="selected"' : '').'>4 - Notice</option>';
											echo '<option data-subtext="[recommended]" value="5"'.($cfg['logs_debug_level'] === '5' ? ' selected="selected"' : '').'>5 - Info</option>';
											echo '<option value="6"'.($cfg['logs_debug_level'] === '6' ? ' selected="selected"' : '').'>6 - Debug</option>';
											?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#witszdesc"><?php echo $lang['witsz']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8 required-field-block-spin">
											<input type="text" class="form-control" name="logs_rotation_size" value="<?php echo $cfg['logs_rotation_size']; ?>">
											<script>
											$("input[name='logs_rotation_size']").TouchSpin({
												min: 1,
												max: 1024,
												verticalbuttons: true,
												prefix: 'MiB:'
											});
											</script>
											<div class="help-block with-errors"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiupchdesc"><?php echo $lang['wiupch']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control basic" name="version_update_channel">
									<?PHP
									echo '<option data-icon="fas fa-parachute-box" ata-subtext="[recommended]" value="stable"'; if($cfg['version_update_channel']=="stable") echo " selected=selected"; echo '>&nbsp;&nbsp;',$lang['wiupch0'],'</option>';
									echo '<option data-icon="fas fa-flask" value="beta"'; if($cfg['version_update_channel']=="beta") echo " selected=selected"; echo '>&nbsp;&nbsp;',$lang['wiupch1'],'</option>';
									?>
									</select>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolhadesc"><?php echo $lang['wishcolha']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control basic" name="rankup_hash_ip_addresses_mode">
									<?PHP
									echo '<option data-icon="fas fa-lock" data-subtext="[recommended]" value="2"'; if($cfg['rankup_hash_ip_addresses_mode']=="2") echo " selected=selected"; echo '>&nbsp;&nbsp;',$lang['wishcolha2'],'</option>';
									echo '<option data-icon="fas fa-shield-alt" value="1"'; if($cfg['rankup_hash_ip_addresses_mode']=="1") echo " selected=selected"; echo '>&nbsp;&nbsp;',$lang['wishcolha1'],'</option>';
									echo '<option data-divider="true">&nbsp;</option>';
									echo '<option data-icon="fas fa-ban" value="0"'; if($cfg['rankup_hash_ip_addresses_mode']=="0") echo " selected=selected"; echo '>&nbsp;&nbsp;',$lang['wishcolha0'],'</option>';
									?>
									</select>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wichdbiddesc"><?php echo $lang['wichdbid']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-lg-8">
									<?PHP if ($cfg['rankup_client_database_id_change_switch'] == 1) {
											echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="rankup_client_database_id_change_switch" value="',$cfg['rankup_client_database_id_change_switch'],'">';
										} else {
											echo '<input class="switch-animate" type="checkbox" data-size="mini" name="rankup_client_database_id_change_switch" value="',$cfg['rankup_client_database_id_change_switch'],'">';
										} ?>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#cleancdesc"><?php echo $lang['cleanc']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
										<?PHP if ($cfg['rankup_clean_clients_switch'] == 1) {
											echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="rankup_clean_clients_switch" value="',$cfg['rankup_clean_clients_switch'],'">';
										} else {
											echo '<input class="switch-animate" type="checkbox" data-size="mini" name="rankup_clean_clients_switch" value="',$cfg['rankup_clean_clients_switch'],'">';
										} ?>
										</div>
									</div>
									<div class="row">&nbsp;</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#cleanpdesc"><?php echo $lang['cleanp']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="rankup_clean_clients_period" value="<?php echo $cfg['rankup_clean_clients_period']; ?>">
											<script>
											$("input[name='rankup_clean_clients_period']").TouchSpin({
												min: 1800,
												max: 9223372036854775807,
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

<div class="modal fade" id="witimedesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['witime']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['witimedesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="widaformdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['widaform']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['widaformdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wilogdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wilog']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wilogdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="widbgdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['widbg']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['widbgdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="witszdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['witsz']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['witszdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wivlangdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wivlang']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wivlangdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiupchdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiupch']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiupchdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolhadesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolha']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolhadesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wichdbiddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wichdbid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wichdbiddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cleancdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['cleanc']; ?></h4>
      </div>
      <div class="modal-body">
	    <?php echo sprintf($lang['cleancdesc'], '<a href="https://ts-n.net/clientcleaner.php" target="_blank">https://ts-n.net/clientcleaner.php</a>'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cleanpdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['cleanp']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['cleanpdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<script>
$('form[data-toggle="validator"]').validator({
	custom: {
		pattern: function ($el) {
			var pattern = new RegExp($el.data('pattern'));
			return pattern.test($el.val());
		}
	},
	delay: 100,
	errors: {
		pattern: "There should be an error in your value, please check all could be right!"
	}
});
</script>
</body>
</html>