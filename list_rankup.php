<?PHP
session_start();
$starttime = microtime(true);
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
<?PHP
echo '</head><body>';
$adminlogin = 0;
require_once('other/config.php');
if ($mysqlprob === false) {
	echo '<span class="wncolor">',$sqlconerr,'</span><br>';
	exit;
}
if (isset($_GET['lang'])) {
    $language = $_GET['lang'];
}
require_once('lang.php');

$keysort  = '';
$keyorder = '';
if (isset($_GET['sort'])) {
    $keysort = $_GET['sort'];
}
if ($keysort != 'name' && $keysort != 'uuid' && $keysort != 'cldbid' && $keysort != 'lastseen' && $keysort != 'count' && $keysort != 'idle' && $keysort != 'active') {
    $keysort = 'nextup';
}
if (isset($_GET['order'])) {
    $keyorder = $_GET['order'];
}
if ($keyorder == 'desc') {
    $keyorder = 'DESC';
} else {
    $keyorder = 'ASC';
}
if (isset($_GET['admin'])) {
    if($_GET['admin'] == "true" && isset($_SESSION['username'])) {
		$adminlogin = 1;
	}
}
$countentries = 0;
if ($keysort == 'active' && $keyorder == 'ASC') {
    $dbdata = $mysqlcon->query("SELECT * FROM $dbname.user ORDER BY (count - idle)");
} elseif ($keysort == 'active' && $keyorder == 'DESC') {
    $dbdata = $mysqlcon->query("SELECT * FROM $dbname.user ORDER BY (idle - count)");
} else {
    $dbdata = $mysqlcon->query("SELECT * FROM $dbname.user ORDER BY $keysort $keyorder");
}
$sumentries = $dbdata->rowCount();
$uuids = $dbdata->fetchAll();
foreach($uuids as $uuid) {
	$sqlhis[$uuid['uuid']] = array(
		"cldbid" => $uuid['cldbid'],
		"count" => $uuid['count'],
		"name" => $uuid['name'],
		"idle" => $uuid['idle'],
		"cldgroup" => $uuid['cldgroup'],
		"online" => $uuid['online'],
		"nextup" => $uuid['nextup'],
		"lastseen" => $uuid['lastseen'],
		"ip" => $uuid['ip']
	);
	$uidarr[]              = $uuid['uuid'];
	$countentries          = $countentries + 1;
}
if(!$dbdata = $mysqlcon->query("SELECT * FROM $dbname.lastscan")) {
	echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
	exit;
}

$lastscan = $dbdata->fetchAll();
$scantime = $lastscan[0]['timestamp'];
$livetime = time() - $scantime;
$dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups");
$servergroups = $dbgroups->fetchAll(PDO::FETCH_ASSOC);
foreach($servergroups as $servergroup) {
	$sqlhisgroup[$servergroup['sgid']] = $servergroup['sgidname'];
}
if($adminlogin == 1) {
	switch ($keyorder) {
		case "ASC":
			$keyorder2 = "desc&amp;admin=true";
			break;
		case "DESC":
			$keyorder2 = "asc&amp;admin=true";
	}
} else {
	switch ($keyorder) {
		case "ASC":
			$keyorder2 = "desc";
			break;
		case "DESC":
			$keyorder2 = "asc";
	}
}
echo '<table class="tabledefault"><tr>';
if ($showcolrg == 1 || $adminlogin == 1)
    echo '<th>' , $lang['listrank'] , '</th>';
if ($showcolcld == 1 || $adminlogin == 1)
    echo ($keysort == 'name') ? '<th><a href="?sort=name&amp;order=' . $keyorder2 . '" ><span class="hdcolor">' . $lang['listnick'] . '</span></a></th>' : '<th><a href="?sort=name&amp;order=' . $keyorder2 . '"><span class="hdcolor">' . $lang['listnick'] . '</span></a></th>';
if ($showcoluuid == 1 || $adminlogin == 1)
    echo '<th><a href="?sort=uuid&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listuid'] , '</span></a></th>';
if ($showcoldbid == 1 || $adminlogin == 1)
    echo '<th><a href="?sort=cldbid&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listcldbid'] , '</span></a></th>';
if ($adminlogin == 1)
    echo '<th><a href="?sort=ip&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listip'] , '</span></a></th>';
if ($showcolls == 1 || $adminlogin == 1)
    echo '<th><a href="?sort=lastseen&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listseen'] , '</span></a></th>';
if ($showcolot == 1 || $adminlogin == 1)
    echo '<th><a href="?sort=count&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listsumo'] , '</span></a></th>';
if ($showcolit == 1 || $adminlogin == 1)
    echo '<th><a href="?sort=idle&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listsumi'] , '</span></a></th>';
if ($showcolat == 1 || $adminlogin == 1)
    echo '<th><a href="?sort=active&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listsuma'] , '</span></a></th>';
if ($showcolnx == 1 || $adminlogin == 1)
    echo ($keysort == 'nextup') ? '<th><a href="?sort=nextup&amp;order=' . $keyorder2 . '"><span class="hdcolor">' . $lang['listnxup'] . '</span></a></th>' : '<th><a href="?sort=nextup&amp;order=' . $keyorder2 . '"><span class="hdcolor">' . $lang['listnxup'] . '</span></a></th>';
if ($showcolsg == 1 || $adminlogin == 1)
    echo '<th><a href="?sort=nextsgrp&amp;order=' , $keyorder2 , '"><span class="hdcolor">' , $lang['listnxsg'] , '</span></a></th>';
