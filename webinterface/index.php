<?PHP
session_start();

require_once('../other/config.php');

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
			$oExec = $WshShell->Run("cmd /C php ".substr(__DIR__,0,-12)."worker.php start", 0, false);
			exit;
		} else {
			exec("rm -f ".substr(__DIR__,0,-12).'logs/pid');
			exec("php ".substr(__DIR__,0,-12)."worker.php start");
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

if(($last_access = $mysqlcon->query("SELECT last_access,count_access FROM $dbname.config")) === false) {
	$err_msg .= print_r($mysqlcon->errorInfo());
}
$last_access = $last_access->fetchAll();

if(($last_access[0]['last_access'] + 1) >= time()) {
	$again = $last_access[0]['last_access'] + 2 - time();
	$err_msg = sprintf($lang['errlogin2'],$again);
	$err_lvl = 3;
} elseif ($last_access[0]['count_access'] >= 10) {
	enter_logfile($logpath,$timezone,3,"Much incorrect logins detected on the webinterface. Blocked login for 300 seconds! Last access from IP ".getclientip().".");
	$err_msg = $lang['errlogin3'];
	$err_lvl = 3;
	$bantime = time() + 299;
	if($mysqlcon->exec("UPDATE $dbname.config SET last_access='$bantime', count_access='0'") === false) { }
} elseif (isset($_POST['username']) && $_POST['username'] == $webuser && password_verify($_POST['password'], $webpass)) {
	$_SESSION['username'] = $webuser;
	$_SESSION['password'] = $webpass;
	$_SESSION['clientip'] = getclientip();
	$_SESSION['newversion'] = $newversion;
	if($mysqlcon->exec("UPDATE $dbname.config SET count_access='0'") === false) { }
	if($_SERVER['HTTPS'] == "on") {
		header("Location: https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/ts.php");
	} else {
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/ts.php");
	}
	exit;
} elseif(isset($_POST['username'])) {
	$nowtime = time();
	if($mysqlcon->exec("UPDATE $dbname.config SET last_access='$nowtime', count_access = count_access + 1") === false) { }
	$err_msg = $lang['errlogin'];
	$err_lvl = 3;
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
												<input type="text" class="form-control" name="username" placeholder="" autofocus>
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
										<button type="submit" class="btn btn-success btn-block"><?PHP echo $lang['login']; ?></button>
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