<?PHP
function calc_serverstats($ts3,$mysqlcon,&$cfg,$dbname,$dbtype,$serverinfo,&$db_cache,$phpcommand,$lang) {
	$starttime = microtime(true);
	$nowtime = time();
	$sqlexec = '';

	if(intval($db_cache['job_check']['calc_donut_chars']['timestamp']) < ($nowtime - 8) || intval($db_cache['job_check']['get_version']['timestamp']) < ($nowtime - 43199)) {
		$db_cache['job_check']['calc_donut_chars']['timestamp'] = $nowtime;
		$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`={$nowtime} WHERE `job_name`='calc_donut_chars';\n";

		$total_user = $total_online_time = $total_inactive_time = $server_used_slots = $server_channel_amount = $user_today = $user_week = $user_month = $user_quarter = 0;
		$country_array = $platform_array = $count_version_user = array();

		foreach($db_cache['all_user'] as $uuid) {
			$nation = (string)$uuid['nation']; 
			$platform = (string)$uuid['platform']; 
			$version = (string)$uuid['version']; 

			if ($uuid['lastseen']>($nowtime-86400)) {
				$user_quarter++; $user_month++; $user_week++; $user_today++;
			} elseif ($uuid['lastseen']>($nowtime-604800)) {
				$user_quarter++; $user_month++; $user_week++;
			} elseif ($uuid['lastseen']>($nowtime-2592000)) {
				$user_quarter++; $user_month++;
			} elseif ($uuid['lastseen']>($nowtime-7776000)) {
				$user_quarter++;
			}

			if(isset($country_array[$nation])) {
				$country_array[$nation]++;
			} else {
				$country_array[$nation] = 1;
			}

			if(isset($platform_array[$platform])) {
				$platform_array[$platform]++;
			} else {
				$platform_array[$platform] = 1;
			}

			if(isset($count_version_user[$version])) {
				$count_version_user[$version]++;
			} else {
				$count_version_user[$version] = 1;
			}

			if($uuid['count']>0) $total_online_time += $uuid['count'];
			if($uuid['idle']>0) $total_inactive_time += $uuid['idle'];
		}

		arsort($country_array);
		arsort($platform_array);
		arsort($count_version_user);
		$total_online_time = round($total_online_time);
		$total_inactive_time = round($total_inactive_time);
		$total_active_time = $total_online_time - $total_inactive_time;

		$total_user = count($db_cache['all_user']);

		if($serverinfo['virtualserver_status']=="online") {
			$server_status = 1;
		} elseif($serverinfo['virtualserver_status']=="offline") {
			$server_status = 2;
		} elseif($serverinfo['virtualserver_status']=="virtual online") {	
			$server_status = 3;
		} else {
			$server_status = 4;
		}


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
				$sqlexec .= "DELETE FROM `$dbname`.`stats_platforms`;\nINSERT INTO `$dbname`.`stats_platforms` (`platform`,`count`) VALUES $allinsertplatform;\n";
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
				$sqlexec .= "UPDATE `$dbname`.`stats_platforms` SET `count`=CASE {$updateplatform}END WHERE `platform` IN ({$allupdateplatform});\n";
			}
			if($insertplatform != '') {
				$insertplatform = substr($insertplatform, 0, -1);
				$sqlexec .= "INSERT INTO `$dbname`.`stats_platforms` (`platform`,`count`) VALUES $insertplatform;\n";
			}
			unset($platform_array,$allupdateplatform,$updateplatform,$insertplatform,$platform,$count);		
		}


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
				$sqlexec .= "DELETE FROM `$dbname`.`stats_nations`;\nINSERT INTO `$dbname`.`stats_nations` (`nation`,`count`) VALUES $allinsertnation;\n";
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
				$sqlexec .= "UPDATE `$dbname`.`stats_nations` SET `count`=CASE {$updatenation}END WHERE `nation` IN ({$allupdatenation});\n";
			}
			if($insertnation != '') {
				$insertnation = substr($insertnation, 0, -1);
				$sqlexec .= "INSERT INTO `$dbname`.`stats_nations` (`nation`,`count`) VALUES $insertnation;\n";
			}
			unset($country_array,$allupdatenation,$updatenation,$insertnation,$nation,$count);
		}


		$version_1 = $version_2 = $version_3 = $version_4 = $version_5 = $version_name_1 = $version_name_2 = $version_name_3 = $version_name_4 = $version_name_5 = $count_version = $version_other = 0;	
		if(!isset($cfg['temp_cache_versions'])) {
			$allinsertversion = '';
			foreach($count_version_user as $version => $count) {
				$count_version++;
				switch ($count_version) {
					case 1:
						$version_name_1 = $version;
						$version_1 = $count;
						break;
					case 2:
						$version_name_2 = $version;
						$version_2 = $count;
						break;
					case 3:
						$version_name_3 = $version;
						$version_3 = $count;
						break;
					case 4:
						$version_name_4 = $version;
						$version_4 = $count;
						break;
					case 5:
						$version_name_5 = $version;
						$version_5 = $count;
						break;
					default:
						$version_other = $version_other + $count;
				}
				$allinsertversion .= "('{$version}',{$count}),";
				$cfg['temp_cache_versions'][$version] = $count;
			}
			if($allinsertversion != '') {
				$allinsertversion = substr($allinsertversion, 0, -1);
				$sqlexec .= "DELETE FROM `$dbname`.`stats_versions`;\nINSERT INTO `$dbname`.`stats_versions` (`version`,`count`) VALUES $allinsertversion;\n";
			}
			unset($allinsertversion);
		} else {
			$allupdatenversion = $updateversion = $insertversion = '';
			if(isset($cfg['temp_cache_versions'])) {
				foreach($count_version_user as $version => $count) {
					$count_version++;
					switch ($count_version) {
						case 1:
							$version_name_1 = $version;
							$version_1 = $count;
							break;
						case 2:
							$version_name_2 = $version;
							$version_2 = $count;
							break;
						case 3:
							$version_name_3 = $version;
							$version_3 = $count;
							break;
						case 4:
							$version_name_4 = $version;
							$version_4 = $count;
							break;
						case 5:
							$version_name_5 = $version;
							$version_5 = $count;
							break;
						default:
							$version_other = $version_other + $count;
					}
					
					if(isset($cfg['temp_cache_versions'][$version]) && $cfg['temp_cache_versions'][$version] == $count) {
						continue;
					} elseif(isset($cfg['temp_cache_versions'][$version])) {
						$cfg['temp_cache_versions'][$version] = $count;
						$allupdatenversion = "'{$version}',";
						$updateversion .= "WHEN `version`='{$version}' THEN {$count} ";
					} else {
						$cfg['temp_cache_versions'][$version] = $count;
						$insertversion .= "('{$version}',{$count}),";
					}
				}
			}
			if($updateversion != '' && $allupdatenversion != '') {
				$allupdatenversion = substr($allupdatenversion, 0, -1);
				$sqlexec .= "UPDATE `$dbname`.`stats_versions` SET `count`=CASE {$updateversion}END WHERE `version` IN ({$allupdatenversion});\n";
			}
			if($insertversion != '') {
				$insertversion = substr($insertversion, 0, -1);
				$sqlexec .= "INSERT INTO `$dbname`.`stats_versions` (`version`,`count`) VALUES $insertversion;\n";
			}
			unset($allupdatenversion,$updateversion,$insertversion);
		}
		

		$server_used_slots = $serverinfo['virtualserver_clientsonline'] - $serverinfo['virtualserver_queryclientsonline'];
		$server_free_slots = $serverinfo['virtualserver_maxclients'] - $server_used_slots;
		$server_name = $mysqlcon->quote($serverinfo['virtualserver_name'], ENT_QUOTES);
		$serverinfo['virtualserver_total_ping'] = round((substr($serverinfo['virtualserver_total_ping'], 0, strpos($serverinfo['virtualserver_total_ping'], '.')).".".substr($serverinfo['virtualserver_total_ping'], (strpos($serverinfo['virtualserver_total_ping'], '.') + 1), 4)));
		if($serverinfo['virtualserver_total_ping'] > 32767) $serverinfo['virtualserver_total_ping'] = 32767;
		if(!isset($serverinfo['virtualserver_weblist_enabled']) || $serverinfo['virtualserver_weblist_enabled'] === NULL) $serverinfo['virtualserver_weblist_enabled'] = 0;

		// Write stats/index and Nations, Platforms & Versions
		$sqlexec .= "UPDATE `$dbname`.`stats_server` SET `total_user`=$total_user,`total_online_time`=$total_online_time,`total_active_time`=$total_active_time,`total_inactive_time`=$total_inactive_time,`country_nation_name_1`='$country_nation_name_1',`country_nation_name_2`='$country_nation_name_2',`country_nation_name_3`='$country_nation_name_3',`country_nation_name_4`='$country_nation_name_4',`country_nation_name_5`='$country_nation_name_5',`country_nation_1`=$country_nation_1,`country_nation_2`=$country_nation_2,`country_nation_3`=$country_nation_3,`country_nation_4`=$country_nation_4,`country_nation_5`=$country_nation_5,`country_nation_other`=$country_nation_other,`platform_1`=$platform_1,`platform_2`=$platform_2,`platform_3`=$platform_3,`platform_4`=$platform_4,`platform_5`=$platform_5,`platform_other`=$platform_other,`version_name_1`='$version_name_1',`version_name_2`='$version_name_2',`version_name_3`='$version_name_3',`version_name_4`='$version_name_4',`version_name_5`='$version_name_5',`version_1`=$version_1,`version_2`=$version_2,`version_3`=$version_3,`version_4`=$version_4,`version_5`=$version_5,`version_other`=$version_other,`server_status`=$server_status,`server_free_slots`=$server_free_slots,`server_used_slots`=$server_used_slots,`server_channel_amount`={$serverinfo['virtualserver_channelsonline']},`server_ping`={$serverinfo['virtualserver_total_ping']},`server_packet_loss`={$serverinfo['virtualserver_total_packetloss_total']},`server_bytes_down`={$serverinfo['connection_bytes_received_total']},`server_bytes_up`={$serverinfo['connection_bytes_sent_total']},`server_uptime`={$serverinfo['virtualserver_uptime']},`server_id`={$serverinfo['virtualserver_id']},`server_name`=$server_name,`server_pass`={$serverinfo['virtualserver_flag_password']},`server_creation_date`={$serverinfo['virtualserver_created']},`server_platform`='{$serverinfo['virtualserver_platform']}',`server_weblist`={$serverinfo['virtualserver_weblist_enabled']},`server_version`='{$serverinfo['virtualserver_version']}',`user_today`=$user_today,`user_week`=$user_week,`user_month`=$user_month,`user_quarter`=$user_quarter;\n";
	}


	// Calc Values for server stats
	if(intval($db_cache['job_check']['calc_server_stats']['timestamp']) < ($nowtime - 899)) {
		$db_cache['job_check']['calc_server_stats']['timestamp'] = $nowtime;
		$weekago = intval($db_cache['job_check']['last_snapshot_id']['timestamp']) - 28;
		$monthago = intval($db_cache['job_check']['last_snapshot_id']['timestamp']) - 120;
		if ($weekago < 1) $weekago = $weekago + 121;
		if ($monthago < 1) $monthago = $monthago + 121;
		
		if(($entry_snapshot_count = $mysqlcon->query("SELECT count(DISTINCT(`id`)) AS `id` FROM `$dbname`.`user_snapshot`")->fetch(PDO::FETCH_ASSOC)) === false) {
			enter_logfile(2,"calc_serverstats 19:".print_r($mysqlcon->errorInfo(), true));
		}
		if ($entry_snapshot_count['id'] > 28) {
			// Calc total_online_week
			#if(($snapshot_count_week = $mysqlcon->query("SELECT (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `id`={$db_cache['job_check']['last_snapshot_id']['timestamp']}) - (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `id`={$weekago}) AS `count`;")->fetch(PDO::FETCH_ASSOC)) === false) {
			if(($snapshot_count_week = $mysqlcon->query("SELECT SUM(`a`.`count` - `b`.`count`) AS `count` FROM `$dbname`.`user_snapshot` `a`, `$dbname`.`user_snapshot` `b` WHERE `b`.`cldbid` = `a`.`cldbid` AND `a`.`id`={$db_cache['job_check']['last_snapshot_id']['timestamp']} AND `b`.`id`={$weekago} AND `a`.`count`>`b`.`count`;")->fetch(PDO::FETCH_ASSOC)) === false) {
				enter_logfile(2,"calc_serverstats 20:".print_r($mysqlcon->errorInfo(), true));
			}
			if($snapshot_count_week['count'] == NULL) {
				$total_online_week = 0;
			} else {
				$total_online_week = $snapshot_count_week['count'];
			}
		} else {
			$total_online_week = 0;
		}
		if ($entry_snapshot_count['id'] > 120) {
			// Calc total_online_month
			#if(($snapshot_count_month = $mysqlcon->query("SELECT (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `id`={$db_cache['job_check']['last_snapshot_id']['timestamp']}) - (SELECT SUM(`count`) FROM `$dbname`.`user_snapshot` WHERE `id`={$monthago}) AS `count`;")->fetch(PDO::FETCH_ASSOC)) === false) {
			if(($snapshot_count_month = $mysqlcon->query("SELECT SUM(`a`.`count` - `b`.`count`) AS `count` FROM `$dbname`.`user_snapshot` `a`, `$dbname`.`user_snapshot` `b` WHERE `b`.`cldbid` = `a`.`cldbid` AND `a`.`id`={$db_cache['job_check']['last_snapshot_id']['timestamp']} AND `b`.`id`={$monthago} AND `a`.`count`>`b`.`count`;")->fetch(PDO::FETCH_ASSOC)) === false) {
				enter_logfile(2,"calc_serverstats 21:".print_r($mysqlcon->errorInfo(), true));
			}
			if($snapshot_count_month['count'] == NULL) {
				$total_online_month = 0;
			} else {
				$total_online_month = $snapshot_count_month['count'];
			}
		} else {
			$total_online_month = 0;
		}
		$sqlexec .= "UPDATE `$dbname`.`stats_server` SET `total_online_month`={$total_online_month},`total_online_week`={$total_online_week};\nUPDATE `$dbname`.`job_check` SET `timestamp`={$nowtime} WHERE `job_name`='calc_server_stats';\n";
		
		if (intval($db_cache['job_check']['get_version']['timestamp']) < ($nowtime - 43199)) {
			$db_cache['job_check']['get_version']['timestamp'] = $nowtime;
			enter_logfile(6,"Get the latest Ranksystem Version.");
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
				";". #old installation path
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
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$cfg['version_latest_available'] = curl_exec($ch);
			unset($ch);

			if(!isset($cfg['stats_news_html']) || $cfg['stats_news_html'] != '') {
				$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='0' WHERE `job_name`='news_html';\nUPDATE `$dbname`.`cfg_params` SET `value`='' WHERE `param`='stats_news_html';\n";
			}
			$newh = curl_init();
			curl_setopt($newh, CURLOPT_URL, 'https://ts-n.net/ranksystem/news_html');
			curl_setopt($newh, CURLOPT_REFERER, 'TSN Ranksystem - News HTML');
			curl_setopt($newh, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($newh, CURLOPT_MAXREDIRS, 10);
			curl_setopt($newh, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($newh, CURLOPT_CONNECTTIMEOUT, 5);
			$cfg['stats_news_html'] = curl_exec($newh);
			unset($newh);
			if($cfg['stats_news_html'] != '') {
				$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$nowtime WHERE `job_name`='news_html';\nUPDATE `$dbname`.`cfg_params` SET `value`='{$cfg['stats_news_html']}' WHERE `param`='stats_news_html';\n";
			}

			if(!isset($cfg['teamspeak_news_bb']) || $cfg['teamspeak_news_bb'] != '') {
				$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='0' WHERE `job_name`='news_bb';\nUPDATE `$dbname`.`cfg_params` SET `value`='' WHERE `param`='teamspeak_news_bb';\n";
			}
			$newb = curl_init();
			curl_setopt($newb, CURLOPT_URL, 'https://ts-n.net/ranksystem/news_bb');
			curl_setopt($newb, CURLOPT_REFERER, 'TSN Ranksystem - News BB');
			curl_setopt($newb, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($newb, CURLOPT_MAXREDIRS, 10);
			curl_setopt($newb, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($newb, CURLOPT_CONNECTTIMEOUT, 5);
			$cfg['teamspeak_news_bb'] = curl_exec($newb);
			unset($newb);
			if($cfg['teamspeak_news_bb'] != '') {
				$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$nowtime WHERE `job_name`='news_bb';\nUPDATE `$dbname`.`cfg_params` SET `value`='{$cfg['teamspeak_news_bb']}' WHERE `param`='teamspeak_news_bb';\n";
			}
			
			if(version_compare($cfg['version_latest_available'], $cfg['version_current_using'], '>') && $cfg['version_latest_available'] != NULL) {
				enter_logfile(4,$lang['upinf']);
				if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
					foreach(array_flip($cfg['webinterface_admin_client_unique_id_list']) as $clientid) {
						usleep($cfg['teamspeak_query_command_delay']);
						try {
							if(isset($cfg['teamspeak_news_bb']) && $cfg['teamspeak_news_bb'] != '') {
								$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $cfg['version_current_using'], $cfg['version_latest_available'], 'https://ts-ranksystem.com/#changelog')."\n\n[U]Latest News:[/U]\n".$cfg['teamspeak_news_bb']);
							} else {
								$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $cfg['version_current_using'], $cfg['version_latest_available'], 'https://ts-ranksystem.com/#changelog'));
							}
							enter_logfile(4,"  ".sprintf($lang['upusrinf'], $clientid));
						} catch (Exception $e) {
							enter_logfile(6,"  ".sprintf($lang['upusrerr'], $clientid));
						}
					}
				}
				$sqlexec .= update_rs($mysqlcon,$lang,$cfg,$dbname);
			}
			$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$nowtime WHERE `job_name`='get_version';\nUPDATE `$dbname`.`cfg_params` SET `value`='{$cfg['version_latest_available']}' WHERE `param`='version_latest_available';\n";
		}
		
		//Calc Rank
		$dbversion = $mysqlcon->getAttribute(PDO::ATTR_SERVER_VERSION);
		preg_match("/^[0-9\.-]+/", $dbversion, $version_number);
		if ($cfg['rankup_time_assess_mode'] == 1) {
			if(version_compare($version_number[0], '10.6', '>=') || version_compare($version_number[0], '8', '>=') && version_compare($version_number[0], '10', '<') || version_compare($version_number[0], '5.5.5-10.6', '>=') && version_compare($version_number[0], '5.5.6', '<')) {
				$sqlexec .= "UPDATE `$dbname`.`user` AS `u` INNER JOIN (SELECT RANK() OVER (ORDER BY (`count` - `idle`) DESC) AS `rank`, `uuid` FROM `$dbname`.`user` WHERE `except`<2) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`rank`;\n";
			} else {
				$sqlexec .= "SET @a:=0;\nUPDATE `$dbname`.`user` AS `u` INNER JOIN (SELECT @a:=@a+1 `nr`,`uuid` FROM `$dbname`.`user` WHERE `except`<2 ORDER BY (`count` - `idle`) DESC) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`nr`;\n";
			}
		} else {
			if(version_compare($version_number[0], '10.6', '>=') || version_compare($version_number[0], '8', '>=') && version_compare($version_number[0], '10', '<') || version_compare($version_number[0], '5.5.5-10.6', '>=') && version_compare($version_number[0], '5.5.6', '<')) {
				$sqlexec .= "UPDATE `$dbname`.`user` AS `u` INNER JOIN (SELECT RANK() OVER (ORDER BY `count` DESC) AS `rank`, `uuid` FROM `$dbname`.`user` WHERE `except`<2) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`rank`;\n";
			} else {
				$sqlexec .= "SET @a:=0;\nUPDATE `$dbname`.`user` AS `u` INNER JOIN (SELECT @a:=@a+1 `nr`,`uuid` FROM `$dbname`.`user` WHERE `except`<2 ORDER BY `count` DESC) AS `s` USING (`uuid`) SET `u`.`rank`=`s`.`nr`;\n";
			}
		}
	}

	enter_logfile(6,"calc_serverstats needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
	return($sqlexec);
}
?>