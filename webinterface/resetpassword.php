<?PHP
require_once('_preload.php');
require_once('_nav.php');

if ($last_access = $mysqlcon->query("SELECT * FROM `$dbname`.`cfg_params` WHERE `param` IN ('webinterface_access_last','webinterface_access_count')")->fetchAll(PDO::FETCH_KEY_PAIR) === false) {
	$err_msg .= print_r($mysqlcon->errorInfo(), true);
}

if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (($last_access['webinterface_access_last'] + 1) >= time()) {
	$again = $last_access['webinterface_access_last'] + 2 - time();
	$err_msg = sprintf($lang['errlogin2'],$again);
	$err_lvl = 3;
} elseif (isset($_POST['resetpw']) && isset($db_csrf[$_POST['csrf_token']]) && ($cfg['webinterface_admin_client_unique_id_list']==NULL || count($cfg['webinterface_admin_client_unique_id_list']) == 0)) {
	$err_msg = sprintf($lang['wirtpw1'], '<a href="https://github.com/Newcomer1989/TSN-Ranksystem/wiki/FAQ#reset-password-webinterface" target="_blank">https://github.com/Newcomer1989/TSN-Ranksystem/wiki/FAQ#reset-password-webinterface</a>'); $err_lvl=3;
} elseif (isset($_POST['resetpw']) && isset($db_csrf[$_POST['csrf_token']])) {
	$nowtime = time();
	$newcount = $last_access['webinterface_access_count'] + 1;
	if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_access_last','{$nowtime}'),('webinterface_access_count','{$newcount}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) { }
	
	require_once(substr(__DIR__,0,-12).'libs/ts3_lib/TeamSpeak3.php');
	try {
		if($cfg['teamspeak_query_encrypt_switch'] == 1) {
			$ts3 = TeamSpeak3::factory("serverquery://".rawurlencode($cfg['teamspeak_query_user']).":".rawurlencode($cfg['teamspeak_query_pass'])."@".$cfg['teamspeak_host_address'].":".$cfg['teamspeak_query_port']."/?server_port=".$cfg['teamspeak_voice_port']."&ssh=1");
		} else {
			$ts3 = TeamSpeak3::factory("serverquery://".rawurlencode($cfg['teamspeak_query_user']).":".rawurlencode($cfg['teamspeak_query_pass'])."@".$cfg['teamspeak_host_address'].":".$cfg['teamspeak_query_port']."/?server_port=".$cfg['teamspeak_voice_port']."&blocking=0");
		}
		
		try {
			usleep($cfg['teamspeak_query_command_delay']);
			$ts3->selfUpdate(array('client_nickname' => "Ranksystem - Reset Password"));
		} catch (Exception $e) { }
		
		try {
			usleep($cfg['teamspeak_query_command_delay']);
			$allclients = $ts3->clientList();

			$pwd = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#*+;:-_~?=%&!()'),0,12);
			$cfg['webinterface_pass'] = password_hash($pwd, PASSWORD_DEFAULT);

			foreach($allclients as $client) {
				if(array_key_exists(htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES), $cfg['webinterface_admin_client_unique_id_list'])) {
					$checkuuid = 1;
					if($client['connection_client_ip'] == getclientip()) {
						$checkip = 1;
						if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_pass','{$cfg['webinterface_pass']}'),('webinterface_access_last','0') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) {
							$err_msg .= $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
						} else {
							try {
								usleep($cfg['teamspeak_query_command_delay']);
								$ts3->clientGetByUid($client['client_unique_identifier'])->message(sprintf($lang['wirtpw4'], $cfg['webinterface_user'], $pwd, '[URL=http'.(!empty($_SERVER['HTTPS'])?"s":"").'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).']','[/URL]'));
								$err_msg .= sprintf($lang['wirtpw5'],'<a href="http'.(!empty($_SERVER['HTTPS'])?"s":"").'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/">','</a>'); $err_lvl = 1;
								enter_logfile($cfg,3,sprintf($lang['wirtpw6'],getclientip()));
							} catch (Exception $e) {
								$err_msg .= $lang['errorts3'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
							}
						}
					}
				}
			}

			if (!isset($checkuuid)) {
				$err_msg = $lang['wirtpw2']; $err_lvl = 3;
			} elseif (!isset($checkip)) {
				$err_msg = $lang['wirtpw3']; $err_lvl = 3;
			}
		} catch (Exception $e) {
			$err_msg = $lang['errorts3'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
		}
	} catch (Exception $e) {
		$err_msg = $lang['errorts3'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
	}
} elseif(isset($_POST['resetpw'])) {
	echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
	rem_session_ts3($rspathhex);
	exit;
}
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
									<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
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