<?PHP
function calc_userstats($ts3,$mysqlcon,$cfg,$dbname,&$db_cache) {
	$starttime = microtime(true);
	$nowtime = time();
	$sqlexec = '';

	$job_begin = intval($db_cache['job_check']['calc_user_limit']['timestamp']);
	
	$limit_calc = ceil(count($db_cache['all_user']) / 600);
	if($limit_calc < 5) $limit_calc = 5;

	if ($job_begin >= (ceil(count($db_cache['all_user']) / $limit_calc) * $limit_calc)) {
		$job_begin = 0;
		$job_end = $limit_calc;
	} else {
		$job_end = $job_begin + $limit_calc;
	}

	$sqlhis = array_slice($db_cache['all_user'],$job_begin ,$limit_calc);

	$cldbids = '';
	foreach ($sqlhis as $uuid => $userstats) {
		$cldbids .= $userstats['cldbid'].',';
	}
	$cldbids = substr($cldbids, 0, -1);

	$dayago = intval($db_cache['job_check']['last_snapshot_id']['timestamp']) - 4;
	$weekago = intval($db_cache['job_check']['last_snapshot_id']['timestamp']) - 28;
	$monthago = intval($db_cache['job_check']['last_snapshot_id']['timestamp']) - 120;
	if ($dayago < 1) $dayago += 121;
	if ($weekago < 1) $weekago += 121;
	if ($monthago < 1) $monthago += 121;

	if(isset($sqlhis) && $sqlhis != NULL) {
		enter_logfile(6,"Update User Stats between ".$job_begin." and ".$job_end.":");
		if(($userdata = $mysqlcon->query("SELECT `cldbid`,`id`,`count`,`idle` FROM `$dbname`.`user_snapshot` WHERE `id` IN ({$db_cache['job_check']['last_snapshot_id']['timestamp']},{$dayago},{$weekago},{$monthago}) AND `cldbid` IN ($cldbids)")->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC)) === false) {
			enter_logfile(2,"calc_userstats 6:".print_r($mysqlcon->errorInfo(), true));
		}

		$allupdateuuid = '';

		foreach ($sqlhis as $uuid => $userstats) {
			if($userstats['lastseen'] > ($nowtime - 2678400)) {
				check_shutdown(); usleep($cfg['teamspeak_query_command_delay']);

				if(isset($userdata[$userstats['cldbid']]) && $userdata[$userstats['cldbid']] != NULL) {
					$keybase = array_search($db_cache['job_check']['last_snapshot_id']['timestamp'], array_column($userdata[$userstats['cldbid']], 'id'));
					$keyday = array_search($dayago, array_column($userdata[$userstats['cldbid']], 'id'));
					$keyweek = array_search($weekago, array_column($userdata[$userstats['cldbid']], 'id'));
					$keymonth = array_search($monthago, array_column($userdata[$userstats['cldbid']], 'id'));

					if(isset($userdata[$userstats['cldbid']]) && isset($userdata[$userstats['cldbid']][$keyday]) && $userdata[$userstats['cldbid']][$keyday]['id'] == $dayago) {
						$count_day = $userdata[$userstats['cldbid']][$keybase]['count'] - $userdata[$userstats['cldbid']][$keyday]['count'];
						$idle_day = $userdata[$userstats['cldbid']][$keybase]['idle'] - $userdata[$userstats['cldbid']][$keyday]['idle'];
						$active_day = $count_day - $idle_day;
						if($count_day < 0 || $count_day < $idle_day || $count_day > 16777215) $count_day = 0;
						if($idle_day < 0 || $count_day < $idle_day || $idle_day > 16777215) $idle_day = 0;
						if($active_day < 0 || $count_day < $idle_day || $active_day > 16777215) $active_day = 0;
					} else {
						$count_day = $idle_day = $active_day = 0;
					}
					if(isset($userdata[$userstats['cldbid']]) && isset($userdata[$userstats['cldbid']][$keyweek]) && $userdata[$userstats['cldbid']][$keyweek]['id'] == $weekago) {
						$count_week = $userdata[$userstats['cldbid']][$keybase]['count'] - $userdata[$userstats['cldbid']][$keyweek]['count'];
						$idle_week = $userdata[$userstats['cldbid']][$keybase]['idle'] - $userdata[$userstats['cldbid']][$keyweek]['idle'];
						$active_week = $count_week - $idle_week;
						if($count_week < 0 || $count_week < $idle_week || $count_week > 16777215) $count_week = 0;
						if($idle_week < 0 || $count_week < $idle_week || $idle_week > 16777215) $idle_week = 0;
						if($active_week < 0 || $count_week < $idle_week || $active_week > 16777215) $active_week = 0;
					} else {
						$count_week = $idle_week = $active_week = 0;
					}
					if(isset($userdata[$userstats['cldbid']]) && isset($userdata[$userstats['cldbid']][$keymonth]) && $userdata[$userstats['cldbid']][$keymonth]['id'] == $monthago) {
						$count_month = $userdata[$userstats['cldbid']][$keybase]['count'] - $userdata[$userstats['cldbid']][$keymonth]['count'];
						$idle_month = $userdata[$userstats['cldbid']][$keybase]['idle'] - $userdata[$userstats['cldbid']][$keymonth]['idle'];
						$active_month = $count_month - $idle_month;
						if($idle_month < 0 || $count_month < $idle_month || $idle_month > 16777215) $idle_month = 0;
						if($count_month < 0 || $count_month < $idle_month || $count_month > 16777215) $count_month = 0;
						if($active_month < 0 || $count_month < $idle_month || $active_month > 16777215) $active_month = 0;
					} else {
						$count_month = $idle_month = $active_month = 0;
					}
				} else {
					$count_day = $idle_day = $active_day = $count_week = $idle_week = $active_week = $count_month = $idle_month = $active_month = 0;
				}

				try {
					$clientinfo = $ts3->clientInfoDb($userstats['cldbid']);
					if($clientinfo['client_description'] !== NULL) {
						$clientdesc = $mysqlcon->quote($clientinfo['client_description'], ENT_QUOTES);
					} else {
						$clientdesc = "NULL";
					}
					if($clientinfo['client_totalconnections'] > 16777215) $clientinfo['client_totalconnections'] = 16777215;
				} catch (Exception $e) {
					if($e->getCode() == 512 || $e->getCode() == 1281) {
						enter_logfile(6,"Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") known by Ranksystem is missing in TS database, perhaps its already deleted or cldbid changed. Searching for client by uuid.");
						try {
							$getcldbid = $ts3->clientFindDb($uuid, TRUE);
							if($getcldbid[0] != $userstats['cldbid']) {
								enter_logfile(4,"  Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") known by the Ranksystem changed its cldbid. New cldbid is ".$getcldbid[0]."."); 
								$db_cache['all_user'][$uuid]['cldbid'] = $getcldbid[0];
								$sqlexec .= "DELETE FROM `$dbname`.`user_snapshot` WHERE `cldbid` IN ('{$userstats['cldbid']}','{$getcldbid[0]}');\n";
								try {
									$clientinfo = $ts3->clientInfoDb($getcldbid[0]);
								} catch (Exception $e) {
									enter_logfile(2,$lang['errorts3'].$e->getCode().': '.$e->getMessage()."; Error due command clientdbinfo for client-database-ID {$getcldbid[0]} (permission: b_virtualserver_client_dbinfo needed).");
								}
								if($cfg['rankup_client_database_id_change_switch'] == 1) {
									$sqlexec .= "UPDATE `$dbname`.`user` SET `count`=0,`idle`=0 WHERE `uuid`='$uuid';\n";
									$sqlexec .= "UPDATE `$dbname`.`stats_user` SET `count_day`=0,`count_week`=0,`count_month`=0,`idle_day`=0,`idle_week`=0,`idle_month`=0,`active_day`=0,`active_week`=0,`active_month`=0 WHERE `uuid`='$uuid';\n";
									enter_logfile(4,"    ".sprintf($lang['changedbid'], $userstats['name'], $uuid, $userstats['cldbid'], $getcldbid[0]));
								} else {
									$sqlexec .= "UPDATE `$dbname`.`user` SET `cldbid`={$getcldbid[0]} WHERE `uuid`='$uuid';\n";
									// select current user_snapshot entries and insert this with the new database-ID
									if(isset($userdata[$userstats['cldbid']])) {
										$allinsert = '';
										foreach($userdata[$userstats['cldbid']] as $id => $data) {
											$allinsert .= "($id,'{$getcldbid[0]}',{$data['count']},{$data['idle']}),";
										}
										if ($allinsert != '') {
											$allinsert = substr($allinsert, 0, -1);
											$sqlexec .= "INSERT INTO `$dbname`.`user_snapshot` (`id`,`cldbid`,`count`,`idle`) VALUES $allinsert ON DUPLICATE KEY UPDATE `count`=VALUES(`count`),`idle`=VALUES(`idle`);\n";
										}
										unset($allinsert);
									}
									enter_logfile(4,"    Store new cldbid ".$getcldbid[0]." for client (uuid: ".$uuid." old cldbid: ".$userstats['cldbid'].")"); 
								}
							} else {
								enter_logfile(3,"  Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") is missing in TS database, but TeamSpeak answers on question with the same cldbid (".$getcldbid[0].").. It's weird!"); 
							}
						} catch (Exception $e) {
							if($e->getCode() == 2568) {
								enter_logfile(4,$e->getCode() . ': ' . $e->getMessage()."; Error due command clientdbfind (permission: b_virtualserver_client_dbsearch needed).");
							} else {
								enter_logfile(6,$e->getCode() . ': ' . $e->getMessage()."; Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") is missing in TS database, it seems to be deleted. Run the !clean command to correct this.");
								$sqlexec .= "UPDATE `$dbname`.`stats_user` SET `count_day`=0,`count_week`=0,`count_month`=0,`idle_day`=0,`idle_week`=0,`idle_month`=0,`active_day`=0,`active_week`=0,`active_month`=0,`removed`=1 WHERE `uuid`='$uuid';\n";
							}
						}
					} else {
						enter_logfile(2,$lang['errorts3'].$e->getCode().': '.$e->getMessage()."; Error due command clientdbinfo for client-database-ID {$userstats['cldbid']} (permission: b_virtualserver_client_dbinfo needed).");
					}

					$clientdesc = $clientinfo['client_base64HashClientUID'] = "NULL";
					$clientinfo['client_totalconnections'] = $clientinfo['client_total_bytes_uploaded'] = $clientinfo['client_total_bytes_downloaded'] = 0;
				}

				$allupdateuuid .= "('$uuid',$count_day,$count_week,$count_month,$idle_day,$idle_week,$idle_month,$active_day,$active_week,$active_month,{$clientinfo['client_totalconnections']},'{$clientinfo['client_base64HashClientUID']}',{$clientinfo['client_total_bytes_uploaded']},{$clientinfo['client_total_bytes_downloaded']},$clientdesc,$nowtime),";
			}
		}
		unset($sqlhis,$userdataweekbegin,$userdataend,$userdatamonthbegin,$clientinfo,$count_day,$idle_day,$active_day,$count_week,$idle_week,$active_week,$count_month,$idle_month,$active_month,$clientdesc);

		$db_cache['job_check']['calc_user_limit']['timestamp'] = $job_end;
		if ($allupdateuuid != '') {
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$job_end WHERE `job_name`='calc_user_limit';\nINSERT INTO `$dbname`.`stats_user` (`uuid`,`count_day`,`count_week`,`count_month`,`idle_day`,`idle_week`,`idle_month`,`active_day`,`active_week`,`active_month`,`total_connections`,`base64hash`,`client_total_up`,`client_total_down`,`client_description`,`last_calculated`) VALUES $allupdateuuid ON DUPLICATE KEY UPDATE `count_day`=VALUES(`count_day`),`count_week`=VALUES(`count_week`),`count_month`=VALUES(`count_month`),`idle_day`=VALUES(`idle_day`),`idle_week`=VALUES(`idle_week`),`idle_month`=VALUES(`idle_month`),`active_day`=VALUES(`active_day`),`active_week`=VALUES(`active_week`),`active_month`=VALUES(`active_month`),`total_connections`=VALUES(`total_connections`),`base64hash`=VALUES(`base64hash`),`client_total_up`=VALUES(`client_total_up`),`client_total_down`=VALUES(`client_total_down`),`client_description`=VALUES(`client_description`),`last_calculated`=VALUES(`last_calculated`);\n";
			unset($allupdateuuid);
		} else {
			$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$job_end WHERE `job_name`='calc_user_limit';\n";
		}
	}
	
	if (intval($db_cache['job_check']['calc_user_removed']['timestamp']) < ($nowtime - 1800)) {
		$db_cache['job_check']['calc_user_removed']['timestamp'] = $nowtime;
		$atime = $nowtime - 3600;
		$sqlexec .= "UPDATE `$dbname`.`stats_user` AS `s` INNER JOIN `$dbname`.`user` AS `u` ON `s`.`uuid`=`u`.`uuid` SET `s`.`removed`='0' WHERE `s`.`removed`='1' AND `u`.`lastseen`>{$atime};\nUPDATE `$dbname`.`job_check` SET `timestamp`='{$nowtime}' WHERE `job_name`='calc_user_removed';\n";
	}

	enter_logfile(6,"calc_userstats needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
	return($sqlexec);
}
?>