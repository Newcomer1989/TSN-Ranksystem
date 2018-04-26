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

if(($last_access = $mysqlcon->query("SELECT last_access,count_access FROM $dbname.config")) === false) {
	$err_msg .= print_r($mysqlcon->errorInfo(), true);
}
$last_access = $last_access->fetchAll();

if (($last_access[0]['last_access'] + 1) >= time()) {
	$again = $last_access[0]['last_access'] + 2 - time();
	$err_msg = sprintf($lang['errlogin2'],$again);
	$err_lvl = 3;
} elseif (isset($_POST['resetpw']) && ($adminuuid==NULL || count($adminuuid) == 0)) {
	$err_msg = $lang['wirtpw1']; $err_lvl=3;
} elseif (isset($_POST['resetpw'])) {
	$nowtime = time();
	if($mysqlcon->exec("UPDATE $dbname.config SET last_access='$nowtime', count_access = count_access + 1") === false) { }
	
	require_once(substr(__DIR__,0,-12).'libs/ts3_lib/TeamSpeak3.php');
	try {
		$ts3 = TeamSpeak3::factory("serverquery://".$ts['user'].":".$ts['pass']."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']."&blocking=0");
		
		try {
			usleep($slowmode);
			$ts3->selfUpdate(array('client_nickname' => "Ranksystem - Reset Password"));
		} catch (Exception $e) { }
		
		usleep($slowmode);
		$allclients = $ts3->clientList();
		$adminuuid_flipped = array_flip($adminuuid);

		foreach ($allclients as $client) {
			if(in_array($client['client_unique_identifier'] , $adminuuid)) {
				$uuid = $client['client_unique_identifier'];
				$checkuuid = 1;
				if($client['connection_client_ip'] == getclientip()) {
					$checkip = 1;
				}
			}
		}
		
		if (!isset($checkuuid)) {
			$err_msg = $lang['wirtpw2']; $err_lvl = 3;
		} elseif (!isset($checkip)) {
			$err_msg = $lang['wirtpw3']; $err_lvl = 3;
		} else {
			usleep($slowmode);
			$pwd = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#*+;:-_~?=%&$�!()"),0,12);
			$webpass = password_hash($pwd, PASSWORD_DEFAULT);
			if($mysqlcon->exec("UPDATE $dbname.config set webpass='$webpass', last_access='0'") === false) { 
				$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			} else {
				try {
					$ts3->clientGetByUid($uuid)->message(sprintf($lang['wirtpw4'], $webuser, $pwd, '[URL=http'.(!empty($_SERVER['HTTPS'])?"s":"").'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).']','[/URL]'));
					$err_msg = sprintf($lang['wirtpw5'],'<a href="http'.(!empty($_SERVER['HTTPS'])?"s":"").'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/">','</a>'); $err_lvl = 1;
					enter_logfile($logpath,$timezone,3,sprintf($lang['wirtpw6'],getclientip()));
				} catch (Exception $e) {
					$err_msg = 'TeamSpeak '.$lang['error'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
				}
			}
		}
	} catch (Exception $e) {
		$err_msg = 'TeamSpeak '.$lang['error'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
	}
}

require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div id="login-overlay" class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
						  <h4 class="modal-title" id="myModalLabel"><?PHP echo $lang['wirtpw7'].' - '.$lang['wi']; ?></h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-xs-12">
									<form id="resetForm" method="POST">
										<?php echo $CSRF; ?>
										<p><?PHP echo $lang['wirtpw8']; ?></p>
										<p><?PHP echo $lang['wirtpw9']; ?>
											<ul>
												<li><?PHP echo $lang['wirtpw10']; ?></li>
												<li><?PHP echo $lang['wirtpw11']; ?></li>
												<li><?PHP echo $lang['wirtpw12']; ?></li>
											</ul>
										</p>
										<br>
										<p>
											<button type="submit" class="btn btn-success btn-block" name="resetpw"><?PHP echo $lang['wirtpw7']; ?></button>
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