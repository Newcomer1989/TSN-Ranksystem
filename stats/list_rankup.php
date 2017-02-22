<?PHP
session_start();
$starttime = microtime(true);

require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

function getclientip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP']))
		return $_SERVER['HTTP_CLIENT_IP'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED']))
		return $_SERVER['HTTP_X_FORWARDED'];
	elseif(!empty($_SERVER['HTTP_FORWARDED_FOR']))
		return $_SERVER['HTTP_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_FORWARDED']))
		return $_SERVER['HTTP_FORWARDED'];
	elseif(!empty($_SERVER['REMOTE_ADDR']))
		return $_SERVER['REMOTE_ADDR'];
	else
		return false;
}

if(!isset($_SESSION['tsuid'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}

if(isset($_POST['username'])) {
	$_GET["search"] = strip_tags(htmlspecialchars($_POST['usersuche']));
	$_GET["seite"] = 1;
}
$filter='';
$searchstring='';
if(isset($_GET["search"]) && $_GET["search"] != '') {
	$getstring = $_GET['search'];
}
if(isset($getstring) && strstr($getstring, 'filter:excepted:')) {
	if(str_replace('filter:excepted:','',$getstring)!='') {
		$searchstring = str_replace('filter:excepted:','',$getstring);
	}
	$filter .= " AND except IN ('2','3')";
} elseif(isset($getstring) && strstr($getstring, 'filter:nonexcepted:')) {
	if(str_replace('filter:nonexcepted:','',$getstring)!='') {
		$searchstring = str_replace('filter:nonexcepted:','',$getstring);
	}
	$filter .= " AND except IN ('0','1')";
} else {
	if(isset($getstring)) {
		$searchstring = $getstring;
	} else {
		$searchstring = '';
	}
	if($showexcld == 0) {
		$filter .= " AND except IN ('0','1')";
	}
}
if(isset($getstring) && strstr($getstring, 'filter:online:')) {
	$searchstring = preg_replace('/filter\:online\:/','',$searchstring);
	$filter .= " AND online='1'";
} elseif(isset($getstring) && strstr($getstring, 'filter:nononline:')) {
	$searchstring = preg_replace('/filter\:nononline\:/','',$searchstring);
	$filter .= " AND online='0'";
}
if(isset($getstring) && strstr($getstring, 'filter:actualgroup:')) {
	preg_match('/filter\:actualgroup\:(.*)\:/',$searchstring,$grpvalue);
	$searchstring = preg_replace('/filter\:actualgroup\:(.*)\:/','',$searchstring);
	$filter .= " AND grpid='".$grpvalue[1]."'";
}
if(isset($getstring) && strstr($getstring, 'filter:country:')) {
	preg_match('/filter\:country\:(.*)\:/',$searchstring,$grpvalue);
	$searchstring = preg_replace('/filter\:country\:(.*)\:/','',$searchstring);
	$filter .= " AND nation='".$grpvalue[1]."'";
}
if(isset($getstring) && strstr($getstring, 'filter:lastseen:')) {
	preg_match('/filter\:lastseen\:(.*)\:(.*)\:/',$searchstring,$seenvalue);
	$searchstring = preg_replace('/filter\:lastseen\:(.*)\:(.*)\:/','',$searchstring);
	if(is_numeric($seenvalue[2])) {
		$lastseen = $seenvalue[2];
	} else {
		$r = date_parse_from_format("Y-m-d H-i",$seenvalue[2]);
		$lastseen = mktime($r['hour'], $r['minute'], $r['second'], $r['month'], $r['day'], $r['year']);
	}
	if($seenvalue[1] == '&lt;' || $seenvalue[1] == '<') {
		$operator = '<';
	} elseif($seenvalue[1] == '&gt;' || $seenvalue[1] == '>') {
		$operator = '>';
	} elseif($seenvalue[1] == '!=') {
		$operator = '!=';
	} else {
		$operator = '=';
	}
	$filter .= " AND lastseen".$operator."'".$lastseen."'";
}

if(isset($getstring)) {
	$dbdata_full = $mysqlcon->prepare("SELECT COUNT(*) FROM $dbname.user WHERE (uuid LIKE :searchvalue OR cldbid LIKE :searchvalue OR name LIKE :searchvalue)$filter");
	$dbdata_full->bindValue(':searchvalue', '%'.$searchstring.'%', PDO::PARAM_STR);
	$dbdata_full->execute();
} else {
	$getstring = '';
	$dbdata_full = $mysqlcon->query("SELECT COUNT(*) FROM $dbname.user");
}
if(!isset($_GET["seite"])) {
	$seite = 1;
} else {
	$seite = preg_replace('/\D/', '', $_GET["seite"]);
}
$adminlogin = 0;

$keysort  = '';
$keyorder = '';
if (isset($_GET['sort'])) {
	$keysort = strip_tags(htmlspecialchars($_GET['sort']));
}
if ($keysort != 'name' && $keysort != 'uuid' && $keysort != 'cldbid' && $keysort != 'rank' && $keysort != 'lastseen' && $keysort != 'count' && $keysort != 'idle' && $keysort != 'active' && $keysort != 'grpsince') {
	$keysort = 'nextup';
}
if (isset($_GET['order'])) {
	$keyorder = strip_tags(htmlspecialchars($_GET['order']));
}
$keyorder = ($keyorder == 'desc' ? 'desc' : 'asc');
if (isset($_GET['admin'])) {
	if($_SESSION['username'] == $webuser && $_SESSION['password'] == $webpass && $_SESSION['clientip'] == getclientip()) {
		$adminlogin = 1;
	}
}
require_once('nav.php');

$countentries = 0;
$sumentries = $dbdata_full->fetch(PDO::FETCH_NUM);

if(!isset($_GET["user"])) {
	$user_pro_seite = 25;
} elseif($_GET['user'] == "all") {
	$user_pro_seite = $sumentries[0];
} else {
	$user_pro_seite = preg_replace('/\D/', '', $_GET["user"]);
}

$start = $seite * $user_pro_seite - $user_pro_seite;

if ($keysort == 'active' && $keyorder == 'asc') {
	$dbdata = $mysqlcon->prepare("SELECT uuid,cldbid,rank,count,name,idle,cldgroup,online,nextup,lastseen,ip,grpid,except,grpsince FROM $dbname.user WHERE (uuid LIKE :searchvalue OR cldbid LIKE :searchvalue OR name LIKE :searchvalue)$filter ORDER BY (count - idle) LIMIT :start, :userproseite");
	$dbdata->bindValue(':searchvalue', '%'.$searchstring.'%', PDO::PARAM_STR);
	$dbdata->bindValue(':start', (int) $start, PDO::PARAM_INT);
	$dbdata->bindValue(':userproseite', (int) $user_pro_seite, PDO::PARAM_INT);
	$dbdata->execute();
} elseif ($keysort == 'active' && $keyorder == 'desc') {
	$dbdata = $mysqlcon->prepare("SELECT uuid,cldbid,rank,count,name,idle,cldgroup,online,nextup,lastseen,ip,grpid,except,grpsince FROM $dbname.user WHERE (uuid LIKE :searchvalue OR cldbid LIKE :searchvalue OR name LIKE :searchvalue)$filter ORDER BY (idle - count) LIMIT :start, :userproseite");
	$dbdata->bindValue(':searchvalue', '%'.$searchstring.'%', PDO::PARAM_STR);
	$dbdata->bindValue(':start', (int) $start, PDO::PARAM_INT);
	$dbdata->bindValue(':userproseite', (int) $user_pro_seite, PDO::PARAM_INT);
	$dbdata->execute();
} elseif ($searchstring == '') {
	$dbdata = $mysqlcon->prepare("SELECT uuid,cldbid,rank,count,name,idle,cldgroup,online,nextup,lastseen,ip,grpid,except,grpsince FROM $dbname.user WHERE 1=1$filter ORDER BY $keysort $keyorder LIMIT :start, :userproseite");
	$dbdata->bindValue(':start', (int) $start, PDO::PARAM_INT);
	$dbdata->bindValue(':userproseite', (int) $user_pro_seite, PDO::PARAM_INT);
	$dbdata->execute();
} else {
	$dbdata = $mysqlcon->prepare("SELECT uuid,cldbid,rank,count,name,idle,cldgroup,online,nextup,lastseen,ip,grpid,except,grpsince FROM $dbname.user WHERE (uuid LIKE :searchvalue OR cldbid LIKE :searchvalue OR name LIKE :searchvalue)$filter ORDER BY $keysort $keyorder LIMIT :start, :userproseite");
	$dbdata->bindValue(':searchvalue', '%'.$searchstring.'%', PDO::PARAM_STR);
	$dbdata->bindValue(':start', (int) $start, PDO::PARAM_INT);
	$dbdata->bindValue(':userproseite', (int) $user_pro_seite, PDO::PARAM_INT);
	$dbdata->execute();
}

$seiten_anzahl_gerundet = ceil($sumentries[0] / $user_pro_seite);

function pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring) {
	?>
	<nav>
		<div class="text-center">
			<ul class="pagination">
				<li>
					<a href="<?PHP echo '?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite=1&amp;user='.$user_pro_seite.'&amp;search='.$getstring; ?>" aria-label="backward">
						<span aria-hidden="true"><span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>&nbsp;</span>
					</a>
				</li>
				<?PHP
				for($a=0; $a < $seiten_anzahl_gerundet; $a++) {
					$b = $a + 1;
					if($seite == $b) {
						echo '<li class="active"><a href="">'.$b.'</a></li>';
					} elseif ($b > $seite - 5 && $b < $seite + 5) {
						echo '<li><a href="?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite='.$b.'&amp;user='.$user_pro_seite.'&amp;search='.$getstring.'">'.$b.'</a></li>';
					}
				}
				?>
				<li>
					<a href="<?PHP echo '?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite='.$seiten_anzahl_gerundet.'&amp;user='.$user_pro_seite.'&amp;search='.$getstring; ?>" aria-label="forward">
						<span aria-hidden="true">&nbsp;<span class="glyphicon glyphicon-step-forward" aria-hidden="true"></span></span>
					</a>
				</li>
			</ul>
		</div>
	</nav>
	<?PHP
}
$uuids = $dbdata->fetchAll();

foreach($uuids as $uuid) {
	$sqlhis[$uuid['uuid']] = array(
		"cldbid" => $uuid['cldbid'],
		"rank" => $uuid['rank'],
		"count" => $uuid['count'],
		"name" => $uuid['name'],
		"idle" => $uuid['idle'],
		"cldgroup" => $uuid['cldgroup'],
		"online" => $uuid['online'],
		"nextup" => $uuid['nextup'],
		"lastseen" => $uuid['lastseen'],
		"ip" => $uuid['ip'],
		"grpid" => $uuid['grpid'],
		"except" => $uuid['except'],
		"cldbid" => $uuid['cldbid'],
		"grpsince" => $uuid['grpsince']
	);
	$uidarr[]			  = $uuid['uuid'];
	$countentries		  = $countentries + 1;
}
if(!$dbdata = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='calc_user_lastscan'")) {
	$err_msg = '<span class="wncolor">'.$mysqlcon->errorCode().'</span><br>';
}

$lastscan = $dbdata->fetchAll();
$scantime = $lastscan[0]['timestamp'];
$livetime = time() - $scantime;
$dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups");
$servergroups = $dbgroups->fetchAll(PDO::FETCH_ASSOC);
foreach($servergroups as $servergroup) {
	$sqlhisgroup[$servergroup['sgid']] = $servergroup['sgidname'];
	if(file_exists('../icons/'.$servergroup['sgid'].'.png')) {
		$sqlhisgroup_file[$servergroup['sgid']] = true;
	} else {
		$sqlhisgroup_file[$servergroup['sgid']] = false;
	}
}
if($adminlogin == 1) {
	switch ($keyorder) {
		case "asc":
			$keyorder2 = "desc&amp;admin=true";
			break;
		case "desc":
			$keyorder2 = "asc&amp;admin=true";
	}
} else {
	switch ($keyorder) {
		case "asc":
			$keyorder2 = "desc";
			break;
		case "desc":
			$keyorder2 = "asc";
	}
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
			<div class="container-fluid">
				<?PHP
				if($user_pro_seite == "all" || $shownav == 0) {
				} else {
					pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring);
				}
				?>
					<table class="table table-striped">
						<thead data-spy="affix" data-offset-top="100">
							<tr>
				<?PHP
				if ($showcolrg == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=rank&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listrank'] , '</span></a></th>';
				if ($showcolcld == 1 || $adminlogin == 1)
					echo ($keysort == 'name') ? '<th class="text-center"><a href="?sort=name&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnick'] . '</span></a></th>' : '<th class="text-center"><a href="?sort=name&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnick'] . '</span></a></th>';
				if ($showcoluuid == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=uuid&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listuid'] , '</span></a></th>';
				if ($showcoldbid == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=cldbid&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listcldbid'] , '</span></a></th>';
				if ($adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=ip&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listip'] , '</span></a></th>';
				if ($showcolls == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=lastseen&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listseen'] , '</span></a></th>';
				if ($showcolot == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=count&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listsumo'] , '</span></a></th>';
				if ($showcolit == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=idle&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listsumi'] , '</span></a></th>';
				if ($showcolat == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=active&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listsuma'] , '</span></a></th>';
				if ($showcolas == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=grpid&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listacsg'] , '</span></a></th>';
				if ($showgrpsince == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=grpsince&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listgrps'] , '</span></a></th>';
				if ($showcolnx == 1 || $adminlogin == 1)
					echo ($keysort == 'nextup') ? '<th class="text-center"><a href="?sort=nextup&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnxup'] . '</span></a></th>' : '<th class="text-center"><a href="?sort=nextup&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnxup'] . '</span></a></th>';
				if ($showcolsg == 1 || $adminlogin == 1)
					echo '<th class="text-center"><a href="?sort=nextsgrp&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listnxsg'] , '</span></a></th>';
				echo '</tr></thead><tbody>';
				ksort($grouptime);
				$countgrp = count($grouptime);
				if ($countentries > 0) {
					$exceptgrp=0;
					$exceptcld=0;
					$countallsum=0;
					foreach ($uidarr as $uid) {
						$cldgroup = $sqlhis[$uid]['cldgroup'];
						$lastseen = $sqlhis[$uid]['lastseen'];
						$count	= $sqlhis[$uid]['count'];
						$idle	 = $sqlhis[$uid]['idle'];
						$status   = $sqlhis[$uid]['online'];
						$nextup   = $sqlhis[$uid]['nextup'];
						$except   = $sqlhis[$uid]['except'];
						$sgroups  = explode(",", $cldgroup);
						$active   = $count - $idle;
						if ($substridle == 1) {
							$activetime = $count - $idle;
						} else {
							$activetime = $count;
						}
						$grpcount=0;
						$countallsum++;
						foreach ($grouptime as $time => $groupid) {
							$grpcount++;
							if ($activetime < $time || $grpcount == $countgrp && $nextup <= 0 && $showhighest == 1 || $grpcount == $countgrp && $nextup == 0 && $adminlogin == 1) {
								if($nextup == 0 && $grpcount == $countgrp) {
									$neededtime = 0;
								} elseif ($status == 1) {
									$neededtime = $time - $activetime - $livetime;
								} else {
									$neededtime = $time - $activetime;
								}
								echo '<tr>';
								if ($showcolrg == 1 || $adminlogin == 1) {
									if($except == 2 || $except == 3) {
										echo '<td class="text-center"></td>';
									} else {
										echo '<td class="text-center">' , $sqlhis[$uid]['rank'] , '</td>';
									}
								}
								if ($adminlogin == 1) {
									echo '<td class="text-center"><a href="http://www.tsviewer.com/index.php?page=search&action=ausgabe_user&nickname=' , $sqlhis[$uid]['name'] , '" target="_blank">' , $sqlhis[$uid]['name'] , '</a></td>';
								} elseif ($showcolcld == 1) {
									echo '<td class="text-center"><a href="profil.php?id='.$sqlhis[$uid]['cldbid'].'">' , $sqlhis[$uid]['name'] , '</a></td>';
								}
								if ($adminlogin == 1) {
									echo '<td class="text-center"><a href="http://ts3index.com/?page=searchclient&uid=' , $uid , '" target="_blank">' , $uid , '</a></td>';
								} elseif ($showcoluuid == 1) {
									echo '<td class="text-center">' , $uid , '</td>';
								}
								if ($showcoldbid == 1 || $adminlogin == 1)
									echo '<td class="text-center">' , $sqlhis[$uid]['cldbid'] , '</td>';
								if ($adminlogin == 1)
									echo '<td class="center"><a href="http://myip.ms/info/whois/' , inet_ntop($sqlhis[$uid]['ip']) , '" target="_blank">' , inet_ntop($sqlhis[$uid]['ip']) , '</a></td>';
								if ($showcolls == 1 || $adminlogin == 1) {
									if ($status == 1) {
										echo '<td class="text-center text-success">' , date('Y-m-d H:i:s',$lastseen), '</td>';
									} else {
										echo '<td class="text-center">' , date('Y-m-d H:i:s',$lastseen), '</td>';
									}
								}
								if ($showcolot == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									$dtF	   = new DateTime("@0");
									$dtT	   = new DateTime("@$count");
									$timecount = $dtF->diff($dtT)->format($timeformat);
									echo $timecount;
								}
								if ($showcolit == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									$dtF	   = new DateTime("@0");
									$dtT	   = new DateTime("@$idle");
									$timecount = $dtF->diff($dtT)->format($timeformat);
									echo $timecount;
								}
								if ($showcolat == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									$dtF	   = new DateTime("@0");
									$dtT	   = new DateTime("@$active");
									$timecount = $dtF->diff($dtT)->format($timeformat);
									echo $timecount;
								}
								if ($showcolas == 1 || $adminlogin == 1) {
									$usergroupid = $sqlhis[$uid]['grpid'];
									if ($sqlhis[$uid]['grpid'] == 0) {
										echo '<td class="text-center"></td>';
									} elseif ($sqlhisgroup_file[$sqlhis[$uid]['grpid']]===true) {
										echo '<td class="text-center"><img src="../icons/'.$sqlhis[$uid]['grpid'].'.png" alt="groupicon">&nbsp;&nbsp;' , $sqlhisgroup[$usergroupid] , '</td>';
									} else {
										echo '<td class="text-center">' , $sqlhisgroup[$usergroupid] , '</td>';
									}
								}
								if ($showgrpsince == 1 || $adminlogin == 1) {
									if ($sqlhis[$uid]['grpsince'] == 0) {
										echo '<td class="text-center"></td>';
									} else {
										echo '<td class="text-center">' , date('Y-m-d H:i:s',$sqlhis[$uid]['grpsince']), '</td>';
									}
								}
								if ($showcolnx == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									$dtF	   = new DateTime("@0");
									$dtT	   = new DateTime("@$neededtime");
									$timecount = $dtF->diff($dtT)->format($timeformat);
									if (($except == 0 || $except == 1) && $neededtime > 0) {
										echo $timecount , '</td>';
									} elseif ($except == 0 || $except == 1) {
										$timecount = 0;
										echo $timecount , '</td>';
									} elseif ($except == 2 || $except == 3) {
										echo '0</td>';
									} else {
										echo $lang['errukwn'], '</td>';
									}
								}
								if ($showcolsg == 1 || $adminlogin == 1) {
									if ($grpcount == $countgrp && $nextup == 0 && $showhighest == 1 || $grpcount == $countgrp && $nextup == 0 && $adminlogin == 1) {
										echo '<td class="text-center"><em>',$lang['highest'],'</em></td>';
									} elseif ($except == 2 || $except == 3) {
										echo '<td class="text-center"><em>',$lang['listexcept'],'</em></td>';
									} elseif (isset($sqlhisgroup_file[$groupid]) && $sqlhisgroup_file[$groupid]===true) {
										echo '<td class="text-center"><img src="../icons/'.$groupid.'.png" alt="groupicon">&nbsp;&nbsp;' , $sqlhisgroup[$groupid] , '</td>';
									} elseif (isset($sqlhisgroup[$groupid])) {
										echo '<td class="text-center">' , $sqlhisgroup[$groupid] , '</td>';
									} else {
										echo '<td class="text-center"></td>';
									}
								}
								echo '</tr>';
								break;
							}
						}
					}
				} else {
					echo '<tr><td colspan="6">' , $lang['noentry'] , '</td></tr>';
				}
				echo '</tbody></table>';
				if($user_pro_seite != "all") {
					pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring);
				}
				?>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	$("th").each(function() {
      $(this).width($(this).width());
	});
	$("td").each(function() {
      $(this).width($(this).width());
	});
	</script>
</body>
</html>