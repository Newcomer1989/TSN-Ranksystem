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
require_once('../other/phpcommand.php');

function enter_logfile($cfg,$loglevel,$logtext,$norotate = false) {
	$file = $cfg['logs_path'].'ranksystem.log';
	if ($loglevel == 1) {
		$loglevel = "  CRITICAL  ";
	} elseif ($loglevel == 2) {
		$loglevel = "  ERROR     ";
	} elseif ($loglevel == 3) {
		$loglevel = "  WARNING   ";
	} elseif ($loglevel == 4) {
		$loglevel = "  NOTICE    ";
	} elseif ($loglevel == 5) {
		$loglevel = "  INFO      ";
	} elseif ($loglevel == 6) {
		$loglevel = "  DEBUG     ";
	}
	$loghandle = fopen($file, 'a');
	fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ").$loglevel.$logtext."\n");
	fclose($loghandle);
	if($norotate == false && filesize($file) > 5242880) {
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ")."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
		fclose($loghandle);
		$file2 = "$file.old";
		if (file_exists($file2)) unlink($file2);
		rename($file, $file2);
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ")."  NOTICE    Rotated logfile...\n");
		fclose($loghandle);
	}
}

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

if(($cfg['webinterface_access_last'] + 1) >= time()) {
	$waittime = $cfg['webinterface_access_last'] + 2 - time();
	$err_msg = sprintf($lang['errlogin2'],$waittime);
	$err_lvl = 3;
} elseif ($cfg['webinterface_access_count'] >= 10) {
	enter_logfile($cfg,3,sprintf($lang['brute'], getclientip()));
	$err_msg = $lang['errlogin3'];
	$err_lvl = 3;
	$bantime = time() + 299;
	if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_access_last','{$bantime}'),('webinterface_access_count','0') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) { }
} elseif (isset($_POST['username']) && $_POST['username'] == $cfg['webinterface_user'] && password_verify($_POST['password'], $cfg['webinterface_pass'])) {
	$_SESSION[$rspathhex.'username'] = $cfg['webinterface_user'];
	$_SESSION[$rspathhex.'password'] = $cfg['webinterface_pass'];
	$_SESSION[$rspathhex.'clientip'] = getclientip();
	$_SESSION[$rspathhex.'newversion'] = $cfg['version_latest_available'];
	enter_logfile($cfg,6,sprintf($lang['brute2'], getclientip()));
	if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_access_count','0') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) { }
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/bot.php");
	exit;
} elseif(isset($_POST['username'])) {
	$nowtime = time();
	enter_logfile($cfg,5,sprintf($lang['brute1'], getclientip(), $_POST['username']));
	$cfg['webinterface_access_count']++;
	if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_access_last','{$nowtime}'),('webinterface_access_count','{$cfg['webinterface_access_count']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) { }
	$err_msg = $lang['errlogin'];
	$err_lvl = 3;
}

if(isset($_SESSION[$rspathhex.'username']) && $_SESSION[$rspathhex.'username'] == $cfg['webinterface_user'] && $_SESSION[$rspathhex.'password'] == $cfg['webinterface_pass']) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/bot.php");
	exit;
}

require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div id="login-overlay" class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
						  <h4 class="modal-title" id="myModalLabel"><?PHP echo $lang['isntwiusrh']; ?></h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-xs-12">
									<form id="loginForm" method="POST">
										<div class="form-group">
											<label for="username" class="control-label"><?PHP echo $lang['user']; ?>:</label>
											<div class="input-group">
												<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
												<input type="text" class="form-control" name="username" placeholder="" maxlength="64" autofocus>
											</div>
										</div>
										<div class="form-group">
											<label for="password" class="control-label"><?PHP echo $lang['pass']; ?>:</label>
											<div class="input-group">
												<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
												<input type="password" class="form-control" name="password" placeholder="<?PHP echo $lang['pass']; ?>">
											</div>
										</div>
										<br>
										<p>
											<button type="submit" class="btn btn-success btn-block"><?PHP echo $lang['login']; ?></button>
										</p>
										<p class="small text-right">
											<a href="resetpassword.php"><?PHP echo $lang['pass5']; ?></a>
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