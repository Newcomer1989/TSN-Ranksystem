<?PHP
function update_groups($ts3,$mysqlcon,$lang,$cfg,$dbname,$serverinfo,$select_arr,$nobreak = 0) {
	$starttime = microtime(true);
	$sqlexec = '';
	try {
		usleep($cfg['teamspeak_query_command_delay']);
		$iconlist = $ts3->channelFileList($cid="0", $cpw="", $path="/icons/");
		
		if(isset($iconlist)) {
			foreach($iconlist as $icon) {
				$iconid = "i".substr($icon['name'], 5);
				$iconarr[$iconid] = $icon['datetime'];
			}
		} else {
			$iconarr["xxxxx"] = 0;
		}
		unset($iconlist);
	} catch (Exception $e) {
		if ($e->getCode() != 1281) {
			enter_logfile($cfg,2,$lang['errorts3'].$e->getCode().': '.$lang['errgrplist'].$e->getMessage());
		} else {
			$iconarr["xxxxx"] = 0;
			enter_logfile($cfg,6,$lang['errorts3'].$e->getCode().': '.$lang['errgrplist'].$e->getMessage());
		}
	}
		
	try {
		usleep($cfg['teamspeak_query_command_delay']);
		$ts3->serverGroupListReset();
		$ts3groups = $ts3->serverGroupList();
		
		// ServerIcon
		if ($serverinfo['virtualserver_icon_id'] < 0) {
			$sIconId = (pow(2, 32)) - ($serverinfo['virtualserver_icon_id'] * -1);
		} else {
			$sIconId = $serverinfo['virtualserver_icon_id'];
		}
		enter_logfile($cfg,6,"Servericon TSiconid:".$serverinfo['virtualserver_icon_id']."; powed TSiconid:".$sIconId."; DBiconid:".$select_arr['groups']['0']['iconid']."; TSicondate:".$iconarr["i".$sIconId]."; DBicondate:".$select_arr['groups']['0']['icondate'].";");
		$sIconFile = 0;
		$extension = 'png';
		if (!isset($select_arr['groups']['0']) || $select_arr['groups']['0']['iconid'] != $sIconId || (isset($iconarr["i".$sIconId]) && $iconarr["i".$sIconId] > $select_arr['groups']['0']['icondate'])) {
			if($sIconId > 600) {
				try {
					usleep($cfg['teamspeak_query_command_delay']);
					enter_logfile($cfg,5,$lang['upgrp0002']);
					$sIconFile = $ts3->iconDownload();
					$extension = mime2extension(TeamSpeak3_Helper_Convert::imageMimeType($sIconFile));
					if(file_put_contents(substr(dirname(__FILE__),0,-4) . "tsicons/servericon." . $extension, $sIconFile) === false) {
						enter_logfile($cfg,2,$lang['upgrp0003'].' '.sprintf($lang['errperm'], 'tsicons'));
					}
				} catch (Exception $e) {
					enter_logfile($cfg,2,$lang['errorts3'].$e->getCode().'; '.$lang['upgrp0004'].$e->getMessage());
				}
			} elseif($sIconId == 0) {
				foreach (glob(substr(dirname(__FILE__),0,-4) . "tsicons/servericon.*") as $file) {
					if(unlink($file) === false) {
						enter_logfile($cfg,2,$lang['upgrp0005'].' '.sprintf($lang['errperm'], 'tsicons'));
					} else {
						enter_logfile($cfg,5,$lang['upgrp0006']);
					}
				}
				$iconarr["i".$sIconId] = 0;
			}
			if(isset($iconarr["i".$sIconId]) && $iconarr["i".$sIconId] > 0) {
				$sicondate = $iconarr["i".$sIconId];
			} else {
				$sicondate = 0;
			}
			$updategroups[] = array(
				"sgid" => "0",
				"sgidname" => "'ServerIcon'",
				"iconid" => $sIconId,
				"icondate" => $sicondate,
				"sortid" => "0",
				"type" => "0",
				"ext" => $mysqlcon->quote($extension, ENT_QUOTES)
			);	
		}
		unset($sIconFile,$sIconId);
		
		// GroupIcons
		$iconcount = 0;
		foreach ($ts3groups as $servergroup) {
			$tsgroupids[$servergroup['sgid']] = 0;
			$sgid = $servergroup['sgid'];
			$extension = 'png';
			$sgname = $mysqlcon->quote((mb_substr($servergroup['name'],0,30)), ENT_QUOTES);
			$iconid = $servergroup['iconid'];
			$iconid = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;
			$iconfile = 0;
			if($iconid > 600) {
				if (!isset($select_arr['groups'][$sgid]) || $select_arr['groups'][$sgid]['iconid'] != $iconid || $iconarr["i".$iconid] > $select_arr['groups'][$sgid]['icondate']) {
					try {
						check_shutdown($cfg); usleep($cfg['teamspeak_query_command_delay']);
						enter_logfile($cfg,5,sprintf($lang['upgrp0011'], $sgname, $sgid));
						$iconfile = $servergroup->iconDownload();
						$extension = mime2extension(TeamSpeak3_Helper_Convert::imageMimeType($iconfile));
						if(file_put_contents(substr(dirname(__FILE__),0,-4) . "tsicons/" . $iconid . "." . $extension, $iconfile) === false) {
							enter_logfile($cfg,2,sprintf($lang['upgrp0007'], $sgname, $sgid).' '.sprintf($lang['errperm'], 'tsicons'));
						}
						$iconcount++;
					} catch (Exception $e) {
						enter_logfile($cfg,2,$lang['errorts3'].$e->getCode().': '.sprintf($lang['upgrp0008'], $sgname, $sgid).$e->getMessage());
					}
				}
			} elseif($iconid == 0) {
				foreach (glob(substr(dirname(__FILE__),0,-4) . "tsicons/" . $iconid . ".*") as $file) {
					if(unlink($file) === false) {
						enter_logfile($cfg,2,sprintf($lang['upgrp0009'], $sgname, $sgid).' '.sprintf($lang['errperm'], 'tsicons'));
					} else {
						enter_logfile($cfg,5,sprintf($lang['upgrp0010'], $sgname, $sgid));
					}
				}
				$iconarr["i".$iconid] = 0;
			}

			if(!isset($iconarr["i".$iconid])) {
				$iconarr["i".$iconid] = 0;
			}

			if(isset($select_arr['groups'][$servergroup['sgid']]) && $select_arr['groups'][$servergroup['sgid']]['sgidname'] == $servergroup['name'] && $select_arr['groups'][$servergroup['sgid']]['iconid'] == $iconid && $select_arr['groups'][$servergroup['sgid']]['icondate'] == $iconarr["i".$iconid] && $select_arr['groups'][$servergroup['sgid']]['sortid'] == $servergroup['sortid']) {
				enter_logfile($cfg,6,"Continue server group ".$sgname." (CID: ".$servergroup['sgid'].")");
				continue;
			} else {
				if($servergroup['sgid'] == 5000) {
					enter_logfile($cfg,6,print_r($select_arr['groups'],true));
				}
				enter_logfile($cfg,5,"Update/Insert server group ".$sgname." (CID: ".$servergroup['sgid'].")");
				$updategroups[] = array(
					"sgid" => $servergroup['sgid'],
					"sgidname" => $sgname,
					"iconid" => $iconid,
					"icondate" => $iconarr["i".$iconid],
					"sortid" => $servergroup['sortid'],
					"type" => $servergroup['type'],
					"ext" => $mysqlcon->quote($extension, ENT_QUOTES)
				);
			}
			if($iconcount > 9 && $nobreak != 1) {
				break;
			}
		}
		unset($ts3groups,$sgname,$sgid,$iconid,$iconfile,$iconcount,$iconarr);

		if (isset($updategroups)) {
			$sqlinsertvalues = '';
			foreach ($updategroups as $updatedata) {
				$sqlinsertvalues .= "({$updatedata['sgid']},{$updatedata['sgidname']},{$updatedata['iconid']},{$updatedata['icondate']},{$updatedata['sortid']},{$updatedata['type']},{$updatedata['ext']}),";
			}
			$sqlinsertvalues = substr($sqlinsertvalues, 0, -1);
			$sqlexec .= "INSERT INTO `$dbname`.`groups` (`sgid`,`sgidname`,`iconid`,`icondate`,`sortid`,`type`,`ext`) VALUES $sqlinsertvalues ON DUPLICATE KEY UPDATE `sgidname`=VALUES(`sgidname`),`iconid`=VALUES(`iconid`),`icondate`=VALUES(`icondate`),`sortid`=VALUES(`sortid`),`type`=VALUES(`type`),`ext`=VALUES(`ext`); ";
			unset($updategroups, $sqlinsertvalues);
		}
		
		if(isset($select_arr['groups'])) {
			foreach ($select_arr['groups'] as $sgid => $groups) {
				if(!isset($tsgroupids[$sgid]) && $sgid != 0 && $sgid != NULL) {
					$delsgroupids .= $sgid . ",";
					if(in_array($sgid, $cfg['rankup_definition'])) {
						enter_logfile($cfg,2,sprintf($lang['upgrp0001'], $sgid, $lang['wigrptime']));
						if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
							foreach ($cfg['webinterface_admin_client_unique_id_list'] as $clientid) {
								usleep($cfg['teamspeak_query_command_delay']);
								try {
									$ts3->clientGetByUid($clientid)->message(sprintf($lang['upgrp0001'], $sgid, $lang['wigrptime']));
								} catch (Exception $e) {
									enter_logfile($cfg,6,"  ".sprintf($lang['upusrerr'], $clientid));
								}
							}
						}
					}
					if(isset($cfg['rankup_boost_definition'][$sgid])) {
						enter_logfile($cfg,2,sprintf($lang['upgrp0001'], $sgid, $lang['wiboost']));
						if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
							foreach ($cfg['webinterface_admin_client_unique_id_list'] as $clientid) {
								usleep($cfg['teamspeak_query_command_delay']);
								try {
									$ts3->clientGetByUid($clientid)->message(sprintf($lang['upgrp0001'], $sgid, $lang['wigrptime']));
								} catch (Exception $e) {
									enter_logfile($cfg,6,"  ".sprintf($lang['upusrerr'], $clientid));
								}
							}
						}
					}
					if(isset($cfg['rankup_excepted_group_id_list'][$sgid])) {
						enter_logfile($cfg,2,sprintf($lang['upgrp0001'], $sgid, $lang['wiexgrp']));
						if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
							foreach ($cfg['webinterface_admin_client_unique_id_list'] as $clientid) {
								usleep($cfg['teamspeak_query_command_delay']);
								try {
									$ts3->clientGetByUid($clientid)->message(sprintf($lang['upgrp0001'], $sgid, $lang['wigrptime']));
								} catch (Exception $e) {
									enter_logfile($cfg,6,"  ".sprintf($lang['upusrerr'], $clientid));
								}
							}
						}
					}
				}
			}
		}
		
		if(isset($delsgroupids)) {
			$delsgroupids = substr($delsgroupids, 0, -1);
			$sqlexec .= "DELETE FROM `$dbname`.`groups` WHERE `sgid` IN ($delsgroupids); ";
		}
		enter_logfile($cfg,6,"update_groups needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
		return($sqlexec);

	} catch (Exception $e) {
		enter_logfile($cfg,2,$lang['errorts3'].$e->getCode().': '.$lang['errgrplist'].$e->getMessage());
	}
}
?>