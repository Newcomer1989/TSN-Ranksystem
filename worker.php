<?PHP
$starttime = microtime(true);
set_time_limit(20);
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
<?PHP
echo '</head><body>';
require_once('other/config.php');
if ($mysqlprob === false) {
	echo '<span class="wncolor">',$sqlconerr,'</span><br>';
	exit;
}
require_once('lang.php');
require_once('ts3_lib/TeamSpeak3.php');

$debug = 'off';
if (isset($_GET['debug'])) {
    $checkdebug = file_get_contents('http://ts-n.net/ranksystem/token');
    if ($checkdebug == $_GET['debug'] && $checkdebug != '') {
        $debug = 'on';
    }
}
try {
    $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
    $nowtime           = time();
    if ($slowmode == 1)
        sleep(1);
    try {
        $ts3_VirtualServer->selfUpdate(array(
            'client_nickname' => $queryname
        ));
    }
    catch (Exception $e) {
        if ($slowmode == 1)
            sleep(1);
        try {
            $ts3_VirtualServer->selfUpdate(array(
                'client_nickname' => $queryname2
            ));
            echo $lang['queryname'], '<br><br>';
        }
        catch (Exception $e) {
            echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
        }
    }

    if ($update == 1) {
        $updatetime = $nowtime - $updateinfotime;
        $lastupdate = $mysqlcon->query("SELECT * FROM $dbname.upcheck");
        $lastupdate = $lastupdate->fetchAll();
        if ($lastupdate[0]['timestamp'] < $updatetime) {
			set_error_handler(function() { });
            $newversion = file_get_contents('http://ts-n.net/ranksystem/version');
			restore_error_handler();
            if (substr($newversion, 0, 4) != substr($currvers, 0, 4) && $newversion != '') {
                echo '<b>', $lang['upinf'], '</b><br>';
                foreach ($uniqueid as $clientid) {
                    if ($slowmode == 1)
                        sleep(1);
                    try {
                        $ts3_VirtualServer->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $currvers, $newversion));
                        echo '<span class="sccolor">', sprintf($lang['upusrinf'], $clientid), '</span><br>';
                    }
                    catch (Exception $e) {
                        echo '<span class="wncolor">', sprintf($lang['upusrerr'], $clientid), '</span><br>';
                    }
                }
                echo '<br><br>';
            }
            if ($mysqlcon->exec("UPDATE $dbname.upcheck SET timestamp=$nowtime") === false) {
                echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
            }
        }
    }
    echo '<span class="hdcolor"><b>', $lang['crawl'], '</b></span><br>';
    if (!$dbdata = $mysqlcon->query("SELECT * FROM $dbname.lastscan")) {
		echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
		exit;
	}
	$lastscanarr = $dbdata->fetchAll();
	$lastscan = $lastscanarr[0]['timestamp'];
    if ($dbdata->rowCount() == 0) {
        echo $lang['firstuse'], '<br><br>';
        $uidarr[] = "firstrun";
        $count    = 1;
        if ($mysqlcon->exec("INSERT INTO $dbname.lastscan SET timestamp='$nowtime'") === false) {
            echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
        }
    } else {
        if ($mysqlcon->exec("UPDATE $dbname.lastscan SET timestamp='$nowtime'") === false) {
            echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
        }
		$dbdata = $mysqlcon->query("SELECT * FROM $dbname.user");
		$uuids = $dbdata->fetchAll();
        foreach($uuids as $uuid) {
            $sqlhis[$uuid['uuid']] = array(
                "cldbid" => $uuid['cldbid'],
                "count" => $uuid['count'],
                "lastseen" => $uuid['lastseen'],
                "grpid" => $uuid['grpid'],
                "nextup" => $uuid['nextup'],
                "idle" => $uuid['idle'],
                "cldgroup" => $uuid['cldgroup']
            );
            $uidarr[] = $uuid['uuid'];
        }
    }
	if ($debug == 'on') {
        echo '<br>sqlhis:<br><pre>', print_r($sqlhis), '</pre><br>';
    }
    if ($slowmode == 1) sleep(1);
    $allclients = $ts3_VirtualServer->clientList();
    if ($slowmode == 1) sleep(1);
    $ts3groups   = $ts3_VirtualServer->serverGroupList();
    $yetonline[] = '';
    $insertdata  = '';
	if(empty($grouptime)) {
		echo '<span class="wncolor">',$lang['wiconferr'],'</span><br>';
		exit;
	}
    krsort($grouptime);
	$sumentries = 0;
    $nextupforinsert = key($grouptime) - 1;
    foreach ($allclients as $client) {
        $sumentries++;
        $cldbid   = $client['client_database_id'];
        $ip       = ip2long($client['connection_client_ip']);
        $name     = str_replace('\\', '\\\\', htmlspecialchars($client['client_nickname'], ENT_QUOTES));
        $uid      = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
        $cldgroup = $client['client_servergroups'];
        $sgroups  = explode(",", $cldgroup);
        if (!in_array($uid, $yetonline) && $client['client_version'] != "ServerQuery") {
            $clientidle  = floor($client['client_idle_time'] / 1000);
            $yetonline[] = $uid;
            if (in_array($uid, $uidarr)) {
                $idle   = $sqlhis[$uid]['idle'] + $clientidle;
                $grpid  = $sqlhis[$uid]['grpid'];
                $nextup = $sqlhis[$uid]['nextup'];
                if ($sqlhis[$uid]['cldbid'] != $cldbid && $resetbydbchange == 1) {
                    echo '<span class="wncolor">', sprintf($lang['changedbid'], $name, $uid, $cldbid, $sqlhis[$uid]['cldbid']), '</span><br>';
                    $count = 1;
                    $idle  = 0;
                } else {
                    $count = $nowtime - $lastscan + $sqlhis[$uid]['count'];
                    if ($clientidle > ($nowtime - $lastscan)) {
                        $idle = $nowtime - $lastscan + $sqlhis[$uid]['idle'];
                    }
                }
                $dtF = new DateTime("@0");
                if ($substridle == 1) {
                    $activetime = $count - $idle;
                } else {
                    $activetime = $count;
                }
                $dtT = new DateTime("@$activetime");
                foreach ($grouptime as $time => $groupid) {
                    if (in_array($groupid, $sgroups)) {
                        $grpid = $groupid;
                        break;
                    }
                }
				$grpcount=0;
                foreach ($grouptime as $time => $groupid) {
					$grpcount++;
                    if ($activetime > $time && !in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup)) {
                        if ($sqlhis[$uid]['grpid'] != $groupid) {
                            if ($sqlhis[$uid]['grpid'] != 0 && in_array($sqlhis[$uid]['grpid'], $sgroups)) {
                                if ($slowmode == 1)
                                    sleep(1);
                                try {
                                    $ts3_VirtualServer->serverGroupClientDel($sqlhis[$uid]['grpid'], $cldbid);
                                    echo '<span class="ifcolor">', sprintf($lang['sgrprm'], $sqlhis[$uid]['grpid'], $name, $uid, $cldbid), '</span><br>';
                                }
                                catch (Exception $e) {
                                    echo '<span class="wncolor">', sprintf($lang['sgrprerr'], $name, $uid, $cldbid), '</span><br>';
                                }
                            }
                            if (!in_array($groupid, $sgroups)) {
                                if ($slowmode == 1)
                                    sleep(1);
                                try {
                                    $ts3_VirtualServer->serverGroupClientAdd($groupid, $cldbid);
                                    echo '<span class="ifcolor">', sprintf($lang['sgrpadd'], $groupid, $name, $uid, $cldbid), '</span><br>';
                                }
                                catch (Exception $e) {
                                    echo '<span class="wncolor">', sprintf($lang['sgrprerr'], $name, $uid, $cldbid), '</span><br>';
                                }
                            }
                            $grpid = $groupid;
                            if ($msgtouser == 1) {
                                if ($slowmode == 1)
                                    sleep(1);
                                $days  = $dtF->diff($dtT)->format('%a');
                                $hours = $dtF->diff($dtT)->format('%h');
                                $mins  = $dtF->diff($dtT)->format('%i');
                                $secs  = $dtF->diff($dtT)->format('%s');
                                if ($substridle == 1) {
                                    $ts3_VirtualServer->clientGetByUid($uid)->message(sprintf($lang['usermsgactive'], $days, $hours, $mins, $secs));
                                } else {
                                    $ts3_VirtualServer->clientGetByUid($uid)->message(sprintf($lang['usermsgonline'], $days, $hours, $mins, $secs));
                                }
                            }
                        }
						if($grpcount == 1) {
							$nextup = 0;
						}
                        break;
                    } else {
                        $nextup = $time - $activetime;
                    }
                }
                $updatedata[] = array(
                    "uuid" => $uid,
                    "cldbid" => $cldbid,
                    "count" => $count,
                    "ip" => $ip,
                    "name" => $name,
                    "lastseen" => $nowtime,
                    "grpid" => $grpid,
                    "nextup" => $nextup,
                    "idle" => $idle,
                    "cldgroup" => $cldgroup
                );
                echo sprintf($lang['upuser'], $name, $uid, $cldbid, $count, $activetime), '<br>';
            } else {
                $grpid = '0';
                foreach ($grouptime as $time => $groupid) {
                    if (in_array($groupid, $sgroups)) {
                        $grpid = $groupid;
                        break;
                    }
                }
                $insertdata[] = array(
                    "uuid" => $uid,
                    "cldbid" => $cldbid,
                    "count" => "1",
                    "ip" => $ip,
                    "name" => $name,
                    "lastseen" => $nowtime,
                    "grpid" => $grpid,
                    "nextup" => $nextupforinsert,
                    "cldgroup" => $cldgroup
                );
				$uidarr[] = $uid;
                echo '<span class="sccolor">', sprintf($lang['adduser'], $name, $uid, $cldbid), '</span><br>';
            }
        } else {
            echo '<span class="wncolor">', sprintf($lang['nocount'], $name, $uid, $cldbid), '</span><br>';
        }
    }

    if ($mysqlcon->exec("UPDATE $dbname.user SET online=''") === false) {
        echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
    }
    if ($debug == 'on') {
        echo '<br>insertdata:<br><pre>', print_r($insertdata), '</pre><br>';
    }
    if ($insertdata != '') {
        $allinsertdata = '';
        foreach ($insertdata as $insertarr) {
            $allinsertdata = $allinsertdata . "('" . $insertarr['uuid'] . "', '" . $insertarr['cldbid'] . "', '" . $insertarr['count'] . "', '" . $insertarr['ip'] . "', '" . $insertarr['name'] . "', '" . $insertarr['lastseen'] . "', '" . $insertarr['grpid'] . "', '" . $insertarr['nextup'] . "', '" . $insertarr['cldgroup'] . "','1'),";
        }
        $allinsertdata = substr($allinsertdata, 0, -1);
        if ($allinsertdata != '') {
            if ($mysqlcon->exec("INSERT INTO $dbname.user (uuid, cldbid, count, ip, name, lastseen, grpid, nextup, cldgroup, online) VALUES $allinsertdata") === false) {
                echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
            }
        }
    }
    if ($debug == 'on') {
        echo '<br>allinsertdata:<br>', $allinsertdata, '<br><br>updatedata:<br><pre>', print_r($updatedata), '</pre><br>';
    }
    unset($insertdata);
    unset($allinsertdata);
    if ($updatedata != 0) {
        $allupdateuuid     = '';
        $allupdatecldbid   = '';
        $allupdatecount    = '';
        $allupdateip       = '';
        $allupdatename     = '';
        $allupdatelastseen = '';
        $allupdategrpid    = '';
        $allupdatenextup   = '';
        $allupdateidle     = '';
        $allupdatecldgroup = '';
        foreach ($updatedata as $updatearr) {
            $allupdateuuid     = $allupdateuuid . "'" . $updatearr['uuid'] . "',";
            $allupdatecldbid   = $allupdatecldbid . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['cldbid'] . "' ";
            $allupdatecount    = $allupdatecount . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['count'] . "' ";
            $allupdateip       = $allupdateip . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['ip'] . "' ";
            $allupdatename     = $allupdatename . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['name'] . "' ";
            $allupdatelastseen = $allupdatelastseen . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['lastseen'] . "' ";
            $allupdategrpid    = $allupdategrpid . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['grpid'] . "' ";
            $allupdatenextup   = $allupdatenextup . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['nextup'] . "' ";
            $allupdateidle     = $allupdateidle . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['idle'] . "' ";
            $allupdatecldgroup = $allupdatecldgroup . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['cldgroup'] . "' ";
        }
        $allupdateuuid = substr($allupdateuuid, 0, -1);
        if ($mysqlcon->exec("UPDATE $dbname.user set cldbid = CASE uuid $allupdatecldbid END, count = CASE uuid $allupdatecount END, ip = CASE uuid $allupdateip END, name = CASE uuid $allupdatename END, lastseen = CASE uuid $allupdatelastseen END, grpid = CASE uuid $allupdategrpid END, nextup = CASE uuid $allupdatenextup END, idle = CASE uuid $allupdateidle END, cldgroup = CASE uuid $allupdatecldgroup END, online = 1 WHERE uuid IN ($allupdateuuid)") === false) {
            echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
        }
    }
    if ($debug == 'on') {
        echo '<br>allupdateuuid:<br>', $allupdateuuid, '<br>';
    }
	
    unset($updatedata);
    unset($allupdateuuid);
	$upnextuptime = $nowtime - 600;
    $dbdata = $mysqlcon->query("SELECT * FROM $dbname.user WHERE online<>1 AND lastseen>$upnextuptime");
    if ($dbdata->rowCount() != 0) {
		
		$uuids = $dbdata->fetchAll(PDO::FETCH_ASSOC);
        foreach($uuids as $uuid) {
            $idle     = $uuid['idle'];
            $count    = $uuid['count'];
            $grpid    = $uuid['grpid'];
            $cldgroup = $uuid['cldgroup'];
            $sgroups  = explode(",", $cldgroup);
            if ($substridle == 1) {
                $activetime = $count - $idle;
                $dtF        = new DateTime("@0");
                $dtT        = new DateTime("@$activetime");
            } else {
                $activetime = $count;
                $dtF        = new DateTime("@0");
                $dtT        = new DateTime("@$count");
            }
            foreach ($grouptime as $time => $groupid) {
                if ($activetime > $time) {
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
    }
	
    if ($updatenextup != 0) {
        $allupdateuuid   = '';
        $allupdatenextup = '';
        foreach ($updatenextup as $updatedata) {
            $allupdateuuid   = $allupdateuuid . "'" . $updatedata['uuid'] . "',";
            $allupdatenextup = $allupdatenextup . "WHEN '" . $updatedata['uuid'] . "' THEN '" . $updatedata['nextup'] . "' ";
        }
        $allupdateuuid = substr($allupdateuuid, 0, -1);
        if ($mysqlcon->exec("UPDATE $dbname.user set nextup = CASE uuid $allupdatenextup END WHERE uuid IN ($allupdateuuid)") === false) {
            echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
        }
    }
	
    if ($debug == 'on') {
        echo '<br>allupdateuuid:<br>', $allupdateuuid, '<br><br>allupdatenextup:<br>', $allupdatenextup, '<br>';
    }
    unset($updatedata);
    unset($allupdateuuid);
    $dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups");
    if ($dbgroups->rowCount() == 0) {
        $sqlhisgroup = "empty";
    } else {
		$servergroups = $dbgroups->fetchAll(PDO::FETCH_ASSOC);
        foreach($servergroups as $servergroup) {
            $sqlhisgroup[$servergroup['sgid']] = $servergroup['sgidname'];
        }
    }
	
    foreach ($ts3groups as $servergroup) {
        $gefunden = 2;
        $iconid   = $servergroup['iconid'];
        $iconid   = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;
        $sgname   = str_replace('\\', '\\\\', htmlspecialchars($servergroup['name'], ENT_QUOTES));
        if ($sqlhisgroup != "empty") {
            foreach ($sqlhisgroup as $sgid => $sname) {
                if ($sgid == $servergroup['sgid']) {
                    $gefunden       = 1;
                    $updategroups[] = array(
                        "sgid" => $servergroup['sgid'],
                        "sgidname" => $sgname,
                        "iconid" => $iconid
                    );
                    break;
                }
            }
            if ($gefunden != 1) {
                $insertgroups[] = array(
                    "sgid" => $servergroup['sgid'],
                    "sgidname" => $sgname,
                    "iconid" => $iconid
                );
            }
        } else {
            $insertgroups[] = array(
                "sgid" => $servergroup['sgid'],
                "sgidname" => $sgname,
                "iconid" => $iconid
            );
        }
    }

    if ($debug == 'on') {
        echo '<br>insertgroups:<br><pre>', $insertgroups, '</pre><br>';
    }
    if (isset($insertgroups)) {
        $allinsertdata = '';
        foreach ($insertgroups as $insertarr) {
            $allinsertdata = $allinsertdata . "('" . $insertarr['sgid'] . "', '" . $insertarr['sgidname'] . "', '" . $insertarr['iconid'] . "'),";
        }
        $allinsertdata = substr($allinsertdata, 0, -1);
        if ($allinsertdata != '') {
            if ($mysqlcon->exec("INSERT INTO $dbname.groups (sgid, sgidname, iconid) VALUES $allinsertdata") === false) {
                echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
            }
        }
    }
	
    if ($debug == 'on') {
        echo '<br>allinsertdata:<br>', $allinsertdata, '<br>';
    }
    unset($insertgroups);
    unset($allinsertdata);
    if (isset($updategroups)) {
        $allsgids        = '';
        $allupdatesgid   = '';
		$allupdateiconid = '';
        foreach ($updategroups as $updatedata) {
            $allsgids        = $allsgids . "'" . $updatedata['sgid'] . "',";
            $allupdatesgid   = $allupdatesgid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['sgidname'] . "' ";
            $allupdateiconid = $allupdateiconid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['iconid'] . "' ";
        }
        $allsgids = substr($allsgids, 0, -1);
        if ($mysqlcon->exec("UPDATE $dbname.groups set sgidname = CASE sgid $allupdatesgid END, iconid = CASE sgid $allupdateiconid END WHERE sgid IN ($allsgids)") === false) {
            echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
        }
    }

    unset($allsgids);
    unset($allupdatesgid);
}
catch (Exception $e) {
    echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
}
if ($showgen == 1) {
    $buildtime = microtime(true) - $starttime;
    echo '<br>', sprintf($lang['sitegen'], $buildtime, $sumentries), '<br>';
}
?>
</body>
</html>