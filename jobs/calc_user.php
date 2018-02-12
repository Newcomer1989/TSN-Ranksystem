<?PHP
function calc_user($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$update,$grouptime,$boostarr,$resetbydbchange,$msgtouser,$uniqueid,$updateinfotime,$currvers,$substridle,$exceptuuid,$exceptgroup,$allclients,$logpath,$rankupmsg,$ignoreidle,$exceptcid,$resetexcept,$phpcommand,$select_arr) {
	$nowtime = time();
	$sqlexec = '';

	if ($select_arr['job_check']['check_update']['timestamp'] < ($nowtime - $updateinfotime)) {
		if(($getversion = $mysqlcon->query("SELECT newversion FROM $dbname.config")->fetch()) === false) {
			enter_logfile($logpath,$timezone,2,"calc_user 1:".print_r($mysqlcon->errorInfo(), true));
		} else {
			if(version_compare($getversion['newversion'], $currvers, '>') && $getversion['newversion'] != NULL) {
				if ($update == 1) {
					enter_logfile($logpath,$timezone,4,$lang['upinf']);
					foreach ($uniqueid as $clientid) {
						check_shutdown($timezone,$logpath); usleep($slowmode);
						try {
							$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $currvers, $getversion['newversion']));
							enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['upusrinf'], $clientid));
							$sqlexec .= "UPDATE $dbname.job_check SET timestamp=$nowtime WHERE job_name='check_update'; ";
						}
						catch (Exception $e) {
							enter_logfile($logpath,$timezone,6,"  ".sprintf($lang['upusrerr'], $clientid));
						}
					}
				}
				update_rs($mysqlcon,$lang,$dbname,$logpath,$timezone,$getversion['newversion'],$phpcommand);
			}
		}
	}

	if(empty($grouptime)) {
		enter_logfile($logpath,$timezone,1,"calc_user:".$lang['wiconferr']."Shuttin down!\n\n");
		exit;
	}
	if($select_arr['job_check']['calc_user_lastscan']['timestamp'] < ($nowtime - 1800)) {
		enter_logfile($logpath,$timezone,4,"Much time gone since last scan.. reset time difference to zero.");
		$select_arr['job_check']['calc_user_lastscan']['timestamp'] = $nowtime;
	} elseif($select_arr['job_check']['calc_user_lastscan']['timestamp'] > $nowtime) {
		enter_logfile($logpath,$timezone,4,"Negative time between now and last scan (Error in your server time!).. reset time difference to zero.");
		$select_arr['job_check']['calc_user_lastscan']['timestamp'] = $nowtime;
	}

	$sqlexec .= "UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='calc_user_lastscan'; ";

	krsort($grouptime);
	$yetonline = array();
	$insertdata = array();
	$updatedata = array();
	
	foreach ($allclients as $client) {
		$cldbid = $client['client_database_id'];
		$name = $mysqlcon->quote($client['client_nickname'], ENT_QUOTES);
		$uid = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
		$sgroups = array_flip(explode(",", $client['client_servergroups']));
		if (!isset($yetonline[$uid]) && $client['client_version'] != "ServerQuery") {
			if(strstr($client['connection_client_ip'], '[')) {
				$ip = $mysqlcon->quote(inet_pton(str_replace(array('[',']'),'',$client['connection_client_ip'])), ENT_QUOTES);
			} else {
				$ip = $mysqlcon->quote(inet_pton($client['connection_client_ip']), ENT_QUOTES);
			}
			$clientidle = floor($client['client_idle_time'] / 1000);
			if(isset($ignoreidle) && $clientidle < $ignoreidle) {
				$clientidle = 0;
			}
			$yetonline[$uid] = 0;
			if(isset($exceptuuid[$uid])) {
				$except = 3;
			} elseif(array_intersect_key($sgroups, $exceptgroup)) {
				$except = 2;
			} else {
				if(isset($select_arr['all_user'][$uid]['except']) && ($select_arr['all_user'][$uid]['except'] == 3 || $select_arr['all_user'][$uid]['except'] == 2) && $resetexcept == 2) { 
				 	$select_arr['all_user'][$uid]['count'] = 0;
				 	$select_arr['all_user'][$uid]['idle'] = 0;
					enter_logfile($logpath,$timezone,5,sprintf($lang['resettime'], $name, $uid, $cldbid));
					$sqlexec .= "DELETE FROM $dbname.user_snapshot WHERE uuid='$uid'; ";
				}
				$except = 0;
			}
			if(isset($select_arr['all_user'][$uid])) {
				$idle   = $select_arr['all_user'][$uid]['idle'] + $clientidle;
				$grpid  = $select_arr['all_user'][$uid]['grpid'];
				$nextup = $select_arr['all_user'][$uid]['nextup'];
				$grpsince = $select_arr['all_user'][$uid]['grpsince'];
				if ($select_arr['all_user'][$uid]['cldbid'] != $cldbid && $resetbydbchange == 1) {
					enter_logfile($logpath,$timezone,5,sprintf($lang['changedbid'], $name, $uid, $cldbid, $select_arr['all_user'][$uid]['cldbid']));
						$count = 1;
						$idle  = 0;
				} else {
					$hitboost = 0;
					$boosttime = $select_arr['all_user'][$uid]['boosttime'];
					if($boostarr!=0) {
						foreach($boostarr as $boost) {
							if(isset($sgroups[$boost['group']])) {
								$hitboost = 1;
								if($select_arr['all_user'][$uid]['boosttime']==0) {
									$boosttime = $nowtime;
								} else {
									if ($nowtime > $select_arr['all_user'][$uid]['boosttime'] + $boost['time']) {
										usleep($slowmode);
										try {
											$ts3->serverGroupClientDel($boost['group'], $cldbid);
											$boosttime = 0;
											enter_logfile($logpath,$timezone,5,sprintf($lang['sgrprm'], $select_arr['groups'][$select_arr['all_user'][$uid]['grpid']]['sgidname'], $select_arr['all_user'][$uid]['grpid'], $name, $uid, $cldbid));
										}
										catch (Exception $e) {
											enter_logfile($logpath,$timezone,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $cldbid, $select_arr['groups'][$select_arr['all_user'][$uid]['grpid']]['sgidname'], $select_arr['all_user'][$uid]['grpid']));
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
					if($boostarr == 0 or $hitboost == 0) {
						$count = $nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp'] + $select_arr['all_user'][$uid]['count'];
						if ($clientidle > ($nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp'])) {
							$idle = $nowtime - $select_arr['job_check']['calc_user_lastscan']['timestamp'] + $select_arr['all_user'][$uid]['idle'];
						}
					}
				}
				$dtF = new DateTime("@0");
				if ($substridle == 1) {
					$activetime = $count - $idle;
				} else {
					$activetime = $count;
				}
				$dtT = new DateTime("@$activetime");
				foreach ($grouptime as $time => $groupid) {
					if (isset($sgroups[$groupid])) {
						$grpid = $groupid;
						break;
					}
				}
				$grpcount=0;
				foreach ($grouptime as $time => $groupid) {
					$grpcount++;
					if(isset($exceptcid[$client['cid']]) || (($select_arr['all_user'][$uid]['except'] == 3 || $select_arr['all_user'][$uid]['except'] == 2) && $resetexcept == 1)) {
						$count = $select_arr['all_user'][$uid]['count'];
						$idle = $select_arr['all_user'][$uid]['idle'];
						if($except != 2 && $except != 3) {
							$except = 1;
						}
					} elseif ($activetime > $time && !isset($exceptuuid[$uid]) && !array_intersect_key($sgroups, $exceptgroup)) {
						if ($select_arr['all_user'][$uid]['grpid'] != $groupid) {
							if ($select_arr['all_user'][$uid]['grpid'] != NULL && isset($sgroups[$select_arr['all_user'][$uid]['grpid']])) {
								usleep($slowmode);
								try {
									$ts3->serverGroupClientDel($select_arr['all_user'][$uid]['grpid'], $cldbid);
									enter_logfile($logpath,$timezone,5,sprintf($lang['sgrprm'], $select_arr['groups'][$select_arr['all_user'][$uid]['grpid']]['sgidname'], $select_arr['all_user'][$uid]['grpid'], $name, $uid, $cldbid));
								}
								catch (Exception $e) {
									enter_logfile($logpath,$timezone,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $cldbid, $select_arr['groups'][$groupid]['sgidname'], $groupid));
								}
							}
							if (!isset($sgroups[$groupid])) {
								usleep($slowmode);
								try {
									$ts3->serverGroupClientAdd($groupid, $cldbid);
									$grpsince = $nowtime;
									enter_logfile($logpath,$timezone,5,sprintf($lang['sgrpadd'], $select_arr['groups'][$groupid]['sgidname'], $groupid, $name, $uid, $cldbid));
								}
								catch (Exception $e) {
									enter_logfile($logpath,$timezone,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $cldbid, $select_arr['groups'][$groupid]['sgidname'], $groupid));
								}
							}
							$grpid = $groupid;
							if ($msgtouser == 1) {
								usleep($slowmode);
								$days  = $dtF->diff($dtT)->format('%a');
								$hours = $dtF->diff($dtT)->format('%h');
								$mins  = $dtF->diff($dtT)->format('%i');
								$secs  = $dtF->diff($dtT)->format('%s');
								try {
									$ts3->clientGetByUid($uid)->message(sprintf($rankupmsg, $days, $hours, $mins, $secs, $select_arr['groups'][$groupid]['sgidname'], $client['client_nickname']));
								} catch (Exception $e) {
									enter_logfile($logpath,$timezone,2,"TS3 error: ".$e->getCode().': '.$e->getMessage()." ; ".sprintf($lang['sgrprerr'], $name, $uid, $cldbid, $select_arr['groups'][$groupid]['sgidname'], $groupid));
								}
							}
						}
						if($grpcount == 1) {
							$nextup = 0;
						}
						break;
					} else {
						$nextup = $time - $activetime;
					}
				}
				$updatedata[] = array(
					"uuid" => $mysqlcon->quote($client['client_unique_identifier'], ENT_QUOTES),
					"cldbid" => $cldbid,
					"count" => $count,
					"ip" => $ip,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $grpid,
					"nextup" => $nextup,
					"idle" => $idle,
					"cldgroup" => $client['client_servergroups'],
					"boosttime" => $boosttime,
					"platform" => $client['client_platform'],
					"nation" => $client['client_country'],
					"version" => $client['client_version'],
					"except" => $except,
					"grpsince" => $grpsince,
					"cid" => $client['cid']
				);
			} else {
				$grpid = '0';
				foreach ($grouptime as $time => $groupid) {
					if (isset($sgroups[$groupid])) {
						$grpid = $groupid;
						break;
					}
				}
				$insertdata[] = array(
					"uuid" => $mysqlcon->quote($client['client_unique_identifier'], ENT_QUOTES),
					"cldbid" => $cldbid,
					"ip" => $ip,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $grpid,
					"nextup" => (key($grouptime) - 1),
					"cldgroup" => $client['client_servergroups'],
					"platform" => $client['client_platform'],
					"nation" => $client['client_country'],
					"version" => $client['client_version'],
					"firstcon" => $client['client_created'],
					"except" => $except
				);
				enter_logfile($logpath,$timezone,5,sprintf($lang['adduser'], $name, $uid, $cldbid));
			}
		}
	}
	unset($yetonline);

	if ($insertdata != NULL) {
		$allinsertdata = '';
		foreach ($insertdata as $insertarr) {
			$allinsertdata = $allinsertdata . "(" . $insertarr['uuid'] . ", '" . $insertarr['cldbid'] . "', '1', " . $insertarr['ip'] . ", " . $insertarr['name'] . ", '" . $insertarr['lastseen'] . "', '" . $insertarr['grpid'] . "', '" . $insertarr['nextup'] . "', '" . $insertarr['cldgroup'] . "', '" . $insertarr['platform'] . "', '" . $insertarr['nation'] . "', '" . $insertarr['version'] . "', '" . $insertarr['firstcon'] . "', '" . $insertarr['except'] . "','1'),";
		}
		$allinsertdata = substr($allinsertdata, 0, -1);
		if ($allinsertdata != NULL) {
			$sqlexec .= "INSERT INTO $dbname.user (uuid, cldbid, count, ip, name, lastseen, grpid, nextup, cldgroup, platform, nation, version, firstcon, except, online) VALUES $allinsertdata; ";
		}
		unset($insertdata, $allinsertdata);
	}

	if ($updatedata != NULL) {
		$allupdateuuid = $allupdatecldbid = $allupdatecount = $allupdateip = $allupdatename = $allupdatelastseen = $allupdategrpid = $allupdatenextup = $allupdateidle = $allupdatecldgroup = $allupdateboosttime = $allupdateplatform = $allupdatenation = $allupdateversion = $allupdateexcept = $allupdategrpsince = $allupdatecid = '';
		foreach ($updatedata as $updatearr) {
			$allupdateuuid	 = $allupdateuuid . $updatearr['uuid'] . ",";
			$allupdatecldbid   = $allupdatecldbid . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['cldbid'] . "' ";
			$allupdatecount	= $allupdatecount . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['count'] . "' ";
			$allupdateip	   = $allupdateip . "WHEN " . $updatearr['uuid'] . " THEN " . $updatearr['ip'] . " ";
			$allupdatename	 = $allupdatename . "WHEN " . $updatearr['uuid'] . " THEN " . $updatearr['name'] . " ";
			$allupdatelastseen = $allupdatelastseen . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['lastseen'] . "' ";
			$allupdategrpid	= $allupdategrpid . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['grpid'] . "' ";
			$allupdatenextup   = $allupdatenextup . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['nextup'] . "' ";
			$allupdateidle	 = $allupdateidle . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['idle'] . "' ";
			$allupdatecldgroup = $allupdatecldgroup . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['cldgroup'] . "' ";
			$allupdateboosttime = $allupdateboosttime . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['boosttime'] . "' ";
			$allupdateplatform = $allupdateplatform . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['platform'] . "' ";
			$allupdatenation = $allupdatenation . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['nation'] . "' ";
			$allupdateversion = $allupdateversion . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['version'] . "' ";
			$allupdateexcept = $allupdateexcept . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['except'] . "' ";
			$allupdategrpsince = $allupdategrpsince . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['grpsince'] . "' ";
			$allupdatecid = $allupdatecid . "WHEN " . $updatearr['uuid'] . " THEN '" . $updatearr['cid'] . "' ";
		}
		$allupdateuuid = substr($allupdateuuid, 0, -1);
		$sqlexec .= "UPDATE $dbname.user SET online='0'; UPDATE $dbname.user set cldbid = CASE uuid $allupdatecldbid END, count = CASE uuid $allupdatecount END, ip = CASE uuid $allupdateip END, name = CASE uuid $allupdatename END, lastseen = CASE uuid $allupdatelastseen END, grpid = CASE uuid $allupdategrpid END, nextup = CASE uuid $allupdatenextup END, idle = CASE uuid $allupdateidle END, cldgroup = CASE uuid $allupdatecldgroup END, boosttime = CASE uuid $allupdateboosttime END, platform = CASE uuid $allupdateplatform END, nation = CASE uuid $allupdatenation END, version = CASE uuid $allupdateversion END, except = CASE uuid $allupdateexcept END, grpsince = CASE uuid $allupdategrpsince END, cid = CASE uuid $allupdatecid END, online = 1 WHERE uuid IN ($allupdateuuid); ";
		unset($updatedata, $allupdateuuid, $allupdatecldbid, $allupdatecount, $allupdateip, $allupdatename, $allupdatelastseen, $allupdategrpid, $allupdatenextup, $allupdateidle, $allupdatecldgroup, $allupdateboosttime, $allupdateplatform, $allupdatenation, $allupdateversion, $allupdateexcept, $allupdategrpsince, $allupdatecid);
	}
	return($sqlexec);
}
?>