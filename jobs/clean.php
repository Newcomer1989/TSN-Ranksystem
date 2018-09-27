<?PHP
function clean($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$cleanclients,$cleanperiod,$logpath,$select_arr) {
	$nowtime = time();
	$sqlexec = '';

	// clean old clients out of the database
	if($select_arr['job_check']['clean_clients']['timestamp'] < ($nowtime - $cleanperiod)) {
		if ($cleanclients == 1) {
			enter_logfile($logpath,$timezone,4,$lang['clean']);
			$start = $countdel = $countts = 0;
			$break=200;
			$count_tsuser['count'] = 0;
			$clientdblist=array();
			enter_logfile($logpath,$timezone,5,"  Get TS3 Clientlist...");
			while($getclientdblist=$ts3->clientListDb($start, $break)) {
				check_shutdown($timezone,$logpath);
				$dummy = $mysqlcon->query("SELECT * FROM `$dbname`.`job_check`");   //TeamSpeak became very slow on sending dblist with 3.3.0.. needs to hold the connection
				$clientdblist=array_merge($clientdblist, $getclientdblist);
				$start=$start+$break;
				$count_tsuser=array_shift($getclientdblist);
				enter_logfile($logpath,$timezone,6,"    Got TS3 Clientlist ".count($clientdblist)." of ".$count_tsuser['count']." Clients.");
				if($count_tsuser['count'] <= $start) {
					break;
				}
				usleep($slowmode);
			}
			enter_logfile($logpath,$timezone,5,"  Get TS3 Clientlist [DONE]");
			foreach($clientdblist as $uuidts) {
				$single_uuid = $uuidts['client_unique_identifier']->toString();
				$uidarrts[$single_uuid]= 1;
			}
			unset($clientdblist);
			
			foreach($select_arr['all_user'] as $uuid => $value) {
				if(isset($uidarrts[$uuid])) {
					$countts++;
				} else {
					$deleteuuids[] = $uuid;
					$countdel++;
				}
			}
			unset($uidarrts);
			enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['cleants'], $countts, $count_tsuser['count']));
			enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['cleanrs'], count($select_arr['all_user'])));

			if(isset($deleteuuids)) {
				$alldeldata = '';
				$fsfilelist = opendir(substr(__DIR__,0,-4).'avatars/');
				while (false !== ($fsfile = readdir($fsfilelist))) {
					if ($fsfile != '.' && $fsfile != '..') {
						$fsfilelistarray[$fsfile] = filemtime(substr(__DIR__,0,-4).'avatars/'.$fsfile);
					}
				}
				unset($fsfilelist);
				$avatarfilepath	= substr(__DIR__,0,-4).'avatars/';
				$convert = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p');
				foreach ($deleteuuids as $uuid) {
					$alldeldata = $alldeldata . "'" . $uuid . "',";
					$uuidasbase16 = '';
					for ($i = 0; $i < 20; $i++) {
						$char = ord(substr(base64_decode($uuid), $i, 1));
						$uuidasbase16 .= $convert[($char & 0xF0) >> 4];
						$uuidasbase16 .= $convert[$char & 0x0F];
					}
					if (isset($fsfilelistarray[$uuidasbase16.'.png'])) {
						if(unlink($avatarfilepath.$uuidasbase16.'.png') === false) {
							enter_logfile($logpath,$timezone,2,"  ".sprintf($lang['clean0002'], $uuidasbase16, $uuid).' '.sprintf($lang['errperm'], 'avatars'));
						} else {
							enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['clean0001'], $uuidasbase16, $uuid));
						}
					}
				}
				unset($$deleteuuids);
				$alldeldata = substr($alldeldata, 0, -1);
				$alldeldata = "(".$alldeldata.")";
				if ($alldeldata != '') {
					$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_clients'; UPDATE `$dbname`.`stats_user` AS `t` LEFT JOIN `$dbname`.`user` AS `u` ON `t`.`uuid`=`u`.`uuid` SET `t`.`removed`='1' WHERE `u`.`uuid` IS NULL; DELETE FROM `$dbname`.`user` WHERE `uuid` IN $alldeldata; ";
					enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['cleandel'], $countdel));
					unset($$alldeldata);
				}
			} else {
				enter_logfile($logpath,$timezone,4,"  ".$lang['cleanno']);
				$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_clients'; ";
			}
		} else {
			enter_logfile($logpath,$timezone,4,$lang['clean0004']);
			$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_clients'; ";
		}
	}
	
	// clean usersnaps older then 1 month + clean old server usage - older then a year
	if ($select_arr['job_check']['clean_db']['timestamp'] < ($nowtime - 86400)) {
		$sqlexec .= "DELETE `a` FROM `$dbname`.`user_snapshot` AS `a` CROSS JOIN(SELECT DISTINCT(`timestamp`) FROM `$dbname`.`user_snapshot` ORDER BY `timestamp` DESC LIMIT 1000 OFFSET 121) AS `b` WHERE `a`.`timestamp`=`b`.`timestamp`; DELETE FROM `$dbname`.`server_usage` WHERE `timestamp` < (UNIX_TIMESTAMP() - 31536000); DELETE `b` FROM `$dbname`.`user` AS `a` RIGHT JOIN `$dbname`.`stats_user` AS `b` ON `a`.`uuid`=`b`.`uuid` WHERE `a`.`uuid` IS NULL; UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_db'; DELETE FROM `$dbname`.`csrf_token` WHERE `timestamp` < (UNIX_TIMESTAMP() - 3600); ";
		enter_logfile($logpath,$timezone,4,$lang['clean0003']);
	}

	// delete IP address of offline user
	$sqlexec .= "DELETE `a` FROM `$dbname`.`user_iphash` AS `a` INNER JOIN `$dbname`.`user` AS `b` ON `a`.`uuid`=`b`.`uuid` WHERE `b`.`online`!=1; ";

	return($sqlexec);
}
?>