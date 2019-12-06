<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL); 
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
require_once('../libs/ts3_lib/TeamSpeak3.php');

$start			= "1";					
$break			= "6000";				
$limit			= "50000";

$lang['clean0005']			= "TeamSpeak ClientCleaner";
$lang['clean0006']			= "ClientCleaner Log";
$lang['clean0007']			= "Cleaner-Nickname:";
$lang['clean0008']			= "Zuletzt gesehen vor:";
$lang['clean0009']			= "Prüfen";
$lang['clean0010']			= "Löschen";
$lang['clean0011']			= "Queryname wird bereits verwendet. Versuche alternative...";
$lang['clean0012']			= "<b>Suche nach alten Clients...</b>";
$lang['clean0013']			= "Client: ";
$lang['clean0014']			= "Datenbank ID vom Client: ";
$lang['clean0015']			= "UUID: ";
$lang['clean0016']			= "zuletzt gesehen am ";
$lang['clean0017']			= "gefunden";
$lang['clean0018']			= "gelöscht.";
$lang['clean0019']			= "und kann nicht gelöscht werden da ein fehler augetreten ist.";
$lang['clean0020']			= " Clients gelöscht. ";
$lang['clean0021']			= " fehler bei der Löschung.";

$lang['lastseendesc']		= "Definiere die Zeit, wie lange ein User offline sein muss, um gelöscht zu werden. ";
$lang['querynamedesc']		= "Der Nickname, mit welchem die TS3 ServerQuery Verbindung aufgebaut werden soll.<br><br>Der Nickname kann frei gewählt werden!<br><br>Der gewählte Nickname wir im Serverchat angezeigt, wenn man ServerQuery-Benutzer sehen kann (Admin-Rechte werden benötig).";



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
	$cfg['cc_query_nickname'] = $_POST['cc_query_nickname'];
	$cfg['cc_deletiontime'] = $_POST['cc_deletiontime'];
if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('cc_deletiontime','{$cfg['cc_deletiontime']}'),('cc_query_nickname','{$cfg['cc_query_nickname']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc'];
		$err_lvl = NULL;
    }
} elseif(isset($_POST['update'])) {
	echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
	rem_session_ts3($rspathhex);
	exit;
}
?>


<?PHP
$starttime = microtime(true);
set_time_limit(20);
?>
<!doctype html>
<html>

<body>
				
<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">
					<?PHP echo $lang['clean0005']; ?>
				</h1>
			</div>
		</div>
	<form class="form-horizontal" name="update" method="POST">
	<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
		<div class="row">
			<div class="col-md-3">
			</div>
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-body">
					<div class="form-group">
					<label class="col-sm-4 control-label" data-toggle="modal" data-target="#querynamedesc"><?php echo $lang['clean0007']; ?><i class="help-hover fas fa-question-circle"></i></label>
					<div class="col-sm-8 required-field-block">
						<input type="text" class="form-control" name="cc_query_nickname" value="<?php echo $cfg['cc_query_nickname']; ?>" maxlength="30" required>
						<div class="required-icon"><div class="text">*</div></div>
					</div>
					<div class="help-block with-errors"></div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label" data-toggle="modal" data-target="#lastseendesc"><?php echo $lang['clean0008']; ?><i class="help-hover fas fa-question-circle"></i></label>
						<div class="col-sm-8 required-field-block">
							<select class="selectpicker show-tick form-control" id="basic" name="cc_deletiontime">
							<?PHP
							echo '<option data-subtext="(30 Days)" value="2592000‬"'; if($cfg['cc_deletiontime']=="2592000‬") echo ' selected="selected"'; echo '>1 Month</option>';
							echo '<option data-divider="true">&nbsp;</option>';
							echo '<option data-subtext="(12 Hours)" value="43200"'; if($cfg['cc_deletiontime']=="43200") echo ' selected="selected"'; echo '>12 Hours</option>';
							echo '<option data-subtext="(24 Hours)" value="86400"'; if($cfg['cc_deletiontime']=="86400") echo ' selected="selected"'; echo '>1 Day</option>';
							echo '<option data-subtext="(7 Days)" value="604800"'; if($cfg['cc_deletiontime']=="604800") echo ' selected="selected"'; echo '>1 Week</option>';
							echo '<option data-subtext="(90 Days)" value="7776000"'; if($cfg['cc_deletiontime']=="7776000") echo ' selected="selected"'; echo '>3 Month</option>';
							echo '<option data-subtext="(180 Days)" value="15552000"'; if($cfg['cc_deletiontime']=="15552000") echo ' selected="selected"'; echo '>6 Month</option>';
							echo '<option data-subtext="(360 Days)" value="31104000‬"'; if($cfg['cc_deletiontime']=="31104000‬") echo ' selected="selected"'; echo '>1 Year</option>';
							?>
							</select>
						</div>
					</div>			
						<div class="text-center">
							<div class="text-center">
								<button type="submit" name="update" class="btn btn-primary"><?php echo $lang['wisvconf']; ?></button>
							</div>
							<div class="row">&nbsp;</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
	</form>
	<div class="container-fluid">
		<div class="row">&nbsp;</div>
			<form class="form-horizontal" name="start" method="POST">
					<div class="text-center">
						<button method="post" type="submit" class="btn btn-primary" name="cccheck">
							<i class="fas fa-database"></i>&nbsp;<?PHP echo $lang['clean0009']; ?>
						</button>
						<div class="row">&nbsp;</div>
						<button method="post" type="submit" class="btn btn-primary" name="ccdelete">
							<i class="fas fa-trash"></i>&nbsp;<?PHP echo $lang['clean0010']; ?>
						</button>
					</div>
					<div class="row">&nbsp;</div>
						<div class="text-left">
							<h4>
								<?PHP echo $lang['clean0006']; ?>
							</h4>
						</div>
				</form>
			</div>
				
