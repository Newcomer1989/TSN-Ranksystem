<?PHP
function calc_user($ts3,$mysqlcon,$lang,$cfg,$dbname,$allclients,$phpcommand,$select_arr) {
	$starttime = microtime(true);
	$nowtime = time();
	$sqlexec = '';

	if(empty($cfg['rankup_definition'])) {
		shutdown($mysqlcon,$cfg,1,"calc_user:".$lang['wiconferr']);
	}
	if($select_arr['job_check']['calc_user_lastscan']['timestamp'] < ($nowtime - 1800)) {
		enter_logfile($cfg,4,"Much time gone since last scan.. reset time difference to zero.");
		$select_arr['job_check']['calc_user_lastscan']['timestamp'] = $nowtime;
	} elseif($select_arr['job_check']['calc_user_lastscan']['timestamp'] > $nowtime) {
		enter_logfile($cfg,3,"Negative time between now and last scan (Error in your server time!).. reset time difference to zero.");
		$select_arr['job_check']['calc_user_lastscan']['timestamp'] = $nowtime;
	}

	$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$nowtime WHERE `job_name`='calc_user_lastscan'; UPDATE `$dbname`.`user` SET `online`=0 WHERE `online`=1; ";

	$multipleonline = $updatedata = $insertdata = array();

	if(isset($select_arr['admin_addtime']) && count($select_arr['admin_addtime']) != 0) {
		foreach($select_arr['admin_addtime'] as $uuid => $value) {
			if(isset($select_arr['all_user'][$uuid])) {
				$isonline = 0;
				foreach($allclients as $client) {
					if($client['client_unique_identifier'] == $uuid) {
						$isonline = 1;
						$select_arr['all_user'][$uuid]['count'] += $value['timecount'];
					}
				}
				if($isonline != 1) {
					$sqlexec .= "UPDATE `$dbname`.`user` SET `count`=`count` + ".$value['timecount']." WHERE `uuid`='$uuid'; ";
				}
				$sqlexec .= "DELETE FROM `$dbname`.`admin_addtime` WHERE `timestamp`=".$value['timestamp']." AND `uuid`='$uuid'; ";
				$sqlexec .= "UPDATE `$dbname`.`user_snapshot` SET `count`=`count` + ".$value['timecount']." WHERE `uuid`='$uuid'; ";
				enter_logfile($cfg,4,sprintf($lang['sccupcount2'],$value['timecount'],$uuid));
			}
		}
	}
	
	foreach ($allclients as $client) {
		$client_groups_rankup = array();
		$name = substr($mysqlcon->quote($client['client_nickname'], ENT_QUOTES),0,30);
		$uid = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
		$sgroups = array_flip(explode(",", $client['client_servergroups']));
		if (!isset($multipleonline[$uid]) && $client['client_version'] != "ServerQuery" && $client['client_type']!="1") {
			$clientidle = floor($client['client_idle_time'] / 1000);
			if(isset($cfg['rankup_ignore_idle_time']) && $clientidle < $cfg['rankup_ignore_idle_time']) {
				$clientidle = 0;
			}
			$multipleonline[$uid] = 0;
			if(isset($cfg['rankup_excepted_unique_client_id_list'][$uid])) {
				$except = 3;
			} elseif(array_intersect_key($sgroups, $cfg['rankup_excepted_group_id_list'])) {
				$except = 2;
			} else {
				if(isset($select_arr['all_user'][$uid]['except']) && ($select_arr['all_user'][$uid]['except'] == 3 || $select_arr['all_user'][$uid]['except'] == 2) && $cfg['rankup_excepted_mode'] == 2) { 
				 	$select_arr['all_user'][$uid]['count'] = 0;
				 	$select_arr['all_user'][$uid]['idle'] = 0;
					enter_logfile($cfg,5,sprintf($lang['resettime'], $name, $uid, $client['client_database_id']));
					$sqlexec .= "DELETE FROM `$dbname`.`user_snapshot` WHERE `uuid`='$uid'; ";
				}
				$except = 0;
			}
			if(isset($select_arr['all_user'][$uid])) {
				$idle = $select_arr['all_user'][$uid]['idle'] + $clientidle;
				if ($select_arr['all_user'][$uid]['cldbid'] != $client['client_database_id'] && $cfg['rankup_client_database_id_change_switch'] == 1) {
					enter_logfile($cfg,5,sprintf($lang['changedbid'], $name, $uid, $client['client_database_id'], $select_arr['all_user'][$uid]['cldbid']));
						$count = 1;
						$idle  = 0;
				} else {
					$hitboost = 0;
					$boosttime = $select_arr['all_user'][$uid]['boosttime'];
					if(isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != NULL) {
						foreach($cfg['rankup_boost_definition'] as $boost) {
							if(isset($sgroups[$boost['group']])) {
								$hitboost = 1;
								if($select_arr['all_user'][$uid]['boosttime']==0) {
									$boosttime = $nowtime;
								} else {
									if ($nowtime > $select_arr['all_user'][$uid]['boosttime'] + $boost['time']) {
										usleep($cfg['teamspeak_query_command_delay']);
										try {
											$ts3->serverGroupClientDel($boost['group'], $client['client_database_id']);
											$boosttime = 0;
											enter_logfile($cfg,5,sprintf($lang['sgrprm'], $select_arr['groups'][$boost['group']]['sgidname'], $boost['group'], $name, $uid, $client['client_database_id']));
										} catch (Exception $e) {
											enter_logfile($cfg,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $client['client_database_id'], $select_arr['groups'][$select_arr['all_user'][$uid]['grpid']]['sgidname'], $select_arr['all_user'][$uid]['grpid']));
										}
									}
								}
								$count = ($nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp']) * $boost['factor'] + $select_arr['all_user'][$uid]['count'];
								if ($clientidle > ($nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp'])) {
									$idle = ($nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp']) * $boost['factor'] + $select_arr['all_user'][$uid]['idle'];
								}
							}
						}
					}
					if($cfg['rankup_boost_definition'] == 0 or $hitboost == 0) {
						$count = $nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp'] + $select_arr['all_user'][$uid]['count'];
						if ($clientidle > ($nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp'])) {
							$idle = $nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp'] + $select_arr['all_user'][$uid]['idle'];
						}
					}
				}
				$dtF = new DateTime("@0");
				if ($cfg['rankup_time_assess_mode'] == 1) {
					$activetime = $count - $idle;
				} else {
					$activetime = $count;
				}
				$dtT = new DateTime("@".round($activetime));

				foreach($sgroups as $clientgroup => $dummy) {
					if(isset($cfg['rankup_definition_flipped'][$clientgroup])) {
						#enter_logfile($cfg,5,"Client ".$name." has group ".$clientgroup);
						$client_groups_rankup[$clientgroup] = 0;
					}
				}

				$grpcount=0;
				foreach ($cfg['rankup_definition'] as $time => $groupid) {
					$grpcount++;
					if(isset($cfg['rankup_excepted_channel_id_list'][$client['cid']]) || (($select_arr['all_user'][$uid]['except'] == 3 || $select_arr['all_user'][$uid]['except'] == 2) && $cfg['rankup_excepted_mode'] == 1)) {
						$count = $select_arr['all_user'][$uid]['count'];
						$idle = $select_arr['all_user'][$uid]['idle'];
						if($except != 2 && $except != 3) {
							$except = 1;
						}
					} elseif ($activetime > $time && !isset($cfg['rankup_excepted_unique_client_id_list'][$uid]) && !array_intersect_key($sgroups, $cfg['rankup_excepted_group_id_list'])) {
						if (!isset($sgroups[$groupid])) {
						#if ($select_arr['all_user'][$uid]['grpid'] != $groupid) {
							if ($select_arr['all_user'][$uid]['grpid'] != NULL && $select_arr['all_user'][$uid]['grpid'] != 0 && isset($sgroups[$select_arr['all_user'][$uid]['grpid']])) {
								usleep($cfg['teamspeak_query_command_delay']);
								try {
									$ts3->serverGroupClientDel($select_arr['all_user'][$uid]['grpid'], $client['client_database_id']);
									enter_logfile($cfg,5,sprintf($lang['sgrprm'], $select_arr['groups'][$select_arr['all_user'][$uid]['grpid']]['sgidname'], $select_arr['all_user'][$uid]['grpid'], $name, $uid, $client['client_database_id']));
									if(isset($client_groups_rankup[$select_arr['all_user'][$uid]['grpid']])) unset($client_groups_rankup[$select_arr['all_user'][$uid]['grpid']]);
								} catch (Exception $e) {
									enter_logfile($cfg,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $client['client_database_id'], $select_arr['groups'][$groupid]['sgidname'], $groupid));
								}
							}
							usleep($cfg['teamspeak_query_command_delay']);
							try {
								$ts3->serverGroupClientAdd($groupid, $client['client_database_id']);
								$select_arr['all_user'][$uid]['grpsince'] = $nowtime;
								enter_logfile($cfg,5,sprintf($lang['sgrpadd'], $select_arr['groups'][$groupid]['sgidname'], $groupid, $name, $uid, $client['client_database_id']));
								if ($cfg['rankup_message_to_user_switch'] == 1) {
									$days  = $dtF->diff($dtT)->format('%a');
									$hours = $dtF->diff($dtT)->format('%h');
									$mins  = $dtF->diff($dtT)->format('%i');
									$secs  = $dtF->diff($dtT)->format('%s');
									sendmessage($ts3, $cfg, $uid, sprintf($cfg['rankup_message_to_user'],$days,$hours,$mins,$secs,$select_arr['groups'][$groupid]['sgidname'],$client['client_nickname']), sprintf($lang['sgrprerr'], $name, $uid, $client['client_database_id'], $select_arr['groups'][$groupid]['sgidname'],$groupid), 2);
								}
							} catch (Exception $e) {
								enter_logfile($cfg,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $client['client_database_id'], $select_arr['groups'][$groupid]['sgidname'], $groupid));
							}
							$select_arr['all_user'][$uid]['grpid'] = $groupid;
						}
						if($grpcount == 1) {
							$select_arr['all_user'][$uid]['nextup'] = 0;
						}
						break;
					} else {
						$select_arr['all_user'][$uid]['nextup'] = $time - $activetime;
					}
				}

				foreach($client_groups_rankup as $removegroup => $dummy) {
					if($removegroup != NULL && $removegroup != 0 && $removegroup != $select_arr['all_user'][$uid]['grpid']){
						try {
							usleep($cfg['teamspeak_query_command_delay']);
							$ts3->serverGroupClientDel($removegroup, $client['client_database_id']);
							enter_logfile($cfg,5,sprintf("Removed WRONG servergroup %s (ID: %s) from user %s (unique Client-ID: %s; Client-database-ID %s).", $select_arr['groups'][$removegroup]['sgidname'], $removegroup, $name, $uid, $client['client_database_id']));
						} catch (Exception $e) {
							enter_logfile($cfg,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $client['client_database_id'], $select_arr['groups'][$groupid]['sgidname'], $groupid));
						}
					}
				}
				unset($client_groups_rankup);
				$updatedata[] = array(
					"uuid" => $mysqlcon->quote($client['client_unique_identifier'], ENT_QUOTES),
					"cldbid" => $client['client_database_id'],
					"count" => $count,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $select_arr['all_user'][$uid]['grpid'],
					"nextup" => $select_arr['all_user'][$uid]['nextup'],
					"idle" => $idle,
					"cldgroup" => $client['client_servergroups'],
					"boosttime" => $boosttime,
					"platform" => $client['client_platform'],
					"nation" => $client['client_country'],
					"version" => $client['client_version'],
					"except" => $except,
					"grpsince" => $select_arr['all_user'][$uid]['grpsince'],
					"cid" => $client['cid']
				);
			} else {
				$select_arr['all_user'][$uid]['grpid'] = '0';
				foreach ($cfg['rankup_definition'] as $time => $groupid) {
					if (isset($sgroups[$groupid])) {
						$select_arr['all_user'][$uid]['grpid'] = $groupid;
						break;
					}
				}
				$insertdata[] = array(
					"uuid" => $mysqlcon->quote($client['client_unique_identifier'], ENT_QUOTES),
					"cldbid" => $client['client_database_id'],
					"count" => 0,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $select_arr['all_user'][$uid]['grpid'],
					"nextup" => (key($cfg['rankup_definition']) - 1),
					"idle" => 0,
					"cldgroup" => $client['client_servergroups'],
					"boosttime" => 0,
					"platform" => $client['client_platform'],
					"nation" => $client['client_country'],
					"version" => $client['client_version'],
					"firstcon" => $nowtime,
					"except" => $except,
					"grpsince" => 0,
					"cid" => $client['cid']
				);
				enter_logfile($cfg,5,sprintf($lang['adduser'], $name, $uid, $client['client_database_id']));
			}
		}
	}
	unset($multipleonline,$allclients,$client,$select_arr);

	if ($updatedata != NULL) {
		$sqlinsertvalues = '';
		foreach ($updatedata as $updatearr) {
			$sqlinsertvalues .= "(".$updatearr['uuid'].",".$updatearr['cldbid'].",".$updatearr['count'].",".$updatearr['name'].",".$updatearr['lastseen'].",".$updatearr['grpid'].",".$updatearr['nextup'].",".$updatearr['idle'].",'".$updatearr['cldgroup']."',".$updatearr['boosttime'].",'".$updatearr['platform']."','".$updatearr['nation']."','".$updatearr['version']."',".$updatearr['except'].",".$updatearr['grpsince'].",".$updatearr['cid'].",1),";
		}
		$sqlinsertvalues = substr($sqlinsertvalues, 0, -1);
		$sqlexec .= "INSERT INTO `$dbname`.`user` (`uuid`,`cldbid`,`count`,`name`,`lastseen`,`grpid`,`nextup`,`idle`,`cldgroup`,`boosttime`,`platform`,`nation`,`version`,`except`,`grpsince`,`cid`,`online`) VALUES $sqlinsertvalues ON DUPLICATE KEY UPDATE `cldbid`=VALUES(`cldbid`),`count`=VALUES(`count`),`name`=VALUES(`name`),`lastseen`=VALUES(`lastseen`),`grpid`=VALUES(`grpid`),`nextup`=VALUES(`nextup`),`idle`=VALUES(`idle`),`cldgroup`=VALUES(`cldgroup`),`boosttime`=VALUES(`boosttime`),`platform`=VALUES(`platform`),`nation`=VALUES(`nation`),`version`=VALUES(`version`),`except`=VALUES(`except`),`grpsince`=VALUES(`grpsince`),`cid`=VALUES(`cid`),`online`=VALUES(`online`); ";
		unset($updatedata, $sqlinsertvalues);
	}
	
	if ($insertdata != NULL) {
		$sqlinsertvalues = '';
		foreach ($insertdata as $updatearr) {
			$sqlinsertvalues .= "(".$updatearr['uuid'].",".$updatearr['cldbid'].",".$updatearr['count'].",".$updatearr['name'].",".$updatearr['lastseen'].",".$updatearr['grpid'].",".$updatearr['nextup'].",".$updatearr['idle'].",'".$updatearr['cldgroup']."',".$updatearr['boosttime'].",'".$updatearr['platform']."','".$updatearr['nation']."','".$updatearr['version']."',".$updatearr['except'].",".$updatearr['grpsince'].",".$updatearr['cid'].",1,".$updatearr['firstcon']."),";
		}
		$sqlinsertvalues = substr($sqlinsertvalues, 0, -1);
		$sqlexec .= "INSERT INTO `$dbname`.`user` (`uuid`,`cldbid`,`count`,`name`,`lastseen`,`grpid`,`nextup`,`idle`,`cldgroup`,`boosttime`,`platform`,`nation`,`version`,`except`,`grpsince`,`cid`,`online`,`firstcon`) VALUES $sqlinsertvalues ON DUPLICATE KEY UPDATE `cldbid`=VALUES(`cldbid`),`count`=VALUES(`count`),`name`=VALUES(`name`),`lastseen`=VALUES(`lastseen`),`grpid`=VALUES(`grpid`),`nextup`=VALUES(`nextup`),`idle`=VALUES(`idle`),`cldgroup`=VALUES(`cldgroup`),`boosttime`=VALUES(`boosttime`),`platform`=VALUES(`platform`),`nation`=VALUES(`nation`),`version`=VALUES(`version`),`except`=VALUES(`except`),`grpsince`=VALUES(`grpsince`),`cid`=VALUES(`cid`),`online`=VALUES(`online`),`firstcon`=VALUES(`firstcon`); ";
		unset($insertdata, $sqlinsertvalues);
	}

	enter_logfile($cfg,6,"calc_user needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
	return($sqlexec);
}
?>