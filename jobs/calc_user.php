<?PHP
function calc_user($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$showgen,$update,$grouptime,$boostarr,$resetbydbchange,$msgtouser,$uniqueid,$updateinfotime,$currvers,$substridle,$exceptuuid,$exceptgroup,$allclients) {
	$starttime = microtime(true);
	$nowtime = time();
	$sqlmsg = '';
	$sqlerr = 0;

	if ($update == 1) {
		$updatetime = $nowtime - $updateinfotime;
		if(($lastupdate = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='check_update'")) === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 1:",print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$lastupdate = $lastupdate->fetchAll();
		if ($lastupdate[0]['timestamp'] < $updatetime) {
			set_error_handler(function() { });
			$newversion = file_get_contents('http://ts-n.net/ranksystem/version');
			restore_error_handler();
			if (substr($newversion, 0, 4) != substr($currvers, 0, 4) && $newversion != '') {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),$lang['upinf'],"\n";
				foreach ($uniqueid as $clientid) {
					usleep($slowmode);
					try {
						$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $currvers, $newversion));
						echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),sprintf($lang['upusrinf'], $clientid),"\n";
					}
					catch (Exception $e) {
						echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),sprintf($lang['upusrerr'], $clientid),"\n";
						$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
						$sqlerr++;
					}
				}
			}
			if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp=$nowtime WHERE job_name='check_update'") === false) {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 3:",print_r($mysqlcon->errorInfo()),"\n";
				$sqlmsg .= print_r($mysqlcon->errorInfo());
				$sqlerr++;
			}
		}
	}

	if(($dbdata = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='calc_user_lastscan'")) === false) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 4:",print_r($mysqlcon->errorInfo()),"\n";
		exit;
	}
	$lastscanarr = $dbdata->fetchAll();
	$lastscan = $lastscanarr[0]['timestamp'];
	if($lastscan < ($nowtime - 86400)) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"Much time gone since last scan.. reset time difference to zero.\n";
		$lastscan = $nowtime;
	}
	if ($dbdata->rowCount() != 0) {
		if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='calc_user_lastscan'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 5:",print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		if(($dbuserdata = $mysqlcon->query("SELECT uuid,cldbid,count,grpid,nextup,idle,boosttime FROM $dbname.user")) === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 6:",print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$uuids = $dbuserdata->fetchAll();
		foreach($uuids as $uuid) {
			$sqlhis[$uuid['uuid']] = array(
				"uuid" => $uuid['uuid'],
				"cldbid" => $uuid['cldbid'],
				"count" => $uuid['count'],
				"grpid" => $uuid['grpid'],
				"nextup" => $uuid['nextup'],
				"idle" => $uuid['idle'],
				"boosttime" => $uuid['boosttime']
			);
			$uidarr[] = $uuid['uuid'];
		}
	}
	unset($uuids);

	usleep($slowmode);
	$yetonline[] = '';
	$insertdata  = '';
	if(empty($grouptime)) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 7:",$lang['wiconferr'],"\n";
		exit;
	}
	krsort($grouptime);
	$sumentries = 0;
	$boosttime = 0;
	$nextupforinsert = key($grouptime) - 1;

	foreach ($allclients as $client) {
		$sumentries++;
		$cldbid   = $client['client_database_id'];
		$ip	   = ip2long($client['connection_client_ip']);
		$name	 = str_replace('\\', '\\\\', htmlspecialchars($client['client_nickname'], ENT_QUOTES));
		$uid	  = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
		$cldgroup = $client['client_servergroups'];
		$sgroups  = explode(",", $cldgroup);
		$platform=$client['client_platform'];
		$nation=$client['client_country'];
		$version=$client['client_version'];
		$firstconnect=$client['client_created'];
		if (!in_array($uid, $yetonline) && $client['client_version'] != "ServerQuery") {
			$clientidle  = floor($client['client_idle_time'] / 1000);
			$yetonline[] = $uid;
			if(in_array($uid, $exceptuuid) || array_intersect($sgroups, $exceptgroup)) {
				$except = 1;
			} else {
				$except = 0;
			}
			if (in_array($uid, $uidarr)) {
				$idle   = $sqlhis[$uid]['idle'] + $clientidle;
				$grpid  = $sqlhis[$uid]['grpid'];
				$nextup = $sqlhis[$uid]['nextup'];
				if ($sqlhis[$uid]['cldbid'] != $cldbid && $resetbydbchange == 1) {
					echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),sprintf($lang['changedbid'], $name, $uid, $cldbid, $sqlhis[$uid]['cldbid']),"\n";
						$count = 1;
						$idle  = 0;
				} else {
					$hitboost = 0;
					if($boostarr!=0) {
						foreach($boostarr as $boost) {
							if(in_array($boost['group'], $sgroups)) {
								$boostfactor = $boost['factor'];
								$hitboost = 1;
								$boosttime = $sqlhis[$uid]['boosttime'];
								if($sqlhis[$uid]['boosttime']==0) {
									$boosttime = $nowtime;
								} else {
									if ($nowtime > $sqlhis[$uid]['boosttime'] + $boost['time']) {
										usleep($slowmode);
										try {
											$ts3->serverGroupClientDel($boost['group'], $cldbid);
											$boosttime = 0;
											echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),sprintf($lang['sgrprm'], $sqlhis[$uid]['grpid'], $name, $uid, $cldbid),"\n";
										}
										catch (Exception $e) {
											echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 8:",sprintf($lang['sgrprerr'], $name, $uid, $cldbid),"\n";
											$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
											$sqlerr++;
										}
									}
								}
								$count = ($nowtime - $lastscan) * $boost['factor'] + $sqlhis[$uid]['count'];
								if ($clientidle > ($nowtime - $lastscan)) {
									$idle = ($nowtime - $lastscan) * $boost['factor'] + $sqlhis[$uid]['idle'];
								}
							}
						}
					}
					if($boostarr == 0 or $hitboost == 0) {
						$count = $nowtime - $lastscan + $sqlhis[$uid]['count'];
						if ($clientidle > ($nowtime - $lastscan)) {
							$idle = $nowtime - $lastscan + $sqlhis[$uid]['idle'];
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
					if (in_array($groupid, $sgroups)) {
						$grpid = $groupid;
						break;
					}
				}
				$grpcount=0;
				foreach ($grouptime as $time => $groupid) {
					$grpcount++;
					if ($activetime > $time && !in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup)) {
						if ($sqlhis[$uid]['grpid'] != $groupid) {
							if ($sqlhis[$uid]['grpid'] != 0 && in_array($sqlhis[$uid]['grpid'], $sgroups)) {
								usleep($slowmode);
								try {
									$ts3->serverGroupClientDel($sqlhis[$uid]['grpid'], $cldbid);
									echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),sprintf($lang['sgrprm'], $sqlhis[$uid]['grpid'], $name, $uid, $cldbid),"\n";
								}
								catch (Exception $e) {
									echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 9:",sprintf($lang['sgrprerr'], $name, $uid, $cldbid),"\n";
									$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
									$sqlerr++;
								}
							}
							if (!in_array($groupid, $sgroups)) {
								usleep($slowmode);
								try {
									$ts3->serverGroupClientAdd($groupid, $cldbid);
									echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),sprintf($lang['sgrpadd'], $groupid, $name, $uid, $cldbid),"\n";
								}
								catch (Exception $e) {
									echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 10:",sprintf($lang['sgrprerr'], $name, $uid, $cldbid),"\n";
									$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
									$sqlerr++;
								}
							}
							$grpid = $groupid;
							if ($msgtouser == 1) {
								usleep($slowmode);
								$days  = $dtF->diff($dtT)->format('%a');
								$hours = $dtF->diff($dtT)->format('%h');
								$mins  = $dtF->diff($dtT)->format('%i');
								$secs  = $dtF->diff($dtT)->format('%s');
								if ($substridle == 1) {
									try {
										$ts3->clientGetByUid($uid)->message(sprintf($lang['usermsgactive'], $days, $hours, $mins, $secs));
									} catch (Exception $e) {
										echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 11:",sprintf($lang['sgrprerr'], $name, $uid, $cldbid),"\n";
										$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
										$sqlerr++;
									}
								} else {
									try {
										$ts3->clientGetByUid($uid)->message(sprintf($lang['usermsgonline'], $days, $hours, $mins, $secs));
									} catch (Exception $e) {
										echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 12:",sprintf($lang['sgrprerr'], $name, $uid, $cldbid),"\n";
										$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
										$sqlerr++;
									}
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
					"uuid" => $uid,
					"cldbid" => $cldbid,
					"count" => $count,
					"ip" => $ip,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $grpid,
					"nextup" => $nextup,
					"idle" => $idle,
					"cldgroup" => $cldgroup,
					"boosttime" => $boosttime,
					"platform" => $platform,
					"nation" => $nation,
					"version" => $version,
					"except" => $except
				);
			} else {
				$grpid = '0';
				foreach ($grouptime as $time => $groupid) {
					if (in_array($groupid, $sgroups)) {
						$grpid = $groupid;
						break;
					}
				}
				$insertdata[] = array(
					"uuid" => $uid,
					"cldbid" => $cldbid,
					"ip" => $ip,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $grpid,
					"nextup" => $nextupforinsert,
					"cldgroup" => $cldgroup,
					"platform" => $platform,
					"nation" => $nation,
					"version" => $version,
					"firstcon" => $firstconnect,
					"except" => $except
				);
				$uidarr[] = $uid;
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),sprintf($lang['adduser'], $name, $uid, $cldbid),"\n";
			}
		}
	}

	if($mysqlcon->exec("UPDATE $dbname.user SET online='0'") === false) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 13:",print_r($mysqlcon->errorInfo()),"\n";
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}

	if ($insertdata != '') {
		$allinsertdata = '';
		foreach ($insertdata as $insertarr) {
			$allinsertdata = $allinsertdata . "('" . $insertarr['uuid'] . "', '" . $insertarr['cldbid'] . "', '1', '" . $insertarr['ip'] . "', '" . $insertarr['name'] . "', '" . $insertarr['lastseen'] . "', '" . $insertarr['grpid'] . "', '" . $insertarr['nextup'] . "', '" . $insertarr['cldgroup'] . "', '" . $insertarr['platform'] . "', '" . $insertarr['nation'] . "', '" . $insertarr['version'] . "', '" . $insertarr['firstcon'] . "', '" . $insertarr['except'] . "','1'),";
		}
		$allinsertdata = substr($allinsertdata, 0, -1);
		if ($allinsertdata != '') {
			if($mysqlcon->exec("INSERT INTO $dbname.user (uuid, cldbid, count, ip, name, lastseen, grpid, nextup, cldgroup, platform, nation, version, firstcon, except, online) VALUES $allinsertdata") === false) {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 14:",print_r($mysqlcon->errorInfo()),"\n";
				$sqlmsg .= print_r($mysqlcon->errorInfo());
				$sqlerr++;
			}
		}
	}

	unset($insertdata);
	unset($allinsertdata);
	if ($updatedata != 0) {
		$allupdateuuid	 = '';
		$allupdatecldbid   = '';
		$allupdatecount	= '';
		$allupdateip	   = '';
		$allupdatename	 = '';
		$allupdatelastseen = '';
		$allupdategrpid	= '';
		$allupdatenextup   = '';
		$allupdateidle	 = '';
		$allupdatecldgroup = '';
		$allupdateboosttime = '';
		$allupdateplatform = '';
		$allupdatenation = '';
		$allupdateversion = '';
		$allupdateexcept = '';
		foreach ($updatedata as $updatearr) {
			$allupdateuuid	 = $allupdateuuid . "'" . $updatearr['uuid'] . "',";
			$allupdatecldbid   = $allupdatecldbid . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['cldbid'] . "' ";
			$allupdatecount	= $allupdatecount . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['count'] . "' ";
			$allupdateip	   = $allupdateip . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['ip'] . "' ";
			$allupdatename	 = $allupdatename . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['name'] . "' ";
			$allupdatelastseen = $allupdatelastseen . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['lastseen'] . "' ";
			$allupdategrpid	= $allupdategrpid . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['grpid'] . "' ";
			$allupdatenextup   = $allupdatenextup . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['nextup'] . "' ";
			$allupdateidle	 = $allupdateidle . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['idle'] . "' ";
			$allupdatecldgroup = $allupdatecldgroup . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['cldgroup'] . "' ";
			$allupdateboosttime = $allupdateboosttime . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['boosttime'] . "' ";
			$allupdateplatform = $allupdateplatform . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['platform'] . "' ";
			$allupdatenation = $allupdatenation . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['nation'] . "' ";
			$allupdateversion = $allupdateversion . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['version'] . "' ";
			$allupdateexcept = $allupdateexcept . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['except'] . "' ";
		}
		$allupdateuuid = substr($allupdateuuid, 0, -1);
		if($mysqlcon->exec("UPDATE $dbname.user set cldbid = CASE uuid $allupdatecldbid END, count = CASE uuid $allupdatecount END, ip = CASE uuid $allupdateip END, name = CASE uuid $allupdatename END, lastseen = CASE uuid $allupdatelastseen END, grpid = CASE uuid $allupdategrpid END, nextup = CASE uuid $allupdatenextup END, idle = CASE uuid $allupdateidle END, cldgroup = CASE uuid $allupdatecldgroup END, boosttime = CASE uuid $allupdateboosttime END, platform = CASE uuid $allupdateplatform END, nation = CASE uuid $allupdatenation END, version = CASE uuid $allupdateversion END, except = CASE uuid $allupdateexcept END, online = 1 WHERE uuid IN ($allupdateuuid)") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 15:",print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
	}

	$buildtime = microtime(true) - $starttime;

	if ($sqlerr == 0) {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 16:",print_r($mysqlcon->errorInfo()),"\n";
		}
	} else {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"calc_user 17:",print_r($mysqlcon->errorInfo()),"\n";
		}
	}
}
?>