<?PHP
function update_channel($ts3,$mysqlcon,$lang,$cfg,$dbname,$serverinfo,&$db_cache,$nobreak = 0) {
	$starttime = microtime(true);
	$nowtime = time();
	$sqlexec = '';
	
	if($db_cache['job_check']['update_channel']['timestamp'] < ($nowtime - 7)) {
		$db_cache['job_check']['update_channel']['timestamp'] = $nowtime;
		$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`={$nowtime} WHERE `job_name`='update_channel';\n";
		
		try {
			usleep($cfg['teamspeak_query_command_delay']);
			$ts3->channelListResettsn();
			$channellist = $ts3->channelListtsn();
			
			foreach($channellist as $channel) {
				$chname = $mysqlcon->quote((mb_substr(mb_convert_encoding($channel['channel_name'],'UTF-8','auto'),0,40)), ENT_QUOTES);
				
				if(isset($db_cache['channel'][$channel['cid']]) &&
				$db_cache['channel'][$channel['cid']]['pid'] == $channel['pid'] &&
				$db_cache['channel'][$channel['cid']]['channel_order'] == $channel['channel_order'] &&
				$db_cache['channel'][$channel['cid']]['channel_name'] == $chname) {
					enter_logfile($cfg,7,"Continue channel ".$chname." (CID: ".$channel['cid'].")");
					continue;
				} else {
					enter_logfile($cfg,6,"Update/Insert channel ".$chname." (CID: ".$channel['cid'].")");
					$updatechannel[] = array(
						"cid" => $channel['cid'],
						"pid" => $channel['pid'],
						"channel_order" => $channel['channel_order'],
						"channel_name" => $chname
					);
				}
			}

			if (isset($updatechannel)) {
				$sqlinsertvalues = '';
				foreach ($updatechannel as $updatedata) {
					$sqlinsertvalues .= "({$updatedata['cid']},{$updatedata['pid']},{$updatedata['channel_order']},{$updatedata['channel_name']}),";
					$db_cache['channel'][$updatedata['cid']]['pid'] = $updatedata['pid'];
					$db_cache['channel'][$updatedata['cid']]['channel_order'] = $updatedata['channel_order'];
					$db_cache['channel'][$updatedata['cid']]['channel_name'] = $updatedata['channel_name'];
				}
				$sqlinsertvalues = substr($sqlinsertvalues, 0, -1);
				$sqlexec .= "INSERT INTO `$dbname`.`channel` (`cid`,`pid`,`channel_order`,`channel_name`) VALUES $sqlinsertvalues ON DUPLICATE KEY UPDATE `pid`=VALUES(`pid`),`channel_order`=VALUES(`channel_order`),`channel_name`=VALUES(`channel_name`);\n";
				unset($updatechannel, $sqlinsertvalues);
			}
		} catch (Exception $e) {
			enter_logfile($cfg,2,$lang['errorts3'].$e->getCode().': '.$lang['errgrplist'].$e->getMessage());
		}
		
		if(isset($db_cache['channel'])) {
			$delchannel = '';
			foreach ($db_cache['channel'] as $cid => $channel) {
				if(!isset($channellist[$cid]) && $cid != 0 && $cid != NULL) {
					$delchannel .= $cid . ",";
					unset($db_cache['channel'][$cid]);
				}
			}
		}

		if(isset($delchannel) && $delchannel != NULL) {
			$delchannel = substr($delchannel, 0, -1);
			$sqlexec .= "DELETE FROM `$dbname`.`channel` WHERE `cid` IN ($delchannel);\n";
			enter_logfile($cfg,6,"DELETE FROM `$dbname`.`channel` WHERE `cid` IN ($delchannel);");
		}

		enter_logfile($cfg,6,"update_channel needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
		return($sqlexec);
	}
}
?>