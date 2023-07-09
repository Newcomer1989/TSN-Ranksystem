<?PHP
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'configs'.DIRECTORY_SEPARATOR.'dbconfig.php');

$rspathhex = get_rspath();

if(isset($db['type']) === false) {
	$db['type']="mysql";
}
$dbname = $db['dbname'];
$dbtype = $db['type'];

if(!isset($persistent)) $persistent=NULL;

$mysqlcon = db_connect($db['type'], $db['host'], $db['dbname'], $db['user'], $db['pass'], $persistent);

if (isset($mysqlcon) && ($newcfg = $mysqlcon->query("SELECT * FROM `$dbname`.`cfg_params`"))) {
	if(isset($newcfg) && $newcfg != NULL) {
		$cfg = $newcfg->fetchAll(PDO::FETCH_KEY_PAIR);
		if(empty($cfg['webinterface_admin_client_unique_id_list'])) {
			$cfg['webinterface_admin_client_unique_id_list'] = NULL;
		} else {
			$cfg['webinterface_admin_client_unique_id_list'] = array_flip(explode(',', $cfg['webinterface_admin_client_unique_id_list']));
		}
		if(empty($cfg['rankup_excepted_unique_client_id_list'])) {
			$cfg['rankup_excepted_unique_client_id_list'] = NULL;
		} else {
			$cfg['rankup_excepted_unique_client_id_list'] = array_flip(explode(',', $cfg['rankup_excepted_unique_client_id_list']));
		}
		if(empty($cfg['rankup_excepted_group_id_list'])) {
			$cfg['rankup_excepted_group_id_list'] = NULL;
		} else {
			$cfg['rankup_excepted_group_id_list'] = array_flip(explode(',', $cfg['rankup_excepted_group_id_list']));
		}
		if(empty($cfg['rankup_excepted_channel_id_list'])) {
			$cfg['rankup_excepted_channel_id_list'] = NULL;
		} else {
			$cfg['rankup_excepted_channel_id_list'] = array_flip(explode(',', $cfg['rankup_excepted_channel_id_list']));
		}
		if(empty($cfg['rankup_definition'])) {
			$cfg['rankup_definition'] = NULL;
		} else {
			foreach (explode(',', $cfg['rankup_definition']) as $entry) {
				if(substr_count($entry, '=>') > 1) {
					list($time, $group, $keepflag) = explode('=>', $entry);
				} else {
					list($time, $group) = explode('=>', $entry);
					$keepflag = 0;
				}
				$addnewvalue1[$time] = array("time"=>$time,"group"=>$group,"keep"=>$keepflag);
				$cfg['rankup_definition'] = $addnewvalue1;
			}
		}
		if(empty($cfg['rankup_boost_definition'])) {
			$cfg['rankup_boost_definition'] = NULL;
		} else {
			foreach (explode(',', $cfg['rankup_boost_definition']) as $entry) {
				list($key, $value1, $value2) = explode('=>', $entry);
				$addnewvalue2[$key] = array("group"=>$key,"factor"=>$value1,"time"=>$value2);
				$cfg['rankup_boost_definition'] = $addnewvalue2;
			}
		}
		if(empty($cfg['stats_api_keys'])) {
			$cfg['stats_api_keys'] = NULL;
		} else {
			foreach (explode(',', $cfg['stats_api_keys']) as $entry) {
				list($key, $desc, $perm_bot) = array_pad(explode('=>', $entry), 3, null);
				if(!$perm_bot) $perm_bot = 0;
				$addnewvalue3[$key] = array("key"=>$key,"desc"=>$desc,"perm_bot"=>$perm_bot);
				$cfg['stats_api_keys'] = $addnewvalue3;
			}
		}
		unset($addnewvalue1, $addnewvalue2, $addnewvalue3, $newcfg);
	}
}

if(empty($cfg['logs_debug_level'])) {
	$GLOBALS['logs_debug_level'] = $cfg['logs_debug_level'] = "5";
} else {
	$GLOBALS['logs_debug_level'] = $cfg['logs_debug_level'];
}
if(empty($cfg['logs_rotation_size'])) {
	$GLOBALS['logs_rotation_size'] = $cfg['logs_rotation_size'] = "5";
} else {
	$GLOBALS['logs_rotation_size'] = $cfg['logs_rotation_size'];
}

if(!isset($cfg['logs_path']) || $cfg['logs_path'] == NULL) { $cfg['logs_path'] = dirname(__DIR__).DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR; }
if(!isset($cfg['logs_timezone'])) {
	$GLOBALS['logs_timezone'] = "Europe/Berlin";
} else {
	$GLOBALS['logs_timezone'] = $cfg['logs_timezone'];
}
date_default_timezone_set($GLOBALS['logs_timezone']);
$GLOBALS['logpath'] = $cfg['logs_path'];
$GLOBALS['logfile'] = $cfg['logs_path'].'ranksystem.log';
$GLOBALS['pidfile'] = $cfg['logs_path'].'pid';
$GLOBALS['autostart'] = $cfg['logs_path'].'autostart_deactivated';
$GLOBALS['langpath'] = dirname(__DIR__).DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR;
if(!isset($cfg['default_language']) || $cfg['default_language'] == NULL) {
	$GLOBALS['default_language'] = 'en';
} else {
	$GLOBALS['default_language'] = $cfg['default_language'];
}
$GLOBALS['stylepath'] = dirname(__DIR__).DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR;
if(isset($cfg['default_style'])) $GLOBALS['style'] = get_style($cfg['default_style']);
$GLOBALS['avatarpath'] = dirname(__DIR__).DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR;

require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'configs'.DIRECTORY_SEPARATOR.'phpcommand.php');
$GLOBALS['phpcommand'] = $phpcommand;
?>
