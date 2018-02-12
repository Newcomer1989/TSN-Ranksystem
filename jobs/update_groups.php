<?PHP
function update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$serverinfo,$logpath,$grouptime,$boostarr,$exceptgroup,$select_arr) {
	$sqlexec = '';
	try {
		usleep($slowmode);
		$iconlist = $ts3->channelFileList($cid="0", $cpw="", $path="/icons/");
	} catch (Exception $e) {
		if ($e->getCode() != 1281) {
			enter_logfile($logpath,$timezone,2,$lang['errorts3'].$e->getCode().': '.$lang['errgrplist'].$e->getMessage());
		}
	}

	foreach($iconlist as $icon) {
		$iconid = "i".substr($icon['name'], 5);
		$iconarr[$iconid] = $icon['datetime'];
	}
	
	try {
		usleep($slowmode);
		$ts3->serverGroupListReset();
		$ts3groups = $ts3->serverGroupList();
	} catch (Exception $e) {
		enter_logfile($logpath,$timezone,2,$lang['errorts3'].$e->getCode().': '.$lang['errgrplist'].$e->getMessage());
	}
	
	// ServerIcon
	if ($serverinfo['virtualserver_icon_id'] < 0) {
		$sIconId = (pow(2, 32)) - ($serverinfo['virtualserver_icon_id'] * -1);
	} else {
		$sIconId = $serverinfo['virtualserver_icon_id'];
	}
	$sIconFile = 0;
	if (!isset($select_arr['groups']['0']) || $select_arr['groups']['0']['iconid'] != $sIconId || $iconarr["i".$sIconId] > $select_arr['groups']['0']['icondate']) {
		if($sIconId > 600) {
			try {
				usleep($slowmode);
				enter_logfile($logpath,$timezone,5,$lang['upgrp0002']);
				$sIconFile = $ts3->iconDownload();
				if(file_put_contents(substr(dirname(__FILE__),0,-4) . "tsicons/servericon.png", $sIconFile) === false) {
					enter_logfile($logpath,$timezone,2,$lang['upgrp0003'].' '.sprintf($lang['errperm'], 'tsicons'));
				}
			} catch (Exception $e) {
				enter_logfile($logpath,$timezone,2,$lang['errorts3'].$e->getCode().'; '.$lang['upgrp0004'].$e->getMessage());
			}
		} elseif($sIconId == 0) {
			if(file_exists(substr(dirname(__FILE__),0,-4) . "tsicons/servericon.png")) {
				if(unlink(substr(dirname(__FILE__),0,-4) . "tsicons/servericon.png") === false) {
					enter_logfile($logpath,$timezone,2,$lang['upgrp0005'].' '.sprintf($lang['errperm'], 'tsicons'));
				} else {
					enter_logfile($logpath,$timezone,5,$lang['upgrp0006']);
				}
			}
			$iconarr["i".$sIconId] = 0;
		}
		if (!isset($select_arr['groups']['0'])) {
			$insertgroups[] = array(
				"sgid" => "0",
				"sgidname" => "'ServerIcon'",
				"iconid" => $sIconId,
				"icondate" => $iconarr["i".$sIconId]
			);
		} else {
			$updategroups[] = array(
				"sgid" => "0",
				"sgidname" => "'ServerIcon'",
				"iconid" => $sIconId,
				"icondate" => $iconarr["i".$sIconId]
			);	
		}
	}
	
	// GroupIcons
	$iconcount= 0;
    foreach ($ts3groups as $servergroup) {
		$tsgroupids[$servergroup['sgid']] = 0;
		$sgid = $servergroup['sgid'];
		$sgname   = $mysqlcon->quote($servergroup['name'], ENT_QUOTES);
        $gefunden = 2;
        $iconid   = $servergroup['iconid'];
        $iconid   = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;
		$iconfile = 0;
		if($iconid > 600) {
			if (!isset($select_arr['groups'][$sgid]) || $select_arr['groups'][$sgid]['iconid'] != $iconid || $iconarr["i".$iconid] > $select_arr['groups'][$sgid]['icondate']) {
				try {
					check_shutdown($timezone,$logpath); usleep($slowmode);
					enter_logfile($logpath,$timezone,5,sprintf($lang['upgrp0011'], $sgname, $sgid));
					$iconfile = $servergroup->iconDownload();
					if(file_put_contents(substr(dirname(__FILE__),0,-4) . "tsicons/" . $sgid . ".png", $iconfile) === false) {
						enter_logfile($logpath,$timezone,2,sprintf($lang['upgrp0007'], $sgname, $sgid).' '.sprintf($lang['errperm'], 'tsicons'));
					}
					$iconcount++;
				} catch (Exception $e) {
					enter_logfile($logpath,$timezone,2,$lang['errorts3'].$e->getCode().': '.sprintf($lang['upgrp0008'], $sgname, $sgid).$e->getMessage());
				}
			}
		} elseif($iconid == 0) {
			if(file_exists(substr(dirname(__FILE__),0,-4) . "tsicons/" . $sgid . ".png")) {
				if(unlink(substr(dirname(__FILE__),0,-4) . "tsicons/" . $sgid . ".png") === false) {
					enter_logfile($logpath,$timezone,2,sprintf($lang['upgrp0009'], $sgname, $sgid).' '.sprintf($lang['errperm'], 'tsicons'));
				} else {
					enter_logfile($logpath,$timezone,5,sprintf($lang['upgrp0010'], $sgname, $sgid));
				}
			}
			$iconarr["i".$iconid] = 0;
		}

		if(!isset($iconarr["i".$iconid])) {
			$iconarr["i".$iconid] = 0;
		}
		if(count($select_arr['groups']) != 0) {
			foreach ($select_arr['groups'] as $sqlgid => $groups) {
				if ($sqlgid == $sgid) {
					$gefunden       = 1;
					$updategroups[] = array(
						"sgid" => $sgid,
						"sgidname" => $sgname,
						"iconid" => $iconid,
						"icondate" => $iconarr["i".$iconid]
					);
					break;
				}
			}
			if ($gefunden != 1) {
				$insertgroups[] = array(
					"sgid" => $servergroup['sgid'],
					"sgidname" => $sgname,
					"iconid" => $iconid,
					"icondate" => $iconarr["i".$iconid]
				);
			}
		} else {
			$insertgroups[] = array(
				"sgid" => $servergroup['sgid'],
				"sgidname" => $sgname,
				"iconid" => $iconid,
				"icondate" => $iconarr["i".$iconid]
			);
		}
		if($iconcount > 9) {
			break;
		}
    }

    if (isset($insertgroups)) {
        $allinsertdata = '';
        foreach ($insertgroups as $insertarr) {
			$allinsertdata = $allinsertdata . "('" . $insertarr['sgid'] . "', " . $insertarr['sgidname'] . ", '" . $insertarr['iconid'] . "', '" . $insertarr['icondate'] . "'),";
        }
        $allinsertdata = substr($allinsertdata, 0, -1);
        if ($allinsertdata != '') {
			$sqlexec .= "INSERT INTO $dbname.groups (sgid, sgidname, iconid, icondate) VALUES $allinsertdata; ";
        }
    }

    if (isset($updategroups)) {
        $allsgids        = '';
        $allupdatesgid   = '';
		$allupdateiconid = '';
		$allupdatedate   = '';
        foreach ($updategroups as $updatedata) {
            $allsgids        = $allsgids . "'" . $updatedata['sgid'] . "',";
            $allupdatesgid   = $allupdatesgid . "WHEN '" . $updatedata['sgid'] . "' THEN " . $updatedata['sgidname'] . " ";
            $allupdateiconid = $allupdateiconid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['iconid'] . "' ";
            $allupdatedate   = $allupdatedate . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['icondate'] . "' ";
        }
        $allsgids = substr($allsgids, 0, -1);
		$sqlexec .= "UPDATE $dbname.groups set sgidname = CASE sgid $allupdatesgid END, iconid = CASE sgid $allupdateiconid END, icondate = CASE sgid $allupdatedate END WHERE sgid IN ($allsgids); ";
    }
	
	if(isset($select_arr['groups'])) {
		foreach ($select_arr['groups'] as $sgid => $groups) {
			if(!isset($tsgroupids[$sgid]) && $sgid != 0 && $sgid != NULL) {
				$delsgroupids .= "'" . $sgid . "',";
				if(in_array($sgid, $grouptime)) {
					enter_logfile($logpath,$timezone,2,sprintf($lang['upgrp0001'], $sgid, $lang['wigrptime']));
				}
				if(isset($boostarr[$sgid])) {
					enter_logfile($logpath,$timezone,2,sprintf($lang['upgrp0001'], $sgid, $lang['wiboost']));
				}
				if(isset($exceptgroup[$sgid])) {
					enter_logfile($logpath,$timezone,2,sprintf($lang['upgrp0001'], $sgid, $lang['wiexgrp']));
				}
			}
		}
	}
	
	if(isset($delsgroupids)) {
		$delsgroupids = substr($delsgroupids, 0, -1);
		$sqlexec .= "DELETE FROM $dbname.groups WHERE sgid IN ($delsgroupids); ";
	}
	return($sqlexec);
}
?>