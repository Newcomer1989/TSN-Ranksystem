<?PHP
function calc_userstats($ts3,$mysqlcon,$cfg,$dbname,$select_arr) {
	$starttime = microtime(true);
	$sqlexec = '';

	$job_begin = $select_arr['job_check']['calc_user_limit']['timestamp'];
	$job_end = ceil(count($select_arr['all_user']) / 10) * 10;
	if ($job_begin >= $job_end) {
		$job_begin = 0;
		$job_end = 10;
	} else {
		$job_end = $job_begin + 10;
	}

	$sqlhis = array_slice($select_arr['all_user'],$job_begin ,10);
	
	$cldbids = '';
	foreach ($sqlhis as $uuid => $userstats) {
		$cldbids .= $userstats['cldbid'].',';
	}
	$cldbids = substr($cldbids, 0, -1);
	
	$weekago = $select_arr['job_check']['last_snapshot_id']['timestamp'] - 28;
	$monthago = $select_arr['job_check']['last_snapshot_id']['timestamp'] - 120;
	if ($weekago < 1) $weekago = $weekago + 121;
	if ($monthago < 1) $monthago = $monthago + 121;

	if(isset($sqlhis)) {
		enter_logfile($cfg,6,"Update User Stats between ".$job_begin." and ".$job_end.":");
		if(($userdata = $mysqlcon->query("SELECT `cldbid`,`id`,`count`,`idle` FROM `$dbname`.`user_snapshot` WHERE `id` IN ({$select_arr['job_check']['last_snapshot_id']['timestamp']},{$weekago},{$monthago}) AND `cldbid` IN ($cldbids)")->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC)) === false) {
			enter_logfile($cfg,2,"calc_userstats 6:".print_r($mysqlcon->errorInfo(), true));
		}

		$allupdateuuid = '';
		
		foreach ($sqlhis as $uuid => $userstats) {
			check_shutdown($cfg); usleep($cfg['teamspeak_query_command_delay']);
			try {
				$clientinfo = $ts3->clientInfoDb($userstats['cldbid']);
				$keybase = array_search($select_arr['job_check']['last_snapshot_id']['timestamp'], array_column($userdata[$userstats['cldbid']], 'id'));
				$keyweek = array_search($weekago, array_column($userdata[$userstats['cldbid']], 'id'));
				$keymonth = array_search($monthago, array_column($userdata[$userstats['cldbid']], 'id'));

				if(isset($userdata[$userstats['cldbid']]) && isset($userdata[$userstats['cldbid']][$keyweek]) && $userdata[$userstats['cldbid']][$keyweek]['id'] == $weekago) {
					$count_week = $userdata[$userstats['cldbid']][$keybase]['count'] - $userdata[$userstats['cldbid']][$keyweek]['count'];
					$idle_week = $userdata[$userstats['cldbid']][$keybase]['idle'] - $userdata[$userstats['cldbid']][$keyweek]['idle'];
					$active_week = $count_week - $idle_week;
				} else {
					$count_week = 0;
					$idle_week = 0;
					$active_week = 0;
				}
				if(isset($userdata[$userstats['cldbid']]) && isset($userdata[$userstats['cldbid']][$keymonth]) && $userdata[$userstats['cldbid']][$keymonth]['id'] == $monthago) {
					$count_month = $userdata[$userstats['cldbid']][$keybase]['count'] - $userdata[$userstats['cldbid']][$keymonth]['count'];
					$idle_month = $userdata[$userstats['cldbid']][$keybase]['idle'] - $userdata[$userstats['cldbid']][$keymonth]['idle'];
					$active_month = $count_month - $idle_month;
				} else {
					$count_month = 0;
					$idle_month = 0;
					$active_month = 0;
				}
				$clientdesc = $mysqlcon->quote($clientinfo['client_description'], ENT_QUOTES);
				$allupdateuuid .= "('$uuid',$count_week,$count_month,$idle_week,$idle_month,$active_week,$active_month,{$clientinfo['client_totalconnections']},'{$clientinfo['client_base64HashClientUID']}',{$clientinfo['client_total_bytes_uploaded']},{$clientinfo['client_total_bytes_downloaded']},$clientdesc),";
			} catch (Exception $e) {
				if($e->getCode() == 512) {
					enter_logfile($cfg,6,"Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") known by Ranksystem is missing in TS database, perhaps its already deleted or cldbid changed. Try to search for client by uuid.");
					try {
						$getcldbid = $ts3->clientFindDb($uuid, TRUE);
						if($getcldbid[0] != $userstats['cldbid']) {
							enter_logfile($cfg,4,"  Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") known by the Ranksystem changed its cldbid. New cldbid is ".$getcldbid[0]."."); 
							if($cfg['rankup_client_database_id_change_switch'] == 1) {
								$sqlexec .= "UPDATE `$dbname`.`user` SET `count`=0,`idle`=0 WHERE `uuid`='$uuid'; UPDATE `$dbname`.`stats_user` SET `count_week`=0,`count_month`=0,`idle_week`=0,`idle_month`=0,`achiev_time`=0,`achiev_time_perc`=0,`active_week`=0,`active_month`=0 WHERE `uuid`='$uuid'; DELETE FROM `$dbname`.`user_snapshot` WHERE `cldbid`='{$userstats['cldbid']}'; ";
								enter_logfile($cfg,4,"    ".sprintf($lang['changedbid'], $userstats['name'], $uuid, $userstats['cldbid'], $getcldbid[0]));
							} else {
								$sqlexec .= "UPDATE `$dbname`.`user` SET `cldbid`={$getcldbid[0]} WHERE `uuid`='$uuid'; ";
								enter_logfile($cfg,4,"    Store new cldbid ".$getcldbid[0]." for client (uuid: ".$uuid." old cldbid: ".$userstats['cldbid'].")"); 
							}
						} else {
							enter_logfile($cfg,3,"  Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") is missing in TS database, but TeamSpeak answers on question with the same cldbid (".$getcldbid[0].").. It's weird!"); 
						}
					} catch (Exception $e) {
						if($e->getCode() == 2568) {
							enter_logfile($cfg,4,$e->getCode() . ': ' . $e->getMessage()."; Error due command clientdbfind (permission: b_virtualserver_client_dbsearch needed).");
						} else {
							enter_logfile($cfg,6,$e->getCode() . ': ' . $e->getMessage()."; Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") is missing in TS database, it seems to be deleted. Run !clean to correct this.");
						}
					}
				} else {
					enter_logfile($cfg,2,$lang['errorts3'].$e->getCode().': '.$e->getMessage()."; Error due command clientdbinfo (permission: b_virtualserver_client_dbinfo needed).");
				}
			}
		}
		unset($sqlhis,$userdataweekbegin,$userdataend,$userdatamonthbegin,$clientinfo,$count_week,$idle_week,$active_week,$count_month,$idle_month,$active_month,$clientdesc);

		if ($allupdateuuid != '') {
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$job_end WHERE `job_name`='calc_user_limit'; INSERT INTO `$dbname`.`stats_user` (`uuid`,`count_week`,`count_month`,`idle_week`,`idle_month`,`active_week`,`active_month`,`total_connections`,`base64hash`,`client_total_up`,`client_total_down`,`client_description`) VALUES $allupdateuuid ON DUPLICATE KEY UPDATE `count_week`=VALUES(`count_week`),`count_month`=VALUES(`count_month`),`idle_week`=VALUES(`idle_week`),`idle_month`=VALUES(`idle_month`),`active_week`=VALUES(`active_week`),`active_month`=VALUES(`active_month`),`total_connections`=VALUES(`total_connections`),`base64hash`=VALUES(`base64hash`),`client_total_up`=VALUES(`client_total_up`),`client_total_down`=VALUES(`client_total_down`),`client_description`=VALUES(`client_description`); ";
			unset($allupdateuuid);
		} else {
			$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$job_end WHERE `job_name`='calc_user_limit'; ";
		}
	}

	enter_logfile($cfg,6,"calc_userstats needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
	return($sqlexec);
}
?>