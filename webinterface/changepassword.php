<?PHP
session_start();

require_once('../other/config.php');
require_once('../other/phpcommand.php');
require_once('../other/csrf_handler.php');

function enter_logfile($logpath,$timezone,$loglevel,$logtext) {
	$file = $logpath.'ranksystem.log';
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
	}
	$input = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ").$loglevel.$logtext."\n";
	$loghandle = fopen($file, 'a');
	fwrite($loghandle, $input);
	if (filesize($file) > 5242880) {
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Restart Bot to continue with new log file...\n");
		fclose($loghandle);
		$file2 = "$file.old";
		if (file_exists($file2)) unlink($file2);
		rename($file, $file2);
		if (substr(php_uname(), 0, 7) == "Windows") {
			exec("del /F ".substr(__DIR__,0,-12).'logs/pid');
			$WshShell = new COM("WScript.Shell");
			$oExec = $WshShell->Run("cmd /C ".$phpcommand." ".substr(__DIR__,0,-12)."worker.php start", 0, false);
			exit;
		} else {
			exec("rm -f ".substr(__DIR__,0,-12).'logs/pid');
			exec($phpcommand." ".substr(__DIR__,0,-12)."worker.php start");
			exit;
		}
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

if (isset($_POST['logout'])) {
    rem_session_ts3($rspathhex);
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

if (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $webuser || $_SESSION[$rspathhex.'password'] != $webpass || $_SESSION[$rspathhex.'clientip'] != getclientip()) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

require_once('nav.php');

if (isset($_POST['changepw']) && $_SESSION[$rspathhex.'username'] == $webuser && $_SESSION[$rspathhex.'password'] == $webpass && $_SESSION[$rspathhex.'clientip'] == getclientip()) {
	$newpass = password_hash($_POST['newpwd1'], PASSWORD_DEFAULT);
	if (!password_verify($_POST['oldpwd'], $webpass)) {
		$err_msg = $lang['wichpw1']; $err_lvl = 3;
	} elseif ($_POST['newpwd1'] != $_POST['newpwd2'] || $_POST['newpwd1'] == NULL) {
		$err_msg = $lang['wichpw2']; $err_lvl = 3;
	} elseif ($mysqlcon->exec("UPDATE $dbname.config set webpass='$newpass'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
    } else {
        enter_logfile($logpath,$timezone,3,sprintf($lang['wichpw3'],getclientip()));
		$err_msg = $lang['wisvsuc']; $err_lvl = NULL;
    }
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
										<?php echo $CSRF; ?>
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
											<button type="submit" class="btn btn-success btn-block" name="changepw"><?PHP echo $lang['wichpw4']; ?></button>
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