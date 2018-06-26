#!/usr/bin/php
<?PHP
set_time_limit(0);
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');
error_reporting(0);

function shutdown($mysqlcon = NULL, $logpath, $timezone, $loglevel, $reason, $nodestroypid = NULL) {
	if($nodestroypid == NULL) {
		if (substr(php_uname(), 0, 7) == "Windows") {
			exec("del /F ".substr(__DIR__,0,-4).'logs/pid');
		} else {
			exec("rm -f ".substr(__DIR__,0,-4).'logs/pid');
		}
	}
	enter_logfile($logpath,$timezone,$loglevel,$reason." Shutting down!");
	if(isset($mysqlcon)) {
		$mysqlcon->close();
	}
	exit;
}

function enter_logfile($logpath,$timezone,$loglevel,$logtext,$norotate = false) {
	global $phpcommand;
	$file = $logpath.'ranksystem.log';
	if ($loglevel == 1) {
		$loglevel = "  CRITICAL  ";
	} elseif ($loglevel == 2) {
		$loglevel = "  ERROR     ";
	} elseif ($loglevel == 3) {
		$loglevel = "  WARNING   ";
	} elseif ($loglevel == 4) {
		$loglevel = "  NOTICE    ";
	} elseif ($loglevel == 5) {
		$loglevel = "  INFO      ";
	} elseif ($loglevel == 6) {
		$loglevel = "  DEBUG     ";
	}
	$loghandle = fopen($file, 'a');
	fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ").$loglevel.$logtext."\n");
	fclose($loghandle);
	if($norotate == false && filesize($file) > 5242880) {
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
		fclose($loghandle);
		$file2 = "$file.old";
		if (file_exists($file2)) unlink($file2);
		rename($file, $file2);
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Rotated logfile...\n");
		fclose($loghandle);
	}
}

require_once(substr(__DIR__,0,-4).'other/config.php');
require_once(substr(__DIR__,0,-4).'other/phpcommand.php');

if(isset($_SERVER['HTTP_HOST']) || isset($_SERVER['REMOTE_ADDR'])) {
	shutdown($mysqlcon, $logpath, $timezone, 1, "Request to start the Ranksystem from ".$_SERVER['REMOTE_ADDR'].". It seems the request came not from the command line!", 1);
}
if(version_compare(phpversion(), '5.5.0', '<')) {
	shutdown($mysqlcon, $logpath, $timezone, 1, "Your PHP version (".phpversion().") is below 5.5.0. Update of PHP is required!");
}
if(!function_exists('simplexml_load_file')) {
	shutdown($mysqlcon, $logpath, $timezone, 1, "SimpleXML is missed. Installation of SimpleXML is required!");
}
if(!in_array('curl', get_loaded_extensions())) {
	shutdown($mysqlcon, $logpath, $timezone, 1, "PHP cURL is missed. Installation of PHP cURL is required!");
}
if(!in_array('zip', get_loaded_extensions())) {
	shutdown($mysqlcon, $logpath, $timezone, 1, "PHP Zip is missed. Installation of PHP Zip is required!");
}

enter_logfile($logpath,$timezone,5,"###################################################################");
enter_logfile($logpath,$timezone,5,"");
enter_logfile($logpath,$timezone,5,"###################################################################");
enter_logfile($logpath,$timezone,5,"Initialize Bot...");
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

enter_logfile($logpath,$timezone,6,"Running on OS: ".php_uname("s")." ".php_uname("r"));
enter_logfile($logpath,$timezone,6,"Using PHP Version: ".phpversion());
enter_logfile($logpath,$timezone,6,"Database Version: ".$mysqlcon->getAttribute(PDO::ATTR_SERVER_VERSION));

$currvers = check_db($mysqlcon,$lang,$dbname,$timezone,$currvers,$logpath);
enter_logfile($logpath,$timezone,5,"Check Ranksystem files for updates...");
if(isset($currvers) && isset($newversion) && $newversion != NULL && version_compare($newversion, $currvers, '>')) {
	update_rs($mysqlcon,$lang,$dbname,$logpath,$timezone,$newversion,$phpcommand);
}
enter_logfile($logpath,$timezone,5,"Check Ranksystem files for updates [done]");

function check_shutdown($timezone,$logpath) {
	if(!is_file(substr(__DIR__,0,-4).'logs/pid')) {
		shutdown($mysqlcon, $logpath, $timezone, 5, "Received signal to stop!");
	}
}

