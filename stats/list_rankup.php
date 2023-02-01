<?PHP
require_once('_preload.php');

try {
	if(is_dir($GLOBALS['langpath'])) {
		foreach(scandir($GLOBALS['langpath']) as $file) {
			if ('.' === $file || '..' === $file || is_dir($file)) continue;
			$sep_lang = preg_split("/[._]/", $file);
			if(isset($sep_lang[0]) && $sep_lang[0] == 'nations' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[2]) && strtolower($sep_lang[2]) == 'php') {
				if(strtolower($cfg['default_language']) == strtolower($sep_lang[1])) {
					require_once($GLOBALS['langpath'].'nations_'.$sep_lang[1].'.php');
					$required_nations = 1;
					break;
				}
			}
		}
		if(!isset($required_nations)) {
			require_once($GLOBALS['langpath'].'nations_en.php');
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

	if($cfg['stats_column_online_day_switch'] == 1 || $cfg['stats_column_idle_day_switch'] == 1 || $cfg['stats_column_active_day_switch'] == 1 || $cfg['stats_column_online_week_switch'] == 1 || $cfg['stats_column_idle_week_switch'] == 1 || $cfg['stats_column_active_week_switch'] == 1 || $cfg['stats_column_online_month_switch'] == 1 || $cfg['stats_column_idle_month_switch'] == 1 || $cfg['stats_column_active_month_switch'] == 1) {
		$stats_user_tbl = ", `$dbname`.`stats_user`";
		$stats_user_where = " AND `stats_user`.`uuid`=`user`.`uuid`";
	} else {
		$stats_user_tbl = $stats_user_where = '';
	}

	if ($searchstring == '') {
		$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`user`$stats_user_tbl WHERE 1=1$filter$stats_user_where ORDER BY $order LIMIT :start, :userproseite");
	} else {
		$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`user`$stats_user_tbl WHERE (`user`.`uuid` LIKE :searchvalue OR `user`.`cldbid` LIKE :searchvalue OR `user`.`name` LIKE :searchvalue) $filter$stats_user_where ORDER BY $order LIMIT :start, :userproseite");
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
			<div id="page-wrapper" class="stats_list_rankup">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<?PHP
					if($user_pro_seite == "all" || $cfg['stats_show_site_navigation_switch'] == 0) {
					} else {
						echo pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring);
					}
					?>
						<table class="table table-striped" id="list-rankup">
							<thead data-spy="affix" data-offset-top="100">
								<tr>
					<?PHP
					$arr_sort_options = sort_options($lang);
					$count_columns = 0;
					foreach ($arr_sort_options as $opt => $val) {
						if ($cfg[$val['config']] == 1 || $adminlogin == 1) {
							echo '<th><a href="?sort=',$val['option'],'&amp;order=',$keyorder2,'&amp;seite=',$seite,'&amp;user=',$user_pro_seite,'&amp;search=',rawurldecode($getstring),'"><span class="hdcolor">',$val['title'],'</span></a></th>';
							$count_columns++;
						}
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
							if($cfg['stats_column_next_server_group_switch'] != 1) {
								echo list_rankup($cfg,$lang,$sqlhisgroup,$value,$adminlogin,$nation,$grpcount);
							} else {
								foreach ($cfg['rankup_definition'] as $rank) {
									$grpcount++;
									if ($activetime < $rank['time'] || $grpcount == count($cfg['rankup_definition']) && $value['nextup'] <= 0 && $cfg['stats_show_clients_in_highest_rank_switch'] == 1 || $grpcount == count($cfg['rankup_definition']) && $value['nextup'] == 0 && $adminlogin == 1) {
										echo list_rankup($cfg,$lang,$sqlhisgroup,$value,$adminlogin,$nation,$grpcount,$rank);
										break;
									}
								}
							}
						}
					} else {
						echo '<tr><td colspan="',$count_columns,'">',$lang['noentry'],'</td></tr>';
					}
					echo '</tbody></table>';
					if($user_pro_seite != "all") {
						echo pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring);
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