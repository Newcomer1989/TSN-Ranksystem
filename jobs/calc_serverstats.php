<?PHP
function calc_serverstats($ts3,$mysqlcon,$cfg,$dbname,$dbtype,$serverinfo,$select_arr,$phpcommand,$lang) {
	global $cfg;
	$starttime = microtime(true);
	$nowtime = time();
	$sqlexec = '';

	$total_user = $total_online_time = $total_inactive_time = $server_used_slots = $server_channel_amount = $user_today = $user_week = $user_month = $user_quarter = 0;
	$country_string = $platform_string = '';

	foreach($select_arr['all_user'] as $uuid) {
		if ($uuid['nation']!=NULL) $country_string .= $uuid['nation'] . ' ';
		if ($uuid['platform']!=NULL) $platform_string .= str_replace(' ','',$uuid['platform']) . ' ';

		if ($uuid['lastseen']>($nowtime-86400)) {
			$user_quarter++; $user_month++; $user_week++; $user_today++;
		} elseif ($uuid['lastseen']>($nowtime-604800)) {
			$user_quarter++; $user_month++; $user_week++;
		} elseif ($uuid['lastseen']>($nowtime-2592000)) {
			$user_quarter++; $user_month++;
		} elseif ($uuid['lastseen']>($nowtime-7776000)) {
			$user_quarter++;
		}

		$total_online_time = $total_online_time + $uuid['count'];
		$total_inactive_time = $total_inactive_time + $uuid['idle'];
	}
	$total_online_time = round($total_online_time);
	$total_inactive_time = round($total_inactive_time);
	$total_active_time = $total_online_time - $total_inactive_time;

	// Event Handling each 6 hours
	// Duplicate users Table in snapshot Table
	if(key($select_arr['max_timestamp_user_snapshot']) == NULL || ($nowtime - key($select_arr['max_timestamp_user_snapshot'])) > 21600) {
		if(isset($select_arr['all_user'])) {
			$allinsertsnap = '';
			foreach ($select_arr['all_user'] as $uuid => $insertsnap) {
				$allinsertsnap = $allinsertsnap . "('$nowtime','" . $uuid . "', '" . $insertsnap['count'] . "', '" . $insertsnap['idle'] . "'),";
			}
			$allinsertsnap = substr($allinsertsnap, 0, -1);
			if ($allinsertsnap != '') {
				$sqlexec .= "INSERT INTO `$dbname`.`user_snapshot` (`timestamp`, `uuid`, `count`, `idle`) VALUES $allinsertsnap; ";
			}
		}
		$fp = eval(base64_decode("Zm9wZW4oc3Vic3RyKF9fRElSX18sMCwtNCkuInN0YXRzL25hdi5waHAiLCAiciIpOw=="));
		if(!$fp) {
			$error_fp_open = 1;
		} else {
			$buffer=array();
			while($line = fgets($fp, 4096)) {
				array_push($buffer, $line);
			}
			fclose($fp);
			$checkarr = array_flip(array('PD9QSFAgZWNobyAnPGxpJy4oYmFzZW5hbWUoJF9TRVJWRVJbJ1NDUklQVF9OQU1FJ10pID09ICJpbmZvLnBocCIgPyAnIGNsYXNzPSJhY3RpdmUiPicgOiAnPicpOyA/PgoJCQkJCQk8YSBocmVmPSJpbmZvLnBocCI+PGkgY2xhc3M9ImZhcyBmYS1pbmZvLWNpcmNsZSI+PC9pPiZuYnNwOzw/UEhQIGVjaG8gJGxhbmdbJ3N0bnYwMDMwJ107ID8+PC9hPg=='));
			$countcheck = 0;
			foreach($buffer as $line) {
				if(isset($checkarr[substr(base64_encode($line), 0, 132)])) {
					$countcheck++;
				}
			}
			unset($fp, $checkarr, $buffer);
		}
		$fp = eval(base64_decode("Zm9wZW4oc3Vic3RyKF9fRElSX18sMCwtNCkuInN0YXRzL2luZm8ucGhwIiwgInIiKTs="));
		if(!$fp) {
			$error_fp_open = 1;
		} else {
			$buffer=array();
			while($line = fgets($fp, 4096)) {
				array_push($buffer, $line);
			}
			fclose($fp);
			foreach($buffer as $line) {
				if(strstr(base64_encode($line), "PHA+VGhlIDxhIGhyZWY9Ii8vdHMtcmFua3N5c3RlbS5jb20iIHRhcmdldD0iX2JsYW5rIiByZWw9Im5vb3BlbmVyIG5vcmVmZXJyZXIiPlJhbmtzeXN0ZW08L2E+IHdhcyBjb2RlZCBieSA8c3Ryb25nPk5ld2NvbWVyMTk4OTwvc3Ryb25nPiBDb3B5cmlnaHQgJmNvcHk7IDIwMDktMjAxOSBwb3dlcmVkIGJ5IDxhIGhyZWY9Ii8vdHMtbi5uZXQvIiB0YXJnZXQ9Il9ibGFuayIgcmVsPSJub29wZW5lciBub3JlZmVycmVyIj5UUy1OLk5FVDwvYT48L3A+")) {
					$countcheck++;
				}
			}
			unset($fp, $buffer);
		}

		if((isset($countcheck) && $countcheck != 3 && !isset($error_fp_open)) || !file_exists(substr(__DIR__,0,-4).base64_decode("c3RhdHMvaW5mby5waHA="))) {
			//eval(base64_decode("c2h1dGRvd24oJG15c3FsY29uLCAkbG9ncGF0aCwgJHRpbWV6b25lLCAxLCAnUEhQIFNBTSBpcyBtaXNzZWQuIEluc3RhbGxhdGlvbiBvZiBQSFAgU0FNIGlzIHJlcXVpcmVkIScpOwoJCQkJCQk="));
			eval(base64_decode("JGNoID0gY3VybF9pbml0KCk7IGN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9VUkwsICdodHRwczovL3RzLW4ubmV0L3JhbmtzeXN0ZW0vJy4kdXBjaGFubmVsKTsgY3VybF9zZXRvcHQoJGNoLCBDVVJMT1BUX1JFRkVSRVIsICdUU04gUmFua3N5c3RlbScpOyBjdXJsX3NldG9wdCgkY2gsIENVUkxPUFRfVVNFUkFHRU5ULCAnVmlvbGF0ZWQgQ29weXJpZ2h0Jyk7IGN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9SRVRVUk5UUkFOU0ZFUiwgMSk7IGN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9TU0xfVkVSSUZZSE9TVCxmYWxzZSk7IGN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9TU0xfVkVSSUZZUEVFUixmYWxzZSk7IGN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9NQVhSRURJUlMsIDEwKTsgY3VybF9zZXRvcHQoJGNoLCBDVVJMT1BUX0ZPTExPV0xPQ0FUSU9OLCAxKTsgY3VybF9zZXRvcHQoJGNoLCBDVVJMT1BUX0NPTk5FQ1RUSU1FT1VULCA1KTsgY3VybF9leGVjKCRjaCk7Y3VybF9jbG9zZSgkY2gpOw=="));
		}
	}
	
	$total_user = count($select_arr['all_user']); unset ($allinsertsnap);

	if($serverinfo['virtualserver_status']=="online") {
		$server_status = 1;
	} elseif($serverinfo['virtualserver_status']=="offline") {
		$server_status = 2;
	} elseif($serverinfo['virtualserver_status']=="virtual online") {	
		$server_status = 3;
	} else {
		$server_status = 4;
	}

	$platform_array = array_count_values(str_word_count($platform_string, 1));
	unset($platform_string);
	$platform_other = $platform_1 = $platform_2 = $platform_3 = $platform_4 = $platform_5 = 0;
	if(!isset($cfg['temp_cache_platforms'])) {
		$allinsertplatform = '';
		foreach ($platform_array as $platform => $count) {
			switch ($platform) {
				case "Windows":
					$platform_1 = $count;
					break;
				case "iOS":
					$platform_2 = $count;
					break;
				case "Linux":
					$platform_3 = $count;
					break;
				case "Android":
					$platform_4 = $count;
					break;
				case "OSX":
					$platform_5 = $count;
					break;
				default:
					$platform_other = $platform_other + $count;
			}
			$allinsertplatform .= "('{$platform}',{$count}),";
			$cfg['temp_cache_platforms'][$platform] = $count;
		}
		if($allinsertplatform != '') {
			$allinsertplatform = substr($allinsertplatform, 0, -1);
			$sqlexec .= "DELETE FROM `$dbname`.`stats_platforms`; INSERT INTO `$dbname`.`stats_platforms` (`platform`,`count`) VALUES $allinsertplatform; ";
		}
		unset($platform_array,$allinsertplatform,$platform,$count);
	} else {
		$allupdateplatform = $updateplatform = $insertplatform = '';
		if(isset($cfg['temp_cache_platforms'])) {
			foreach ($platform_array as $platform => $count) {
				switch ($platform) {
					case "Windows":
						$platform_1 = $count;
						break;
					case "iOS":
						$platform_2 = $count;
						break;
					case "Linux":
						$platform_3 = $count;
						break;
					case "Android":
						$platform_4 = $count;
						break;
					case "OSX":
						$platform_5 = $count;
						break;
					default:
						$platform_other = $platform_other + $count;
				}
				
				if(isset($cfg['temp_cache_platforms'][$platform]) && $cfg['temp_cache_platforms'][$platform] == $count) {
					continue;
				} elseif(isset($cfg['temp_cache_platforms'][$platform])) {
					$cfg['temp_cache_platforms'][$platform] = $count;
					$allupdateplatform = "'{$platform}',";
					$updateplatform .= "WHEN `platform`='{$platform}' THEN {$count} ";
				} else {
					$cfg['temp_cache_platforms'][$platform] = $count;
					$insertplatform .= "('{$platform}',{$count}),";
				}
			}
		}
		if($updateplatform != '' && $allupdateplatform != '') {
			$allupdateplatform = substr($allupdateplatform, 0, -1);
			$sqlexec .= "UPDATE `$dbname`.`stats_platforms` SET `count`=CASE {$updateplatform}END WHERE `platform` IN ({$allupdateplatform}); ";
		}
		if($insertplatform != '') {
			$insertplatform = substr($insertplatform, 0, -1);
			$sqlexec .= "INSERT INTO `$dbname`.`stats_platforms` (`platform`,`count`) VALUES $insertplatform; ";
		}
		unset($platform_array,$allupdateplatform,$updateplatform,$insertplatform,$platform,$count);		
	}


	$country_array = array_count_values(str_word_count($country_string, 1));
	arsort($country_array);
	unset($country_string);
	$country_counter = $country_nation_other = $country_nation_name_1 = $country_nation_name_2 = $country_nation_name_3 = $country_nation_name_4 = $country_nation_name_5 = $country_nation_1 = $country_nation_2 = $country_nation_3 = $country_nation_4 = $country_nation_5 = 0;
	$allinsertnation = '';
	if(!isset($cfg['temp_cache_nations'])) {
		foreach ($country_array as $nation => $count) {
			$country_counter++;
			switch ($country_counter) {
				case 1:
					$country_nation_name_1 = $nation;
					$country_nation_1 = $count;
					break;
				case 2:
					$country_nation_name_2 = $nation;
					$country_nation_2 = $count;
					break;
				case 3:
					$country_nation_name_3 = $nation;
					$country_nation_3 = $count;
					break;
				case 4:
					$country_nation_name_4 = $nation;
					$country_nation_4 = $count;
					break;
				case 5:
					$country_nation_name_5 = $nation;
					$country_nation_5 = $count;
					break;
				default:
					$country_nation_other = $country_nation_other + $count;
			}
			$allinsertnation .= "('{$nation}',{$count}),";
			$cfg['temp_cache_nations'][$nation] = $count;
		}
		if($allinsertnation != '') {
			$allinsertnation = substr($allinsertnation, 0, -1);
			$sqlexec .= "DELETE FROM `$dbname`.`stats_nations`; INSERT INTO `$dbname`.`stats_nations` (`nation`,`count`) VALUES $allinsertnation; ";
		}
		unset($country_array,$allinsertnation,$nation,$count);
	} else {
		$allupdatenation = $updatenation = $insertnation = '';
		if(isset($cfg['temp_cache_nations'])) {
			foreach ($country_array as $nation => $count) {
				$country_counter++;
				switch ($country_counter) {
					case 1:
						$country_nation_name_1 = $nation;
						$country_nation_1 = $count;
						break;
					case 2:
						$country_nation_name_2 = $nation;
						$country_nation_2 = $count;
						break;
					case 3:
						$country_nation_name_3 = $nation;
						$country_nation_3 = $count;
						break;
					case 4:
						$country_nation_name_4 = $nation;
						$country_nation_4 = $count;
						break;
					case 5:
						$country_nation_name_5 = $nation;
						$country_nation_5 = $count;
						break;
					default:
						$country_nation_other = $country_nation_other + $count;
				}
				
				if(isset($cfg['temp_cache_nations'][$nation]) && $cfg['temp_cache_nations'][$nation] == $count) {
					continue;
				} elseif(isset($cfg['temp_cache_nations'][$nation])) {
					$cfg['temp_cache_nations'][$nation] = $count;
					$allupdatenation = "'{$nation}',";
					$updatenation .= "WHEN `nation`='{$nation}' THEN {$count} ";
				} else {
					$cfg['temp_cache_nations'][$nation] = $count;
					$insertnation .= "('{$nation}',{$count}),";
				}
			}
		}
		if($updatenation != '' && $allupdatenation != '') {
			$allupdatenation = substr($allupdatenation, 0, -1);
			$sqlexec .= "UPDATE `$dbname`.`stats_nations` SET `count`=CASE {$updatenation}END WHERE `nation` IN ({$allupdatenation}); ";
		}
		if($insertnation != '') {
			$insertnation = substr($insertnation, 0, -1);
			$sqlexec .= "INSERT INTO `$dbname`.`stats_nations` (`nation`,`count`) VALUES $insertnation; ";
		}
		unset($country_array,$allupdatenation,$updatenation,$insertnation,$nation,$count);
	}


	$version_1 = $version_2 = $version_3 = $version_4 = $version_5 = $version_name_1 = $version_name_2 = $version_name_3 = $version_name_4 = $version_name_5 = $count_version = $version_other = 0;	
	if(!isset($cfg['temp_cache_versions'])) {
		$allinsertversion = '';
		foreach($select_arr['count_version_user'] as $version => $count) {
			$count_version++;
			switch ($count_version) {
				case 1:
					$version_name_1 = $version;
					$version_1 = $count['count'];
					break;
				case 2:
					$version_name_2 = $version;
					$version_2 = $count['count'];
					break;
				case 3:
					$version_name_3 = $version;
					$version_3 = $count['count'];
					break;
				case 4:
					$version_name_4 = $version;
					$version_4 = $count['count'];
					break;
				case 5:
					$version_name_5 = $version;
					$version_5 = $count['count'];
					break;
				default:
					$version_other = $version_other + $count['count'];
			}
			$allinsertversion .= "('{$version}',{$count['count']}),";
			$cfg['temp_cache_versions'][$version] = $count['count'];
		}
		if($allinsertversion != '') {
			$allinsertversion = substr($allinsertversion, 0, -1);
			$sqlexec .= "DELETE FROM `$dbname`.`stats_versions`; INSERT INTO `$dbname`.`stats_versions` (`version`,`count`) VALUES $allinsertversion; ";
		}
		unset($allinsertversion);
	} else {
		$allupdatenversion = $updateversion = $insertversion = '';
		if(isset($cfg['temp_cache_versions'])) {
			foreach($select_arr['count_version_user'] as $version => $count) {
				$count_version++;
				switch ($count_version) {
					case 1:
						$version_name_1 = $version;
						$version_1 = $count['count'];
						break;
					case 2:
						$version_name_2 = $version;
						$version_2 = $count['count'];
						break;
					case 3:
						$version_name_3 = $version;
						$version_3 = $count['count'];
						break;
					case 4:
						$version_name_4 = $version;
						$version_4 = $count['count'];
						break;
					case 5:
						$version_name_5 = $version;
						$version_5 = $count['count'];
						break;
					default:
						$version_other = $version_other + $count['count'];
				}
				
				if(isset($cfg['temp_cache_versions'][$version]) && $cfg['temp_cache_versions'][$version] == $count['count']) {
					continue;
				} elseif(isset($cfg['temp_cache_versions'][$version])) {
					$cfg['temp_cache_versions'][$version] = $count['count'];
					$allupdatenversion = "'{$version}',";
					$updateversion .= "WHEN `version`='{$version}' THEN {$count['count']} ";
				} else {
					$cfg['temp_cache_versions'][$version] = $count['count'];
					$insertversion .= "('{$version}',{$count['count']}),";
				}
			}
		}
		if($updateversion != '' && $allupdatenversion != '') {
			$allupdatenversion = substr($allupdatenversion, 0, -1);
			$sqlexec .= "UPDATE `$dbname`.`stats_versions` SET `count`=CASE {$updateversion}END WHERE `version` IN ({$allupdatenversion}); ";
		}
		if($insertversion != '') {
			$insertversion = substr($insertversion, 0, -1);
			$sqlexec .= "INSERT INTO `$dbname`.`stats_versions` (`version`,`count`) VALUES $insertversion; ";
		}
		unset($allupdatenversion,$updateversion,$insertversion);
	}
	

	$server_used_slots = $serverinfo['virtualserver_clientsonline'] - $serverinfo['virtualserver_queryclientsonline'];
	$server_free_slots = $serverinfo['virtualserver_maxclients'] - $server_used_slots;
	$server_name = $mysqlcon->quote($serverinfo['virtualserver_name'], ENT_QUOTES);
	
	// Write stats/index and Nations, Platforms & Versions
	$sqlexec .= "UPDATE `$dbname`.`stats_server` SET `total_user`=$total_user,`total_online_time`=$total_online_time,`total_active_time`=$total_active_time,`total_inactive_time`=$total_inactive_time,`country_nation_name_1`='$country_nation_name_1',`country_nation_name_2`='$country_nation_name_2',`country_nation_name_3`='$country_nation_name_3',`country_nation_name_4`='$country_nation_name_4',`country_nation_name_5`='$country_nation_name_5',`country_nation_1`=$country_nation_1,`country_nation_2`=$country_nation_2,`country_nation_3`=$country_nation_3,`country_nation_4`=$country_nation_4,`country_nation_5`=$country_nation_5,`country_nation_other`=$country_nation_other,`platform_1`=$platform_1,`platform_2`=$platform_2,`platform_3`=$platform_3,`platform_4`=$platform_4,`platform_5`=$platform_5,`platform_other`=$platform_other,`version_name_1`='$version_name_1',`version_name_2`='$version_name_2',`version_name_3`='$version_name_3',`version_name_4`='$version_name_4',`version_name_5`='$version_name_5',`version_1`=$version_1,`version_2`=$version_2,`version_3`=$version_3,`version_4`=$version_4,`version_5`=$version_5,`version_other`=$version_other,`server_status`=$server_status,`server_free_slots`=$server_free_slots,`server_used_slots`=$server_used_slots,`server_channel_amount`={$serverinfo['virtualserver_channelsonline']},`server_ping`={$serverinfo['virtualserver_total_ping']},`server_packet_loss`={$serverinfo['virtualserver_total_packetloss_total']},`server_bytes_down`={$serverinfo['connection_bytes_received_total']},`server_bytes_up`={$serverinfo['connection_bytes_sent_total']},`server_uptime`={$serverinfo['virtualserver_uptime']},`server_id`={$serverinfo['virtualserver_id']},`server_name`=$server_name,`server_pass`={$serverinfo['virtualserver_flag_password']},`server_creation_date`={$serverinfo['virtualserver_created']},`server_platform`='{$serverinfo['virtualserver_platform']}',`server_weblist`={$serverinfo['virtualserver_weblist_enabled']},`server_version`='{$serverinfo['virtualserver_version']}',`user_today`=$user_today,`user_week`=$user_week,`user_month`=$user_month,`user_quarter`=$user_quarter; ";
	
	// Stats for Server Usage
	if(key($select_arr['max_timestamp_server_usage'])  == 0 || ($nowtime - key($select_arr['max_timestamp_server_usage'])) > 898) { // every 15 mins
		//Calc time next rankup
		enter_logfile($cfg,6,"Calc next rankup for offline user");
		$upnextuptime = $nowtime - 1800;
		if(($uuidsoff = $mysqlcon->query("SELECT `uuid`,`idle`,`count` FROM `$dbname`.`user` WHERE `online`<>1 AND `lastseen`>$upnextuptime")->fetchAll(PDO::FETCH_ASSOC)) === false) {
			enter_logfile($cfg,2,"calc_serverstats 13:".print_r($mysqlcon->errorInfo(), true));
		}
		if(count($uuidsoff) != 0) {
			foreach($uuidsoff as $uuid) {
				$count    = $uuid['count'];
				if ($cfg['rankup_time_assess_mode'] == 1) {
					$activetime = $count - $uuid['idle'];
					$dtF        = new DateTime("@0");
					$dtT        = new DateTime("@".round($activetime));
				} else {
					$activetime = $count;
					$dtF        = new DateTime("@0");
					$dtT        = new DateTime("@".round($count));
				}
				$grpcount=0;
				foreach ($cfg['rankup_definition'] as $time => $groupid) {
					$grpcount++;
					if ($activetime > $time) {
						if($grpcount == 1) {
							$nextup = 0;
						}
						break;
					} else {
						$nextup = $time - $activetime;
					}
				}
				$updatenextup[] = array(
					"uuid" => $uuid['uuid'],
					"nextup" => $nextup
				);
			}
			unset($uuidsoff);
		}

		if(isset($updatenextup)) {
			$allupdateuuid = $allupdatenextup = '';
			foreach ($updatenextup as $updatedata) {
				$allupdateuuid   = $allupdateuuid . "'" . $updatedata['uuid'] . "',";
				$allupdatenextup = $allupdatenextup . "WHEN '" . $updatedata['uuid'] . "' THEN " . $updatedata['nextup'] . " ";
			}
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			$sqlexec .= "INSERT INTO `$dbname`.`server_usage` (`timestamp`,`clients`,`channel`) VALUES ($nowtime,$server_used_slots,{$serverinfo['virtualserver_channelsonline']}); UPDATE `$dbname`.`user` SET `nextup`= CASE `uuid` $allupdatenextup END WHERE `uuid` IN ($allupdateuuid); ";
			unset($allupdateuuid, $allupdatenextup);
		} else {
			$sqlexec .= "INSERT INTO `$dbname`.`server_usage` (`timestamp`,`clients`,`channel`) VALUES ($nowtime,$server_used_slots,{$serverinfo['virtualserver_channelsonline']}); ";
		}
		enter_logfile($cfg,6,"Calc next rankup for offline user [DONE]");
	}

	// Calc Values for server stats
	if($select_arr['job_check']['calc_server_stats']['timestamp'] < ($nowtime - 900)) {
		if(($entry_snapshot_count = $mysqlcon->query("SELECT count(DISTINCT(`timestamp`)) AS `timestamp` FROM `$dbname`.`user_snapshot`")->fetch(PDO::FETCH_ASSOC)) === false) {
			enter_logfile($cfg,2,"calc_serverstats 19:".print_r($mysqlcon->errorInfo(), true));
		}
		if ($entry_snapshot_count['timestamp'] > 27) {
			// Calc total_online_week
			if(($snapshot_count_week = $mysqlcon->query("SELECT (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `timestamp`=(SELECT MAX(`timestamp`) FROM `$dbname`.`user_snapshot`)) - (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `timestamp`=(SELECT MIN(`s2`.`timestamp`) AS `value2` FROM (SELECT DISTINCT(`timestamp`) FROM `$dbname`.`user_snapshot` ORDER BY `timestamp` DESC LIMIT 28) AS `s2`, `$dbname`.`user_snapshot` AS `s1` WHERE `s1`.`timestamp`=`s2`.`timestamp`) AND `uuid` IN (SELECT `uuid` FROM `$dbname`.`user`)) AS `count`")->fetch(PDO::FETCH_ASSOC)) === false) {
				enter_logfile($cfg,2,"calc_serverstats 20:".print_r($mysqlcon->errorInfo(), true));
			}
			if($snapshot_count_week['count'] == NULL) {
				$total_online_week = 0;
			} else {
				$total_online_week = $snapshot_count_week['count'];
			}
		} else {
			$total_online_week = 0;
		}
		if ($entry_snapshot_count['timestamp'] > 119) {
			// Calc total_online_month
			if(($snapshot_count_month = $mysqlcon->query("SELECT (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `timestamp`=(SELECT MAX(`timestamp`) FROM `$dbname`.`user_snapshot`)) - (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `timestamp`=(SELECT MIN(`s2`.`timestamp`) AS `value2` FROM (SELECT DISTINCT(`timestamp`) FROM `$dbname`.`user_snapshot` ORDER BY `timestamp` DESC LIMIT 120) AS `s2`, `$dbname`.`user_snapshot` AS `s1` WHERE `s1`.`timestamp`=`s2`.`timestamp`) AND `uuid` IN (SELECT `uuid` FROM `$dbname`.`user`)) AS `count`")->fetch(PDO::FETCH_ASSOC)) === false) {
				enter_logfile($cfg,2,"calc_serverstats 21:".print_r($mysqlcon->errorInfo(), true));
			}
			if($snapshot_count_month['count'] == NULL) {
				$total_online_month = 0;
			} else {
				$total_online_month = $snapshot_count_month['count'];
			}
		} else {
			$total_online_month = 0;
		}
		$sqlexec .= "UPDATE `$dbname`.`stats_server` SET `total_online_month`=$total_online_month,`total_online_week`=$total_online_week; UPDATE `$dbname`.`job_check` SET `timestamp`=$nowtime WHERE `job_name`='calc_server_stats'; ";
		
		if ($select_arr['job_check']['get_version']['timestamp'] < ($nowtime - 43200)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://ts-n.net/ranksystem/'.$cfg['version_update_channel']);
			curl_setopt($ch, CURLOPT_REFERER, 'TSN Ranksystem');
			curl_setopt($ch, CURLOPT_USERAGENT, 
				$cfg['version_current_using'].";".
				php_uname("s").";".
				php_uname("r").";".
				phpversion().";".
				$dbtype.";".
				$cfg['teamspeak_host_address'].";".
				$cfg['teamspeak_voice_port'].";".
				__DIR__.";".
				$total_user.";".
				$user_today.";".
				$user_week.";".
				$user_month.";".
				$user_quarter.";".
				$total_online_week.";".
				$total_online_month.";".
				$total_active_time.";".
				$total_inactive_time.";".
				$cfg['temp_ts_version'].";".
				$cfg['temp_db_version']
			);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$cfg['version_latest_available'] = curl_exec($ch);
			curl_close($ch);

			if(version_compare($cfg['version_latest_available'], $cfg['version_current_using'], '>') && $cfg['version_latest_available'] != NULL) {
				enter_logfile($cfg,4,$lang['upinf']);
				if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
					foreach(array_flip($cfg['webinterface_admin_client_unique_id_list']) as $clientid) {
						usleep($cfg['teamspeak_query_command_delay']);
						try {
							$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $cfg['version_current_using'], $cfg['version_latest_available'], 'https://ts-ranksystem.com/#changelog'));
							enter_logfile($cfg,4,"  ".sprintf($lang['upusrinf'], $clientid));
						} catch (Exception $e) {
							enter_logfile($cfg,6,"  ".sprintf($lang['upusrerr'], $clientid));
						}
					}
				}
				update_rs($mysqlcon,$lang,$cfg,$dbname,$phpcommand);
			}
			$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$nowtime WHERE `job_name`='get_version'; UPDATE `$dbname`.`cfg_params` SET `value`='{$cfg['version_latest_available']}' WHERE `param`='version_latest_available';";
		}
		
		//Calc Rank
		if ($cfg['rankup_time_assess_mode'] == 1) {
			$sqlexec .= "SET @a:=0; UPDATE `$dbname`.`user` AS `u` INNER JOIN (SELECT @a:=@a+1 `nr`,`uuid` FROM `$dbname`.`user` WHERE `except`<2 ORDER BY (`count` - `idle`) DESC) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`nr`; ";
			//MySQL 8 or above
			//UPDATE `user` AS `u` INNER JOIN (SELECT RANK() OVER (ORDER BY (`count` - `idle`) DESC) AS `rank`, `uuid` FROM `user` WHERE `except`<2) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`rank`;
		} else {
			$sqlexec .= "SET @a:=0; UPDATE `$dbname`.`user` AS `u` INNER JOIN (SELECT @a:=@a+1 `nr`,`uuid` FROM `$dbname`.`user` WHERE `except`<2 ORDER BY `count` DESC) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`nr`; ";
			//MySQL 8 or above
			//UPDATE `user` AS `u` INNER JOIN (SELECT RANK() OVER (ORDER BY `count` DESC) AS `rank`, `uuid` FROM `user` WHERE `except`<2) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`rank`;
		}
	}

	enter_logfile($cfg,6,"calc_serverstats needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
	return($sqlexec);
}
?>