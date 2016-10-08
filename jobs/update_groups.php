<?PHP
function update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$serverinfo,$logpath) {

	try {
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$iconlist = $ts3->channelFileList($cid="0", $cpw="", $path="/icons/");
	} catch (Exception $e) {
		if ($e->getCode() != 1281) {
			enter_logfile($logpath,$timezone,2,"update_groups 1:".$e->getCode().': '."Error while getting servergrouplist: ".$e->getMessage());
		}
	}

	foreach($iconlist as $icon) {
		$iconid = "i".substr($icon['name'], 5);
		$iconarr[$iconid] = $icon['datetime'];
	}
	
	try {
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$ts3->serverGroupListReset();
		$ts3groups = $ts3->serverGroupList();
	} catch (Exception $e) {
		enter_logfile($logpath,$timezone,2,"update_groups 2:".$e->getCode().': '."Error while getting servergrouplist: ".$e->getMessage());
	}
	
    if(($dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups")) === false) {
		enter_logfile($logpath,$timezone,2,"update_groups 3:".print_r($mysqlcon->errorInfo()));
	}
    if ($dbgroups->rowCount() == 0) {
        $sqlhisgroup = "empty";
    } else {
		$servergroups = $dbgroups->fetchAll(PDO::FETCH_ASSOC);
        foreach($servergroups as $servergroup) {
            $sqlhisgroup[$servergroup['sgid']] = array (
				"sgid"     => $servergroup['sgid'],
				"iconid"   => $servergroup['iconid'],
				"sgidname" => $servergroup['sgidname'],
				"icondate" => $servergroup['icondate']
				);
        }
    }
	
	// ServerIcon
	$sIconId = $serverinfo['virtualserver_icon_id'];
	$sIconId = ($sIconId < 0) ? (pow(2, 32)) - ($sIconId * -1) : $sIconId;
	$sIconFile = 0;
	if (!isset($sqlhisgroup['0']) || $sqlhisgroup['0']['iconid'] != $sIconId || $iconarr["i".$sIconId] > $sqlhisgroup['0']['icondate']) {
		if($sIconId > 600) {
			try {
				check_shutdown($timezone,$logpath); usleep($slowmode);
				enter_logfile($logpath,$timezone,5,"Download new ServerIcon");
				$sIconFile = $ts3->iconDownload();
				if(file_put_contents(substr(dirname(__FILE__),0,-4) . "icons/servericon.png", $sIconFile) === false) {
					enter_logfile($logpath,$timezone,2,"Error while writing out the servericon. Please check the permission for the folder 'icons'");
				}
			} catch (Exception $e) {
				enter_logfile($logpath,$timezone,2,"update_groups 4:".$e->getCode().': '."Error while downloading servericon: ".$e->getMessage());
			}
		}
		if (!isset($sqlhisgroup['0'])) {
			$insertgroups[] = array(
				"sgid" => "0",
				"sgidname" => $mysqlcon->quote("ServerIcon", ENT_QUOTES),
				"iconid" => $sIconId,
				"icon" => $sIconFile,
				"icondate" => $iconarr["i".$sIconId]
			);
		} else {
			$updategroups[] = array(
				"sgid" => "0",
				"sgidname" => $mysqlcon->quote("ServerIcon", ENT_QUOTES),
				"iconid" => $sIconId,
				"icon" => $sIconFile,
				"icondate" => $iconarr["i".$sIconId]
			);
		}
	}
	
	// GroupIcons
    foreach ($ts3groups as $servergroup) {
		$tsgroupids[] = $servergroup['sgid'];
		$sgid = $servergroup['sgid'];
		$sgname   = $mysqlcon->quote($servergroup['name'], ENT_QUOTES);
        $gefunden = 2;
        $iconid   = $servergroup['iconid'];
        $iconid   = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;
		$iconfile = 0;
		if($iconid > 600) {
			if (!isset($sqlhisgroup[$sgid]) || $sqlhisgroup[$sgid]['iconid'] != $iconid || $iconarr["i".$iconid] > $sqlhisgroup[$sgid]['icondate']) {
				try {
					check_shutdown($timezone,$logpath); usleep($slowmode);
					enter_logfile($logpath,$timezone,5,"Download new ServerGroupIcon for group ".$sgname." with ID: ".$sgid);
					try {
						$iconfile = $servergroup->iconDownload();
					} catch (Exception $e) {
						enter_logfile($logpath,$timezone,2,"update_groups 5:".$e->getCode().': '."Error while downloading servericon: ".$e->getMessage());
					}
					if(file_put_contents(substr(dirname(__FILE__),0,-4) . "icons/" . $sgid . ".png", $iconfile) === false) {
						enter_logfile($logpath,$timezone,2,"Error while writing out the servergroup icon. Please check the permission for the folder 'icons'");
					}
				} catch (Exception $e) {
					enter_logfile($logpath,$timezone,2,"update_groups 6:".$e->getCode().': '."Error while downloading servergroup icon: ".$e->getMessage());
				}
			}
		}
		if(!isset($iconarr["i".$iconid])) {
			$iconarr["i".$iconid] = 0;
		}
		if ($sqlhisgroup != "empty") {
			foreach ($sqlhisgroup as $groups) {
				if ($groups['sgid'] == $sgid) {
					$gefunden       = 1;
					$updategroups[] = array(
						"sgid" => $sgid,
						"sgidname" => $sgname,
						"iconid" => $iconid,
						"icon" => $iconfile,
						"icondate" => $groups['icondate']
					);
					break;
				}
			}
			if ($gefunden != 1) {
				$insertgroups[] = array(
					"sgid" => $servergroup['sgid'],
					"sgidname" => $sgname,
					"iconid" => $iconid,
					"icon" => $iconfile,
					"icondate" => $iconarr["i".$iconid]
				);
			}
		} else {
			$insertgroups[] = array(
				"sgid" => $servergroup['sgid'],
				"sgidname" => $sgname,
				"iconid" => $iconid,
				"icon" => $iconfile,
				"icondate" => $iconarr["i".$iconid]
			);
		}
    }

    if (isset($insertgroups)) {
        $allinsertdata = '';
        foreach ($insertgroups as $insertarr) {
			if( $insertarr['iconid'] == 0 || $insertarr['icondate'] == null || $insertarr['icondate'] == 0) {
				//enter_logfile($logpath,$timezone,6,"IconID is 0 for (servergroup) ".$insertarr['sgidname']." (".$insertarr['sgid'].")");
				continue;
			}
			$allinsertdata = $allinsertdata . "('" . $insertarr['sgid'] . "', " . $insertarr['sgidname'] . ", '" . $insertarr['iconid'] . "', '" . $insertarr['icondate'] . "'),";
        }
        $allinsertdata = substr($allinsertdata, 0, -1);
        if ($allinsertdata != '') {
            if($mysqlcon->exec("INSERT INTO $dbname.groups (sgid, sgidname, iconid, icondate) VALUES $allinsertdata") === false) {
				enter_logfile($logpath,$timezone,2,"update_groups 7:".$allinsertdata.print_r($mysqlcon->errorInfo()));
			}
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
        if($mysqlcon->exec("UPDATE $dbname.groups set sgidname = CASE sgid $allupdatesgid END, iconid = CASE sgid $allupdateiconid END, icondate = CASE sgid $allupdatedate END WHERE sgid IN ($allsgids)") === false) {
			enter_logfile($logpath,$timezone,2,"update_groups 8:".print_r($mysqlcon->errorInfo()));
		}
    }
	
	if(isset($sqlhisgroup)) {
		foreach ($sqlhisgroup as $groups) {
			if(!in_array($groups['sgid'], $tsgroupids) && $groups['sgid'] != 0) {
				$delsgroupids = $delsgroupids . "'" . $groups['sgid'] . "',";
			}
		}
	}
	
	if(isset($delsgroupids)) {
		$delsgroupids = substr($delsgroupids, 0, -1);
		if($mysqlcon->exec("DELETE FROM $dbname.groups WHERE sgid IN ($delsgroupids)") === false) {
			enter_logfile($logpath,$timezone,2,"update_groups 9:".print_r($mysqlcon->errorInfo()));
		}
	}
}
?>