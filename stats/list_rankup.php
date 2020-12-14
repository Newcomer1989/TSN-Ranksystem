<?PHP
require_once('_preload.php');

try {
	if(is_dir(substr(__DIR__,0,-5).'languages/')) {
		foreach(scandir(substr(__DIR__,0,-5).'languages/') as $file) {
			if ('.' === $file || '..' === $file || is_dir($file)) continue;
			$sep_lang = preg_split("/[._]/", $file);
			if(isset($sep_lang[0]) && $sep_lang[0] == 'nations' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[2]) && strtolower($sep_lang[2]) == 'php') {
				if(strtolower($cfg['default_language']) == strtolower($sep_lang[1])) {
					require_once('../languages/nations_'.$sep_lang[1].'.php');
					$required_nations = 1;
					break;
				}
			}
		}
		if(!isset($required_nations)) {
			require_once('../languages/nations_en.php');
		}
	}

	$start = ($seite * $user_pro_seite) - $user_pro_seite;

	if ($keysort == 'active' && $keyorder == 'asc') {
		$order = '(`count` - `idle`)';
	} elseif ($keysort == 'active' && $keyorder == 'desc') {
		$order = '(`idle` - `count`)';
	} else {
		$order = "`{$keysort}` ".$keyorder;
	}

	if ($cfg['stats_column_default_sort_2'] == 'active' && $cfg['stats_column_default_order_2'] == 'asc') {
		$order .= ', (`count` - `idle`)';
	} elseif ($keysort == 'active' && $keyorder == 'desc') {
		$order .= ', (`idle` - `count`)';
	} else {
		$order .= ", `{$cfg['stats_column_default_sort_2']}` ".$cfg['stats_column_default_order_2'];
	}
	
	if ($searchstring == '') {
		$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`user` WHERE 1=1$filter ORDER BY $order LIMIT :start, :userproseite");
	} else {
		$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`user` WHERE (`uuid` LIKE :searchvalue OR `cldbid` LIKE :searchvalue OR `name` LIKE :searchvalue) $filter ORDER BY $order LIMIT :start, :userproseite");
		$dbdata->bindValue(':searchvalue', '%'.$searchstring.'%', PDO::PARAM_STR);
	}

	$dbdata->bindValue(':start', (int) $start, PDO::PARAM_INT);
	$dbdata->bindValue(':userproseite', (int) $user_pro_seite, PDO::PARAM_INT);
	$dbdata->execute();

	if($user_pro_seite > 0 && isset($sumentries[0])) {
		$seiten_anzahl_gerundet = ceil($sumentries[0] / $user_pro_seite);
	} else {
		$seiten_anzahl_gerundet = 0;
	}

	if(($sqlhisgroup = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
	}

	$sqlhis = $dbdata->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);

	if($adminlogin == 1) {
		switch ($keyorder) {
			case "asc":
				$keyorder2 = "desc&amp;admin=true";
				break;
			case "desc":
				$keyorder2 = "asc&amp;admin=true";
		}
		$keyorder .= "&amp;admin=true";
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
					if($user_pro_seite == "all" || $cfg['stats_show_site_navigation_switch'] == 0) {
					} else {
						$pag = pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring);
						echo $pag;
					}
					?>
						<table class="table table-striped">
							<thead data-spy="affix" data-offset-top="100">
								<tr>
					<?PHP
					if ($cfg['stats_column_rank_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=rank&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listrank'] , '</span></a></th>';
					if ($cfg['stats_column_client_name_switch'] == 1 || $adminlogin == 1)
						echo ($keysort == 'name') ? '<th class="text-center"><a href="?sort=name&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnick'] . '</span></a></th>' : '<th class="text-center"><a href="?sort=name&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnick'] . '</span></a></th>';
					if ($cfg['stats_column_unique_id_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=uuid&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listuid'] , '</span></a></th>';
					if ($cfg['stats_column_client_db_id_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=cldbid&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listcldbid'] , '</span></a></th>';
					if ($cfg['stats_column_last_seen_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=lastseen&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listseen'] , '</span></a></th>';
					if ($cfg['stats_column_nation_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=nation&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listnat'] , '</span></a></th>';
					if ($cfg['stats_column_version_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=version&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listver'] , '</span></a></th>';
					if ($cfg['stats_column_platform_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=platform&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listpla'] , '</span></a></th>';
					if ($cfg['stats_column_online_time_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=count&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listsumo'] , '</span></a></th>';
					if ($cfg['stats_column_idle_time_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=idle&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listsumi'] , '</span></a></th>';
					if ($cfg['stats_column_active_time_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=active&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listsuma'] , '</span></a></th>';
					if ($cfg['stats_column_current_server_group_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=grpid&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listacsg'] , '</span></a></th>';
					if ($cfg['stats_column_current_group_since_switch'] == 1 || $adminlogin == 1)
						echo '<th class="text-center"><a href="?sort=grpsince&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listgrps'] , '</span></a></th>';
					if ($cfg['stats_column_next_rankup_switch'] == 1 || $adminlogin == 1)
						echo ($keysort == 'nextup') ? '<th class="text-center"><a href="?sort=nextup&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnxup'] . '</span></a></th>' : '<th class="text-center"><a href="?sort=nextup&amp;order=' . $keyorder2 . '&amp;seite=' . $seite . '&amp;user=' . $user_pro_seite . '&amp;search=' . $getstring . '"><span class="hdcolor">' . $lang['listnxup'] . '</span></a></th>';
					if (($cfg['stats_column_next_server_group_switch'] == 1 || $adminlogin == 1) && $cfg['rankup_time_assess_mode'] == 1) {
						echo '<th class="text-center"><a href="?sort=active&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listnxsg'] , '</span></a></th>';
					} elseif (($cfg['stats_column_next_server_group_switch'] == 1 || $adminlogin == 1) && $cfg['rankup_time_assess_mode'] != 1) {
						echo '<th class="text-center"><a href="?sort=count&amp;order=' , $keyorder2 , '&amp;seite=' , $seite , '&amp;user=' , $user_pro_seite , '&amp;search=' , $getstring , '"><span class="hdcolor">' , $lang['listnxsg'] , '</span></a></th>';
					}
					echo '</tr></thead><tbody>';
					ksort($cfg['rankup_definition']);
					if (count($sqlhis) > 0) {
						foreach ($sqlhis as $uuid => $value) {
							if ($cfg['rankup_time_assess_mode'] == 1) {
								$activetime = $value['count'] - $value['idle'];
							} else {
								$activetime = $value['count'];
							}
							$grpcount=0;
							foreach ($cfg['rankup_definition'] as $rank) {
								$grpcount++;
								if ($activetime < $rank['time'] || $grpcount == count($cfg['rankup_definition']) && $value['nextup'] <= 0 && $cfg['stats_show_clients_in_highest_rank_switch'] == 1 || $grpcount == count($cfg['rankup_definition']) && $value['nextup'] == 0 && $adminlogin == 1) {
									echo '<tr>';
									if ($cfg['stats_column_rank_switch'] == 1 || $adminlogin == 1) {
										if($value['except'] == 2 || $value['except'] == 3) {
											echo '<td class="text-center"></td>';
										} else {
											echo '<td class="text-center">' , $value['rank'] , '</td>';
										}
									}
									if ($adminlogin == 1) {
										echo '<td class="text-center"><a href="//tsviewer.com/index.php?page=search&action=ausgabe_user&nickname=' , htmlspecialchars($value['name']) , '" target="_blank">' , htmlspecialchars($value['name']) , '</a></td>';
									} elseif ($cfg['stats_column_client_name_switch'] == 1) {
										echo '<td class="text-center">' , htmlspecialchars($value['name']) , '</td>';
									}
									if ($adminlogin == 1) {
										echo '<td class="text-center"><a href="//ts3index.com/?page=searchclient&uid=' , $uuid , '" target="_blank">' , $uuid , '</a></td>';
									} elseif ($cfg['stats_column_unique_id_switch'] == 1) {
										echo '<td class="text-center">' , $uuid , '</td>';
									}
									if ($cfg['stats_column_client_db_id_switch'] == 1 || $adminlogin == 1)
										echo '<td class="text-center">' , $value['cldbid'] , '</td>';
									if ($cfg['stats_column_last_seen_switch'] == 1 || $adminlogin == 1) {
										if ($value['online'] == 1) {
											echo '<td class="text-center text-success">online</td>';
										} else {
											echo '<td class="text-center">' , date('Y-m-d H:i:s',$value['lastseen']), '</td>';
										}
									}
									if ($cfg['stats_column_nation_switch'] == 1 || $adminlogin == 1) {
										if(strtoupper($value['nation']) == 'XX' || $value['nation'] == NULL) {
											echo '<td class="text-center"><i class="fas fa-question-circle" title="' , $lang['unknown'] , '"></i></td>';
										} else {
											echo '<td class="text-center"><span class="flag-icon flag-icon-' , strtolower(htmlspecialchars($value['nation'])) , '" title="' , $nation[$value['nation']] , '"></span></td>';
										}
									}
									if ($cfg['stats_column_version_switch'] == 1 || $adminlogin == 1) {
										echo '<td class="text-center">' , htmlspecialchars($value['version']) , '</td>';
									}
									if ($cfg['stats_column_platform_switch'] == 1 || $adminlogin == 1) {
										echo '<td class="text-center">' , htmlspecialchars($value['platform']) , '</td>';
									}
									if ($cfg['stats_column_online_time_switch'] == 1 || $adminlogin == 1) {
										echo '<td title="',round($value['count']),' sec." class="text-center">';
										$dtF	   = new DateTime("@0");
										$dtT	   = new DateTime("@".round($value['count']));
										echo $dtF->diff($dtT)->format($cfg['default_date_format']);
									}
									if ($cfg['stats_column_idle_time_switch'] == 1 || $adminlogin == 1) {
										echo '<td title="',round($value['idle']),' sec." class="text-center">';
										$dtF	   = new DateTime("@0");
										$dtT	   = new DateTime("@".round($value['idle']));
										echo $dtF->diff($dtT)->format($cfg['default_date_format']);
									}
									if ($cfg['stats_column_active_time_switch'] == 1 || $adminlogin == 1) {
										echo '<td title="',(round($value['count'])-round($value['idle'])),' sec." class="text-center">';
										$dtF	   = new DateTime("@0");
										$dtT	   = new DateTime("@".(round($value['count'])-round($value['idle'])));
										echo $dtF->diff($dtT)->format($cfg['default_date_format']);
									}
									if ($cfg['stats_column_current_server_group_switch'] == 1 || $adminlogin == 1) {
										if ($value['grpid'] == 0) {
											echo '<td class="text-center"></td>';
										} elseif(isset($sqlhisgroup[$value['grpid']]) && $sqlhisgroup[$value['grpid']]['iconid'] != 0) {
											echo '<td class="text-center"><img src="../tsicons/',$sqlhisgroup[$value['grpid']]['iconid'],'.',$sqlhisgroup[$value['grpid']]['ext'],'" width="16" height="16" alt="groupicon">&nbsp;&nbsp;' , $sqlhisgroup[$value['grpid']]['sgidname'] , '</td>';
										} elseif(isset($sqlhisgroup[$value['grpid']])) {
											echo '<td class="text-center">' , $sqlhisgroup[$value['grpid']]['sgidname'] , '</td>';
										} else {
											echo '<td class="text-center"><i>',$lang['unknown'],'</i></td>';
										}
									}
									if ($cfg['stats_column_current_group_since_switch'] == 1 || $adminlogin == 1) {
										if ($value['grpsince'] == 0) {
											echo '<td class="text-center"></td>';
										} else {
											echo '<td class="text-center">' , date('Y-m-d H:i:s',$value['grpsince']), '</td>';
										}
									}
									if ($cfg['stats_column_next_rankup_switch'] == 1 || $adminlogin == 1) {
										echo '<td title="';
										if (($value['except'] == 0 || $value['except'] == 1) && $value['nextup'] > 0) {
											$dtF	   = new DateTime("@0");
											$dtT	   = new DateTime("@".$value['nextup']);
											echo round($value['nextup']),' sec." class="text-center">',$dtF->diff($dtT)->format($cfg['default_date_format']) , '</td>';
										} elseif ($value['except'] == 0 || $value['except'] == 1) {
											echo '0 sec." class="text-center">0</td>';
										} elseif ($value['except'] == 2 || $value['except'] == 3) {
											echo '0 sec." class="text-center">0</td>';
										} else {
											echo $lang['errukwn'], '</td>';
										}
									}
									if ($cfg['stats_column_next_server_group_switch'] == 1 || $adminlogin == 1) {
										if ($grpcount == count($cfg['rankup_definition']) && $value['nextup'] == 0 && $cfg['stats_show_clients_in_highest_rank_switch'] == 1 || $grpcount == count($cfg['rankup_definition']) && $value['nextup'] == 0 && $adminlogin == 1) {
											echo '<td class="text-center"><em>',$lang['highest'],'</em></td>';
										} elseif ($value['except'] == 2 || $value['except'] == 3) {
											echo '<td class="text-center"><em>',$lang['listexcept'],'</em></td>';
										} elseif (isset($sqlhisgroup[$rank['group']]) && $sqlhisgroup[$rank['group']]['iconid'] != 0) {
											echo '<td class="text-center"><img src="../tsicons/',$sqlhisgroup[$rank['group']]['iconid'],'.',$sqlhisgroup[$rank['group']]['ext'],'" width="16" height="16" alt="missed_icon">&nbsp;&nbsp;' , $sqlhisgroup[$rank['group']]['sgidname'] , '</td>';
										} elseif (isset($sqlhisgroup[$rank['group']])) {
											echo '<td class="text-center">' , $sqlhisgroup[$rank['group']]['sgidname'] , '</td>';
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
						$pag = pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring);
						echo $pag;
					}
					?>
				</div>
			</div>
		</div>
		<?PHP require_once('_footer.php'); ?>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>