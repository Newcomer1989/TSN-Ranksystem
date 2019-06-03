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

$start			= "0";					
$break			= "200";				
$limit			= "50000";
$deletetime = time() - $cfg['cc_deletiontime'];


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
	if (isset($_POST['simulate_mode'])) $cfg['simulate_mode'] = 1; else $cfg['simulate_mode'] = 0;
	if (isset($_POST['cc_slowmode'])) $cfg['cc_slowmode'] = 1; else $cfg['cc_slowmode'] = 0;
if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('cc_deletiontime','{$cfg['cc_deletiontime']}'), ('simulate_mode','{$cfg['simulate_mode']}'),('cc_slowmode','{$cfg['cc_slowmode']}'),('cc_query_nickname','{$cfg['cc_query_nickname']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
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
require_once('../other/config.php');
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
					<?PHP echo $lang['clean0006']; ?>
				</h1>
			</div>
		</div>
	<form class="form-horizontal" name="update" method="POST">
	<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-body">
					</div>
					<label class="col-sm-4 control-label" data-toggle="modal"><?php echo $lang['clean0024']; ?></label>
					<div class="col-sm-8 required-field-block">
						<input type="text" class="form-control" name="cc_query_nickname" value="<?php echo $cfg['cc_query_nickname']; ?>" maxlength="30" required>
						<div class="required-icon"><div class="text">*</div></div>
					</div>
					<div class="row">&nbsp;</div>
					<div class="row">&nbsp;</div>
					
						<label class="col-sm-4 control-label" data-toggle="modal"><?php echo $lang['clean0025']; ?></label>
						<div class="col-sm-8 required-field-block">
							<select class="selectpicker show-tick form-control" id="basic" name="cc_deletiontime">
							<?PHP
							echo '<option data-subtext="(30 Days)" value="2592000‬"'; if($cfg['cc_deletiontime']=="2592000‬") echo ' selected="selected"'; echo '>1 Month</option>';
							echo '<option data-divider="true">&nbsp;</option>';
							echo '<option data-subtext="(24 Hours)" value="86400"'; if($cfg['cc_deletiontime']=="86400") echo ' selected="selected"'; echo '>1 Day</option>';
							echo '<option data-subtext="(7 Days)" value="604800"'; if($cfg['cc_deletiontime']=="604800") echo ' selected="selected"'; echo '>1 Week</option>';
							echo '<option data-subtext="(90 Days)" value="7776000"'; if($cfg['cc_deletiontime']=="7776000") echo ' selected="selected"'; echo '>3 Month</option>';
							echo '<option data-subtext="(180 Days)" value="15552000"'; if($cfg['cc_deletiontime']=="15552000") echo ' selected="selected"'; echo '>6 Month</option>';
							echo '<option data-subtext="(360 Days)" value="31104000‬"'; if($cfg['cc_deletiontime']=="31104000‬") echo ' selected="selected"'; echo '>1 Year</option>';
							?>
							</select>
						</div>
					<div class="row">&nbsp;</div>
					<div class="row">&nbsp;</div>

					
					<label class="col-sm-4 control-label" data-toggle="modal" ><?php echo $lang['clean0022']; ?></label>
						<div class="col-sm-8">
							<?PHP if ($cfg['simulate_mode'] == 1) {
								echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="simulate_mode" value="',$cfg['simulate_mode'],'">';
							} else {
							echo '<input class="switch-animate" type="checkbox" data-size="mini" name="simulate_mode" value="',$cfg['simulate_mode'],'">';
							} ?>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">&nbsp;</div>
					<label class="col-sm-4 control-label" data-toggle="modal" ><?php echo $lang['clean0023']; ?></label>
						<div class="col-sm-8">
							<?PHP if ($cfg['cc_slowmode'] == 1) {
								echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="cc_slowmode" value="',$cfg['cc_slowmode'],'">';
							} else {
							echo '<input class="switch-animate" type="checkbox" data-size="mini" name="cc_slowmode" value="',$cfg['cc_slowmode'],'">';
							} ?>
						</div>						
						<div class="row">&nbsp;</div>
						<div class="row">&nbsp;</div>
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
						<button method="post" type="submit" class="btn btn-primary" name="ccstart">
							<i class="fa fa-fw fa-power-off"></i>&nbsp;<?PHP echo $lang['clean0007']; ?>
						</button>
					</div>
					<div class="row">&nbsp;</div>
						<div class="text-left">
							<h4>
								<?PHP echo $lang['clean0008']; ?>
							</h4>
						</div>
				</form>
			</div>
				
<div class="panel panel-default">
<div class="panel-body">

<?php
if (isset($_POST['ccstart'])){	


try {
    $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $cfg['teamspeak_query_user'] . ":" . $cfg['teamspeak_query_pass'] . "@" . $cfg['teamspeak_host_address'] . ":" . $cfg['teamspeak_query_port'] . "/?server_port=" . $cfg['teamspeak_voice_port']);
    $nowtime           = time();
    if ($cfg['cc_slowmode'] == 1)
        sleep(1);
    try {
        $ts3_VirtualServer->selfUpdate(array(
            'client_nickname' => $cfg['cc_query_nickname']
        ));
    }
    catch (Exception $e) {
        if ($cfg['cc_slowmode'] == 1)
            sleep(1);
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
		if ($cfg['cc_query_nickname'] == 1)
            sleep(1);
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
					if ($cfg['cc_query_nickname'] == 1)
						sleep(1);
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
