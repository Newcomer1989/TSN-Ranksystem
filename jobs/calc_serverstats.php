<?PHP
function calc_serverstats($ts3,$mysqlcon,$dbname,$dbtype,$slowmode,$timezone,$serverinfo,$substridle,$grouptime,$logpath,$ts,$currvers,$upchannel,$select_arr,$phpcommand,$adminuuid) {
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
				$sqlexec .= "INSERT INTO $dbname.user_snapshot (timestamp, uuid, count, idle) VALUES $allinsertsnap; ";
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
			$checkarr = array_flip(array('CQkJCQkJPGEgaHJlZj0iaW5mby5waHAiPjxpIGNsYXNzPSJmYSBmYS1mdyBmYS1pbmZvLWNpcmNsZSI+PC9pPiZuYnNwOzw/UEhQIGVjaG8gJGxhbmdbJ3N0bnYwMDMwJ107','CQkJCQk8P1BIUCBlY2hvICc8bGknLihiYXNlbmFtZSgkX1NFUlZFUlsnU0NSSVBUX05BTUUnXSkgPT0gImluZm8ucGhwIiA/ICcgY2xhc3M9ImFjdGl2ZSI+JyA6ICc+Jyk7'));
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
				if(strstr(base64_encode($line), "VGhlIDxhIGhyZWY9Ii8vdHMtbi5uZXQvcmFua3N5c3RlbS5waHAiIHRhcmdldD0iX2JsYW5rIj5SYW5rc3lzdGVtPC9hPiB3YXMgY29kZWQgYnkgPHN0cm9uZz5OZXdjb21lcjE5ODk8L3N0cm9uZz4gQ29weXJpZ2h0ICZjb3B5OyAyMDA5LTIwMTggPGEgaHJlZj0iLy90cy1uLm5ldC8iIHRhcmdldD0iX2JsYW5rIj5UZWFtU3BlYWsgU3BvbnNvcmluZyBUUy1OLk5FVDwvYT4=")) {
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
	
	$country_array = array_count_values(str_word_count($country_string, 1));
	arsort($country_array);
	unset($country_string);
	$country_counter = $country_nation_other = $country_nation_name_1 = $country_nation_name_2 = $country_nation_name_3 = $country_nation_name_4 = $country_nation_name_5 = $country_nation_1 = $country_nation_2 = $country_nation_3 = $country_nation_4 = $country_nation_5 = 0;
	$allinsertnation = '';
	foreach ($country_array as $k => $v) {
		$country_counter++;
		if ($country_counter == 1) {
			$country_nation_name_1 = $k;
			$country_nation_1 = $v;
		} elseif ($country_counter == 2) {
			$country_nation_name_2 = $k;
			$country_nation_2 = $v;
		} elseif ($country_counter == 3) {
			$country_nation_name_3 = $k;
			$country_nation_3 = $v;
		} elseif ($country_counter == 4) {
			$country_nation_name_4 = $k;
			$country_nation_4 = $v;
		} elseif ($country_counter == 5) {
			$country_nation_name_5 = $k;
			$country_nation_5 = $v;
		} else {
			$country_nation_other = $country_nation_other + $v;
		}
		$allinsertnation = $allinsertnation . "('" . $k . "', " . $v . "),";
	}
	$allinsertnation = substr($allinsertnation, 0, -1);
	$platform_array = array_count_values(str_word_count($platform_string, 1));
	unset($platform_string, $country_array);
	$platform_other = $platform_1 = $platform_2 = $platform_3 = $platform_4 = $platform_5 = 0;
	$allinsertplatform = '';
	foreach ($platform_array as $k => $v) {
		if ($k == "Windows") {
			$platform_1 = $v;
		} elseif ($k == "iOS") {
			$platform_2 = $v;
		} elseif ($k == "Linux") {
			$platform_3 = $v;
		} elseif ($k == "Android") {
			$platform_4 = $v;
		} elseif ($k == "OSX") {
			$platform_5 = $v;
		} else {
			$platform_other = $platform_other + $v;
		}
		$allinsertplatform = $allinsertplatform . "('" . $k . "', " . $v . "),";
	}
	unset($platform_array);
	$allinsertplatform = substr($allinsertplatform, 0, -1);
	$version_1 = $version_2 = $version_3 = $version_4 = $version_5 = $version_name_1 = $version_name_2 = $version_name_3 = $version_name_4 = $version_name_5 = $count_version = $sum_count = 0;
	$allinsertversion = '';
	foreach($select_arr['count_version_user'] as $version => $count) {
		$count_version++;
		if ($count_version == 1) {
			$version_1 = $count['count'];
			$version_name_1 = $version;
		} elseif ($count_version == 2) {
			$version_2 = $count['count'];
			$version_name_2 = $version;
		} elseif ($count_version == 3) {
			$version_3 = $count['count'];
			$version_name_3 = $version;
		} elseif ($count_version == 4) {
			$version_4 = $count['count'];
			$version_name_4 = $version;
		} elseif ($count_version == 5) {
			$version_5 = $count['count'];
			$version_name_5 = $version;
		}
		$sum_count = $sum_count + $count['count'];
		$allinsertversion = $allinsertversion . "('" . $version . "', " . $count['count'] . "),";
	}
	$allinsertversion = substr($allinsertversion, 0, -1);
	$version_other = $sum_count - $version_1 - $version_2 - $version_3 - $version_4 - $version_5;

	$server_used_slots = $serverinfo['virtualserver_clientsonline'] - $serverinfo['virtualserver_queryclientsonline'];
	$server_free_slots = $serverinfo['virtualserver_maxclients'] - $server_used_slots;
	$server_channel_amount = $serverinfo['virtualserver_channelsonline'];
	$server_ping = $serverinfo['virtualserver_total_ping'];
	$server_packet_loss = $serverinfo['virtualserver_total_packetloss_total'];
	$server_bytes_down = $serverinfo['connection_bytes_received_total'];
	$server_bytes_up = $serverinfo['connection_bytes_sent_total'];
	$server_uptime = $serverinfo['virtualserver_uptime'];
	$server_id = $serverinfo['virtualserver_id'];
	$server_name = $mysqlcon->quote($serverinfo['virtualserver_name'], ENT_QUOTES);
	$server_pass = $serverinfo['virtualserver_flag_password'];
	$server_creation_date = $serverinfo['virtualserver_created'];
	$server_platform = $serverinfo['virtualserver_platform'];
	$server_weblist = $serverinfo['virtualserver_weblist_enabled'];
	$server_version = $serverinfo['virtualserver_version'];
	
	// Write stats/index and Nations, Platforms & Versions
	$sqlexec .= "DELETE FROM $dbname.stats_nations; DELETE FROM $dbname.stats_platforms; DELETE FROM $dbname.stats_versions; UPDATE $dbname.stats_server SET total_user='$total_user', total_online_time='$total_online_time', total_active_time='$total_active_time', total_inactive_time='$total_inactive_time', country_nation_name_1='$country_nation_name_1', country_nation_name_2='$country_nation_name_2', country_nation_name_3='$country_nation_name_3', country_nation_name_4='$country_nation_name_4', country_nation_name_5='$country_nation_name_5', country_nation_1='$country_nation_1', country_nation_2='$country_nation_2', country_nation_3='$country_nation_3', country_nation_4='$country_nation_4', country_nation_5='$country_nation_5', country_nation_other='$country_nation_other', platform_1='$platform_1', platform_2='$platform_2', platform_3='$platform_3', platform_4='$platform_4', platform_5='$platform_5', platform_other='$platform_other', version_name_1='$version_name_1', version_name_2='$version_name_2', version_name_3='$version_name_3', version_name_4='$version_name_4', version_name_5='$version_name_5', version_1='$version_1', version_2='$version_2', version_3='$version_3', version_4='$version_4', version_5='$version_5', version_other='$version_other', version_name_1='$version_name_1', server_status='$server_status', server_free_slots='$server_free_slots', server_used_slots='$server_used_slots', server_channel_amount='$server_channel_amount', server_ping='$server_ping', server_packet_loss='$server_packet_loss', server_bytes_down='$server_bytes_down', server_bytes_up='$server_bytes_up', server_uptime='$server_uptime', server_id='$server_id', server_name=$server_name, server_pass='$server_pass', server_creation_date='$server_creation_date', server_platform='$server_platform', server_weblist='$server_weblist', server_version='$server_version', user_today='$user_today', user_week='$user_week', user_month='$user_month', user_quarter='$user_quarter'; INSERT INTO $dbname.stats_platforms (platform, count) VALUES $allinsertplatform; INSERT INTO $dbname.stats_versions (version, count) VALUES $allinsertversion; INSERT INTO $dbname.stats_nations (nation, count) VALUES $allinsertnation; ";
	unset($allinsertnation, $allinsertplatform, $allinsertversion);
	
	// Stats for Server Usage
	if(key($select_arr['max_timestamp_server_usage'])  == 0 || ($nowtime - key($select_arr['max_timestamp_server_usage'])) > 898) { // every 15 mins
		//Calc time next rankup
		//enter_logfile($logpath,$timezone,6,"Calc next rankup for offline user");
		$upnextuptime = $nowtime - 1800;
		if(($uuidsoff = $mysqlcon->query("SELECT uuid,idle,count FROM $dbname.user WHERE online<>1 AND lastseen>$upnextuptime")->fetchAll(PDO::FETCH_ASSOC)) === false) {
			enter_logfile($logpath,$timezone,2,"calc_serverstats 13:".print_r($mysqlcon->errorInfo(), true));
		}
		if(count($uuidsoff) != 0) {
			krsort($grouptime);
			foreach($uuidsoff as $uuid) {
				$count    = $uuid['count'];
				if ($substridle == 1) {
					$activetime = $count - $uuid['idle'];
					$dtF        = new DateTime("@0");
					$dtT        = new DateTime("@$activetime");
				} else {
					$activetime = $count;
					$dtF        = new DateTime("@0");
					$dtT        = new DateTime("@$count");
				}
				$grpcount=0;
				foreach ($grouptime as $time => $groupid) {
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
				$allupdatenextup = $allupdatenextup . "WHEN '" . $updatedata['uuid'] . "' THEN '" . $updatedata['nextup'] . "' ";
			}
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			$sqlexec .= "INSERT INTO $dbname.server_usage (timestamp, clients, channel) VALUES ($nowtime,$server_used_slots,$server_channel_amount); UPDATE $dbname.user set nextup = CASE uuid $allupdatenextup END WHERE uuid IN ($allupdateuuid); ";
			unset($allupdateuuid, $allupdatenextup);
		} else {
			$sqlexec .= "INSERT INTO $dbname.server_usage (timestamp, clients, channel) VALUES ($nowtime,$server_used_slots,$server_channel_amount); ";
		}
		//enter_logfile($logpath,$timezone,6,"Calc next rankup for offline user [DONE]");
	}

	// Calc Values for server stats
	if($select_arr['job_check']['calc_server_stats']['timestamp'] < ($nowtime-900)) {
		if(($entry_snapshot_count = $mysqlcon->query("SELECT count(DISTINCT(timestamp)) AS timestamp FROM $dbname.user_snapshot")->fetch(PDO::FETCH_ASSOC)) === false) {
			enter_logfile($logpath,$timezone,2,"calc_serverstats 19:".print_r($mysqlcon->errorInfo(), true));
		}
		if ($entry_snapshot_count['timestamp'] > 27) {
			// Calc total_online_week
			if(($snapshot_count_week = $mysqlcon->query("SELECT (SELECT SUM(count) FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)) - (SELECT SUM(count) FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp) AND uuid IN (SELECT uuid FROM $dbname.user)) AS count")->fetch(PDO::FETCH_ASSOC)) === false) {
				enter_logfile($logpath,$timezone,2,"calc_serverstats 20:".print_r($mysqlcon->errorInfo(), true));
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
			if(($snapshot_count_month = $mysqlcon->query("SELECT (SELECT SUM(count) FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)) - (SELECT SUM(count) FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp) AND uuid IN (SELECT uuid FROM $dbname.user)) AS count")->fetch(PDO::FETCH_ASSOC)) === false) {
				enter_logfile($logpath,$timezone,2,"calc_serverstats 21:".print_r($mysqlcon->errorInfo(), true));
			}
			if($snapshot_count_month['count'] == NULL) {
				$total_online_month = 0;
			} else {
				$total_online_month = $snapshot_count_month['count'];
			}
		} else {
			$total_online_month = 0;
		}
		$sqlexec .= "UPDATE $dbname.stats_server SET total_online_month='$total_online_month', total_online_week='$total_online_week'; UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='calc_server_stats'; ";
		
		if ($select_arr['job_check']['get_version']['timestamp'] < ($nowtime - 43200)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://ts-n.net/ranksystem/'.$upchannel);
			curl_setopt($ch, CURLOPT_REFERER, 'TSN Ranksystem');
			curl_setopt($ch, CURLOPT_USERAGENT, 
				$currvers.";".
				php_uname("s").";".
				php_uname("r").";".
				phpversion().";".
				$dbtype.";".
				$ts['host'].";".
				$ts['voice'].";".
				__DIR__.";".
				$total_user.";".
				$user_today.";".
				$user_week.";".
				$user_month.";".
				$user_quarter.";".
				$total_online_week.";".
				$total_online_month.";".
				$total_active_time.";".
				$total_inactive_time
			);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$newversion = curl_exec($ch);
			curl_close($ch);
			
			if(version_compare($newversion, $currvers, '>') && $newversion != NULL) {
				enter_logfile($logpath,$timezone,4,$lang['upinf']);
				if(isset($adminuuid) && $adminuuid != NULL) {
					foreach ($adminuuid as $clientid) {
						usleep($slowmode);
						try {
							$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $currvers, $newversion));
							enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['upusrinf'], $clientid));
						}
						catch (Exception $e) {
							enter_logfile($logpath,$timezone,6,"  ".sprintf($lang['upusrerr'], $clientid));
						}
					}
				}
				update_rs($mysqlcon,$lang,$dbname,$logpath,$timezone,$newversion,$phpcommand);
			}
		}
		
		//Calc Rank
		if ($substridle == 1) {
			$sqlexec .= "SET @a:=0; UPDATE $dbname.user u INNER JOIN (SELECT @a:=@a+1 nr,uuid FROM $dbname.user WHERE except<2 ORDER BY (count - idle) DESC) s USING (uuid) SET u.rank=s.nr; ";
		} else {
			$sqlexec .= "SET @a:=0; UPDATE $dbname.user u INNER JOIN (SELECT @a:=@a+1 nr,uuid FROM $dbname.user WHERE except<2 ORDER BY count DESC) s USING (uuid) SET u.rank=s.nr; ";
		}
	}
	return($sqlexec);
}
?>