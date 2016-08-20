<?PHP
function calc_userstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$logpath) {
	$starttime = microtime(true);
	$sqlmsg = '';
	$sqlerr = 0;

	if(($count_user = $mysqlcon->query("SELECT count(*) as count FROM ((SELECT u.uuid FROM $dbname.user AS u INNER JOIN $dbname.stats_user As s On u.uuid=s.uuid) UNION (SELECT u.uuid FROM $dbname.user AS u LEFT JOIN $dbname.stats_user As s On u.uuid=s.uuid WHERE s.uuid IS NULL)) x")) === false) {
		enter_logfile($logpath,$timezone,2,"calc_userstats 1:".print_r($mysqlcon->errorInfo()));
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
	$count_user = $count_user->fetchAll(PDO::FETCH_ASSOC);
	$total_user = $count_user[0]['count'];

	if(($job_begin = $mysqlcon->query("SELECT timestamp FROM $dbname.job_check WHERE job_name='calc_user_limit'")) === false) {
		enter_logfile($logpath,$timezone,2,"calc_userstats 2:".print_r($mysqlcon->errorInfo()));
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
	$job_begin = $job_begin->fetchAll();
	$job_begin = $job_begin[0]['timestamp'];
	$job_end = ceil($total_user / 10) * 10;
	if ($job_begin >= $job_end) {
		$job_begin = 0;
		$job_end = 10;
	} else {
		$job_end = $job_begin + 10;
	}

	if(($uuids = $mysqlcon->query("(SELECT u.uuid,u.rank,u.cldbid FROM $dbname.user AS u INNER JOIN $dbname.stats_user As s On u.uuid=s.uuid) UNION (SELECT u.uuid,u.rank,u.cldbid FROM $dbname.user AS u LEFT JOIN $dbname.stats_user As s On u.uuid=s.uuid WHERE s.uuid IS NULL) ORDER BY cldbid ASC LIMIT $job_begin, 10")) === false) {
		enter_logfile($logpath,$timezone,2,"calc_userstats 3:".print_r($mysqlcon->errorInfo()));
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
	$uuids = $uuids->fetchAll();
	foreach($uuids as $uuid) {
		$sqlhis[$uuid['uuid']] = array(
			"uuid" => $uuid['uuid'],
			"rank" => $uuid['rank'],
			"cldbid" => $uuid['cldbid']
		);
	}

	// Calc Client Stats
	if ($mysqlcon->exec("UPDATE $dbname.stats_user AS t LEFT JOIN $dbname.user AS u ON t.uuid=u.uuid SET t.removed='1' WHERE u.uuid IS NULL") === false) {
		enter_logfile($logpath,$timezone,2,"calc_userstats 4:".print_r($mysqlcon->errorInfo()));
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
	
	if(($statsuserhis = $mysqlcon->query("SELECT uuid, removed FROM $dbname.stats_user")) === false) {
		enter_logfile($logpath,$timezone,2,"calc_userstats 5:".print_r($mysqlcon->errorInfo()));
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
	$statsuserhis = $statsuserhis->fetchAll();
	foreach($statsuserhis as $userhis) {
		$uidarrstats[$userhis['uuid']] = $userhis['removed'];
	}
	unset($statsuserhis);

	if(isset($sqlhis)) {
		//enter_logfile($logpath,$timezone,2,"Update User Stats between ".$job_begin." and ".$job_end.":");
		if(($userdataweekbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 6:".print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$userdataweekbegin = $userdataweekbegin->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		if(($userdataweekend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 7:".print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$userdataweekend = $userdataweekend->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		if(($userdatamonthbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 8:".print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$userdatamonthbegin = $userdatamonthbegin->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		if(($userdatamonthend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 9:".print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$userdatamonthend = $userdatamonthend->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

		$allupdateuuid = '';
		$allupdaterank = '';
		$allupdatecountw = '';
		$allupdatecountm = '';
		$allupdateidlew = '';
		$allupdateidlem = '';
		$allupdatetotac = '';
		$allupdatebase64 = '';
		$allupdatecldtup = '';
		$allupdatecldtdo = '';
		$allupdateclddes = '';
		$allinsertuserstats = '';
		
		foreach ($sqlhis as $userstats) {
			try {
				$clientinfo = $ts3->clientInfoDb($userstats['cldbid']);

				if(isset($userdataweekend[$userstats['uuid']]) && isset($userdataweekbegin[$userstats['uuid']])) {
					$count_week = $userdataweekend[$userstats['uuid']][0]['count'] - $userdataweekbegin[$userstats['uuid']][0]['count'];
					$idle_week = $userdataweekend[$userstats['uuid']][0]['idle'] - $userdataweekbegin[$userstats['uuid']][0]['idle'];
				} else {
					$count_week = 0;
					$idle_week = 0;
				}
				if(isset($userdatamonthend[$userstats['uuid']]) && isset($userdatamonthbegin[$userstats['uuid']])) {
					$count_month = $userdatamonthend[$userstats['uuid']][0]['count'] - $userdatamonthbegin[$userstats['uuid']][0]['count'];
					$idle_month = $userdatamonthend[$userstats['uuid']][0]['idle'] - $userdatamonthbegin[$userstats['uuid']][0]['idle'];
				} else {
					$count_month = 0;
					$idle_month = 0;
				}
				$clientdesc = $mysqlcon->quote($clientinfo['client_description'], ENT_QUOTES);;
				if(isset($uidarrstats[$userstats['uuid']])) {
					$allupdateuuid = $allupdateuuid . "'" . $userstats['uuid'] . "',";
					$allupdaterank = $allupdaterank . "WHEN '" . $userstats['uuid'] . "' THEN '" . $userstats['rank'] . "' ";
					$allupdatecountw = $allupdatecountw . "WHEN '" . $userstats['uuid'] . "' THEN '" . $count_week . "' ";
					$allupdatecountm = $allupdatecountm . "WHEN '" . $userstats['uuid'] . "' THEN '" . $count_month . "' ";
					$allupdateidlew = $allupdateidlew . "WHEN '" . $userstats['uuid'] . "' THEN '" . $idle_week . "' ";
					$allupdateidlem = $allupdateidlem . "WHEN '" . $userstats['uuid'] . "' THEN '" . $idle_month . "' ";
					$allupdatetotac = $allupdatetotac . "WHEN '" . $userstats['uuid'] . "' THEN '" . $clientinfo['client_totalconnections'] . "' ";
					$allupdatebase64 = $allupdatebase64 . "WHEN '" . $userstats['uuid'] . "' THEN '" . $clientinfo['client_base64HashClientUID'] . "' ";
					$allupdatecldtup = $allupdatecldtup . "WHEN '" . $userstats['uuid'] . "' THEN '" . $clientinfo['client_total_bytes_uploaded'] . "' ";
					$allupdatecldtdo = $allupdatecldtdo . "WHEN '" . $userstats['uuid'] . "' THEN '" . $clientinfo['client_total_bytes_downloaded'] . "' ";
					$allupdateclddes = $allupdateclddes . "WHEN '" . $userstats['uuid'] . "' THEN " . $clientdesc . " ";
				} else {
					$allinsertuserstats = $allinsertuserstats . "('" . $userstats['uuid'] . "', '" .$userstats['rank'] . "', '" . $count_week . "', '" . $count_month . "', '" . $idle_week . "', '" . $idle_month . "', '" . $clientinfo['client_totalconnections'] . "', '" . $clientinfo['client_base64HashClientUID'] . "', '" . $clientinfo['client_total_bytes_uploaded'] . "', '" . $clientinfo['client_total_bytes_downloaded'] . "', " . $clientdesc . "),";
				}
			} catch (Exception $e) {
				//error would be, when client is missing in ts db
			}
		}
		
		if ($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp=$job_end WHERE job_name='calc_user_limit'") === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 11:".print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		
		if ($allupdateuuid != '') {
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			if ($mysqlcon->exec("UPDATE $dbname.stats_user set rank = CASE uuid $allupdaterank END, count_week = CASE uuid $allupdatecountw END, count_month = CASE uuid $allupdatecountm END, idle_week = CASE uuid $allupdateidlew END, idle_month = CASE uuid $allupdateidlem END, total_connections = CASE uuid $allupdatetotac END, base64hash = CASE uuid $allupdatebase64 END, client_total_up = CASE uuid $allupdatecldtup END, client_total_down = CASE uuid $allupdatecldtdo END, client_description = CASE uuid $allupdateclddes END WHERE uuid IN ($allupdateuuid)") === false) {
				enter_logfile($logpath,$timezone,2,"calc_userstats 12:".print_r($mysqlcon->errorInfo()));
				$sqlmsg .= print_r($mysqlcon->errorInfo());
				$sqlerr++;
			}
		}

		if($allinsertuserstats != '') {
			$allinsertuserstats = substr($allinsertuserstats, 0, -1);
			if ($mysqlcon->exec("INSERT INTO $dbname.stats_user (uuid,rank,count_week,count_month,idle_week,idle_month,total_connections,base64hash,client_total_up,client_total_down,client_description) VALUES $allinsertuserstats") === false) {
				enter_logfile($logpath,$timezone,2,"calc_userstats 13:".print_r($mysqlcon->errorInfo()));
				$sqlmsg .= print_r($mysqlcon->errorInfo());
				$sqlerr++;
			}
		}
	}
	
	$buildtime = microtime(true) - $starttime;
	if ($buildtime < 0) { $buildtime = 0; }

	if ($sqlerr == 0) {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 14:".print_r($mysqlcon->errorInfo()));
		}
	} else {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 15:".print_r($mysqlcon->errorInfo()));
		}
	}
}
?>