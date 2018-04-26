<?PHP
session_start();

require_once('../other/config.php');
require_once('../other/phpcommand.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');
require_once('../other/csrf_handler.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if(isset($_REQUEST['token']) && isset($_SESSION[$rspathhex.'temp_uuid'])) {
	if($_REQUEST['token'] == NULL) {
		$err_msg = $lang['stve0003']; $err_lvl = 1;
	} elseif($_REQUEST['token'] != $_SESSION[$rspathhex.'token']) {
		$err_msg = $lang['stve0004']; $err_lvl = 3;
	} elseif($_REQUEST['token'] == $_SESSION[$rspathhex.'token']) {
		$err_msg = $lang['stve0005']; $err_lvl = NULL;
		$_SESSION[$rspathhex.'serverport'] = $ts['voice'];
		$_SESSION[$rspathhex.'uuid_verified'] = $_SESSION[$rspathhex.'temp_uuid'];
		$_SESSION[$rspathhex.'tsuid'] = $_SESSION[$rspathhex.'temp_uuid'];
		$_SESSION[$rspathhex.'multiple'] = '';
		$_SESSION[$rspathhex.'connected'] = 1;
		$_SESSION[$rspathhex.'tscldbid'] = $_SESSION[$rspathhex.'temp_cldbid'];
        $_SESSION[$rspathhex.'tsname']   = $_SESSION[$rspathhex.'temp_name'];
		foreach ($adminuuid as $auuid) {
			if ($_SESSION[$rspathhex.'uuid_verified'] == $auuid) {
				$_SESSION[$rspathhex.'admin'] = TRUE;
			}
		}
		$dbdata = $mysqlcon->prepare("SELECT a.firstcon AS firstcon, b.total_connections AS total_connections FROM $dbname.user a INNER JOIN $dbname.stats_user b ON a.uuid = b.uuid WHERE b.uuid = :uuid");
		$dbdata->bindValue(':uuid', $_SESSION[$rspathhex.'tsuid'], PDO::PARAM_STR);
		$dbdata->execute();
		$clientinfo = $dbdata->fetchAll();
		if ($clientinfo[0]['total_connections'] != NULL) {
			$_SESSION[$rspathhex.'tsconnections'] = $clientinfo[0]['total_connections'];
		} else {
			$_SESSION[$rspathhex.'tsconnections'] = 0;
		}
		if ($clientinfo[0]['firstcon'] == 0) {
			$_SESSION[$rspathhex.'tscreated'] = "unkown";
		} else {
			$_SESSION[$rspathhex.'tscreated'] = date('d-m-Y', $clientinfo[0]['firstcon']);
		}
		$uuidasbase16 = '';
		for ($i = 0; $i < 20; $i++) {
			$char = ord(substr(base64_decode($_SESSION[$rspathhex.'tsuid']), $i, 1));
			$uuidasbase16 .= $convert[($char & 0xF0) >> 4];
			$uuidasbase16 .= $convert[$char & 0x0F];
		}
		if (is_file('../avatars/' . $uuidasbase16 . '.png')) {
			$_SESSION[$rspathhex.'tsavatar'] = $uuidasbase16 . '.png';
		} else {
			$_SESSION[$rspathhex.'tsavatar'] = "none";
		}
		$_SESSION[$rspathhex.'language']  = $language;
	} else {
		$err_msg = $lang['stve0006']; $err_lvl = 3;
	}
}

