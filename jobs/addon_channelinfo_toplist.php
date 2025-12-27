<?PHP
function addon_channelinfo_toplist(&$addons_config,$ts3,$mysqlcon,$cfg,$dbname,$lang,&$db_cache) {
	$sqlexec = '';
	$nowtime = time();

	$smarty = new Smarty\Smarty;

	$smarty->setTemplateDir($GLOBALS['logpath'].'smarty/templates');
	$smarty->setCompileDir($GLOBALS['logpath'].'smarty/templates_c');
	$smarty->setCacheDir($GLOBALS['logpath'].'smarty/cache');
	$smarty->setConfigDir($GLOBALS['logpath'].'smarty/configs');

	if(isset($addons_config['channelinfo_toplist_active']['value']) && $addons_config['channelinfo_toplist_active']['value'] == '1') {
		if($addons_config['channelinfo_toplist_lastupdate']['value'] < ($nowtime - $addons_config['channelinfo_toplist_delay']['value'])) {

			switch($addons_config['channelinfo_toplist_modus']['value']) {
				case 1: $filter = "ORDER BY (`count_week`-`idle_week`)"; break;
				case 2: $filter = "ORDER BY `count_week`"; break;
				case 3: $filter = "ORDER BY (`count_month`-`idle_month`)"; break;
				case 4: $filter = "ORDER BY `count_month`"; break;
				case 5: $filter = "ORDER BY (`count`-`idle`)"; break;
				case 6: $filter = "ORDER BY `count`"; break;
				default: $filter = "ORDER BY (`count_week`-`idle_week`)";
			}
			
			$notinuuid = '';
			if($cfg['rankup_excepted_unique_client_id_list'] != NULL) {
				foreach($cfg['rankup_excepted_unique_client_id_list'] as $uuid => $value) {
					$notinuuid .= "'".$uuid."',";
				}
				$notinuuid = substr($notinuuid, 0, -1);
			} else {
				$notinuuid = "'0'";
			}

			$notingroup = '';
			$andnotgroup = '';
			if($cfg['rankup_excepted_group_id_list'] != NULL) {
				foreach($cfg['rankup_excepted_group_id_list'] as $group => $value) {
					$notingroup .= "'".$group."',";
					$andnotgroup .= " AND `user`.`cldgroup` NOT LIKE ('".$group.",%') AND `user`.`cldgroup` NOT LIKE ('%,".$group.",%') AND `user`.`cldgroup` NOT LIKE ('%,".$group."')";
				}
				$notingroup = substr($notingroup, 0, -1);
			} else {
				$notingroup = '0';
			}
			
			$filter = " AND `user`.`uuid` NOT IN ($notinuuid) AND `user`.`cldgroup` NOT IN ($notingroup) $andnotgroup ".$filter;
			#enter_logfile(2,'SQL: '."SELECT * FROM `$dbname`.`stats_user` INNER JOIN `$dbname`.`user` ON `user`.`uuid` = `stats_user`.`uuid` WHERE `removed`='0' {$filter} DESC LIMIT 10");

			if(($userdata = $mysqlcon->query("SELECT * FROM `$dbname`.`stats_user` INNER JOIN `$dbname`.`user` ON `user`.`uuid` = `stats_user`.`uuid` WHERE `removed`='0' {$filter} DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC)) === false) {
				enter_logfile(2,'addon_channelinfo1: '.print_r($mysqlcon->errorInfo(), true));
			}

			$smarty->assign('LAST_UPDATE_TIME',(DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s")));

			for ($nr = 0; $nr < 10; $nr++) {
				$smarty->assign('CLIENT_UNIQUE_IDENTIFIER_'.($nr + 1),$userdata[$nr]['uuid']);
				$smarty->assign('CLIENT_DATABASE_ID_'.($nr + 1),$userdata[$nr]['cldbid']);
				$smarty->assign('CLIENT_NICKNAME_'.($nr + 1),$userdata[$nr]['name']);
				
				if($userdata[$nr]['firstcon'] == 0) {
					$smarty->assign('CLIENT_CREATED_'.($nr + 1),$lang['unknown']);
				} else {
					$smarty->assign('CLIENT_CREATED_'.($nr + 1),date('Y-m-d H:i:s',$userdata[$nr]['firstcon']));
				}
				$smarty->assign('CLIENT_LAST_SEEN_'.($nr + 1),date('Y-m-d H:i:s',$userdata[$nr]['lastseen']));
				$smarty->assign('CLIENT_TOTAL_CONNECTIONS_'.($nr + 1),$userdata[$nr]['total_connections']);
				$smarty->assign('CLIENT_DESCRIPTION_'.($nr + 1),$userdata[$nr]['client_description']);
				$smarty->assign('CLIENT_CURRENT_CHANNEL_ID_'.($nr + 1),$userdata[$nr]['cid']);
				if(isset($db_cache['channel'][$userdata[$nr]['cid']]['channel_name'])) {
					$smarty->assign('CLIENT_CURRENT_CHANNEL_NAME_'.($nr + 1),substr($db_cache['channel'][$userdata[$nr]['cid']]['channel_name'],1,-1));
				} else {
					$smarty->assign('CLIENT_CURRENT_CHANNEL_NAME_'.($nr + 1),$lang['unknown']);
				}
				$smarty->assign('CLIENT_VERSION_'.($nr + 1),$userdata[$nr]['version']);
				$smarty->assign('CLIENT_PLATFORM_'.($nr + 1),$userdata[$nr]['platform']);
				$smarty->assign('CLIENT_COUNTRY_'.($nr + 1),$userdata[$nr]['nation']);
			
				if($userdata[$nr]['grpsince'] == 0) {
					$smarty->assign('CLIENT_LAST_RANKUP_TIME_'.($nr + 1),$lang['unknown']);
				} else {
					$smarty->assign('CLIENT_LAST_RANKUP_TIME_'.($nr + 1),date('Y-m-d H:i:s',$userdata[$nr]['grpsince']));
				}
				$smarty->assign('CLIENT_RANK_POSITION_'.($nr + 1),$userdata[$nr]['rank']);
				if($userdata[$nr]['online'] == 1) {
					$smarty->assign('CLIENT_ONLINE_STATUS_'.($nr + 1),$lang['stix0024']);
				} else {
					$smarty->assign('CLIENT_ONLINE_STATUS_'.($nr + 1),$lang['stix0025']);
				}
				
				$smarty->assign('CLIENT_NEXT_RANKUP_TIME_'.($nr + 1),$userdata[$nr]['nextup']);
				
				$smarty->assign('CLIENT_CURRENT_RANK_GROUP_ID_'.($nr + 1),$userdata[$nr]['grpid']);
				if(isset($db_cache['groups'][$userdata[$nr]['grpid']]['sgidname']) && $userdata[$nr]['grpid'] != 0) {
					$smarty->assign('CLIENT_CURRENT_RANK_GROUP_NAME_'.($nr + 1),substr($db_cache['groups'][$userdata[$nr]['grpid']]['sgidname'],1,-1));
				} else {
					$smarty->assign('CLIENT_CURRENT_RANK_GROUP_NAME_'.($nr + 1),'unknown_group');
				}
				if(isset($db_cache['groups'][$userdata[$nr]['grpid']]['iconid']) && isset($db_cache['groups'][$userdata[$nr]['grpid']]['ext']) && $userdata[$nr]['grpid'] != 0) {
					$smarty->assign('CLIENT_CURRENT_RANK_GROUP_ICON_URL_'.($nr + 1),'tsicons/'.$db_cache['groups'][$userdata[$nr]['grpid']]['iconid'].'.'.$db_cache['groups'][$userdata[$nr]['grpid']]['ext']);
				} else {
					$smarty->assign('CLIENT_CURRENT_RANK_GROUP_ICON_URL_'.($nr + 1),'file_not_found');
				}
				$active_all = round($userdata[$nr]['count']) - round($userdata[$nr]['idle']);
				$smarty->assign('CLIENT_ACTIVE_TIME_ALL_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".$active_all))->format($cfg['default_date_format'])));
				$smarty->assign('CLIENT_ONLINE_TIME_ALL_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".round($userdata[$nr]['count'])))->format($cfg['default_date_format'])));
				$smarty->assign('CLIENT_IDLE_TIME_ALL_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".round($userdata[$nr]['idle'])))->format($cfg['default_date_format'])));
				$active_week = round($userdata[$nr]['count_week']) - round($userdata[$nr]['idle_week']);
				$smarty->assign('CLIENT_ACTIVE_TIME_LAST_WEEK_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".$active_week))->format($cfg['default_date_format'])));
				$smarty->assign('CLIENT_ONLINE_TIME_LAST_WEEK_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".round($userdata[$nr]['count_week'])))->format($cfg['default_date_format'])));
				$smarty->assign('CLIENT_IDLE_TIME_LAST_WEEK_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".round($userdata[$nr]['idle_week'])))->format($cfg['default_date_format'])));
				$active_month = round($userdata[$nr]['count_month']) - round($userdata[$nr]['idle_month']);
				$smarty->assign('CLIENT_ACTIVE_TIME_LAST_MONTH_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".$active_month))->format($cfg['default_date_format'])));
				$smarty->assign('CLIENT_ONLINE_TIME_LAST_MONTH_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".round($userdata[$nr]['count_month'])))->format($cfg['default_date_format'])));
				$smarty->assign('CLIENT_IDLE_TIME_LAST_MONTH_'.($nr + 1),((new DateTime("@0"))->diff(new DateTime("@".round($userdata[$nr]['idle_month'])))->format($cfg['default_date_format'])));
			}

			try {
				$toplist_desc = $smarty->fetch('string:'.$addons_config['channelinfo_toplist_desc']['value']);
				if ($addons_config['channelinfo_toplist_lastdesc']['value'] != $toplist_desc) {
					try {
						$ts3->channelGetById($addons_config['channelinfo_toplist_channelid']['value'])->modify(array('cid='.$addons_config['channelinfo_toplist_channelid']['value'], 'channel_description='.$toplist_desc));
						$addons_config['channelinfo_toplist_lastdesc']['value'] = $toplist_desc;
						$addons_config['channelinfo_toplist_lastupdate']['value'] = $nowtime;
						$toplist_desc = $mysqlcon->quote($toplist_desc, ENT_QUOTES);
						$sqlexec .= "INSERT IGNORE INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('channelinfo_toplist_lastdesc',{$toplist_desc}),('channelinfo_toplist_lastupdate','{$nowtime}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);\n";
						enter_logfile(5,'  Addon: \'channelinfo_toplist\' writing new channelinfo toplist to channel description.');
					} catch (Exception $e) {
						enter_logfile(2,'addon_channelinfo2: ['.$e->getCode().']: '.$e->getMessage());
					}
				}
			} catch (Exception $e) {
				$errmsg = str_replace('"', '\'', $e->getMessage());
				$addons_config['channelinfo_toplist_lastupdate']['value'] = $nowtime;
				$sqlexec .= "INSERT IGNORE INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('channelinfo_toplist_lastupdate','{$nowtime}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);\n";
				enter_logfile(2,'  Addon: \'channelinfo_toplist\'; There might be a syntax error in your \'channel description\', which is defined in the webinterface! Error message: ['.$e->getCode().']: '.$errmsg);
			}
		}
	}

	unset($smarty);
	return $sqlexec;
}
?>