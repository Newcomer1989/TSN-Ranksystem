#!/usr/bin/php
<?PHP
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');
if(ini_get('max_execution_time') !== NULL) {
	$max_execution_time = ini_get('max_execution_time');
} else {
	$max_execution_time = "{none set}";
}
if(ini_get('memory_limit') !== NULL) {
	$memory_limit = ini_get('memory_limit');
} else {
	$memory_limit = "{none set}";
}
set_time_limit(0);
error_reporting(E_ALL);
ini_set("log_errors", 1);

require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'other/_functions.php');
$persistent = 1;
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'other/config.php');
$lang = set_language(get_language());

set_error_handler("php_error_handling");
ini_set("error_log", $GLOBALS['logfile']);

if((isset($_SERVER['HTTP_HOST']) || isset($_SERVER['REMOTE_ADDR'])) && isset($cfg['default_cmdline_sec_switch']) && $cfg['default_cmdline_sec_switch'] == 1) {
	shutdown($mysqlcon,3,"Request to start the Ranksystem from ".$_SERVER['REMOTE_ADDR'].". It seems the request came not from the command line!",FALSE);
}
if(version_compare(PHP_VERSION, '5.5.0', '<')) {
	shutdown($mysqlcon,1,"Your PHP version (".PHP_VERSION.") is below 5.5.0. Update of PHP is required!");
}
if(!function_exists('simplexml_load_file')) {
	shutdown($mysqlcon,1,sprintf($lang['errphp'],'PHP SimpleXML'));
}
if(!in_array('curl', get_loaded_extensions())) {
	shutdown($mysqlcon,1,sprintf($lang['errphp'],'PHP cURL'));
}
if(!in_array('zip', get_loaded_extensions())) {
	shutdown($mysqlcon,1,sprintf($lang['errphp'],'PHP Zip'));
}
if(!in_array('mbstring', get_loaded_extensions())) {
	shutdown($mysqlcon,1,sprintf($lang['errphp'],'PHP mbstring'));
}


enter_logfile(9,"");
enter_logfile(9,"###################################################################");
enter_logfile(9,"Initialize Bot...");
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs/ts3_lib/TeamSpeak3.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/calc_user.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/get_avatars.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/update_channel.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/update_groups.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/calc_serverstats.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/server_usage.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/calc_user_snapshot.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/calc_userstats.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/clean.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/check_db.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/handle_messages.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/event_userenter.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/update_rs.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/reset_rs.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/db_ex_imp.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs/smarty/libs/Smarty.class.php');

enter_logfile(9,"Running on OS: ".php_uname("s")." ".php_uname("r"));
enter_logfile(9,"Installation Path: ".__DIR__);
enter_logfile(9,"Using PHP Version: ".PHP_VERSION);
if(version_compare(PHP_VERSION, '8.0.0', '<')) {
	enter_logfile(3,"Your PHP Version: (".PHP_VERSION.") is outdated and no longer supported. Please update it!");
}
enter_logfile(9,"Database Version: ".$mysqlcon->getAttribute(PDO::ATTR_SERVER_VERSION));

enter_logfile(9,"Starting connection test to the Ranksystem update-server (may need a few seconds)...");
$update_server = fsockopen('193.70.102.252', 443, $errno, $errstr, 10);
if(!$update_server) {
	enter_logfile(2,"  Connection to Ranksystem update-server failed: $errstr ($errno)");
	enter_logfile(3,"    This connection is neccessary to receive updates for the Ranksystem!");
	enter_logfile(3,"    Please whitelist the IP 193.70.102.252 (TCP port 443) on your network (firewall)");
} else {
	enter_logfile(9,"  Connection test successful");
}
enter_logfile(9,"Starting connection test to the Ranksystem update-server [done]");