enter_logfile($logpath,$timezone,5,"Ranksystem Version: ".$currvers);
enter_logfile($logpath,$timezone,5,"Loading addons...");
require_once(substr(__DIR__,0,-4).'other/load_addons_config.php');
$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);
if($addons_config['assign_groups_active']['value'] == '1') {
	enter_logfile($logpath,$timezone,5,"  Addon: 'assign_groups' [ON]");
	include(substr(__DIR__,0,-4).'jobs/addon_assign_groups.php');
	define('assign_groups',1);
} else {
	enter_logfile($logpath,$timezone,5,"  Addon: 'assign_groups' [OFF]");
}
enter_logfile($logpath,$timezone,5,"Loading addons [done]");

enter_logfile($logpath,$timezone,5,"Connect to TS3 Server (Address: \"".$ts['host']."\" Voice-Port: \"".$ts['voice']."\" Query-Port: \"".$ts['query']."\").");
try {
    $ts3 = TeamSpeak3::factory("serverquery://".rawurlencode($ts['user']).":".rawurlencode($ts['pass'])."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']."&blocking=0");
	enter_logfile($logpath,$timezone,5,"  Connection to TS3 Server established.");
	try {
		usleep($slowmode);
		$ts3->notifyRegister("server");
		$ts3->notifyRegister("textprivate");
		$ts3->notifyRegister("textchannel");
		$ts3->notifyRegister("textserver");
		TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "handle_messages");
		TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyCliententerview", "event_userenter");
	} catch (Exception $e) {
		enter_logfile($logpath,$timezone,2,"  Error due register notifyTextmessage ".$e->getCode().': '.$e->getMessage());
	}
	
    try {
		usleep($slowmode);
        $ts3->selfUpdate(array('client_nickname' => $queryname));
    }
    catch (Exception $e) {
        try {
			usleep($slowmode);
            $ts3->selfUpdate(array('client_nickname' => $queryname2));
        }
        catch (Exception $e) {
            enter_logfile($logpath,$timezone,2,$lang['error'].$e->getCode().': '.$e->getMessage());
        }
    }
	
	usleep($slowmode);
	$whoami = $ts3->whoami();
	if($defchid != 0) {
		try {
			usleep($slowmode);
			$ts3->clientMove($whoami['client_id'],$defchid);
			enter_logfile($logpath,$timezone,5,"  Joined to specified Channel.");
		} catch (Exception $e) {
			if($e->getCode() != 770) {
				enter_logfile($logpath,$timezone,5,"  Could not join specified channel.");
			} else {
				enter_logfile($logpath,$timezone,5,"  Joined to specified channel.");
			}
		}
	} else {
		enter_logfile($logpath,$timezone,4,"  No channel defined where the Ranksystem Bot should be entered.");
	}

	enter_logfile($logpath,$timezone,5,"Config check started...");
	
	if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		enter_logfile($logpath,$timezone,1,"  Select on DB failed for group check: ".print_r($mysqlcon->errorInfo(), true));
	}
	
	$checkgroups = 0;
	if(isset($groupslist) && $groupslist != NULL) {
		if(isset($grouptime) && $grouptime != NULL) {
			foreach($grouptime as $time => $groupid) {
				if(!isset($groupslist[$groupid]) && $groupid != NULL) {
					$checkgroups++;
				}
			}
		}
		if(isset($boostarr) && $boostarr != NULL) {
			foreach($boostarr as $groupid => $value) {
				if(!isset($groupslist[$groupid]) && $groupid != NULL) {
					$checkgroups++;
				}
			}
		}
		if(isset($exceptgroup) && $exceptgroup != NULL) {
			foreach($exceptgroup as $groupid => $value) {
				if(!isset($groupslist[$groupid]) && $groupid != NULL) {
					$checkgroups++;
				}
			}
		}
	}
	if($checkgroups > 0) {
		enter_logfile($logpath,$timezone,4,"  Found servergroups in config, which are unknown. Redownload all servergroups from TS3 server.");
		if($mysqlcon->exec("DELETE FROM groups;") === false) {
			enter_logfile($logpath,$timezone,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
		}
		$nobreak = 1;
		$sqlexec = '';
		$serverinfo = $ts3->serverInfo();
		$select_arr = array();
		$sqlexec .= update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$serverinfo,$logpath,$grouptime,$boostarr,$exceptgroup,$select_arr,$nobreak);
		if($mysqlcon->exec($sqlexec) === false) {
			enter_logfile($logpath,$timezone,2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
		}
		unset($sqlexec, $select_arr, $groupslist);
		$errcnf = 0;
		enter_logfile($logpath,$timezone,4,"  Downloading of servergroups finished. Recheck the config.");
		
		if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
			enter_logfile($logpath,$timezone,1,"  Select on DB failed for group check: ".print_r($mysqlcon->errorInfo(), true));
		}
		
		if(isset($groupslist) && $groupslist != NULL) {
			if(isset($grouptime) && $grouptime != NULL) {
				foreach($grouptime as $time => $groupid) {
					if(!isset($groupslist[$groupid]) && $groupid != NULL) {
						enter_logfile($logpath,$timezone,1,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wigrptime']));
						$errcnf++;
					}
				}
			}
			if(isset($boostarr) && $boostarr != NULL) {
				foreach($boostarr as $groupid => $value) {
					if(!isset($groupslist[$groupid]) && $groupid != NULL) {
						enter_logfile($logpath,$timezone,2,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wiboost']));
					}
				}
			}
			if(isset($exceptgroup) && $exceptgroup != NULL) {
				foreach($exceptgroup as $groupid => $value) {
					if(!isset($groupslist[$groupid]) && $groupid != NULL) {
						enter_logfile($logpath,$timezone,2,'    '.sprintf($lang['upgrp0001'], $groupid, $lang['wiexgrp']));
					}
				}
			}
		}
		if($errcnf > 0) {
			shutdown($mysqlcon, $logpath, $timezone, 1, "Critical Config error!");
		} else {
			enter_logfile($logpath,$timezone,4,"  No critical problems found! All seems to be fine...");
		}
	}
	
	if(($lastupdate = $mysqlcon->query("SELECT `timestamp` FROM `$dbname`.`job_check` WHERE `job_name`='last_update'")->fetch()) === false) {
		enter_logfile($logpath,$timezone,1,"  Select on DB failed for job check: ".print_r($mysqlcon->errorInfo(), true));
	} else {
		if($lastupdate['timestamp'] != 0 && ($lastupdate['timestamp'] + 10) > time()) {
			if(isset($adminuuid) && $adminuuid != NULL) {
				foreach ($adminuuid as $clientid) {
					usleep($slowmode);
					try {
						$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg2'], $currvers));
						enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['upusrinf'], $clientid));
					}
					catch (Exception $e) {
						enter_logfile($logpath,$timezone,6,"  ".sprintf($lang['upusrerr'], $clientid));
					}
				}
			}
		}
	}
	
	unset($groupslist,$errcnf,$checkgroups);
	enter_logfile($logpath,$timezone,5,"Config check [done]");

	enter_logfile($logpath,$timezone,5,"Bot starts now his work!");
	$looptime = $rotated_cnt = 0; $rotated = '';
	usleep(3000000);
	while(1) {
		$sqlexec = $sqlexec2 = '';
		$starttime = microtime(true);
		$weekago = time() - 604800;
		$monthago = time() - 2592000;
		
		if(($get_db_data = $mysqlcon->query("SELECT * FROM `$dbname`.`user`; SELECT MAX(`timestamp`) AS `timestamp` FROM `$dbname`.`user_snapshot`; SELECT `version`, COUNT(`version`) AS `count` FROM `$dbname`.`user` GROUP BY `version` ORDER BY `count` DESC; SELECT MAX(`timestamp`) AS `timestamp` FROM `$dbname`.`server_usage`; SELECT * FROM `$dbname`.`job_check`; SELECT `uuid` FROM `$dbname`.`stats_user`; SELECT `timestamp` FROM `$dbname`.`user_snapshot` WHERE `timestamp`>$weekago ORDER BY `timestamp` ASC LIMIT 1; SELECT `timestamp` FROM `$dbname`.`user_snapshot` WHERE `timestamp`>$monthago ORDER BY `timestamp` ASC LIMIT 1; SELECT * FROM `$dbname`.`groups`; SELECT * FROM `$dbname`.`addon_assign_groups`; SELECT * FROM `$dbname`.`admin_addtime`; ")) === false) {
			shutdown($mysqlcon, $logpath, $timezone, 1, "Select on DB failed: ".print_r($mysqlcon->errorInfo(), true));
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
		unset($get_db_data, $fetched_array, $single_select);

		check_shutdown($timezone,$logpath);
		$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);
		$ts3->clientListReset();
		usleep($slowmode);
		$allclients = $ts3->clientList();
		$ts3->serverInfoReset();
		usleep($slowmode);
		$serverinfo = $ts3->serverInfo();
		$sqlexec .= calc_user($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$grouptime,$boostarr,$resetbydbchange,$msgtouser,$currvers,$substridle,$exceptuuid,$exceptgroup,$allclients,$logpath,$rankupmsg,$ignoreidle,$exceptcid,$resetexcept,$phpcommand,$select_arr);
		get_avatars($ts3,$slowmode,$timezone,$logpath,$avatar_delay);
		$sqlexec .= clean($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$cleanclients,$cleanperiod,$logpath,$select_arr);
		$sqlexec .= calc_serverstats($ts3,$mysqlcon,$dbname,$dbtype,$slowmode,$timezone,$serverinfo,$substridle,$grouptime,$logpath,$ts,$currvers,$upchannel,$select_arr,$phpcommand,$adminuuid);
		$sqlexec .= calc_userstats($ts3,$mysqlcon,$dbname,$slowmode,$timezone,$logpath,$select_arr);
		$sqlexec .= update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$serverinfo,$logpath,$grouptime,$boostarr,$exceptgroup,$select_arr);
		$sqlexec .= $sqlexec2;
		
		if($addons_config['assign_groups_active']['value'] == '1') {
			if(!defined('assign_groups')) {
				enter_logfile($logpath,$timezone,5,"Loading new addon...");
				enter_logfile($logpath,$timezone,5,"  Addon: 'assign_groups' [ON]");
				include(substr(__DIR__,0,-4).'jobs/addon_assign_groups.php');
				define('assign_groups',1);
				enter_logfile($logpath,$timezone,5,"Loading new addon [done]");
			}
			$sqlexec .= addon_assign_groups($addons_config,$ts3,$dbname,$slowmode,$timezone,$logpath,$allclients,$select_arr);
		}
		
		if($mysqlcon->exec($sqlexec) === false) {
			enter_logfile($logpath,$timezone,2,"Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
		}
		unset($sqlexec, $sqlexec2, $select_arr);

		$looptime = microtime(true) - $starttime;
		$rotated = substr((number_format(round($looptime, 5),5) . ';' . $rotated),0,79);

		if($looptime < 1) {
			$loopsleep = (1 - $looptime) * 1000000;
			#enter_logfile($logpath,$timezone,6,"last loop: ".round($looptime, 5)." sec.");
			usleep($loopsleep);
		} elseif($slowmode == 0) {
			#enter_logfile($logpath,$timezone,6,"last loop: ".round($looptime, 5)." sec.");
			$rotated_cnt++;
			if($rotated_cnt > 3600) {
				$rotated_arr = explode(';', $rotated);
				$sum_time = 0;
				foreach ($rotated_arr as $time) {
					$sum_time = $sum_time + $time;
				}
				if(($sum_time / 10) > 1) {
					$rotated_cnt = 0;
					enter_logfile($logpath,$timezone,4,"  Your Ranksystem seems to be slow. This is not a big deal, but it needs more ressources then necessary.");
					enter_logfile($logpath,$timezone,4,"  Here you'll find some information to optimize it: https://ts-n.net/ranksystem.php#optimize");
					enter_logfile($logpath,$timezone,4,"  Last 10 runtimes in seconds (lower values are better): ".$rotated);
					foreach ($uniqueid as $clientid) {
						usleep($slowmode);
						try {
							$ts3->clientGetByUid($clientid)->message("\nYour Ranksystem seems to be slow. This is not a big deal, but it needs more ressources then necessary.\nHere you'll find some information to optimize it: [URL]https://ts-n.net/ranksystem.php#optimize[/URL]\nLast 10 runtimes in seconds (lower values are better):\n".$rotated);
						} catch (Exception $e) { }
					}
				}
			}
		}
	}
}
catch (Exception $e) {
    enter_logfile($logpath,$timezone,2,$lang['error'].$e->getCode().': '.$e->getMessage());
	$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
	if(in_array($e->getCode(), $offline_status)) {
		if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
			enter_logfile($logpath,$timezone,2,$lang['error'].print_r($mysqlcon->errorInfo(), true));
		}
	}
}
?>
