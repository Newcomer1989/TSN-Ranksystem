<?PHP
function reset_rs($ts3,$mysqlcon,$lang,$cfg,$dbname,$select_arr) {
	$starttime = microtime(true);
	
	if (in_array($select_arr['job_check']['reset_user_time']['timestamp'], ["1","2"], true) || in_array($select_arr['job_check']['reset_user_delete']['timestamp'], ["1","2"], true) || in_array($select_arr['job_check']['reset_group_withdraw']['timestamp'], ["1","2"], true) || in_array($select_arr['job_check']['reset_webspace_cache']['timestamp'], ["1","2"], true) || in_array($select_arr['job_check']['reset_usage_graph']['timestamp'], ["1","2"], true)) {

		enter_logfile($cfg,4,"Reset job(s) started");
		$err_cnt = 0;
		
		if (in_array($select_arr['job_check']['reset_group_withdraw']['timestamp'], ["1","2"], true)) {
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='2' WHERE `job_name`='reset_group_withdraw';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err_cnt++;
			} else {
				enter_logfile($cfg,4,"  Started job '".$lang['wihladm32']."'");
			}
		
			krsort($cfg['rankup_definition']);

			if (($all_clients = $mysqlcon->query("SELECT cldbid,uuid,name FROM `$dbname`.`user`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
				shutdown($mysqlcon,$cfg,1,"Select on DB failed: ".print_r($mysqlcon->errorInfo(), true));
			}
		
			foreach ($cfg['rankup_definition'] as $time => $groupid) {
				enter_logfile($cfg,5,"    Getting TS3 servergrouplist for ".$select_arr['groups'][$groupid]['sgidname']." (ID: ".$groupid.")");
				try {
					usleep($cfg['teamspeak_query_command_delay']);
					$tsclientlist = $ts3->servergroupclientlist($groupid);
					
					foreach ($tsclientlist as $tsclient) {
						if (isset($all_clients[$tsclient['cldbid']])) {
							try {
								usleep($cfg['teamspeak_query_command_delay']);
								$ts3->serverGroupClientDel($groupid, $tsclient['cldbid']);
								enter_logfile($cfg,5,"      ".sprintf($lang['sgrprm'], $select_arr['groups'][$groupid]['sgidname'], $groupid, $all_clients[$tsclient['cldbid']]['name'], $all_clients[$tsclient['cldbid']]['uuid'], $tsclient['cldbid']));
							} catch (Exception $e) {
								enter_logfile($cfg,2,"      TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $all_clients[$tsclient['cldbid']]['name'], $all_clients[$tsclient['cldbid']]['uuid'], $tsclient['cldbid'], $select_arr['groups'][$groupid]['sgidname'], $groupid));
								$err_cnt++;
							}
						}
					}
				} catch (Exception $e) {
					enter_logfile($cfg,2,"    TS3 error: ".$e->getCode().': '.$e->getMessage()." due getting servergroupclientlist for group ".$groupid);
					$err_cnt++;
				}
			}
			if ($err_cnt == 0) {
				if ($mysqlcon->exec("UPDATE `$dbname`.`user` SET `grpid`=0; UPDATE `$dbname`.`job_check` SET `timestamp`='4' WHERE `job_name`='reset_group_withdraw';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				} else {
					enter_logfile($cfg,4,"  Finished job '".$lang['wihladm32']."'");
				}
			} else {
				if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='3' WHERE `job_name`='reset_group_withdraw';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
			}
		}


		if ($err_cnt == 0 && in_array($select_arr['job_check']['reset_user_time']['timestamp'], ["1","2"], true)) {
			$err = 0;
			
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='2' WHERE `job_name`='reset_user_time';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"  Started job '".$lang['wihladm31']."' (".$lang['wisupidle'].": ".$lang['wihladm311'].")");
			}
			
			// zero  times
			if($mysqlcon->exec("UPDATE `$dbname`.`stats_server` SET `total_online_time`='0', `total_online_month`='0', `total_online_week`='0', `total_active_time`='0', `total_inactive_time`='0';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Reset Server statistics summary (table: stats_server)");
			}
			if($mysqlcon->exec("UPDATE `$dbname`.`stats_user` SET `rank`='0', `count_week`='0', `count_month`='0', `idle_week`='0', `idle_month`='0', `achiev_count`='0', `achiev_time`='0', `achiev_connects`='0', `achiev_time_perc`='0', `achiev_connects_perc`='0', `total_connections`='0', `active_week`='0', `active_month`='0';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Reset My statistics (table: stats_user)");
			}
			if($mysqlcon->exec("UPDATE `$dbname`.`user` SET `count`='0', `grpid`='0', `nextup`='0', `idle`='0', `boosttime`='0', `rank`='0', `grpsince`='0';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Reset List Rankup / user statistics (table: user)");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`user_snapshot`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned Top users / user statistic snapshots (table: user_snapshot)");
			}


			if ($err == 0) {
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='4' WHERE `job_name`='reset_user_time';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				} else {
					enter_logfile($cfg,4,"  Finished job '".$lang['wihladm31']."' (".$lang['wisupidle'].": ".$lang['wihladm311'].")");
				}
			} else {
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='3' WHERE `job_name`='reset_user_time';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
			}
		}
		
		
		if ($err_cnt == 0 && in_array($select_arr['job_check']['reset_user_delete']['timestamp'], ["1","2"], true)) {
			$err = 0;
			
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='2' WHERE `job_name`='reset_user_delete';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"  Started job '".$lang['wihladm31']."' (".$lang['wisupidle'].": ".$lang['wihladm312'].")");
			}
			
			// remove clients
			if($mysqlcon->exec("DELETE FROM `$dbname`.`stats_nations`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned donut chart nations (table: stats_nations)");
			}
			if($mysqlcon->exec("UPDATE `$dbname`.`stats_platforms` SET `count`=0;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned donut chart platforms (table: stats_platforms)");
			}
			if($mysqlcon->exec("UPDATE `$dbname`.`stats_server` SET `total_user`='0', `total_online_time`='0', `total_online_month`='0', `total_online_week`='0', `total_active_time`='0', `total_inactive_time`='0', `country_nation_name_1`='', `country_nation_name_2`='', `country_nation_name_3`='', `country_nation_name_4`='', `country_nation_name_5`='', `country_nation_1`='0', `country_nation_2`='0', `country_nation_3`='0', `country_nation_4`='0', `country_nation_5`='0', `country_nation_other`='0', `platform_1`='0', `platform_2`='0', `platform_3`='0', `platform_4`='0', `platform_5`='0', `platform_other`='0', `version_name_1`='', `version_name_2`='', `version_name_3`='', `version_name_4`='', `version_name_5`='', `version_1`='0', `version_2`='0', `version_3`='0', `version_4`='0', `version_5`='0', `version_other`='0';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Reset Server statistics summary (table: stats_server)");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`stats_user`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned My statistics (table: stats_user)");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`stats_versions`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned donut chart versions (table: stats_versions)");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`user`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned List Rankup / user statistics (table: user)");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`user_iphash`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned user ip-hash values (table: user_iphash)");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`user_snapshot`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				$err++;
			} else {
				enter_logfile($cfg,4,"    Cleaned Top users / user statistic snapshots (table: user_snapshot)");
			}
			
			
			if ($err == 0) {
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='4' WHERE `job_name`='reset_user_delete';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				} else {
					enter_logfile($cfg,4,"  Finished job '".$lang['wihladm31']."' (".$lang['wisupidle'].": ".$lang['wihladm312'].")");
				}
			} else {
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='3' WHERE `job_name`='reset_user_delete';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
			}
		}

		if (in_array($select_arr['job_check']['reset_webspace_cache']['timestamp'], ["1","2"], true)) {
			if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='2' WHERE `job_name`='reset_webspace_cache';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
			} else {
				enter_logfile($cfg,4,"  Started job '".$lang['wihladm33']."'");
			}
			
			$del_folder = array('avatars/','tsicons/');
			$err_cnt = 0;
			
			if (!function_exists('rm_file_reset')) {
				function rm_file_reset($folder,$cfg) {
					foreach(scandir($folder) as $file) {
						if ('.' === $file || '..' === $file || 'rs.png' === $file || is_dir($folder.$file)) {
							continue;
						} else {
							if(unlink($folder.$file)) {
								enter_logfile($cfg,4,"    File ".$folder.$file." successfully deleted.");
							} else {
								enter_logfile($cfg,2,"    File ".$folder.$file." couldn't be deleted. Please check the file permissions.");
								$err_cnt++;
							}
						}
					}
				}
			}

			foreach ($del_folder as $folder) {
				if(is_dir(substr(__DIR__,0,-4).$folder)) {
					rm_file_reset(substr(__DIR__,0,-4).$folder,$cfg);
				}
			}
			
			if ($err_cnt == 0) {
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='4' WHERE `job_name`='reset_webspace_cache';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				} else {
					enter_logfile($cfg,4,"  Finished job '".$lang['wihladm33']."'");
				}
			} else {
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='3' WHERE `job_name`='reset_webspace_cache';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
			}
		}


		if (in_array($select_arr['job_check']['reset_usage_graph']['timestamp'], ["1","2"], true)) {
			if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='2' WHERE `job_name`='reset_usage_graph';") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
			} else {
				enter_logfile($cfg,4,"  Started job '".$lang['wihladm34']."'");
			}
			if ($mysqlcon->exec("DELETE FROM `$dbname`.`server_usage`;") === false) {
				enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='3' WHERE `job_name`='reset_usage_graph';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				}
			} else {
				enter_logfile($cfg,4,"    Cleaned server usage graph (table: server_usage)");
				if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='4' WHERE `job_name`='reset_usage_graph';") === false) {
					enter_logfile($cfg,2,"  Executing SQL commands failed: ".print_r($mysqlcon->errorInfo(), true));
				} else {
					enter_logfile($cfg,4,"  Finished job '".$lang['wihladm34']."'");
				}
			}
		}

		enter_logfile($cfg,4,"Reset job(s) finished");
		
		if($select_arr['job_check']['reset_stop_after']['timestamp'] == "1") {
			$path = substr(__DIR__, 0, -4);
			if (substr(php_uname(), 0, 7) == "Windows") {
				exec("start ".$phpcommand." ".$path."worker.php stop");
				file_put_contents(substr(__DIR__,0,-4).'logs\autostart_deactivated',"");
			} else {
				exec($phpcommand." ".$path."worker.php stop > /dev/null &");
				file_put_contents(substr(__DIR__,0,-4).'logs/autostart_deactivated',"");
			}
			shutdown($mysqlcon,$cfg,4,"Stop requested after Reset job. Wait for manually start.");
		}
	}
	enter_logfile($cfg,6,"reset_rs needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
}
?>