$cfg['temp_updatedone'] = check_db($mysqlcon,$lang,$cfg,$dbname);
$cfg['temp_db_version'] = $mysqlcon->getAttribute(PDO::ATTR_SERVER_VERSION);
$cfg['temp_last_botstart'] = time();
$cfg['temp_reconnect_attempts'] = 0;
$cfg['temp_ts_no_reconnection'] = 0;
enter_logfile(4,"Check Ranksystem files for updates...");
if(isset($cfg['version_current_using']) && isset($cfg['version_latest_available']) && $cfg['version_latest_available'] != NULL && version_compare($cfg['version_latest_available'], $cfg['version_current_using'], '>')) {
	update_rs($mysqlcon,$lang,$cfg,$dbname);
}
enter_logfile(4,"Check Ranksystem files for updates [done]");
enter_logfile(9,"Ranksystem Version: ".$cfg['version_current_using']." (on Update-Channel: ".$cfg['version_update_channel'].")");
enter_logfile(4,"Loading addons...");
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'other/load_addons_config.php');
$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);
	if($addons_config['assign_groups_active']['value'] == '1') {
		enter_logfile(4,"  Addon: 'assign_groups' [ON]");
		include(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/addon_assign_groups.php');
		$cfg['temp_addon_assign_groups'] = "enabled";
	} else {
		enter_logfile(4,"  Addon: 'assign_groups' [OFF]");
		$cfg['temp_addon_assign_groups'] = "disabled";
	}
	if($addons_config['channelinfo_toplist_active']['value'] == '1') {
		enter_logfile(4,"  Addon: 'channelinfo_toplist' [ON]");
		include(dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs/addon_channelinfo_toplist.php');
		$cfg['temp_addon_channelinfo_toplist'] = "enabled";
	} else {
		enter_logfile(4,"  Addon: 'channelinfo_toplist' [OFF]");
		$cfg['temp_addon_channelinfo_toplist'] = "disabled";
	}
enter_logfile(4,"Loading addons [done]");

function run_bot(&$cfg) {
	global $mysqlcon, $db, $dbname, $dbtype, $lang, $phpcommand, $addons_config, $max_execution_time, $memory_limit, $ts3server;

	enter_logfile(9,"Connect to TS3 Server (Address: '".$cfg['teamspeak_host_address']."' Voice-Port: '".$cfg['teamspeak_voice_port']."' Query-Port: '".$cfg['teamspeak_query_port']."' SSH: '".$cfg['teamspeak_query_encrypt_switch']."' Query-Slowmode: '".number_format(($cfg['teamspeak_query_command_delay']/1000000),1)."').");

	try {
		if($cfg['temp_ts_no_reconnection'] != 1) {
			if($cfg['teamspeak_query_encrypt_switch'] == 1) {
				$ts3host = TeamSpeak3::factory("serverquery://".rawurlencode($cfg['teamspeak_query_user']).":".rawurlencode($cfg['teamspeak_query_pass'])."@".$cfg['teamspeak_host_address'].":".$cfg['teamspeak_query_port']."/?ssh=1");
			} else {
				$ts3host = TeamSpeak3::factory("serverquery://".rawurlencode($cfg['teamspeak_query_user']).":".rawurlencode($cfg['teamspeak_query_pass'])."@".$cfg['teamspeak_host_address'].":".$cfg['teamspeak_query_port']."/?blocking=0");
			}

			enter_logfile(9,"Connection to TS3 Server established.");
			try{
				$ts3version = $ts3host->version();
				enter_logfile(5,"  TS3 Server version: ".$ts3version['version']." on ".$ts3version['platform']." [Build: ".$ts3version['build']." from ".date("Y-m-d H:i:s",$ts3version['build'])."]");
				$cfg['temp_ts_version'] = $ts3version['version'];
			} catch (Exception $e) {
				enter_logfile(2,"  Error due getting TS3 server version - ".$e->getCode().': '.$e->getMessage());
			}
			
			if(version_compare($ts3version['version'],'3.13.7','<') && version_compare($ts3version['version'],'3.0.0','>=')) {
				enter_logfile(3,"      Your TS3 server is outdated, please update it!");
			}

			enter_logfile(9,"    Select virtual server...");
			try {
				if(version_compare($ts3version['version'],'3.4.0','>=') || version_compare($ts3version['version'],'3.0.0','<=')) {
					usleep($cfg['teamspeak_query_command_delay']);
					$ts3server = $ts3host->serverGetByPort($cfg['teamspeak_voice_port'], $cfg['teamspeak_query_nickname']);
				} else {
					usleep($cfg['teamspeak_query_command_delay']);
					$ts3server = $ts3host->serverGetByPort($cfg['teamspeak_voice_port']);
					for ($updcld = 0; $updcld < 10; $updcld++) {
						try {
							usleep($cfg['teamspeak_query_command_delay']);
							if($updcld == 0) {
								$ts3server->selfUpdate(array('client_nickname' => $cfg['teamspeak_query_nickname']));
							} else {
								$ts3server->selfUpdate(array('client_nickname' => $cfg['teamspeak_query_nickname'].$updcld));
							}
							break;
						} catch (Exception $e) {
							enter_logfile(3,'      '.$lang['errorts3'].$e->getCode().': '.$e->getMessage());
							shutdown($mysqlcon,1,"Critical TS3 error on core function!");
						}
					}
				}
				enter_logfile(9,"    Select virtual server [done]");
				$cfg['temp_reconnect_attempts'] = 0;
			} catch (Exception $e) {
				enter_logfile(2,"  Error due selecting virtual server - ".$e->getCode().': '.$e->getMessage()." (bad Voice-Port or Bot name?)");
			}

			try {
				usleep($cfg['teamspeak_query_command_delay']);
				$ts3server->notifyRegister("server");
				$ts3server->notifyRegister("textprivate");
				$ts3server->notifyRegister("textchannel");
				$ts3server->notifyRegister("textserver");
				TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "handle_messages");
				TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyCliententerview", "event_userenter");
			} catch (Exception $e) {
				enter_logfile(2,"  Error due notifyRegister on TS3 server - ".$e->getCode().': '.$e->getMessage());
			}

			$whoami = $ts3server->whoami();
			if(isset($cfg['teamspeak_default_channel_id']) && $cfg['teamspeak_default_channel_id'] != 0 && $cfg['teamspeak_default_channel_id'] != '') {
				try {
					usleep($cfg['teamspeak_query_command_delay']);
					$ts3server->clientMove($whoami['client_id'],$cfg['teamspeak_default_channel_id']);
					enter_logfile(5,"  Joined to specified TS channel with channel-ID ".$cfg['teamspeak_default_channel_id'].".");
				} catch (Exception $e) {
					if($e->getCode() != 770) {
						enter_logfile(2,"  Could not join specified TS channel (channel-ID: ".$cfg['teamspeak_default_channel_id'].") - ".$e->getCode().': '.$e->getMessage());
					} else {
						enter_logfile(5,"  Joined to specified TS channel with channel-ID ".$cfg['teamspeak_default_channel_id']." (already member of it).");
					}
				}
			} else {
				enter_logfile(5,"  No channel defined where the Ranksystem Bot should be entered.");
			}
			
			if($cfg['temp_updatedone'] === TRUE) {
				enter_logfile(4,$lang['upinf']);
				if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
					foreach(array_flip($cfg['webinterface_admin_client_unique_id_list']) as $clientid) {
						usleep($cfg['teamspeak_query_command_delay']);
						try {
							if(isset($cfg['teamspeak_news_bb']) && $cfg['teamspeak_news_bb'] != '') {
								sendmessage($ts3server, $cfg, $clientid, sprintf($lang['upmsg2'], $cfg['version_current_using'], 'https://ts-ranksystem.com/#changelog')."\n\n[U]Latest News:[/U]\n".$cfg['teamspeak_news_bb'], 1, NULL, sprintf($lang['upusrerr'], $clientid), 6, sprintf($lang['upusrinf'], $clientid));
							} else {
								sendmessage($ts3server, $cfg, $clientid, sprintf($lang['upmsg2'], $cfg['version_current_using'], 'https://ts-ranksystem.com/#changelog'), 1, NULL, sprintf($lang['upusrerr'], $clientid), 6, sprintf($lang['upusrinf'], $clientid));
							}
							enter_logfile(4,"  ".sprintf($lang['upusrinf'], $clientid));
						} catch (Exception $e) {
							enter_logfile(6,"  ".sprintf($lang['upusrerr'], $clientid));
						}
					}
				}
			}

			enter_logfile(9,"Config check started...");
			switch ($cfg['logs_debug_level']) {
				case 1:
					$loglevel = "1 - CRITICAL";
					break;
				case 2:
					$loglevel = "2 - ERROR";
					break;
				case 3:
					$loglevel = "3 - WARNING";
					break;
				case 4:
					$loglevel = "4 - NOTICE";
					break;
				case 5:
					$loglevel = "5 - INFO";
					break;
				case 6:
					$loglevel = "6 - DEBUG";
					break;
				default:
					$loglevel = "UNKNOWN";
			}
			enter_logfile(9,"  Log Level: ".$loglevel);
			enter_logfile(6,"  Serverside config 'max_execution_time' (PHP.ini): ".$max_execution_time." sec.");
			enter_logfile(6,"  Serverside config 'memory_limit' (PHP.ini): ".$memory_limit);
			krsort($cfg['rankup_definition']);

			if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
				enter_logfile(1,"  Select on DB failed for group check: ".print_r($mysqlcon->errorInfo(), true));
			}
			
			$checkgroups = 0;
			if(isset($groupslist) && $groupslist != NULL) {
				if(isset($cfg['rankup_definition']) && $cfg['rankup_definition'] != NULL) {
					foreach($cfg['rankup_definition'] as $rank) {
						if(!isset($groupslist[$rank['group']]) && $rank['group'] != NULL) {
							$checkgroups++;
						}
					}
				}
				if(isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != NULL) {
					foreach($cfg['rankup_boost_definition'] as $groupid => $value) {
						if(!isset($groupslist[$groupid]) && $groupid != NULL) {
							$checkgroups++;
						}
					}
				}
				if(isset($cfg['rankup_excepted_group_id_list']) && $cfg['rankup_excepted_group_id_list'] != NULL) {
					foreach($cfg['rankup_excepted_group_id_list'] as $groupid => $value) {
						if(!isset($groupslist[$groupid]) && $groupid != NULL) {
							$checkgroups++;
						}
					}
				}
			}
			if($checkgroups > 0) {
				enter_logfile(3,"  Found servergroups in config, which are unknown. Redownload all servergroups from TS3 server.");
				if($mysqlcon->exec("DELETE FROM `$dbname`.`groups`;") === false) {
					enter_logfile(2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}

				$serverinfo = $ts3server->serverInfo();
				$select_arr = array();
				$db_cache = array();
				$sqlexec2 = update_groups($ts3server,$mysqlcon,$lang,$cfg,$dbname,$serverinfo,$db_cache,1);
				if($sqlexec2 != NULL && $mysqlcon->exec($sqlexec2) === false) {
					enter_logfile(2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
				unset($sqlexec2,$select_arr,$db_cache,$groupslist,$serverinfo,$ts3version);
				$errcnf = 0;
				enter_logfile(4,"  Downloading of servergroups finished. Recheck the config.");
				
				if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
					enter_logfile(1,"  Select on DB failed for group check: ".print_r($mysqlcon->errorInfo(), true));
				}
				
				if(isset($groupslist) && $groupslist != NULL) {
					if(isset($cfg['rankup_definition']) && $cfg['rankup_definition'] != NULL) {
						foreach($cfg['rankup_definition'] as $rank) {
							if(!isset($groupslist[$rank['group']]) && $rank['group'] != NULL) {
								enter_logfile(1,'    '.sprintf($lang['upgrp0001'], $rank['group'], $lang['wigrptime']));
								$errcnf++;
							}
						}
					}
					if(isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != NULL) {
						foreach($cfg['rankup_boost_definition'] as $groupid => $value) {
							if(!isset($groupslist[$groupid]) && $groupid != NULL) {
								enter_logfile(2,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wiboost']));
							}
						}
					}
					if(isset($cfg['rankup_excepted_group_id_list']) && $cfg['rankup_excepted_group_id_list'] != NULL) {
						foreach($cfg['rankup_excepted_group_id_list'] as $groupid => $value) {
							if(!isset($groupslist[$groupid]) && $groupid != NULL) {
								enter_logfile(2,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wiexgrp']));
							}
						}
					}
				}
				if($errcnf > 0) {
					shutdown($mysqlcon,1,"Critical Config error!");
				} else {
					enter_logfile(4,"  No critical problems found! All seems to be fine...");
				}
			}

			if(isset($cfg['webinterface_fresh_installation']) && $cfg['webinterface_fresh_installation'] == 1) {
				if($mysqlcon->exec("UPDATE `$dbname`.`cfg_params` SET `value`=0 WHERE `param`='webinterface_fresh_installation'") === false) {
					enter_logfile(2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
			}

			unset($groupslist,$errcnf,$checkgroups,$lastupdate,$updcld,$loglevel,$whoami,$ts3host,$max_execution_time,$memory_limit,$memory_limit);
			enter_logfile(9,"Config check [done]");
		} else {
			enter_logfile(9,"  Try to use the restored TS3 connection");
		}

		enter_logfile(9,"Bot starts now his work!");
		$looptime = $cfg['temp_count_laps'] = $cfg['temp_whole_laptime'] = $cfg['temp_count_laptime'] = 0; $cfg['temp_last_laptime'] = '';
		usleep(3000000);

		if(($get_db_data = $mysqlcon->query("SELECT * FROM `$dbname`.`user`; SELECT MAX(`timestamp`) AS `timestamp` FROM `$dbname`.`server_usage`; SELECT * FROM `$dbname`.`job_check`; SELECT * FROM `$dbname`.`groups`; SELECT * FROM `$dbname`.`channel`; SELECT * FROM `$dbname`.`addon_assign_groups`; SELECT * FROM `$dbname`.`admin_addtime`; SELECT * FROM `$dbname`.`admin_mrgclient`; ")) === false) {
			shutdown($mysqlcon,1,"Select on DB failed: ".print_r($mysqlcon->errorInfo(), true));
		}

		$count_select = 0;
		$db_cache = array();
		while($single_select = $get_db_data) {
			$fetched_array = $single_select->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
			$count_select++;

			switch ($count_select) {
				case 1:
					$db_cache['all_user'] = $fetched_array;
					break;
				case 2:
					$db_cache['max_timestamp_server_usage'] = $fetched_array;
					break;
				case 3:
					$db_cache['job_check'] = $fetched_array; 
					break;
				case 4:
					$db_cache['groups'] = $fetched_array;
					break;
				case 5:
					$db_cache['channel'] = $fetched_array;
					break;
				case 6:
					$db_cache['addon_assign_groups'] = $fetched_array;
					break;
				case 7:
					$db_cache['admin_addtime'] = $fetched_array;
					break;
				case 8:
					$db_cache['admin_mrgclient'] = $fetched_array;
				break 2;
			}
			$get_db_data->nextRowset();
		}
		unset($get_db_data,$fetched_array,$single_select);

		$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);

		while(1) {
			$sqlexec = '';
			$starttime = microtime(true);

			unset($db_cache['job_check']);
			if(($db_cache['job_check'] = $mysqlcon->query("SELECT * FROM `$dbname`.`job_check`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
				enter_logfile(3,"  Select on DB failed for job check: ".print_r($mysqlcon->errorInfo(), true));
			}
			
			if(intval($db_cache['job_check']['reload_trigger']['timestamp']) == 1) {
				unset($db_cache['addon_assign_groups'],$db_cache['admin_addtime'],$db_cache['admin_mrgclient']);
				if(($get_db_data = $mysqlcon->query("SELECT * FROM `$dbname`.`addon_assign_groups`; SELECT * FROM `$dbname`.`admin_addtime`; SELECT * FROM `$dbname`.`admin_mrgclient`; SELECT * FROM `$dbname`.`groups`; SELECT * FROM `$dbname`.`channel`;")) === false) {
					shutdown($mysqlcon,1,"Select on DB failed: ".print_r($mysqlcon->errorInfo(), true));
				}

				$count_select = 0;
				while($single_select = $get_db_data) {
					$fetched_array = $single_select->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
					$count_select++;

					switch ($count_select) {
						case 1:
							$db_cache['addon_assign_groups'] = $fetched_array;
							break;
						case 2:
							$db_cache['admin_addtime'] = $fetched_array;
							break;
						case 3:
							$db_cache['admin_mrgclient'] = $fetched_array;
							break;
						case 4:
							$db_cache['groups'] = $fetched_array;
							break;
						case 5:
							$db_cache['channel'] = $fetched_array;
						break 2;
					}
					$get_db_data->nextRowset();
				}
				unset($get_db_data,$fetched_array,$single_select,$count_select);
				$db_cache['job_check']['reload_trigger']['timestamp'] = 0;
				$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=0 WHERE `job_name`='reload_trigger';\n";
			}

			enter_logfile(6,"SQL Select needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));

			check_shutdown();

			$ts3server->clientListReset();
			$allclients = $ts3server->clientListtsn("-uid -groups -times -info -country");
			usleep($cfg['teamspeak_query_command_delay']);
			$ts3server->serverInfoReset();
			$serverinfo = $ts3server->serverInfo();
			usleep($cfg['teamspeak_query_command_delay']);

			$sqlexec .= calc_user($ts3server,$mysqlcon,$lang,$cfg,$dbname,$allclients,$phpcommand,$db_cache);
			$sqlexec .= calc_userstats($ts3server,$mysqlcon,$cfg,$dbname,$db_cache);
			$sqlexec .= get_avatars($ts3server,$cfg,$dbname,$db_cache);
			$sqlexec .= clean($ts3server,$mysqlcon,$lang,$cfg,$dbname,$db_cache);
			$sqlexec .= calc_serverstats($ts3server,$mysqlcon,$cfg,$dbname,$dbtype,$serverinfo,$db_cache,$phpcommand,$lang);
			$sqlexec .= server_usage($mysqlcon,$cfg,$dbname,$serverinfo,$db_cache);
			$sqlexec .= calc_user_snapshot($cfg,$dbname,$db_cache);
			$sqlexec .= update_groups($ts3server,$mysqlcon,$lang,$cfg,$dbname,$serverinfo,$db_cache);
			$sqlexec .= update_channel($ts3server,$mysqlcon,$lang,$cfg,$dbname,$serverinfo,$db_cache);

			if($addons_config['assign_groups_active']['value'] == '1') {
				$sqlexec .= addon_assign_groups($addons_config,$ts3server,$cfg,$dbname,$allclients,$db_cache);
			}
			if($addons_config['channelinfo_toplist_active']['value'] == '1') {
				$sqlexec .= addon_channelinfo_toplist($addons_config,$ts3server,$mysqlcon,$cfg,$dbname,$lang,$db_cache);
			}

			$startsql = microtime(true);
			if($cfg['logs_debug_level'] > 5) {
				$sqlexec = substr($sqlexec, 0, -1);
				$sqlarr = explode(";\n", $sqlexec);
				foreach($sqlarr as $singlesql) {
					if(strpos($singlesql, 'UPDATE') !== false || strpos($singlesql, 'INSERT') !== false || strpos($singlesql, 'DELETE') !== false || strpos($singlesql, 'SET') !== false) {
						if($mysqlcon->exec($singlesql) === false) {
							enter_logfile(4,"Executing SQL: ".$singlesql);
							enter_logfile(2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
						}
					} elseif(strpos($singlesql, ' ') === false) {
						enter_logfile(2,"Command not recognized as SQL: ".$singlesql);
					}
				}
				$sqlfile = $GLOBALS['logpath'].'temp_sql_dump.sql';
				$sqldump = fopen($sqlfile, 'wa+');
				fwrite($sqldump, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ")." SQL:\n".$sqlexec);
				fclose($sqldump);
			} else {
				if($mysqlcon->exec($sqlexec) === false) {
					enter_logfile(2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
			}
			enter_logfile(6,"SQL executions needs: ".(number_format(round((microtime(true) - $startsql), 5),5)));

			reset_rs($ts3server,$mysqlcon,$lang,$cfg,$dbname,$phpcommand,$db_cache);
			db_ex_imp($ts3server,$mysqlcon,$lang,$cfg,$dbname,$db_cache);

			unset($sqlexec,$select_arr,$sqldump);

			$looptime = microtime(true) - $starttime;
			$cfg['temp_whole_laptime'] = $cfg['temp_whole_laptime'] + $looptime;
			$cfg['temp_count_laptime']++;
			$cfg['temp_last_laptime'] = substr((number_format(round($looptime, 5),5) . ';' . $cfg['temp_last_laptime']),0,79);

			enter_logfile(6,"last loop: ".round($looptime, 5)." sec.");

			if($looptime < 1) {
				$loopsleep = (1 - $looptime);
				if($cfg['teamspeak_query_encrypt_switch'] == 1 || version_compare($cfg['temp_ts_version'],'1.4.0','>=') && version_compare($cfg['temp_ts_version'],'2.9.9','<=')) {
					// no wait for data to become available on the stream on SSH due issues with non-blocking mode or TeaSpeak
					usleep(round($loopsleep * 1000000));
				} else {
					$ts3server->getAdapter()->waittsn($loopsleep, 50000);  // 50ms delay for CPU reason
				}
			}
		}
	} catch (Exception $e) {
		enter_logfile(2,$lang['error'].': ['.$e->getCode().']: '.$e->getMessage());
		if(in_array($e->getCode(), array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793))) {
			if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
				enter_logfile(2,$lang['error'].print_r($mysqlcon->errorInfo(), true));
			}
		}

		if($cfg['temp_last_botstart'] < (time() - 10)) {
			if($cfg['temp_reconnect_attempts'] < 4) {
				$wait_reconnect = 5;
			} elseif($cfg['temp_reconnect_attempts'] < 10) {
				$wait_reconnect = 60;
			} elseif($cfg['temp_reconnect_attempts'] < 20) {
				$wait_reconnect = 300;
			} elseif($cfg['temp_reconnect_attempts'] < 66) {
				$wait_reconnect = 1800;
			} elseif($cfg['temp_reconnect_attempts'] < 288) {
				$wait_reconnect = 3600;
			} else {
				$wait_reconnect = 43200;
			}

			enter_logfile(4,"Try to reconnect in ".$wait_reconnect." seconds.");

			for($z = 1; $z <= $wait_reconnect; $z++) {
				sleep(1);
				check_shutdown();
			}

			$check_db_arr = array_flip(array('HY000',10054,70100));
			if(isset($check_db_arr[$e->getCode()])) {
				$cfg['temp_ts_no_reconnection'] = 1;
				try {
					$mysqlcon = db_connect($db['type'], $db['host'], $db['dbname'], $db['user'], $db['pass'], "no_exit");
					enter_logfile(9,"Connection to database restored");
				} catch (Exception $e) {
					enter_logfile(2,$lang['error'].print_r($mysqlcon->errorInfo(), true));
				}
			} else {
				$cfg['temp_ts_no_reconnection'] = 0;
			}

			$cfg['temp_reconnect_attempts']++;
			return $ts3server;
		} else {
			shutdown($mysqlcon,1,"Critical TS3 error on core function!");
		}
	}
}

while(1) {
	run_bot($cfg);
}
?>