<?PHP
function handle_messages(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {

	global $lang,$cfg,$mysqlcon, $dbname, $phpcommand;
	
	if($host->whoami()["client_unique_identifier"] != $event["invokeruid"]) {
		$uuid = $event["invokeruid"];
		$admin = 0;
		foreach(array_flip($cfg['webinterface_admin_client_unique_id_list']) as $auuid) {
			if($uuid == $auuid) {
				$admin = 1;
				break;
			}
		}

		if((strstr($event["msg"], '!nextup') || strstr($event["msg"], '!next')) && $cfg['rankup_next_message_mode'] != 0) {
			//enter_logfile($cfg,6,"Client ".$event["invokername"]." (".$event["invokeruid"].") sent textmessage: ".$event["msg"]);
			if(($user = $mysqlcon->query("SELECT `count`,`nextup`,`idle`,`except`,`name` FROM `$dbname`.`user` WHERE `uuid`='$uuid'")->fetch()) === false) {
				enter_logfile($cfg,2,"handle_messages 1:".print_r($mysqlcon->errorInfo(), true));
			}

			if(($sqlhisgroup = $mysqlcon->query("SELECT `sgid`,`sgidname` FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
				enter_logfile($cfg,2,"handle_messages 2:".print_r($mysqlcon->errorInfo(), true));
			}

			ksort($cfg['rankup_definition']);
			$countgrp = count($cfg['rankup_definition']);
			$grpcount = 0;
			foreach ($cfg['rankup_definition'] as $time => $groupid) {
				if ($cfg['rankup_time_assess_mode'] == 1) {
					$nextup = $time - $user['count'] + $user['idle'];
				} else {
					$nextup = $time - $user['count'];
				}
				$dtF = new DateTime("@0");
				$dtT = new DateTime("@$nextup");
				$days  = $dtF->diff($dtT)->format('%a');
				$hours = $dtF->diff($dtT)->format('%h');
				$mins  = $dtF->diff($dtT)->format('%i');
				$secs  = $dtF->diff($dtT)->format('%s');
				$name = $user['name'];
				$grpcount++;
				if ($nextup > 0 && $nextup < $time || $grpcount == $countgrp && $nextup <= 0) {
					check_shutdown($cfg); usleep($cfg['teamspeak_query_command_delay']);
					if ($grpcount == $countgrp && $nextup <= 0) {
						usleep($cfg['teamspeak_query_command_delay']);
						try {
							$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message(sprintf($cfg['rankup_next_message_2'], $days, $hours, $mins, $secs, $sqlhisgroup[$groupid]['sgidname'], $name));
						} catch (Exception $e) {
							enter_logfile($cfg,2,"handle_messages 3:".$e->getCode().': '.$e->getMessage());
						}
					} elseif ($user['except'] == 2 || $user['except'] == 3) {
						usleep($cfg['teamspeak_query_command_delay']);
						try {
							$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message(sprintf($cfg['rankup_next_message_3'], $days, $hours, $mins, $secs, $sqlhisgroup[$groupid]['sgidname'], $name));
						} catch (Exception $e) {
							enter_logfile($cfg,2,"handle_messages 4:".$e->getCode().': '.$e->getMessage());
						}
					} else {
						usleep($cfg['teamspeak_query_command_delay']);
						try {
							$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message(sprintf($cfg['rankup_next_message_1'], $days, $hours, $mins, $secs, $sqlhisgroup[$groupid]['sgidname'], $name));
						} catch (Exception $e) {
							enter_logfile($cfg,2,"handle_messages 5:".$e->getCode().': '.$e->getMessage());
						}
					}
					if($cfg['rankup_next_message_mode'] == 1) {
						break;
					}
				}
			}
		}
		
		if(strstr($event["msg"], '!version')) {
			if(version_compare($cfg['version_latest_available'], $cfg['version_current_using'], '>') && $cfg['version_latest_available'] != '') {
				usleep($cfg['teamspeak_query_command_delay']);
				try {
					$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message(sprintf($lang['upmsg'], $cfg['version_current_using'], $cfg['version_latest_available']));
				} catch (Exception $e) {
					enter_logfile($cfg,2,"handle_messages 6:".$e->getCode().': '.$e->getMessage());
				}
			} else {
				usleep($cfg['teamspeak_query_command_delay']);
				try {
					$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message(sprintf($lang['msg0001'], $cfg['version_current_using']));
				} catch (Exception $e) {
					enter_logfile($cfg,2,"handle_messages 7:".$e->getCode().': '.$e->getMessage());
				}
			}
		}
		
		if(strstr($event["msg"], '!help') || strstr($event["msg"], '!info') || strstr($event["msg"], '!commands')) {
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0002']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 8:".$e->getCode().': '.$e->getMessage());
			}
		}
		
		if((strstr($event["msg"], '!shutdown') || strstr($event["msg"], '!quit') || strstr($event["msg"], '!stop')) && $admin == 1) {
			enter_logfile($cfg,5,sprintf($lang['msg0004'], $event["invokername"], $event["invokeruid"]));
			$path = substr(__DIR__, 0, -4);
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0005']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 9:".$e->getCode().': '.$e->getMessage());
			}
			exec($phpcommand." ".$path."worker.php stop");
		} elseif (strstr($event["msg"], '!shutdown') || strstr($event["msg"], '!quit') || strstr($event["msg"], '!stop')) {
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0003']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 10:".$e->getCode().': '.$e->getMessage());
			}
		}
		
		if((strstr($event["msg"], '!restart') || strstr($event["msg"], '!reboot')) && $admin == 1) {
			enter_logfile($cfg,5,sprintf($lang['msg0007'], $event["invokername"], $event["invokeruid"]));
			$path = substr(__DIR__, 0, -4);
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0006']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 11:".$e->getCode().': '.$e->getMessage());
			}
			if (substr(php_uname(), 0, 7) == "Windows") {
				exec("start ".$phpcommand." ".$path."worker.php restart");
			} else {
				exec($phpcommand." ".$path."worker.php restart > /dev/null 2>/dev/null &");
			}
		} elseif (strstr($event["msg"], '!restart') || strstr($event["msg"], '!reboot')) {
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0003']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 12:".$e->getCode().': '.$e->getMessage());
			}
		}
		
		if((strstr($event["msg"], '!checkupdate') || strstr($event["msg"], '!update')) && $admin == 1) {
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='0' WHERE `job_name` IN ('check_update','get_version','calc_server_stats')") === false) {
				enter_logfile($cfg,4,"handle_messages 13:".print_r($mysqlcon->errorInfo(), true));
			}
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0008']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 14:".$e->getCode().': '.$e->getMessage());
			}
		} elseif(strstr($event["msg"], '!checkupdate') || strstr($event["msg"], '!update')) {
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0003']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 15:".$e->getCode().': '.$e->getMessage());
			}
		}
		
		if((strstr($event["msg"], '!clean')) && $admin == 1) {
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='0' WHERE `job_name` IN ('clean_db','clean_clients')") === false) {
				enter_logfile($cfg,4,"handle_messages 13:".print_r($mysqlcon->errorInfo(), true));
			}
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0009']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 14:".$e->getCode().': '.$e->getMessage());
			}
		} elseif(strstr($event["msg"], '!clean')) {
			usleep($cfg['teamspeak_query_command_delay']);
			try {
				$host->serverGetSelected()->clientGetByUid($event["invokeruid"])->message($lang['msg0003']);
			} catch (Exception $e) {
				enter_logfile($cfg,2,"handle_messages 15:".$e->getCode().': '.$e->getMessage());
			}
		}
	}
}
?>