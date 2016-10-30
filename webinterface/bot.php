<?PHP
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

function getlog($logpath,$number_lines,$filters) {
	$lines=array();
	if(file_exists($logpath."ranksystem.log")) {
		$fp = fopen($logpath."ranksystem.log", "r");
		while(!feof($fp)) {
			$line = fgets($fp, 4096);
			foreach($filters as $filter) {
				if($filter != NULL && strstr($line, $filter)) {
					array_push($lines, $line);
					if (count($lines)>$number_lines) array_shift($lines);
					continue;
				}
			}
		}
		fclose($fp);
	} else {
		$lines[] = "No log entry found...\n";
		$lines[] = "The logfile will be created with next startup.\n";
	}
	return $lines;
}

$number_lines = 20;
if (isset($_POST['number'])) {
	if($_POST['number'] == 20) {
		$number_lines = 20;
	} elseif($_POST['number'] == 50) {
		$number_lines = 50;
	} elseif($_POST['number'] == 100) {
		$number_lines = 100;
	} elseif($_POST['number'] == 200) {
		$number_lines = 200;
	} elseif($_POST['number'] == 500) {
		$number_lines = 500;
	} elseif($_POST['number'] == 9999) {
		$number_lines = 9999;
	} else {
		$number_lines = 20;
	}
	$_SESSION['number_lines'] = $number_lines;
} elseif (isset($_SESSION['number_lines'])) {
	$number_lines = $_SESSION['number_lines'];
}

$filters = '';
if (isset($_POST['logfilter']) && in_array('critical', $_POST['logfilter'])) {
	$filters .= "CRITICAL,";
}
if (isset($_POST['logfilter']) && in_array('error', $_POST['logfilter'])) {
	$filters .= "ERROR,";
}
if (isset($_POST['logfilter']) && in_array('warning', $_POST['logfilter'])) {
	$filters .= "WARNING,";
}
if (isset($_POST['logfilter']) && in_array('notice', $_POST['logfilter'])) {
	$filters .= "NOTICE,";
}
if (isset($_POST['logfilter']) && in_array('info', $_POST['logfilter'])) {
	$filters .= "INFO,";
}
if (isset($_POST['logfilter']) && in_array('debug', $_POST['logfilter'])) {
	$filters .= "DEBUG,";
}

if($filters != '') {
	$_SESSION['logfilter'] = $filters;
}

if (!isset($_SESSION['logfilter'])) {
	$_SESSION['logfilter'] = "CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG";
}

$filters = explode(',', $_SESSION['logfilter']);

