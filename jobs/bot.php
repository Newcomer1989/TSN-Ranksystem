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
error_reporting(0);

function shutdown($mysqlcon = NULL,$cfg,$loglevel,$reason,$nodestroypid = TRUE) {
	if($nodestroypid === TRUE) {
		if (file_exists(substr(__DIR__,0,-4).'logs/pid')) {
			unlink(substr(__DIR__,0,-4).'logs/pid');
		}
	}
	enter_logfile($cfg,$loglevel,$reason." Shutting down!");
	enter_logfile($cfg,9,"###################################################################");
	if(isset($mysqlcon)) {
		$mysqlcon = null;
	}
	exit;
}

function enter_logfile($cfg,$loglevel,$logtext,$norotate = false) {
	if($loglevel!=9 && $loglevel > $cfg['logs_debug_level']) return;
	$file = $cfg['logs_path'].'ranksystem.log';
	switch ($loglevel) {
		case 1:
			$loglevel = "  CRITICAL  ";
			break;
		case 2:
			$loglevel = "  ERROR     ";
			break;
		case 3:
			$loglevel = "  WARNING   ";
			break;
		case 4:
			$loglevel = "  NOTICE    ";
			break;
		case 5:
			$loglevel = "  INFO      ";
			break;
		case 6:
			$loglevel = "  DEBUG     ";
			break;
		default:
			$loglevel = "  NONE      ";
	}
	$loghandle = fopen($file, 'a');
	fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ").$loglevel.$logtext."\n");
	fclose($loghandle);
	if($norotate == false && filesize($file) > ($cfg['logs_rotation_size'] * 1048576)) {
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ")."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
		fclose($loghandle);
		$file2 = "$file.old";
		if(file_exists($file2)) unlink($file2);
		rename($file, $file2);
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ")."  NOTICE    Rotated logfile...\n");
		fclose($loghandle);
	}
}

require_once(substr(__DIR__,0,-4).'other/config.php');
require_once(substr(__DIR__,0,-4).'other/phpcommand.php');

if(isset($_SERVER['HTTP_HOST']) || isset($_SERVER['REMOTE_ADDR'])) {
	shutdown($mysqlcon,$cfg,1,"Request to start the Ranksystem from ".$_SERVER['REMOTE_ADDR'].". It seems the request came not from the command line!");
}
if(version_compare(phpversion(), '5.5.0', '<')) {
	shutdown($mysqlcon,$cfg,1,"Your PHP version (".phpversion().") is below 5.5.0. Update of PHP is required!");
}
if(!function_exists('simplexml_load_file')) {
	shutdown($mysqlcon,$cfg,1,sprintf($lang['errphp'],'PHP SimpleXML'));
}
if(!in_array('curl', get_loaded_extensions())) {
	shutdown($mysqlcon,$cfg,1,sprintf($lang['errphp'],'PHP cURL'));
}
if(!in_array('zip', get_loaded_extensions())) {
	shutdown($mysqlcon,$cfg,1,sprintf($lang['errphp'],'PHP Zip'));
}
if(!in_array('mbstring', get_loaded_extensions())) {
	shutdown($mysqlcon,$cfg,1,sprintf($lang['errphp'],'PHP mbstring'));
}
if(!in_array('ssh2', get_loaded_extensions()) && $cfg['teamspeak_query_encrypt_switch'] == 1) {
	shutdown($mysqlcon,$cfg,1,"PHP SSH2 is missed. Installation of PHP SSH2 is required, when using secured (SSH) connection to TeamSpeak! If you are not able to install PHP SSH2 (i.e. hosted webspace), you need to deactivate the TS3 Query encryption inside the Webinterface.");
}