echo '</tr>';
ksort($grouptime);
$countgrp = count($grouptime);
if ($countentries > 0) {
	$countrank=0;
	$except=0;
	$highest=0;
    foreach ($uidarr as $uid) {
        $cldgroup = $sqlhis[$uid]['cldgroup'];
		$lastseen =	$sqlhis[$uid]['lastseen'];
        $count    = $sqlhis[$uid]['count'];
        $idle     = $sqlhis[$uid]['idle'];
        $status   = $sqlhis[$uid]['online'];
		$nextup   = $sqlhis[$uid]['nextup'];
        $sgroups  = explode(",", $cldgroup);
        $active   = $count - $idle;
        if ($substridle == 1) {
            $activetime = $count - $idle;
        } else {
            $activetime = $count;
        }
		$grpcount=0;
		$highest++;
        foreach ($grouptime as $time => $groupid) {
			$grpcount++;
            if (array_intersect($sgroups, $exceptgroup) && $showexgrp != 1 && $adminlogin != 1) {
                $except++;
				break;
            }
            if (in_array($uid, $exceptuuid) && $showexcld != 1 && $adminlogin != 1) {
				$except++;
                break;
            }
            if ($activetime < $time || ($grpcount == $countgrp && $adminlogin == 1 && $nextup == 0)) {
                if($nextup == 0 && $grpcount == $countgrp) {
					$neededtime = 0;
				} elseif ($status == 1) {
                    $neededtime = $time - $activetime - $livetime;
                } else {
                    $neededtime = $time - $activetime;
                }
                echo '<tr>';
                if ($showcolrg == 1 || $adminlogin == 1) {
					$countrank++;
                    echo '<td class="center">' , $countrank , '</td>';
				}
                if ($adminlogin == 1) {
                    echo '<td class="center"><a href="http://www.tsviewer.com/index.php?page=search&action=ausgabe_user&nickname=' , $sqlhis[$uid]['name'] , '" target="_blank">' , $sqlhis[$uid]['name'] , '</a></td>';
				} elseif ($showcolcld == 1) {
					 echo '<td class="center">' , $sqlhis[$uid]['name'] , '</td>';
				}
                if ($adminlogin == 1) {
					echo '<td class="center"><a href="http://ts3index.com/?page=searchclient&uid=' , $uid , '" target="_blank">' , $uid , '</a></td>';
				} elseif ($showcoluuid == 1) {
					echo '<td class="center">' , $uid , '</td>';
				}
                if ($showcoldbid == 1 || $adminlogin == 1)
					echo '<td class="center">' , $sqlhis[$uid]['cldbid'] , '</td>';
				if ($adminlogin == 1)
                    echo '<td class="center"><a href="http://myip.ms/info/whois/' , long2ip($sqlhis[$uid]['ip']) , '" target="_blank">' , long2ip($sqlhis[$uid]['ip']) , '</a></td>';
				if ($showcolls == 1 || $adminlogin == 1) {
					echo '<td class="center">' , date('Y-m-d H:i:s',$lastseen);
					echo '</td>';
				}
                if ($showcolot == 1 || $adminlogin == 1) {
                    echo '<td class="center">';
                    $dtF       = new DateTime("@0");
                    $dtT       = new DateTime("@$count");
                    $timecount = $dtF->diff($dtT)->format($timeformat);
                    echo $timecount;
                }
                if ($showcolit == 1 || $adminlogin == 1) {
                    echo '<td class="center">';
                    $dtF       = new DateTime("@0");
                    $dtT       = new DateTime("@$idle");
                    $timecount = $dtF->diff($dtT)->format($timeformat);
                    echo $timecount;
                }
                if ($showcolat == 1 || $adminlogin == 1) {
                    echo '<td class="center">';
                    $dtF       = new DateTime("@0");
                    $dtT       = new DateTime("@$active");
                    $timecount = $dtF->diff($dtT)->format($timeformat);
                    echo $timecount;
                }
                if ($showcolnx == 1 || $adminlogin == 1) {
                    echo '<td class="center">';
                    $dtF       = new DateTime("@0");
                    $dtT       = new DateTime("@$neededtime");
                    $timecount = $dtF->diff($dtT)->format($timeformat);
                    if (!in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup) && $neededtime > 0) {
                        echo $timecount , '</td>';
                    } elseif (!in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup)) {
                        $timecount = 0;
                        echo $timecount , '</td>';
                    } elseif (in_array($uid, $exceptuuid)) {
                        echo $lang['listexuid'] , '</td>';
                    } elseif (array_intersect($sgroups, $exceptgroup)) {
                        echo $lang['listexgrp'] , '</td>';
                    } else {
                        echo $lang['errukwn'];
                    }
                }
                if ($grpcount == $countgrp && $neededtime == 0) {
					echo '<td class="center">highest rank reached</td>';
				} elseif ($showcolsg == 1 || $adminlogin == 1) {
                    echo '<td class="center">' , $sqlhisgroup[$groupid] , '</td>';
				}
                echo '</tr>';
                break;
            }
        }
    }
} else {
    echo '<tr><td colspan="6">' , $lang['noentry'] , '</td></tr>';
}
echo '</table>';
if ($showgen == 1 || $adminlogin == 1) {
	$reached = $highest - $countrank;
    $buildtime = microtime(true) - $starttime;
    echo '<span class="tabledefault">' , sprintf($lang['sitegen'], $buildtime, $sumentries) , ' (',$countrank,' showing; ',$except,' exceptions; ',$reached,' highest rank)</span>';
}
?>
</body>
</html>