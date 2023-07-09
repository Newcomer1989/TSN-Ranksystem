<?php
require_once '_preload.php';

try {
    $number_lines = 20;
    if (isset($_POST['number'])) {
        if ($_POST['number'] == 20) {
            $number_lines = 20;
        } elseif ($_POST['number'] == 50) {
            $number_lines = 50;
        } elseif ($_POST['number'] == 100) {
            $number_lines = 100;
        } elseif ($_POST['number'] == 200) {
            $number_lines = 200;
        } elseif ($_POST['number'] == 500) {
            $number_lines = 500;
        } elseif ($_POST['number'] == 2000) {
            $number_lines = 2000;
        } elseif ($_POST['number'] == 9999) {
            $number_lines = 9999;
        } else {
            $number_lines = 20;
        }
        $_SESSION[$rspathhex.'number_lines'] = $number_lines;
    } elseif (isset($_SESSION[$rspathhex.'number_lines'])) {
        $number_lines = $_SESSION[$rspathhex.'number_lines'];
    }

    if (isset($_SESSION[$rspathhex.'logfilter2'])) {
        $filter2 = $_SESSION[$rspathhex.'logfilter2'];
    } else {
        $filter2 = '';
    }
    $filters = '';
    $inactivefilter = '';
    if (isset($_POST['logfilter']) && in_array('critical', $_POST['logfilter'])) {
        $filters .= 'CRITICAL,';
    } elseif (isset($_POST['logfilter'])) {
        $inactivefilter .= 'CRITICAL,';
    }
    if (isset($_POST['logfilter']) && in_array('error', $_POST['logfilter'])) {
        $filters .= 'ERROR,';
    } elseif (isset($_POST['logfilter'])) {
        $inactivefilter .= 'ERROR,';
    }
    if (isset($_POST['logfilter']) && in_array('warning', $_POST['logfilter'])) {
        $filters .= 'WARNING,';
    } elseif (isset($_POST['logfilter'])) {
        $inactivefilter .= 'WARNING,';
    }
    if (isset($_POST['logfilter']) && in_array('notice', $_POST['logfilter'])) {
        $filters .= 'NOTICE,';
    } elseif (isset($_POST['logfilter'])) {
        $inactivefilter .= 'NOTICE,';
    }
    if (isset($_POST['logfilter']) && in_array('info', $_POST['logfilter'])) {
        $filters .= 'INFO,';
    } elseif (isset($_POST['logfilter'])) {
        $inactivefilter .= 'INFO,';
    }
    if (isset($_POST['logfilter']) && in_array('debug', $_POST['logfilter'])) {
        $filters .= 'DEBUG,';
    } elseif (isset($_POST['logfilter'])) {
        $inactivefilter .= 'DEBUG,';
    }
    if (isset($_POST['logfilter'][0])) {
        $filter2 = htmlspecialchars($_POST['logfilter'][0]);
        $_SESSION[$rspathhex.'logfilter2'] = $filter2;
    }

    if ($filters != '') {
        $_SESSION[$rspathhex.'logfilter'] = $filters;
    }

    if ($inactivefilter != '') {
        $_SESSION[$rspathhex.'inactivefilter'] = $inactivefilter;
    }
    if (isset($_SESSION[$rspathhex.'inactivefilter']) && $_SESSION[$rspathhex.'inactivefilter'] != null) {
        $inactivefilter = explode(',', $_SESSION[$rspathhex.'inactivefilter']);
    }

    if (! isset($_SESSION[$rspathhex.'logfilter'])) {
        $_SESSION[$rspathhex.'logfilter'] = 'CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG,';
    }

    $filters = explode(',', ($_SESSION[$rspathhex.'logfilter'].'NONE'));

    require_once '_nav.php';

    if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
        $err_lvl = 3;
    }

    if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC)) === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
        $err_lvl = 3;
    }

    if ((isset($_POST['start']) || isset($_POST['stop']) || isset($_POST['restart']) || isset($_POST['logfilter'])) && ! isset($db_csrf[$_POST['csrf_token']])) {
        echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
        rem_session_ts3();
        exit;
    }

    $logoutput = getlog($number_lines, $filters, $filter2, $inactivefilter);

    if (isset($_POST['start']) && isset($db_csrf[$_POST['csrf_token']])) {
        if (! is_writable($GLOBALS['logpath'])) {
            $err_msg = '!!!! Logs folder is not writable !!!!<br>Cancel start request!';
            $err_lvl = 3;
        } else {
            $output = '';
            exec($phpcommand.' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.php start', $resultexec);
            if (file_exists($GLOBALS['autostart'])) {
                unlink($GLOBALS['autostart']);
            }
            foreach ($resultexec as $line) {
                $output .= print_r($line, true).'<br>';
            }
            $err_msg = $lang['wibot2'].'<br><br>Result of worker.php:<br><pre>'.$output.'</pre>';
            $err_lvl = 1;
            usleep(80000);
            $logoutput = getlog($number_lines, $filters, $filter2, $inactivefilter);
        }
    }

    if (isset($_POST['stop']) && isset($db_csrf[$_POST['csrf_token']])) {
        if (! is_writable($GLOBALS['logpath'])) {
            $err_msg = '!!!! Logs folder is not writable !!!!<br>Cancel stop request!';
            $err_lvl = 3;
        } else {
            $output = '';
            exec($phpcommand.' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.php stop', $resultexec);
            file_put_contents($GLOBALS['autostart'], '');
            foreach ($resultexec as $line) {
                $output .= print_r($line, true).'<br>';
            }
            $err_msg = $lang['wibot1'].'<br><br>Result of worker.php:<br><pre>'.$output.'</pre>';
            $err_lvl = 1;
            usleep(80000);
            $logoutput = getlog($number_lines, $filters, $filter2, $inactivefilter);
        }
    }

    if (isset($_POST['restart']) && isset($db_csrf[$_POST['csrf_token']])) {
        if (! is_writable($GLOBALS['logpath'])) {
            $err_msg = '!!!! Logs folder is not writable !!!!<br>Cancel restart request!';
            $err_lvl = 3;
        } else {
            $output = '';
            exec($phpcommand.' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.php restart', $resultexec);
            if (file_exists($GLOBALS['autostart'])) {
                unlink($GLOBALS['autostart']);
            }
            foreach ($resultexec as $line) {
                $output .= print_r($line, true).'<br>';
            }
            $err_msg = $lang['wibot3'].'<br><br>Result of worker.php:<br><pre>'.$output.'</pre>';
            $err_lvl = 1;
            usleep(80000);
            $logoutput = getlog($number_lines, $filters, $filter2, $inactivefilter);
        }
    }

    $disabled = '';
    if ($cfg['teamspeak_host_address'] == null || $cfg['teamspeak_query_port'] == null || $cfg['teamspeak_voice_port'] == null || $cfg['teamspeak_query_user'] == null || $cfg['teamspeak_query_pass'] == null || $cfg['teamspeak_query_nickname'] == null || $cfg['rankup_definition'] == null || $GLOBALS['logpath'] == null) {
        $disabled = 1;
        $err_msg = $lang['wibot9'];
        $err_lvl = 2;
    }
    ?>
			<div id="page-wrapper" class="webinterface_bot">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['wibot4']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="start" method="POST">
					<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="start"<?php if ($disabled == 1) {
								    echo ' disabled';
								} ?>>
								<i class="fas fa-power-off"></i><span class="item-margin"><?php echo $lang['wibot5']; ?></span>
								</button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</form>
					<form class="form-horizontal" name="stop" method="POST">
					<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="stop">
								<i class="fas fa-times"></i><span class="item-margin"><?php echo $lang['wibot6']; ?></span>
								</button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</form>
					<form class="form-horizontal" name="restart" method="POST">
					<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="restart"<?php if ($disabled == 1) {
								    echo ' disabled';
								} ?>>
								<i class="fas fa-sync"></i><span class="item-margin"><?php echo $lang['wibot7']; ?></span>
								</button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</form>
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="col-lg-2">
							<h4>
								<?php echo $lang['wibot8']; ?>
							</h4>
						</div>
						<form class="form-horizontal" name="logfilter" method="POST">
						<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
						<div class="col-lg-2">
							<div class="col-sm-12">
								<?php if ($filter2 != null) { ?>
									<input type="text" class="form-control" name="logfilter[]" value="<?php echo $filter2; ?>" data-switch-no-init onchange="this.form.submit();">
								<?php } else { ?>
									<input type="text" class="form-control" name="logfilter[]" placeholder="filter the log entries..." data-switch-no-init onchange="this.form.submit();">
								<?php } ?>
							</div>
						</div>
						<div class="col-lg-1">
							<div class="checkbox">
								<label><input class="switch-create-destroy" type="checkbox" name="logfilter[]" value="critical" data-switch-no-init onchange="this.form.submit();"
								<?php if (in_array('CRITICAL', $filters)) {
								    echo 'checked';
								} ?>
								>Critical</label>
							</div>
						</div>
						<div class="col-lg-1">
							<div class="checkbox">
								<label><input class="switch-create-destroy" type="checkbox" name="logfilter[]" value="error" data-switch-no-init onchange="this.form.submit();"
								<?php if (in_array('ERROR', $filters)) {
								    echo 'checked';
								} ?>
								>Error</label>
							</div>
						</div>
						<div class="col-lg-1">
							<div class="checkbox">
								<label><input class="switch-create-destroy" type="checkbox" name="logfilter[]" value="warning" data-switch-no-init onchange="this.form.submit();"
								<?php if (in_array('WARNING', $filters)) {
								    echo 'checked';
								} ?>
								>Warning</label>
							</div>
						</div>
						<div class="col-lg-1">
							<div class="checkbox">
								<label><input class="switch-create-destroy" type="checkbox" name="logfilter[]" value="notice" data-switch-no-init onchange="this.form.submit();"
								<?php if (in_array('NOTICE', $filters)) {
								    echo 'checked';
								} ?>
								>Notice</label>
							</div>
						</div>
						<div class="col-lg-1">
							<div class="checkbox">
								<label><input class="switch-create-destroy" type="checkbox" name="logfilter[]" value="info" data-switch-no-init onchange="this.form.submit();"
								<?php if (in_array('INFO', $filters)) {
								    echo 'checked';
								} ?>
								>Info</label>
							</div>
						</div>
						<div class="col-lg-1">
							<div class="checkbox">
								<label><input class="switch-create-destroy" type="checkbox" name="logfilter[]" value="debug" data-switch-no-init onchange="this.form.submit();"
								<?php if (in_array('DEBUG', $filters)) {
								    echo 'checked';
								} ?>
								>Debug</label>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="col-sm-8 pull-left">
								<select class="selectpicker show-tick form-control" id="number" name="number" onchange="this.form.submit();">
								<?php
                                echo '<option value="20"';
    if ($number_lines == '20') {
        echo ' selected=selected';
    } echo '>20</option>';
    echo '<option value="50"';
    if ($number_lines == '50') {
        echo ' selected=selected';
    } echo '>50</option>';
    echo '<option value="100"';
    if ($number_lines == '100') {
        echo ' selected=selected';
    } echo '>100</option>';
    echo '<option value="200"';
    if ($number_lines == '200') {
        echo ' selected=selected';
    } echo '>200</option>';
    echo '<option value="500"';
    if ($number_lines == '500') {
        echo ' selected=selected';
    } echo '>500</option>';
    echo '<option value="2000"';
    if ($number_lines == '2000') {
        echo ' selected=selected';
    } echo '>2000</option>';
    echo '<option value="9999"';
    if ($number_lines == '9999') {
        echo ' selected=selected';
    } echo '>9999</option>';
    ?>
								</select>
							</div>
						</div>
						</form>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<pre><?php foreach ($logoutput as $line) {
							    echo $line;
							} ?></pre>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>