enter_logfile($cfg,9,"");
enter_logfile($cfg,9,"###################################################################");
enter_logfile($cfg,9,"Initialize Bot...");
require_once(substr(__DIR__,0,-4).'libs/ts3_lib/TeamSpeak3.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_user.php');
require_once(substr(__DIR__,0,-4).'jobs/get_avatars.php');
require_once(substr(__DIR__,0,-4).'jobs/update_groups.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_serverstats.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_userstats.php');
require_once(substr(__DIR__,0,-4).'jobs/clean.php');
require_once(substr(__DIR__,0,-4).'jobs/check_db.php');
require_once(substr(__DIR__,0,-4).'jobs/handle_messages.php');
require_once(substr(__DIR__,0,-4).'jobs/event_userenter.php');
require_once(substr(__DIR__,0,-4).'jobs/update_rs.php');
require_once(substr(__DIR__,0,-4).'jobs/reset_rs.php');

enter_logfile($cfg,9,"Running on OS: ".php_uname("s")." ".php_uname("r"));
enter_logfile($cfg,9,"Using PHP Version: ".phpversion());
enter_logfile($cfg,9,"Database Version: ".$mysqlcon->getAttribute(PDO::ATTR_SERVER_VERSION));

$cfg = check_db($mysqlcon,$lang,$cfg,$dbname);
$cfg['temp_db_version'] = $mysqlcon->getAttribute(PDO::ATTR_SERVER_VERSION);
$cfg['temp_last_botstart'] = time();
$cfg['temp_reconnect_attempts'] = 0;
enter_logfile($cfg,4,"Check Ranksystem files for updates...");
if(isset($cfg['version_current_using']) && isset($cfg['version_latest_available']) && $cfg['version_latest_available'] != NULL && version_compare($cfg['version_latest_available'], $cfg['version_current_using'], '>')) {
	update_rs($mysqlcon,$lang,$cfg,$dbname,$phpcommand);
}
enter_logfile($cfg,4,"Check Ranksystem files for updates [done]");

function check_shutdown($cfg) {
	if(!file_exists(substr(__DIR__,0,-4).'logs/pid')) {
		shutdown(NULL,$cfg,4,"Received signal to stop!");
	}
}

enter_logfile($cfg,9,"Ranksystem Version: ".$cfg['version_current_using']." (on Update-Channel: ".$cfg['version_update_channel'].")");
enter_logfile($cfg,4,"Loading addons...");
require_once(substr(__DIR__,0,-4).'other/load_addons_config.php');
$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);
if($addons_config['assign_groups_active']['value'] == '1') {
	enter_logfile($cfg,4,"  Addon: 'assign_groups' [ON]");
	include(substr(__DIR__,0,-4).'jobs/addon_assign_groups.php');
	$cfg['temp_addon_assign_groups'] = "enabled";
} else {
	enter_logfile($cfg,4,"  Addon: 'assign_groups' [OFF]");
	$cfg['temp_addon_assign_groups'] = "disabled";
}
enter_logfile($cfg,4,"Loading addons [done]");

function sendmessage($ts3, $cfg, $uuid, $msg, $erromsg=NULL, $errcode=NULL, $successmsg=NULL, $nolog=NULL) {
	try {
		if(strlen($msg) > 1024) {
			$fragarr = explode("##*##", wordwrap($msg, 1022, "##*##", TRUE), 1022);
			foreach($fragarr as $frag) {
				usleep($cfg['teamspeak_query_command_delay']);
				$ts3->serverGetSelected()->clientGetByUid($uuid)->message("\n".$frag);
				if($nolog==NULL) {
					enter_logfile($cfg,6,"sendmessage to uuid $uuid (fragment): ".$frag);
				}
			}
		} else {
			usleep($cfg['teamspeak_query_command_delay']);
			$ts3->serverGetSelected()->clientGetByUid($uuid)->message($msg);
			if($nolog==NULL) {
				enter_logfile($cfg,6,"sendmessage to uuid $uuid: ".$msg);
			}
		}
		if($successmsg!=NULL) {
			enter_logfile($cfg,5,$successmsg);
		}
	} catch (Exception $e) {
		if($errcode!=NULL) {
			enter_logfile($cfg,$errcode,$erromsg." TS3: ".$e->getCode().': '.$e->getMessage());
		} else {
			enter_logfile($cfg,3,"sendmessage: ".$e->getCode().': '.$e->getMessage());
		}
	}
}