<div class="panel panel-default">
<div class="panel-body">
<div class="modal fade" id="querynamedesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['clean0007']; ?></h4>
      </div>
      <div class="modal-body">
	    <?php echo sprintf($lang['querynamedesc']); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="lastseendesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['clean0008']; ?></h4>
      </div>
      <div class="modal-body">
	    <?php echo sprintf($lang['lastseendesc']); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>

<?php
if (isset($_POST['cccheck'])) {
$cfg['simulate_mode'] = 1;
$deletetime = time() - $cfg['cc_deletiontime'];
try {
    $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $cfg['teamspeak_query_user'] . ":" . $cfg['teamspeak_query_pass'] . "@" . $cfg['teamspeak_host_address'] . ":" . $cfg['teamspeak_query_port'] . "/?server_port=" . $cfg['teamspeak_voice_port']);
    $nowtime           = time();
	usleep($cfg['teamspeak_query_command_delay']);
    try {
        $ts3_VirtualServer->selfUpdate(array(
            'client_nickname' => $cfg['cc_query_nickname']
        ));
    }
    catch (Exception $e) {
        usleep($cfg['teamspeak_query_command_delay']);
        try {
            $ts3_VirtualServer->selfUpdate(array(
                'client_nickname' => $queryname2
            ));
            echo $lang['clean0011'];
        }
        catch (Exception $e) {
            echo $lang['error'] , $e->getCode(), ': ', $e->getMessage();
        }
    }
		
	$clientdblist=array();
	while($getclientdblist=$ts3_VirtualServer->clientListDb($start, $break)) {
		if(count($getclientdblist)<$break) {
			break;
		}
		$clientdblist=array_merge($clientdblist, $getclientdblist);
		$start=$start+$break;
		if ($start == $limit) {
			break;
		}
		usleep($cfg['teamspeak_query_command_delay']);
	}	
	
	$delcount = 0;
	$errcount = 0;

	echo $lang['clean0012'], '<br><br>';
	foreach ($clientdblist as $client) {
		if ($client['client_lastconnected'] < $deletetime) {
			if ($cfg['simulate_mode'] == 1) {
				echo $lang['clean0013'] , $client['client_nickname'], '&nbsp;' , $lang['clean0014'] , $client['cldbid'], '&nbsp;' , $lang['clean0015'] , $client['client_unique_identifier'], '&nbsp;' , $lang['clean0016'] , date('Y-m-d H:i:s',$client['client_lastconnected']), '&nbsp;' , $lang['clean0017'], '<br>';
			} else {
				try {
					usleep($cfg['teamspeak_query_command_delay']);
					$ts3_VirtualServer->clientDeleteDb($client['cldbid']);
					echo $lang['clean0013'] , $client['client_nickname'] , $lang['clean0014'] , $client['cldbid'] , $lang['clean0015'] , $client['client_unique_identifier'] , $lang['clean0016'] , date('Y-m-d H:i:s',$client['client_lastconnected']) , $lang['clean0018'] , '<br>';
					$delcount++;
				}
				catch (Exception $e) {
				echo $lang['clean0013'] , $client['client_nickname'] , $lang['clean0014'] , $client['cldbid'] , $lang['clean0015'] , $client['client_unique_identifier'] , $lang['clean0016'] , date('Y-m-d H:i:s',$client['client_lastconnected']) , $lang['clean0019'] , '<br>';
				$errcount++;
				}
			}
		}
	}
}
catch (Exception $e) {
    echo $e->getCode() . ': ' . $e->getMessage();
}
echo '</table><br>',$delcount, $lang['clean0020'] , $errcount,$lang['clean0021'];
}

