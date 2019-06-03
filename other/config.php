<?PHP
require_once('dbconfig.php');

function set_language($language) {
	if(strtolower($language) == "ar") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_ar.php');
	} elseif(strtolower($language) == "cz") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_cz.php');
	} elseif(strtolower($language) == "de") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_de.php');
	} elseif(strtolower($language) == "es") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_es.php');
	} elseif(strtolower($language) == "fr") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_fr.php');
	} elseif(strtolower($language) == "it") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_it.php');
	} elseif(strtolower($language) == "nl") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_nl.php');
	} elseif(strtolower($language) == "pl") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_pl.php');
	} elseif(strtolower($language) == "ro") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_ro.php');
	} elseif(strtolower($language) == "ru") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_ru.php');
	} elseif(strtolower($language) == "pt") {
		include(substr(dirname(__FILE__),0,-5).'languages/core_pt.php');
	} else {
		include(substr(dirname(__FILE__),0,-5).'languages/core_en.php');
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

if(isset($_GET["lang"])) {
	$language = htmlspecialchars($_GET["lang"]);
	$lang = set_language($language);
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
		echo "Database Connection failed: ".$e->getMessage()."\n"; $err_lvl = 3;
		exit;
	}
}

if (isset($mysqlcon) && ($oldcfg = $mysqlcon->query("SELECT * FROM `$dbname`.`config`"))) {
	if(isset($oldcfg) && $oldcfg != NULL) {
		$config = $oldcfg->fetch();
		$cfg['teamspeak_host_address'] = $config['tshost'];
		$cfg['teamspeak_query_port'] = $config['tsquery'];
		$cfg['teamspeak_query_encrypt_switch'] = $config['tsencrypt'];
		$cfg['teamspeak_voice_port'] = $config['tsvoice'];
		$cfg['teamspeak_query_user'] = $config['tsuser'];
		$cfg['teamspeak_query_pass'] = $config['tspass'];
		$cfg['webinterface_user'] = $config['webuser'];
		$cfg['webinterface_pass'] = $config['webpass'];
		if(!isset($_GET["lang"])) {
			if(isset($_SESSION[$rspathhex.'language'])) {
				$cfg['default_language'] = $_SESSION[$rspathhex.'language'];
			} else {
				$cfg['default_language'] = $config['language'];
			}
		} elseif($_GET["lang"] == "ar") {
			$cfg['default_language'] = "ar";
			$_SESSION[$rspathhex.'language'] = "ar";
		} elseif($_GET["lang"] == "cz") {
			$cfg['default_language'] = "cz";
			$_SESSION[$rspathhex.'language'] = "cz";
		} elseif($_GET["lang"] == "de") {
			$cfg['default_language'] = "de";
			$_SESSION[$rspathhex.'language'] = "de";
		} elseif($_GET["lang"] == "fr") {
			$cfg['default_language'] = "fr";
			$_SESSION[$rspathhex.'language'] = "fr";
		} elseif($_GET["lang"] == "it") {
			$cfg['default_language'] = "it";
			$_SESSION[$rspathhex.'language'] = "it";
		} elseif($_GET["lang"] == "nl") {
			$cfg['default_language'] = "nl";
			$_SESSION[$rspathhex.'language'] = "nl";
		} elseif($_GET["lang"] == "pl") {
			$cfg['default_language'] = "pl";
			$_SESSION[$rspathhex.'language'] = "pl";
		} elseif($_GET["lang"] == "ro") {
			$cfg['default_language'] = "ro";
			$_SESSION[$rspathhex.'language'] = "ro";
		} elseif($_GET["lang"] == "ru") {
			$cfg['default_language'] = "ru";
			$_SESSION[$rspathhex.'language'] = "ru";
		} elseif($_GET["lang"] == "pt") {
			$cfg['default_language'] = "pt";
			$_SESSION[$rspathhex.'language'] = "pt";
		} else {
			$cfg['default_language'] = "en";
			$_SESSION[$rspathhex.'language'] = "en";
		}
		$lang = set_language($cfg['default_language']);
		$cfg['teamspeak_query_nickname'] = $config['queryname'];
		$cfg['teamspeak_query_command_delay'] = $config['slowmode'];
		if(empty($config['grouptime'])) {
			$cfg['rankup_definition'] = null;
		} else {
			$grouptimearr = explode(',', $config['grouptime']);
			foreach ($grouptimearr as $entry) {
				list($key, $value) = explode('=>', $entry);
				$addnewvalue1[$key] = $value;
				$cfg['rankup_definition'] = $addnewvalue1;
			}
		}
		if(empty($config['boost'])) {
			$cfg['rankup_boost_definition'] = null;
		} else {
			$boostexp = explode(',', $config['boost']);
			foreach ($boostexp as $entry) {
				list($key, $value1, $value2) = explode('=>', $entry);
				$addnewvalue2[$key] = array("group"=>$key,"factor"=>$value1,"time"=>$value2);
				$cfg['rankup_boost_definition'] = $addnewvalue2;
			}
		}
		$cfg['rankup_client_database_id_change_switch'] = $config['resetbydbchange'];
		$cfg['rankup_message_to_user_switch'] = $config['msgtouser'];
		$cfg['version_current_using'] = $config['currvers'];
		$cfg['rankup_time_assess_mode'] = $config['substridle'];
		$cfg['rankup_excepted_unique_client_id_list'] = array_flip(explode(',', $config['exceptuuid']));
		$cfg['rankup_excepted_group_id_list'] = array_flip(explode(',', $config['exceptgroup']));
		$cfg['rankup_excepted_channel_id_list'] = array_flip(explode(',', $config['exceptcid']));
		$cfg['default_date_format'] = $config['dateformat'];
		/* Required for ClientCleaner*/
		$cfg['simulate_mode'] = $config['simmosw'];
		$cfg['cc_slowmode'] = $config['ccslw'];
		$cfg['cc_query_nickname'] = $config['ccslw'];
		$cfg['cc_deletiontime'] = $config['ccdel'];
		/*Require End */
		$cfg['stats_show_excepted_clients_switch'] = $config['showexcld'];
		$cfg['stats_show_clients_in_highest_rank_switch'] = $config['showhighest'];
		$cfg['stats_column_rank_switch'] = $config['showcolrg'];
		$cfg['stats_column_client_name_switch'] = $config['showcolcld'];
		$cfg['stats_column_unique_id_switch'] = $config['showcoluuid'];
		$cfg['stats_column_client_db_id_switch'] = $config['showcoldbid'];
		$cfg['stats_column_last_seen_switch'] = $config['showcolls'];
		$cfg['stats_column_online_time_switch'] = $config['showcolot'];
		$cfg['stats_column_idle_time_switch'] = $config['showcolit'];
		$cfg['stats_column_active_time_switch'] = $config['showcolat'];
		$cfg['stats_column_current_server_group_switch'] = $config['showcolas'];
		$cfg['stats_column_next_rankup_switch'] = $config['showcolnx'];
		$cfg['stats_column_next_server_group_switch'] = $config['showcolsg'];
		$cfg['rankup_clean_clients_switch'] = $config['cleanclients'];
		$cfg['rankup_clean_clients_period'] = $config['cleanperiod'];
		$cfg['teamspeak_default_channel_id'] = $config['defchid'];
		$cfg['logs_path'] = $config['logpath'];
		if ($config['timezone'] == NULL) {
			$cfg['logs_timezone'] = "Europe/Berlin";
		} else {
			$cfg['logs_timezone'] = $config['timezone'];
		}
		date_default_timezone_set($cfg['logs_timezone']);
		$cfg['webinterface_access_count'] = $config['count_access'];
		$cfg['webinterface_access_last'] = $config['last_access'];
		$cfg['rankup_ignore_idle_time'] = $config['ignoreidle'];
		$cfg['rankup_message_to_user'] = $config['rankupmsg'];
		$cfg['version_latest_available'] = $config['newversion'];
		$cfg['stats_server_news'] = $config['servernews'];
		if(empty($config['adminuuid'])) {
			$cfg['webinterface_admin_client_unique_id_list'] = NULL;
		} else {
			$cfg['webinterface_admin_client_unique_id_list'] = explode(',', $config['adminuuid']);
		}
		$cfg['rankup_next_message_mode'] = $config['nextupinfo'];
		$cfg['rankup_next_message_1'] = $config['nextupinfomsg1'];
		$cfg['rankup_next_message_2'] = $config['nextupinfomsg2'];
		$cfg['rankup_next_message_3'] = $config['nextupinfomsg3'];
		$cfg['stats_show_site_navigation_switch'] = $config['shownav'];
		$cfg['stats_column_current_group_since_switch'] = $config['showgrpsince'];
		$cfg['rankup_excepted_mode'] = $config['resetexcept'];
		$cfg['version_update_channel'] = $config['upchannel'];
		$cfg['teamspeak_avatar_download_delay'] = $config['avatar_delay'];
		$cfg['teamspeak_verification_channel_id'] = $config['registercid'];
		$cfg['rankup_hash_ip_addresses_mode'] = $config['iphash'];
		unset($addnewvalue1, $addnewvalue2, $oldcfd, $config);
	}
} elseif(!isset($_GET["lang"])) {
	$lang = set_language("en");
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
				list($key, $value) = explode('=>', $entry);
				$addnewvalue1[$key] = $value;
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
		if(!isset($_GET["lang"])) {
			if(isset($_SESSION[$rspathhex.'language'])) {
				$cfg['default_language'] = $_SESSION[$rspathhex.'language'];
			}
		} elseif($_GET["lang"] == "ar") {
			$cfg['default_language'] = "ar";
			$_SESSION[$rspathhex.'language'] = "ar";
		} elseif($_GET["lang"] == "cz") {
			$cfg['default_language'] = "cz";
			$_SESSION[$rspathhex.'language'] = "cz";
		} elseif($_GET["lang"] == "de") {
			$cfg['default_language'] = "de";
			$_SESSION[$rspathhex.'language'] = "de";
		} elseif($_GET["lang"] == "es") {
			$cfg['default_language'] = "es";
			$_SESSION[$rspathhex.'language'] = "es";
		}  elseif($_GET["lang"] == "fr") {
			$cfg['default_language'] = "fr";
			$_SESSION[$rspathhex.'language'] = "fr";
		} elseif($_GET["lang"] == "it") {
			$cfg['default_language'] = "it";
			$_SESSION[$rspathhex.'language'] = "it";
		} elseif($_GET["lang"] == "nl") {
			$cfg['default_language'] = "nl";
			$_SESSION[$rspathhex.'language'] = "nl";
		} elseif($_GET["lang"] == "pl") {
			$cfg['default_language'] = "pl";
			$_SESSION[$rspathhex.'language'] = "pl";
		} elseif($_GET["lang"] == "ro") {
			$cfg['default_language'] = "ro";
			$_SESSION[$rspathhex.'language'] = "ro";
		} elseif($_GET["lang"] == "ru") {
			$cfg['default_language'] = "ru";
			$_SESSION[$rspathhex.'language'] = "ru";
		} elseif($_GET["lang"] == "pt") {
			$cfg['default_language'] = "pt";
			$_SESSION[$rspathhex.'language'] = "pt";
		} else {
			$cfg['default_language'] = "en";
			$_SESSION[$rspathhex.'language'] = "en";
		}
		$lang = set_language($cfg['default_language']);
		unset($addnewvalue1, $addnewvalue2, $newcfd);
	}
} elseif(!isset($_GET["lang"])) {
	$lang = set_language("en");
}
?>
