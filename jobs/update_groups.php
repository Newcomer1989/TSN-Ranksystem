<?PHP
function update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$serverinfo) {
	$starttime = microtime(true);
	$sqlmsg = '';
	$sqlerr = 0;
	
	try {
		usleep($slowmode);
		$iconlist = $ts3->channelFileList($cid="0", $cpw="", $path="/icons/");
	} catch (Exception $e) {
		if ($e->getCode() != 1281) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 1:",$e->getCode(),': ',"Error by getting servergrouplist: ",$e->getMessage(),"\n";
			$sqlmsg .= $e->getCode() . ': ' . "Error by getting servergrouplist: " . $e->getMessage();
			$sqlerr++;
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
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 3:",$e->getCode(),': ',"Error by getting servergrouplist: ",$e->getMessage(),"\n";
		$sqlmsg .= $e->getCode() . ': ' . "Error by getting servergrouplist: " . $e->getMessage();
		$sqlerr++;
	}
	
    if(($dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups")) === false) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 2:",print_r($mysqlcon->errorInfo()),"\n";
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
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
				usleep($slowmode);
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"Download new ServerIcon\n";
				$sIconFile = $ts3->iconDownload();
				file_put_contents(substr(dirname(__FILE__),0,-4) . "icons/servericon.png", $sIconFile);
			} catch (Exception $e) {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 1:",$e->getCode(),': ',"Error by downloading Icon: ",$e->getMessage(),"\n";
				$sqlmsg .= $e->getCode() . ': ' . "Error by downloading Icon: " . $e->getMessage();
				$sqlerr++;
			}
		}
		if (!isset($sqlhisgroup['0'])) {
			$insertgroups[] = array(
				"sgid" => "0",
				"sgidname" => "ServerIcon",
				"iconid" => $sIconId,
				"icon" => $sIconFile,
				"icondate" => $iconarr["i".$sIconId]
			);
		} else {
			$updategroups[] = array(
				"sgid" => "0",
				"sgidname" => "ServerIcon",
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
        $gefunden = 2;
        $iconid   = $servergroup['iconid'];
        $iconid   = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;
		$iconfile = 0;
		if($iconid > 600) {
			if (!isset($sqlhisgroup[$sgid]) || $sqlhisgroup[$sgid]['iconid'] != $iconid || $iconarr["i".$iconid] > $sqlhisgroup[$sgid]['icondate']) {
				try {
					echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"Download new ServerGroupIcon for group ",$servergroup['name']," with ID: ",$sgid,"\n";
					$iconfile = $servergroup->iconDownload();
					file_put_contents(substr(dirname(__FILE__),0,-4) . "icons/" . $sgid . ".png", $iconfile);
				} catch (Exception $e) {
					echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 4:",$e->getCode(),': ',"Error by downloading Icon: ",$e->getMessage(),"\n";
					$sqlmsg .= $e->getCode() . ': ' . "Error by downloading Icon: " . $e->getMessage();
					$sqlerr++;
				}
			}
		}
		$sgname   = str_replace('\\', '\\\\', htmlspecialchars($servergroup['name'], ENT_QUOTES));
		if ($sqlhisgroup != "empty") {
			foreach ($sqlhisgroup as $groups) {
				if ($groups['sgid'] == $sgid) {
					$gefunden       = 1;
					$updategroups[] = array(
						"sgid" => $sgid,
						"sgidname" => $sgname,
						"iconid" => $iconid,
						"icon" => $iconfile,
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
		if($insertarr['sgidname'] != "ServerIcon" && $insertarr['icondate'] != 0) {
				$allinsertdata = $allinsertdata . "('" . $insertarr['sgid'] . "', '" . $insertarr['sgidname'] . "', '" . $insertarr['iconid'] . "', '" . $insertarr['icondate'] . "'),";
			}
        }
        $allinsertdata = substr($allinsertdata, 0, -1);
        if ($allinsertdata != '') {
            if($mysqlcon->exec("INSERT INTO $dbname.groups (sgid, sgidname, iconid, icondate) VALUES $allinsertdata") === false) {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 5:",$allinsertdata,print_r($mysqlcon->errorInfo()),"\n";
				$sqlmsg .= print_r($mysqlcon->errorInfo());
				$sqlerr++;
			}
        }
    }

    if (isset($updategroups)) {
        $allsgids        = '';
        $allupdatesgid   = '';
		$allupdateiconid = '';
        foreach ($updategroups as $updatedata) {
            $allsgids        = $allsgids . "'" . $updatedata['sgid'] . "',";
            $allupdatesgid   = $allupdatesgid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['sgidname'] . "' ";
            $allupdateiconid = $allupdateiconid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['iconid'] . "' ";
            $allupdatedate   = $allupdatedate . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['icondate'] . "' ";
        }
        $allsgids = substr($allsgids, 0, -1);
        if($mysqlcon->exec("UPDATE $dbname.groups set sgidname = CASE sgid $allupdatesgid END, iconid = CASE sgid $allupdateiconid END, icondate = CASE sgid $allupdatedate END WHERE sgid IN ($allsgids)") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 6:",print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
    }
	
	foreach ($sqlhisgroup as $groups) {
		if(!in_array($groups['sgid'], $tsgroupids) && $groups['sgid'] != 0) {
			$delsgroupids = $delsgroupids . "'" . $groups['sgid'] . "',";
		}
	}
	
	if(isset($delsgroupids)) {
		$delsgroupids = substr($delsgroupids, 0, -1);
		if($mysqlcon->exec("DELETE FROM groups WHERE sgid IN ($delsgroupids)") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 7:",print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
	}
	
	$buildtime = microtime(true) - $starttime;

	if ($sqlerr == 0) {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 8:",print_r($mysqlcon->errorInfo()),"\n";
		}
	} else {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"update_groups 9:",print_r($mysqlcon->errorInfo()),"\n";
		}
	}
}
?>