if (isset($_POST['logout'])) {
	echo "logout";
    $_SESSION = array();
    session_destroy();
	if($_SERVER['HTTPS'] == "on") {
		header("Location: https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	} else {
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	}
	exit;
}

if (!isset($_SESSION['username']) || $_SESSION['username'] != $webuser || $_SESSION['password'] != $webpass || $_SESSION['clientip'] != getclientip()) {
	if($_SERVER['HTTPS'] == "on") {
		header("Location: https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	} else {
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	}
	exit;
}

require_once('nav.php');
$logoutput = getlog($logpath,$number_lines,$filters);

if (isset($_POST['start']) && $_SESSION['username'] == $webuser && $_SESSION['password'] == $webpass && $_SESSION['clientip'] == getclientip()) {
	if (substr(php_uname(), 0, 7) == "Windows") {
		$WshShell = new COM("WScript.Shell");
		$oExec = $WshShell->Run("cmd /C php ".substr(__DIR__,0,-12)."\worker.php start", 0, false); 
	} else {
		exec("php ".substr(__DIR__,0,-12)."worker.php start");
	}
	$err_msg = $lang['wibot2'];
	$err_lvl = 1;
	usleep(80000);
	$logoutput = getlog($logpath,$number_lines,$filters);
}

if (isset($_POST['stop']) && $_SESSION['username'] == $webuser && $_SESSION['password'] == $webpass && $_SESSION['clientip'] == getclientip()) {
	if (substr(php_uname(), 0, 7) == "Windows") {
		$WshShell = new COM("WScript.Shell");
		$oExec = $WshShell->Run("cmd /C php ".substr(__DIR__,0,-12)."\worker.php stop", 0, false); 
	} else {
		exec("php ".substr(__DIR__,0,-12)."worker.php stop");
	}
	$err_msg = $lang['wibot1'];
	$err_lvl = 1;
	usleep(80000);
	$logoutput = getlog($logpath,$number_lines,$filters);
}

if (isset($_POST['restart']) && $_SESSION['username'] == $webuser && $_SESSION['password'] == $webpass && $_SESSION['clientip'] == getclientip()) {
	if (substr(php_uname(), 0, 7) == "Windows") {
		$WshShell = new COM("WScript.Shell");
		$oExec = $WshShell->Run("cmd /C php ".substr(__DIR__,0,-12)."\worker.php restart", 0, false); 
	} else {
		exec("php ".substr(__DIR__,0,-12)."worker.php restart");
	}
	$err_msg = $lang['wibot3'];
	$err_lvl = 1;
	usleep(80000);
	$logoutput = getlog($logpath,$number_lines,$filters);
}

$disabled = '';
if($ts['host'] == NULL || $ts['query'] == NULL || $ts['voice'] == NULL || $ts['user'] == NULL || $ts['pass'] == NULL || $queryname == NULL || $queryname2 == NULL || $grouptime == NULL || $logpath == NULL) {
	$disabled = 1;
	$err_msg = $lang['wibot9'];
	$err_lvl = 2;
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['wibot4']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" name="start" method="POST">
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" class="btn btn-primary" name="start"<?PHP if($disabled == 1) echo " disabled"; ?>>
							<i class="fa fa-fw fa-power-off"></i>&nbsp;<?PHP echo $lang['wibot5']; ?>
							</button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</form>
				<form class="form-horizontal" name="stop" method="POST">
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" class="btn btn-primary" name="stop">
							<i class="fa fa-fw fa-close"></i>&nbsp;<?PHP echo $lang['wibot6']; ?>
							</button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</form>
				<form class="form-horizontal" name="restart" method="POST">
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" class="btn btn-primary" name="restart"<?PHP if($disabled == 1) echo " disabled"; ?>>
							<i class="fa fa-fw fa-refresh"></i>&nbsp;<?PHP echo $lang['wibot7']; ?>
							</button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</form>
				<div class="row">&nbsp;</div>
				<div class="row">
					<div class="col-lg-4">
						<h4>
							<?PHP echo $lang['wibot8']; ?>
						</h4>
					</div>
					<form class="form-horizontal" name="logfilter" method="POST">
					<div class="col-lg-1">
						<div class="checkbox">
							<label><input id="switch-create-destroy" type="checkbox" name="logfilter[]" value="critical" data-switch-no-init onchange="this.form.submit();"
							<?PHP if(in_array('CRITICAL', $filters)) { echo "checked"; } ?>
							>Critical</label>
						</div>
					</div>
					<div class="col-lg-1">
						<div class="checkbox">
							<label><input id="switch-create-destroy" type="checkbox" name="logfilter[]" value="error" data-switch-no-init onchange="this.form.submit();"
							<?PHP if(in_array('ERROR', $filters)) { echo "checked"; } ?>
							>Error</label>
						</div>
					</div>
					<div class="col-lg-1">
						<div class="checkbox">
							<label><input id="switch-create-destroy" type="checkbox" name="logfilter[]" value="warning" data-switch-no-init onchange="this.form.submit();"
							<?PHP if(in_array('WARNING', $filters)) { echo "checked"; } ?>
							>Warning</label>
						</div>
					</div>
					<div class="col-lg-1">
						<div class="checkbox">
							<label><input id="switch-create-destroy" type="checkbox" name="logfilter[]" value="notice" data-switch-no-init onchange="this.form.submit();"
							<?PHP if(in_array('NOTICE', $filters)) { echo "checked"; } ?>
							>Notice</label>
						</div>
					</div>
					<div class="col-lg-1">
						<div class="checkbox">
							<label><input id="switch-create-destroy" type="checkbox" name="logfilter[]" value="info" data-switch-no-init onchange="this.form.submit();"
							<?PHP if(in_array('INFO', $filters)) { echo "checked"; } ?>
							>Info</label>
						</div>
					</div>
					<div class="col-lg-1">
						<div class="checkbox">
							<label><input id="switch-create-destroy" type="checkbox" name="logfilter[]" value="debug" data-switch-no-init onchange="this.form.submit();"
							<?PHP if(in_array('DEBUG', $filters)) { echo "checked"; } ?>
							>Debug</label>
						</div>
					</div>
					<div class="col-lg-2">
						<div class="col-sm-8 pull-right">
							<select class="selectpicker show-tick form-control" id="number" name="number" onchange="this.form.submit();">
							<?PHP
							echo '<option value="20"'; if($number_lines=="20") echo " selected=selected"; echo '>20</option>';
							echo '<option value="50"'; if($number_lines=="50") echo " selected=selected"; echo '>50</option>';
							echo '<option value="100"'; if($number_lines=="100") echo " selected=selected"; echo '>100</option>';
							echo '<option value="200"'; if($number_lines=="200") echo " selected=selected"; echo '>200</option>';
							echo '<option value="500"'; if($number_lines=="500") echo " selected=selected"; echo '>500</option>';
							echo '<option value="9999"'; if($number_lines=="9999") echo " selected=selected"; echo '>9999</option>';
							?>
							</select>
						</div>
					</div>
					</form>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<pre><?PHP krsort($logoutput); foreach ($logoutput as $line) { echo $line; } ?></pre>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>