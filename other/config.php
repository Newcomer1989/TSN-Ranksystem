<?PHP
error_reporting(E_ERROR | E_PARSE);
require_once('dbconfig.php');

function set_language($language) {
	if(is_dir(substr(__DIR__,0,-5).'languages/')) {
		foreach(scandir(substr(__DIR__,0,-5).'languages/') as $file) {
			if ('.' === $file || '..' === $file || is_dir($file)) continue;
			$sep_lang = preg_split("/[._]/", $file);
			if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
				if(strtolower($language) == strtolower($sep_lang[1])) {
					include(substr(__DIR__,0,-5).'languages/core_'.$sep_lang[1].'_'.$sep_lang[2].'_'.$sep_lang[3].'.'.$sep_lang[4]);
					$required_lang = 1;
					break;
				}
			}
		}
	}
	if(!isset($required_lang)) {
		include('../languages/core_en_english_gb.php');
	}
	return $lang;
}

function rem_session_ts3($rspathhex) {
	unset($_SESSION[$rspathhex.'admin']);
	unset($_SESSION[$rspathhex.'clientip']);
	unset($_SESSION[$rspathhex.'connected']);
	unset($_SESSION[$rspathhex.'inactivefilter']);
	unset($_SESSION[$rspathhex.'language']);
	unset($_SESSION[$rspathhex.'logfilter']);
	unset($_SESSION[$rspathhex.'logfilter2']);
	unset($_SESSION[$rspathhex.'multiple']);
	unset($_SESSION[$rspathhex.'newversion']);
	unset($_SESSION[$rspathhex.'number_lines']);
	unset($_SESSION[$rspathhex.'password']);
	unset($_SESSION[$rspathhex.'serverport']);
	unset($_SESSION[$rspathhex.'temp_cldbid']);
	unset($_SESSION[$rspathhex.'temp_name']);
	unset($_SESSION[$rspathhex.'temp_uuid']);
	unset($_SESSION[$rspathhex.'token']);
	unset($_SESSION[$rspathhex.'tsavatar']);
	unset($_SESSION[$rspathhex.'tscldbid']);
	unset($_SESSION[$rspathhex.'tsconnections']);
	unset($_SESSION[$rspathhex.'tscreated']);
	unset($_SESSION[$rspathhex.'tsname']);
	unset($_SESSION[$rspathhex.'tsuid']);
	unset($_SESSION[$rspathhex.'upinfomsg']);
	unset($_SESSION[$rspathhex.'username']);
	unset($_SESSION[$rspathhex.'uuid_verified']);
}

$rspathhex = 'rs_'.dechex(crc32(__DIR__)).'_';

if(isset($db['type']) === false) {
	$db['type']="mysql";
}
$dbname = $db['dbname'];
$dbtype = $db['type'];
if($db['type'] != "type") {
	$dbserver  = $db['type'].':host='.$db['host'].';dbname='.$dbname.';charset=utf8mb4';
	if ($db['type'] == 'mysql') {
		$dboptions = array(
			PDO::ATTR_PERSISTENT => true
		);
	} else {
		$dboptions = array();
	}
	try {
		$mysqlcon = new PDO($dbserver, $db['user'], $db['pass'], $dboptions);
	} catch (PDOException $e) {
		echo 'Database Connection failed: <b>'.$e->getMessage().'</b><br><br>Check:<br>- You have already installed the Ranksystem? Run <a href="../install.php">install.php</a> first!<br>- Is the database reachable?<br>- You have installed all needed PHP extenstions? Have a look here for <a href="//ts-ranksystem.com/#windows">Windows</a> or <a href="//ts-ranksystem.com/#linux">Linux</a>?'; $err_lvl = 3;
		exit;
	}
}

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
				list($time, $group, $keepflag) = explode('=>', $entry);
				if($keepflag == NULL) $keepflag = 0;
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
		if(!isset($_GET["lang"])) {
			if(isset($_SESSION[$rspathhex.'language'])) {
				$cfg['default_language'] = $_SESSION[$rspathhex.'language'];
			}
		} else {
			if(is_dir(substr(__DIR__,0,-5).'languages/')) {
				foreach(scandir(substr(__DIR__,0,-5).'languages/') as $file) {
					if ('.' === $file || '..' === $file || is_dir($file)) continue;
					$sep_lang = preg_split("/[._]/", $file);
					if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
						if(strtolower($_GET["lang"]) == strtolower($sep_lang[1])) {
							$cfg['default_language'] = $sep_lang[1];
							$_SESSION[$rspathhex.'language'] = $sep_lang[1];
							$required_lang = 1;
							break;
						}
					}
				}
			}
			if(!isset($required_lang)) {
				$cfg['default_language'] = "en";
				$_SESSION[$rspathhex.'language'] = "en";
			}
		}
		if(isset($cfg['default_language'])) {
			$lang = set_language($cfg['default_language']);
		} else {
			$lang = set_language("en");
		}
		unset($addnewvalue1, $addnewvalue2, $newcfg);
	}
} elseif(!isset($_GET["lang"])) {
	$lang = set_language("en");
}

if(empty($cfg['logs_debug_level'])) {
	$cfg['logs_debug_level'] = "5";
}
if(empty($cfg['logs_rotation_size'])) {
	$cfg['logs_rotation_size'] = "5";
}
?>