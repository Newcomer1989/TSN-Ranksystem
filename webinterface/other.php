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
	$cfg['default_language'] = $_SESSION[$rspathhex.'language'] = $_POST['default_language'];
	unset($lang); $lang = set_language($cfg['default_language']);
	$cfg['webinterface_admin_client_unique_id_list'] = $_POST['webinterface_admin_client_unique_id_list'];

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('logs_timezone','{$cfg['logs_timezone']}'),('default_date_format','{$cfg['default_date_format']}'),('logs_path','{$cfg['logs_path']}'),('default_language','{$cfg['default_language']}'),('webinterface_admin_client_unique_id_list','{$cfg['webinterface_admin_client_unique_id_list']}'),('rankup_hash_ip_addresses_mode','{$cfg['rankup_hash_ip_addresses_mode']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button
		type="submit" class="btn btn-primary" name="restart"><i class="fa fa-fw fa-refresh"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
		$err_lvl = NULL;
    }
	$cfg['webinterface_admin_client_unique_id_list'] = array_flip(explode(',', $cfg['webinterface_admin_client_unique_id_list']));
	$cfg['logs_path'] = $_POST['logs_path'];
	if(!isset($cfg['default_language']) || $cfg['default_language'] == "en") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_en.php');
	} elseif($cfg['default_language'] == "ar") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_ar.php');
	} elseif($cfg['default_language'] == "cz") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_cz.php');
	} elseif($cfg['default_language'] == "de") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_de.php');
	} elseif($cfg['default_language'] == "es") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_es.php');
	} elseif($cfg['default_language'] == "fr") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_fr.php');
	} elseif($cfg['default_language'] == "it") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_it.php');
	} elseif($cfg['default_language'] == "nl") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_nl.php');
	} elseif($cfg['default_language'] == "pl") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_pl.php');
	} elseif($cfg['default_language'] == "ro") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_ro.php');
	} elseif($cfg['default_language'] == "ru") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_ru.php');
	} elseif($cfg['default_language'] == "pt") {
		require_once(substr(dirname(__FILE__),0,-12).'languages/core_pt.php');
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
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#witimedesc"><?php echo $lang['witime']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
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
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#widaformdesc"><?php echo $lang['widaform']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="default_date_format" value="<?php echo $cfg['default_date_format']; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wilogdesc"><?php echo $lang['wilog']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8 required-field-block">
									<input type="text" class="form-control" data-pattern=".*(\/|\\)$" data-error="The Logpath must end with / or \" name="logs_path" value="<?php echo $cfg['logs_path']; ?>" required>
									<div class="help-block with-errors"></div>
									<div class="required-icon"><div class="text">*</div></div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wivlangdesc"><?php echo $lang['wivlang']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control" name="default_language">
									<?PHP
									echo '<option data-subtext="العربية" value="ar"'.($cfg['default_language'] === 'ar' ? ' selected="selected"' : '').'>AR</option>';
									echo '<option data-subtext="čeština" value="cz"'.($cfg['default_language'] === 'cz' ? ' selected="selected"' : '').'>CZ</option>';
									echo '<option data-subtext="Deutsch" value="de"'.($cfg['default_language'] === 'de' ? ' selected="selected"' : '').'>DE</option>';
									echo '<option data-subtext="English" value="en"'.($cfg['default_language'] === 'en' ? ' selected="selected"' : '').'>EN</option>';
									echo '<option data-subtext="español" value="es"'.($cfg['default_language'] === 'es' ? ' selected="selected"' : '').'>ES</option>';
									echo '<option data-subtext="français" value="fr"'.($cfg['default_language'] === 'fr' ? ' selected="selected"' : '').'>FR</option>';
									echo '<option data-subtext="Italiano" value="it"'.($cfg['default_language'] === 'it' ? ' selected="selected"' : '').'>IT</option>';
									echo '<option data-subtext="Nederlands" value="nl"'.($cfg['default_language'] === 'nl' ? ' selected="selected"' : '').'>NL</option>';
									echo '<option data-subtext="polski" value="pl"'.($cfg['default_language'] === 'pl' ? ' selected="selected"' : '').'>PL</option>';
									echo '<option data-subtext="Română" value="ro"'.($cfg['default_language'] === 'ro' ? ' selected="selected"' : '').'>RO</option>';
									echo '<option data-subtext="Pусский" value="ru"'.($cfg['default_language'] === 'ru' ? ' selected="selected"' : '').'>RU</option>';
									echo '<option data-subtext="Português" value="pt"'.($cfg['default_language'] === 'pt' ? ' selected="selected"' : '').'>PT</option>';
									?>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiadmuuiddesc"><?php echo $lang['wiadmuuid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8 required-field-block">
									<textarea class="form-control" data-pattern="^([A-Za-z0-9\\\/\+]{27}=,)*([A-Za-z0-9\\\/\+]{27}=)$" data-error="Check all unique IDs are correct and your list do not ends with a comma!" rows="1" name="webinterface_admin_client_unique_id_list" maxlength="500"><?php if(!empty($cfg['webinterface_admin_client_unique_id_list'])) echo implode(',',array_flip($cfg['webinterface_admin_client_unique_id_list'])); ?></textarea>
									<div class="help-block with-errors"></div>
									<div class="required-icon"><div class="text">*</div></div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolhadesc"><?php echo $lang['wishcolha']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control basic" name="rankup_hash_ip_addresses_mode">
									<?PHP
									echo '<option data-subtext="[recommended]" value="2"'; if($cfg['rankup_hash_ip_addresses_mode']=="2") echo " selected=selected"; echo '>',$lang['wishcolha2'],'</option>';
									echo '<option value="1"'; if($cfg['rankup_hash_ip_addresses_mode']=="1") echo " selected=selected"; echo '>',$lang['wishcolha1'],'</option>';
									echo '<option data-divider="true">&nbsp;</option>';
									echo '<option value="0"'; if($cfg['rankup_hash_ip_addresses_mode']=="0") echo " selected=selected"; echo '>',$lang['wishcolha0'],'</option>';
									?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" name="update" class="btn btn-primary"><?php echo $lang['wisvconf']; ?></button>
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
<div class="modal fade" id="wiadmuuiddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiadmuuid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiadmuuiddesc']; ?>
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