if(!isset($_SESSION[$rspathhex.'tsuid'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}

if(isset($_SESSION[$rspathhex.'multiple'])) {
	$multi_uuid = explode(',', substr($_SESSION[$rspathhex.'multiple'], 0, -1));
}

if($_SESSION[$rspathhex.'multiple'] == NULL && count($multi_uuid) < 2 && ($registercid == NULL || $registercid == 0)) {
	$err_msg = $lang['verify0001']."<br><br>".$lang['verify0003'];
	$err_lvl = 3;
} elseif($_SESSION[$rspathhex.'connected'] == 0 && $registercid != NULL && $registercid != 0) {
	$err_msg = $lang['verify0001']; $err_lvl = 1;
	$uuids = $mysqlcon->query("SELECT name,uuid FROM $dbname.user WHERE online='1' and cid='$registercid' ORDER BY name ASC")->fetchAll();
	foreach($uuids as $entry) {
		$multiple_uuid[$entry['uuid']] = $entry['name'];
	}
} elseif(count($multi_uuid) == 1 && $_SESSION[$rspathhex.'connected'] == 1) {
	$err_msg = $lang['stve0005']; $err_lvl = 1;
} elseif(count($multi_uuid) > 1) {
	$multi_uuid = explode(',', substr($_SESSION[$rspathhex.'multiple'], 0, -1));
	foreach ($multi_uuid as $entry) {
		list($key, $value) = explode('=>', $entry);
		$multiple_uuid[$key] = $value;
	}
} else {
	$err_msg = $lang['stve0006']; $err_lvl = 3;
}

if(isset($_POST['uuid']) && !isset($_SESSION[$rspathhex.'temp_uuid'])) {
	require_once('../libs/ts3_lib/TeamSpeak3.php');
	try {
		$ts3 = TeamSpeak3::factory("serverquery://".$ts['user'].":".$ts['pass']."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']."&blocking=0");
		
		try {
			usleep($slowmode);
			$ts3->selfUpdate(array('client_nickname' => "Ranksystem - Verification"));
		} catch (Exception $e) {
			$err_msg = $lang['errorts3'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
		}

		try {
			usleep($slowmode);
			$allclients = $ts3->clientList();
		} catch (Exception $e) {
			$err_msg = $lang['errorts3'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
		}
		
		foreach ($allclients as $client) {
			if($client['client_unique_identifier'] == $_POST['uuid']) {
				$cldbid = $client['client_database_id'];
				$nickname = htmlspecialchars($client['client_nickname'], ENT_QUOTES);
				$_SESSION[$rspathhex.'temp_uuid'] = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
				$_SESSION[$rspathhex.'temp_cldbid'] = $cldbid;
				$_SESSION[$rspathhex.'temp_name'] = $nickname;
				$pwd = substr(str_shuffle("abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789"),0,6);
				$_SESSION[$rspathhex.'token'] = $pwd;
				$tokenlink = '[URL]http'.(!empty($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?token='.$pwd.'[/URL]';
				try {
					$ts3->clientGetByUid($_SESSION[$rspathhex.'temp_uuid'])->message(sprintf($lang['stve0001'], $nickname, $tokenlink, $pwd));
					$err_msg = $lang['stve0002']; $err_lvl = 1;
				} catch (Exception $e) {
					$err_msg = $lang['errorts3'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
				}
				break;
			}
		}
	} catch (Exception $e) {
		$err_msg = $lang['errorts3'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
	}
}

require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); 
		if(count($multi_uuid) > 1 || ($_SESSION[$rspathhex.'connected'] == 0 && $registercid != NULL && $registercid != 0)) {
			?>
			<div class="container-fluid">
				<div id="login-overlay" class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
						  <h4 class="modal-title" id="myModalLabel"><?PHP echo $lang['stve0007']; ?></h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-xs-12">
									<form name="verify" method="POST">
										<?php echo $CSRF; ?>
										<?PHP
										if($_SESSION[$rspathhex.'connected'] == 0) {
											$ts3link = '<a href="ts3server://';
											if (($ts['host']=='localhost' || $ts['host']=='127.0.0.1') && strpos($_SERVER['HTTP_HOST'], 'www.') == 0) {
												$ts3link .= preg_replace('/www\./','',$_SERVER['HTTP_HOST']);
											} elseif ($ts['host']=='localhost' || $ts['host']=='127.0.0.1') {
												$ts3link .= $_SERVER['HTTP_HOST'];
											} else {
												$ts3link .= $ts['host'];
											}
											$ts3link .= ':'.$ts['voice'].'?cid='.$registercid.'">';
											echo '<p>1. ',sprintf($lang['verify0002'], $ts3link, '</a>'),'</p>';
										}
										?>
										<p><?PHP echo ($_SESSION[$rspathhex.'connected'] == 0) ? '2. '.$lang['stve0008'] : '1. '.$lang['stve0008']; ?></p>
										<div class="form-group">
											<div class="input-group col-sm-12">
												<select class="selectpicker show-tick form-control" name="uuid" id="uuid" onchange="this.form.submit();">
													<?PHP
													if(count($multiple_uuid) == 0) {
														echo '<option disabled value="" selected>'.$lang['verify0004'].'</option>';
													} else {
														echo '<option disabled value=""';
														if(!isset($_SESSION[$rspathhex.'temp_uuid'])) echo ' selected','>',$lang['stve0009'];
														echo '</option>';
													}
													foreach($multiple_uuid as $uuid => $nickname) {
														echo '<option data-subtext="',$uuid,'" value="',$uuid,'"'; if(isset($_SESSION[$rspathhex.'temp_uuid']) && $_SESSION[$rspathhex.'temp_uuid'] == $uuid) echo ' selected'; echo '>',$nickname,'</option>';
													}
													?>
												</select>
											</div>
										</div>
										<p><?PHP echo ($_SESSION[$rspathhex.'connected'] == 0) ? '3. '.$lang['stve0010'] : '2. '.$lang['stve0010']; ?></p>
										<div class="form-group">
											<div class="input-group">
												<span class="input-group-addon"><?PHP echo $lang['stve0011']; ?></span>
												<input type="text" class="form-control" name="token" placeholder="" maxlength="64">
											</div>
										</div>
										<br>
										<p>
											<button type="submit" class="btn btn-success btn-block" name="verify"><?PHP echo $lang['stve0012']; ?></button>
										</p>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?PHP } ?>
		</div>
	</div>
</body>
</html>