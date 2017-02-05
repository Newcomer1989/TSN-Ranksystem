<?PHP
session_start();

require_once('../other/config.php');
require_once('../other/phpcommand.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if(!isset($_SESSION['tsuid']) || isset($_SESSION['uuid_verified'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}

$multi_uuid = explode(',', substr($_SESSION['multiple'], 0, -1));
foreach ($multi_uuid as $entry) {
	list($key, $value) = explode('=>', $entry);
	$multiple_uuid[$key] = $value;
}

if(isset($_POST['uuid']) && !isset($_SESSION['temp_uuid'])) {
	require_once('../libs/ts3_lib/TeamSpeak3.php');
	try {
		$ts3 = TeamSpeak3::factory("serverquery://".$ts['user'].":".$ts['pass']."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']."&blocking=0");
		
		try {
			usleep($slowmode);
			$ts3->selfUpdate(array('client_nickname' => "Ranksystem - Verification"));
		} catch (Exception $e) {
			$err_msg = 'TeamSpeak '.$lang['error'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
		}

		try {
			usleep($slowmode);
			$allclients = $ts3->clientList();
		} catch (Exception $e) {
			$err_msg = 'TeamSpeak '.$lang['error'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
		}
		
		foreach ($allclients as $client) {
			if($client['client_unique_identifier'] == $_POST['uuid']) {
				$cldbid = $client['client_database_id'];
				$nickname = htmlspecialchars($client['client_nickname'], ENT_QUOTES);
				$_SESSION['temp_uuid'] = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
				$pwd = substr(str_shuffle("abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789"),0,6);
				$_SESSION['token'] = $pwd;
				$link = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?token={$pwd}";
				try {
					$ts3->clientGetByUid($_SESSION['temp_uuid'])->message(sprintf($lang['stve0001'], $nickname, $pwd, $link));
					$err_msg = $lang['stve0002']; $err_lvl = 1;
				} catch (Exception $e) {
					$err_msg = 'TeamSpeak '.$lang['error'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
				}
				break;
			}
		}
	} catch (Exception $e) {
		$err_msg = 'TeamSpeak '.$lang['error'].$e->getCode().': '.$e->getMessage(); $err_lvl = 3;
	}
}

if(isset($_REQUEST['token']) && isset($_SESSION['temp_uuid'])) {
	if($_REQUEST['token'] == NULL) {
		$err_msg = $lang['stve0003']; $err_lvl = 1;
	} elseif($_REQUEST['token'] != $_SESSION['token']) {
		$err_msg = $lang['stve0004']; $err_lvl = 3;
	} elseif($_REQUEST['token'] == $_SESSION['token']) {
		$err_msg = $lang['stve0005']; $err_lvl = NULL;
		$_SESSION['uuid_verified'] = $_SESSION['temp_uuid'];
		$_SESSION['multiple'] = '';
	} else {
		$err_msg = $lang['stve0006']; $err_lvl = 3;
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
						  <h4 class="modal-title" id="myModalLabel"><?PHP echo $lang['stve0007']; ?></h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-xs-12">
									<form name="verify" method="POST">
										<p><?PHP echo $lang['stve0008']; ?></p>
										<div class="form-group">
											<div class="input-group col-sm-12">
												<select class="selectpicker show-tick form-control" name="uuid" id="uuid" onchange="this.form.submit();">
													<option disabled value=""<?PHP if(!isset($_SESSION['temp_uuid'])) echo ' selected','>',$lang['stve0009']; ?></option>
													<?PHP
													foreach($multiple_uuid as $uuid => $nickname) {
														echo '<option data-subtext="',$uuid,'" value="',$uuid,'"'; if(isset($_SESSION['temp_uuid']) && $_SESSION['temp_uuid'] == $uuid) echo ' selected'; echo '>',$nickname,'</option>';
													}
													?>
												</select>
											</div>
										</div>
										<p><?PHP echo $lang['stve0010']; ?></p>
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
		</div>
	</div>
</body>
</html>