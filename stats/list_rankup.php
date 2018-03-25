<?PHP
session_start();

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

if(!isset($_SESSION[$rspathhex.'tsuid'])) {
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
	if(isset($seenvalue[2]) && is_numeric($seenvalue[2])) {
		$lastseen = $seenvalue[2];
	} elseif(isset($seenvalue[2])) {
		$r = date_parse_from_format("Y-m-d H-i",$seenvalue[2]);
		$lastseen = mktime($r['hour'], $r['minute'], $r['second'], $r['month'], $r['day'], $r['year']);
	} else {
		$lastseen = 0;
	}
	if(isset($seenvalue[1]) && ($seenvalue[1] == '&lt;' || $seenvalue[1] == '<')) {
		$operator = '<';
	} elseif(isset($seenvalue[1]) && ($seenvalue[1] == '&gt;' || $seenvalue[1] == '>')) {
		$operator = '>';
	} elseif(isset($seenvalue[1]) && $seenvalue[1] == '!=') {
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
	$sumentries = $dbdata_full->fetch(PDO::FETCH_NUM);
} else {
	$getstring = '';
	$sumentries = $mysqlcon->query("SELECT COUNT(*) FROM $dbname.user")->fetch(PDO::FETCH_NUM);
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
if ($keysort != 'name' && $keysort != 'uuid' && $keysort != 'cldbid' && $keysort != 'rank' && $keysort != 'lastseen' && $keysort != 'count' && $keysort != 'idle' && $keysort != 'active' && $keysort != 'grpid' && $keysort != 'grpsince') {
	$keysort = 'nextup';
}
if (isset($_GET['order'])) {
	$keyorder = strip_tags(htmlspecialchars($_GET['order']));
}
$keyorder = ($keyorder == 'desc' ? 'desc' : 'asc');
if (isset($_GET['admin'])) {
	if($_SESSION[$rspathhex.'username'] == $webuser && $_SESSION[$rspathhex.'password'] == $webpass && $_SESSION[$rspathhex.'clientip'] == getclientip()) {
		$adminlogin = 1;
	}
}
require_once('nav.php');

$countentries = 0;


if(!isset($_GET["user"])) {
	$user_pro_seite = 25;
} elseif($_GET['user'] == "all") {
	$user_pro_seite = $sumentries[0];
} else {
	$user_pro_seite = preg_replace('/\D/', '', $_GET["user"]);
}

$getstring = htmlspecialchars($getstring);

$start = ($seite * $user_pro_seite) - $user_pro_seite;

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
$sqlhis = $dbdata->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);

$sqlhisgroup = $mysqlcon->query("SELECT sgid,sgidname FROM $dbname.groups")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
foreach($sqlhisgroup as $sgid => $servergroup) {
	if(file_exists('../tsicons/'.$sgid.'.png')) {
		$sqlhisgroup[$sgid]['iconfile'] = 1;
	} else {
		$sqlhisgroup[$sgid]['iconfile'] = 0;
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
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
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
				if (($showcolsg == 1 || $adminlogin == 1) && $substridle == 1) {
					echo '<th class="text-center"><a href="?sort=active&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listnxsg'] , '</span></a></th>';
				} elseif (($showcolsg == 1 || $adminlogin == 1) && $substridle != 1) {
					echo '<th class="text-center"><a href="?sort=count&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listnxsg'] , '</span></a></th>';
				}
				echo '</tr></thead><tbody>';
				ksort($grouptime);
				if (count($sqlhis) > 0) {
					foreach ($sqlhis as $uuid => $value) {
						if ($substridle == 1) {
							$activetime = $value['count'] - $value['idle'];
						} else {
							$activetime = $value['count'];
						}
						$grpcount=0;
						foreach ($grouptime as $time => $groupid) {
							$grpcount++;
							if ($activetime < $time || $grpcount == count($grouptime) && $value['nextup'] <= 0 && $showhighest == 1 || $grpcount == count($grouptime) && $value['nextup'] == 0 && $adminlogin == 1) {
								echo '<tr>';
								if ($showcolrg == 1 || $adminlogin == 1) {
									if($value['except'] == 2 || $value['except'] == 3) {
										echo '<td class="text-center"></td>';
									} else {
										echo '<td class="text-center">' , $value['rank'] , '</td>';
									}
								}
								if ($adminlogin == 1) {
									echo '<td class="text-center"><a href="//tsviewer.com/index.php?page=search&action=ausgabe_user&nickname=' , htmlspecialchars($value['name']) , '" target="_blank">' , htmlspecialchars($value['name']) , '</a></td>';
								} elseif ($showcolcld == 1) {
									echo '<td class="text-center">' , htmlspecialchars($value['name']) , '</td>';
								}
								if ($adminlogin == 1) {
									echo '<td class="text-center"><a href="//ts3index.com/?page=searchclient&uid=' , $uuid , '" target="_blank">' , $uuid , '</a></td>';
								} elseif ($showcoluuid == 1) {
									echo '<td class="text-center">' , $uuid , '</td>';
								}
								if ($showcoldbid == 1 || $adminlogin == 1)
									echo '<td class="text-center">' , $value['cldbid'] , '</td>';
								if ($adminlogin == 1)
									echo '<td class="center"><a href="//myip.ms/info/whois/' , inet_ntop($value['ip']) , '" target="_blank">' , inet_ntop($value['ip']) , '</a></td>';
								if ($showcolls == 1 || $adminlogin == 1) {
									if ($value['online'] == 1) {
										echo '<td class="text-center text-success">online</td>';
									} else {
										echo '<td class="text-center">' , date('Y-m-d H:i:s',$value['lastseen']), '</td>';
									}
								}
								if ($showcolot == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									$dtF	   = new DateTime("@0");
									$dtT	   = new DateTime("@".$value['count']);
									echo $dtF->diff($dtT)->format($timeformat);
								}
								if ($showcolit == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									$dtF	   = new DateTime("@0");
									$dtT	   = new DateTime("@".$value['idle']);
									echo $dtF->diff($dtT)->format($timeformat);
								}
								if ($showcolat == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									$dtF	   = new DateTime("@0");
									$dtT	   = new DateTime("@".($value['count']-$value['idle']));
									echo $dtF->diff($dtT)->format($timeformat);
								}
								if ($showcolas == 1 || $adminlogin == 1) {
									if ($value['grpid'] == 0) {
										echo '<td class="text-center"></td>';
									} elseif ($sqlhisgroup[$value['grpid']]['iconfile'] == 1) {
										echo '<td class="text-center"><img src="../tsicons/'.$value['grpid'].'.png" alt="groupicon">&nbsp;&nbsp;' , $sqlhisgroup[$value['grpid']]['sgidname'] , '</td>';
									} else {
										echo '<td class="text-center">' , $sqlhisgroup[$value['grpid']]['sgidname'] , '</td>';
									}
								}
								if ($showgrpsince == 1 || $adminlogin == 1) {
									if ($value['grpsince'] == 0) {
										echo '<td class="text-center"></td>';
									} else {
										echo '<td class="text-center">' , date('Y-m-d H:i:s',$value['grpsince']), '</td>';
									}
								}
								if ($showcolnx == 1 || $adminlogin == 1) {
									echo '<td class="text-center">';
									if (($value['except'] == 0 || $value['except'] == 1) && $value['nextup'] > 0) {
										$dtF	   = new DateTime("@0");
										$dtT	   = new DateTime("@".$value['nextup']);
										echo $dtF->diff($dtT)->format($timeformat) , '</td>';
									} elseif ($value['except'] == 0 || $value['except'] == 1) {
										echo '0</td>';
									} elseif ($value['except'] == 2 || $value['except'] == 3) {
										echo '0</td>';
									} else {
										echo $lang['errukwn'], '</td>';
									}
								}
								if ($showcolsg == 1 || $adminlogin == 1) {
									if ($grpcount == count($grouptime) && $value['nextup'] == 0 && $showhighest == 1 || $grpcount == count($grouptime) && $value['nextup'] == 0 && $adminlogin == 1) {
										echo '<td class="text-center"><em>',$lang['highest'],'</em></td>';
									} elseif ($value['except'] == 2 || $value['except'] == 3) {
										echo '<td class="text-center"><em>',$lang['listexcept'],'</em></td>';
									} elseif (isset($sqlhisgroup[$groupid]) && $sqlhisgroup[$groupid]['iconfile'] == 1) {
										echo '<td class="text-center"><img src="../tsicons/'.$groupid.'.png" alt="groupicon">&nbsp;&nbsp;' , $sqlhisgroup[$groupid]['sgidname'] , '</td>';
									} elseif (isset($sqlhisgroup[$groupid])) {
										echo '<td class="text-center">' , $sqlhisgroup[$groupid]['sgidname'] , '</td>';
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