$sqlexec = '';

function run_bot() {
	global $cfg, $mysqlcon, $dbname, $dbtype, $lang, $phpcommand, $addons_config, $sqlexec, $max_execution_time, $memory_limit;
	
	enter_logfile($cfg,9,"Connect to TS3 Server (Address: \"".$cfg['teamspeak_host_address']."\" Voice-Port: \"".$cfg['teamspeak_voice_port']."\" Query-Port: \"".$cfg['teamspeak_query_port']."\" SSH: \"".$cfg['teamspeak_query_encrypt_switch']."\" Query-Slowmode: \"".number_format(($cfg['teamspeak_query_command_delay']/1000000),1)."\").");

	try {
		if($cfg['teamspeak_query_encrypt_switch'] == 1) {
			$ts3host = TeamSpeak3::factory("serverquery://".rawurlencode($cfg['teamspeak_query_user']).":".rawurlencode($cfg['teamspeak_query_pass'])."@".$cfg['teamspeak_host_address'].":".$cfg['teamspeak_query_port']."/?ssh=1");
		} else {
			$ts3host = TeamSpeak3::factory("serverquery://".rawurlencode($cfg['teamspeak_query_user']).":".rawurlencode($cfg['teamspeak_query_pass'])."@".$cfg['teamspeak_host_address'].":".$cfg['teamspeak_query_port']."/?blocking=0");
		}

		enter_logfile($cfg,9,"Connection to TS3 Server established.");
		try{
			$ts3version = $ts3host->version();
			enter_logfile($cfg,5,"  TS3 Server version: ".$ts3version['version']." on ".$ts3version['platform']." [Build: ".$ts3version['build']." from ".date("Y-m-d H:i:s",$ts3version['build'])."]");
			$cfg['temp_ts_version'] = $ts3version['version'];
		} catch (Exception $e) {
			enter_logfile($cfg,2,"  Error due getting TS3 server version - ".$e->getCode().': '.$e->getMessage());
		}
		
		if(version_compare($ts3version['version'],'3.6.9','=<')) {
			enter_logfile($cfg,3,"      Your TS3 server is outdated, please update it.. also to be ready for TS5!");
		}

		enter_logfile($cfg,9,"    Select virtual server...");
		try {
			if(version_compare($ts3version['version'],'3.4.0','>=')) {
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
						enter_logfile($cfg,3,'      '.$lang['errorts3'].$e->getCode().': '.$e->getMessage());
						shutdown($mysqlcon,$cfg,1,"Critical TS3 error on core function!");
					}
				}
			}
			enter_logfile($cfg,9,"    Select virtual server [done]");
			$cfg['temp_reconnect_attempts'] = 0;
		} catch (Exception $e) {
			enter_logfile($cfg,2,"  Error due selecting virtual server - ".$e->getCode().': '.$e->getMessage()." (bad Voice-Port or Bot name?)");
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
			enter_logfile($cfg,2,"  Error due notifyRegister on TS3 server - ".$e->getCode().': '.$e->getMessage());
		}

		$whoami = $ts3server->whoami();
		if($cfg['teamspeak_default_channel_id'] != 0) {
			try {
				usleep($cfg['teamspeak_query_command_delay']);
				$ts3server->clientMove($whoami['client_id'],$cfg['teamspeak_default_channel_id']);
				enter_logfile($cfg,5,"  Joined to specified TS channel with channel-ID ".$cfg['teamspeak_default_channel_id'].".");
			} catch (Exception $e) {
				if($e->getCode() != 770) {
					enter_logfile($cfg,2,"  Could not join specified TS channel (channel-ID: ".$cfg['teamspeak_default_channel_id'].") - ".$e->getCode().': '.$e->getMessage());
				} else {
					enter_logfile($cfg,5,"  Joined to specified TS channel with channel-ID ".$cfg['teamspeak_default_channel_id']." (already member of it).");
				}
			}
		} else {
			enter_logfile($cfg,5,"  No channel defined where the Ranksystem Bot should be entered.");
		}

		enter_logfile($cfg,9,"Config check started...");
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
				$loglevel = "UNKOWN";
		}
		enter_logfile($cfg,9,"  Log Level: ".$loglevel);
		enter_logfile($cfg,6,"  Serverside config 'max_execution_time' (PHP.ini): ".$max_execution_time." sec.");
		enter_logfile($cfg,6,"  Serverside config 'memory_limit' (PHP.ini): ".$memory_limit);
		$cfg['rankup_definition_flipped'] = array_flip($cfg['rankup_definition']);
		krsort($cfg['rankup_definition']);

		if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
			enter_logfile($cfg,1,"  Select on DB failed for group check: ".print_r($mysqlcon->errorInfo(), true));
		}
		
		$checkgroups = 0;
		if(isset($groupslist) && $groupslist != NULL) {
			if(isset($cfg['rankup_definition']) && $cfg['rankup_definition'] != NULL) {
				foreach($cfg['rankup_definition'] as $time => $groupid) {
					if(!isset($groupslist[$groupid]) && $groupid != NULL) {
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
			enter_logfile($cfg,3,"  Found servergroups in config, which are unknown. Redownload all servergroups from TS3 server.");
			if($mysqlcon->exec("DELETE FROM `$dbname`.`groups`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
			}

			$serverinfo = $ts3server->serverInfo();
			$select_arr = array();
			$sqlexec2 .= update_groups($ts3server,$mysqlcon,$lang,$cfg,$dbname,$serverinfo,$select_arr,1);
			if($mysqlcon->exec($sqlexec2) === false) {
				enter_logfile($cfg,2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
			}
			unset($sqlexec2,$select_arr,$groupslist,$serverinfo,$ts3version);
			$errcnf = 0;
			enter_logfile($cfg,4,"  Downloading of servergroups finished. Recheck the config.");
			
			if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
				enter_logfile($cfg,1,"  Select on DB failed for group check: ".print_r($mysqlcon->errorInfo(), true));
			}
			
			if(isset($groupslist) && $groupslist != NULL) {
				if(isset($cfg['rankup_definition']) && $cfg['rankup_definition'] != NULL) {
					foreach($cfg['rankup_definition'] as $time => $groupid) {
						if(!isset($groupslist[$groupid]) && $groupid != NULL) {
							enter_logfile($cfg,1,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wigrptime']));
							$errcnf++;
						}
					}
				}
				if(isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != NULL) {
					foreach($cfg['rankup_boost_definition'] as $groupid => $value) {
						if(!isset($groupslist[$groupid]) && $groupid != NULL) {
							enter_logfile($cfg,2,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wiboost']));
						}
					}
				}
				if(isset($cfg['rankup_excepted_group_id_list']) && $cfg['rankup_excepted_group_id_list'] != NULL) {
					foreach($cfg['rankup_excepted_group_id_list'] as $groupid => $value) {
						if(!isset($groupslist[$groupid]) && $groupid != NULL) {
							enter_logfile($cfg,2,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wiexgrp']));
						}
					}
				}
			}
			if($errcnf > 0) {
				shutdown($mysqlcon,$cfg,1,"Critical Config error!");
			} else {
				enter_logfile($cfg,4,"  No critical problems found! All seems to be fine...");
			}
		}

		if(($lastupdate = $mysqlcon->query("SELECT `timestamp` FROM `$dbname`.`job_check` WHERE `job_name`='last_update'")->fetch()) === false) {
			enter_logfile($cfg,1,"  Select on DB failed for job check: ".print_r($mysqlcon->errorInfo(), true));
		} else {
			if($lastupdate['timestamp'] != 0 && ($lastupdate['timestamp'] + 10) > time()) {
				if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
					foreach(array_flip($cfg['webinterface_admin_client_unique_id_list']) as $clientid) {
						sendmessage($ts3server, $cfg, $clientid, sprintf($lang['upmsg2'], $cfg['version_current_using'], 'https://ts-ranksystem.com/#changelog'), sprintf($lang['upusrerr'], $clientid), 6, sprintf($lang['upusrinf'], $clientid));
					}
				}
			}
		}
		
		unset($groupslist,$errcnf,$checkgroups,$lastupdate,$updcld,$loglevel,$whoami,$ts3host,$max_execution_time,$memory_limit,$memory_limit);
		enter_logfile($cfg,9,"Config check [done]");

		enter_logfile($cfg,9,"Bot starts now his work!");
		$looptime = $cfg['temp_count_laps'] = $cfg['temp_whole_laptime'] = $cfg['temp_count_laptime'] = 0; $cfg['temp_last_laptime'] = '';
		usleep(3000000);

		while(1) {
			$starttime = microtime(true);
			$weekago = time() - 604800;
			$monthago = time() - 2592000;
			
			if(($get_db_data = $mysqlcon->query("SELECT * FROM `$dbname`.`user`; SELECT MAX(`timestamp`) AS `timestamp` FROM `$dbname`.`user_snapshot`; SELECT `version`, COUNT(`version`) AS `count` FROM `$dbname`.`user` GROUP BY `version` ORDER BY `count` DESC; SELECT MAX(`timestamp`) AS `timestamp` FROM `$dbname`.`server_usage`; SELECT * FROM `$dbname`.`job_check`; SELECT `uuid` FROM `$dbname`.`stats_user`; SELECT `timestamp` FROM `$dbname`.`user_snapshot` WHERE `timestamp`>$weekago ORDER BY `timestamp` ASC LIMIT 1; SELECT `timestamp` FROM `$dbname`.`user_snapshot` WHERE `timestamp`>$monthago ORDER BY `timestamp` ASC LIMIT 1; SELECT * FROM `$dbname`.`groups`; SELECT * FROM `$dbname`.`addon_assign_groups`; SELECT * FROM `$dbname`.`admin_addtime`; ")) === false) {
				shutdown($mysqlcon,$cfg,1,"Select on DB failed: ".print_r($mysqlcon->errorInfo(), true));
			}

			$count_select = 0;
			$select_arr = array();
			while($single_select = $get_db_data) {
				$fetched_array = $single_select->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
				$count_select++;

				switch ($count_select) {
					case 1:
						$select_arr['all_user'] = $fetched_array;
						break;
					case 2:
						$select_arr['max_timestamp_user_snapshot'] = $fetched_array;
						break;
					case 3:
						$select_arr['count_version_user'] = $fetched_array;
						break;
					case 4:
						$select_arr['max_timestamp_server_usage'] = $fetched_array;
						break;
					case 5:
						$select_arr['job_check'] = $fetched_array;
						break;
					case 6:
						$select_arr['uuid_stats_user'] = $fetched_array;
						break;
					case 7:
						$select_arr['usersnap_min_week'] = $fetched_array;
						break;
					case 8:
						$select_arr['usersnap_min_month'] = $fetched_array;
						break;
					case 9:
						$select_arr['groups'] = $fetched_array;
						break;
					case 10:
						$select_arr['addon_assign_groups'] = $fetched_array;
						break;
					case 11:
						$select_arr['admin_addtime'] = $fetched_array;
						break 2;
				}
				$get_db_data->nextRowset();
			}
			unset($get_db_data,$fetched_array,$single_select);
			enter_logfile($cfg,6,"SQL select needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));

			check_shutdown($cfg);
			$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);
			$ts3server->clientListReset();
			usleep($cfg['teamspeak_query_command_delay']);
			$allclients = $ts3server->clientList();
			usleep($cfg['teamspeak_query_command_delay']);
			$ts3server->serverInfoReset();
			usleep($cfg['teamspeak_query_command_delay']);
			$serverinfo = $ts3server->serverInfo();
			$sqlexec .= calc_user($ts3server,$mysqlcon,$lang,$cfg,$dbname,$allclients,$phpcommand,$select_arr);
			get_avatars($ts3server,$cfg);
			$sqlexec .= clean($ts3server,$mysqlcon,$lang,$cfg,$dbname,$select_arr);
			$sqlexec .= calc_serverstats($ts3server,$mysqlcon,$cfg,$dbname,$dbtype,$serverinfo,$select_arr,$phpcommand,$lang);
			$sqlexec .= calc_userstats($ts3server,$mysqlcon,$cfg,$dbname,$select_arr);
			$sqlexec .= update_groups($ts3server,$mysqlcon,$lang,$cfg,$dbname,$serverinfo,$select_arr);

			if($addons_config['assign_groups_active']['value'] == '1') {
				if($cfg['temp_addon_assign_groups'] == "disabled") {
					enter_logfile($cfg,5,"Loading new addon...");
					enter_logfile($cfg,5,"  Addon: 'assign_groups' [ON]");
					if(!function_exists('addon_assign_groups')) {
						include(substr(__DIR__,0,-4).'jobs/addon_assign_groups.php');
					}
					$cfg['temp_addon_assign_groups'] = "enabled";
					enter_logfile($cfg,5,"Loading new addon [done]");
				}
				$sqlexec .= addon_assign_groups($addons_config,$ts3server,$cfg,$dbname,$allclients,$select_arr);
			} elseif ($cfg['temp_addon_assign_groups'] == "enabled") {
				enter_logfile($cfg,5,"Disable addon...");
				enter_logfile($cfg,5,"  Addon: 'assign_groups' [OFF]");
				$cfg['temp_addon_assign_groups'] = "disabled";
				enter_logfile($cfg,5,"Disable addon [done]");
			}
			
			$startsql = microtime(true);
			if($mysqlcon->exec($sqlexec) === false) {
				enter_logfile($cfg,2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
			}
			enter_logfile($cfg,6,"SQL exections needs: ".(number_format(round((microtime(true) - $startsql), 5),5)));
			if($cfg['logs_debug_level'] > 5) {
				$sqlfile = $cfg['logs_path'].'temp_sql_dump.sql';
				$sqldump = fopen($sqlfile, 'wa+');
				fwrite($sqldump, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ").' SQL: '.$sqlexec."\n");
				fclose($sqldump);
			}

			reset_rs($ts3server,$mysqlcon,$lang,$cfg,$dbname,$select_arr);

			unset($sqlexec,$select_arr,$sqldump);
			$sqlexec = '';
			
			$looptime = microtime(true) - $starttime;
			$cfg['temp_whole_laptime'] = $cfg['temp_whole_laptime'] + $looptime;
			$cfg['temp_count_laptime']++;
			$cfg['temp_last_laptime'] = substr((number_format(round($looptime, 5),5) . ';' . $cfg['temp_last_laptime']),0,79);

			enter_logfile($cfg,6,"last loop: ".round($looptime, 5)." sec.");

			if($looptime < 1) {
				$loopsleep = (1 - $looptime);
				if($cfg['teamspeak_query_encrypt_switch'] == 1) {
					// no wait for data to become available on the stream on SSH due issues with non-blocking mode
					usleep(round($loopsleep * 1000000));
				} else {
					$ts3server->getAdapter()->waittsn($loopsleep, 50000);  // 50ms delay for CPU reason
				}
			}
		}
	} catch (Exception $e) {
		enter_logfile($cfg,2,$lang['errorts3'].$e->getCode().': '.$e->getMessage());
		$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
		if(in_array($e->getCode(), $offline_status)) {
			if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
				enter_logfile($cfg,2,$lang['error'].print_r($mysqlcon->errorInfo(), true));
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

			enter_logfile($cfg,4,"Try to reconnect in ".$wait_reconnect." seconds.");

			for($z = 1; $z <= $wait_reconnect; $z++) {
				sleep(1);
				check_shutdown($cfg);
			}

			$cfg['temp_reconnect_attempts'] = $cfg['temp_reconnect_attempts'] + 1;
			return $cfg;
		} else {
			shutdown($mysqlcon,$cfg,1,"Critical TS3 error on core function!");
		}
		unset($offline_status,$wait_reconnect,$z);
	}
}

while(1) {
	run_bot();
}
?>