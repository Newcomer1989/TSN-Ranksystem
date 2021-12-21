<?PHP
require_once('dbconfig.php');

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
		if (!isset($cfg['logs_timezone']) || $cfg['logs_timezone'] == NULL) {
			$cfg['logs_timezone'] = "Europe/Berlin";
		}
		date_default_timezone_set($cfg['logs_timezone']);

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
				list($key, $value) = explode('=>', $entry);
				$addnewvalue3[$key] = $value;
				$cfg['stats_api_keys'] = $addnewvalue3;
			}
		}
		unset($addnewvalue1, $addnewvalue2, $newcfg);
	}
}

if(empty($cfg['logs_debug_level'])) {
	$cfg['logs_debug_level'] = "5";
}
if(empty($cfg['logs_rotation_size'])) {
	$cfg['logs_rotation_size'] = "5";
}
?>