if (isset($_POST['ccdelete'])){	
$cfg['simulate_mode'] = 0;

try {
    $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $cfg['teamspeak_query_user'] . ":" . $cfg['teamspeak_query_pass'] . "@" . $cfg['teamspeak_host_address'] . ":" . $cfg['teamspeak_query_port'] . "/?server_port=" . $cfg['teamspeak_voice_port']);
    $nowtime           = time();
    usleep($cfg['teamspeak_query_command_delay']);
    try {
        $ts3_VirtualServer->selfUpdate(array(
            'client_nickname' => $cfg['cc_query_nickname']
        ));
    }
    catch (Exception $e) {
        usleep($cfg['teamspeak_query_command_delay']);
        try {
            $ts3_VirtualServer->selfUpdate(array(
                'client_nickname' => $queryname2
            ));
            echo $lang['clean0011'];
        }
        catch (Exception $e) {
            echo $lang['error'] , $e->getCode(), ': ', $e->getMessage();
        }
    }
		
	$clientdblist=array();
	while($getclientdblist=$ts3_VirtualServer->clientListDb($start, $break)) {
		if(count($getclientdblist)<$break) {
			break;
		}
		$clientdblist=array_merge($clientdblist, $getclientdblist);
		$start=$start+$break;
		if ($start == $limit) {
			break;
		}
		usleep($cfg['teamspeak_query_command_delay']);
	}	
	
	$delcount = 0;
	$errcount = 0;

	echo $lang['clean0012'], '<br><br>';
	foreach ($clientdblist as $client) {
		if ($client['client_lastconnected'] < $deletetime) {
			if ($cfg['simulate_mode'] == 1) {
				echo $lang['clean0013'] , $client['client_nickname'], '&nbsp;' , $lang['clean0014'] , $client['cldbid'], '&nbsp;' , $lang['clean0015'] , $client['client_unique_identifier'], '&nbsp;' , $lang['clean0016'] , date('Y-m-d H:i:s',$client['client_lastconnected']), '&nbsp;' , $lang['clean0017'], '<br>';
			} else {
				try {
					usleep($cfg['teamspeak_query_command_delay']);
					$ts3_VirtualServer->clientDeleteDb($client['cldbid']);
					echo $lang['clean0013'] , $client['client_nickname'] , $lang['clean0014'] , $client['cldbid'] , $lang['clean0015'] , $client['client_unique_identifier'] , $lang['clean0016'] , date('Y-m-d H:i:s',$client['client_lastconnected']) , $lang['clean0018'] , '<br>';
					$delcount++;
				}
				catch (Exception $e) {
				echo $lang['clean0013'] , $client['client_nickname'] , $lang['clean0014'] , $client['cldbid'] , $lang['clean0015'] , $client['client_unique_identifier'] , $lang['clean0016'] , date('Y-m-d H:i:s',$client['client_lastconnected']) , $lang['clean0019'] , '<br>';
				$errcount++;
				}
			}
		}
	}
}
catch (Exception $e) {
    echo $e->getCode() . ': ' . $e->getMessage();
}
echo '</table><br>',$delcount, $lang['clean0020'] , $errcount,$lang['clean0021'];
}
?>

</div>
</div>
</div>
</body>
</html>
