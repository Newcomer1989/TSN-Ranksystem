<?PHP
function check_db($mysqlcon,$lang,$cfg,$dbname) {
	$cfg['version_latest_available'] = '1.3.11';
	enter_logfile($cfg,5,"Check Ranksystem database for updates...");

	function check_double_cldbid($mysqlcon,$cfg,$dbname) {
		$maxcldbid = $mysqlcon->query("SELECT MAX(`cldbid`) AS `cldbid` FROM `$dbname`.`user`")->fetchAll(PDO::FETCH_ASSOC);
		$maxcldbid = $maxcldbid[0]['cldbid'] + 100000;
		do {
			$doublecldbidarr = $mysqlcon->query("SELECT `cldbid` FROM `$dbname`.`user` GROUP BY `cldbid` HAVING COUNT(`cldbid`) > 1")->fetchAll(PDO::FETCH_ASSOC);
			if($doublecldbidarr != NULL) {
				$doublecldbid = '';
				foreach($doublecldbidarr as $row) {
					$doublecldbid .= $row['cldbid'].',';
				}
				$doublecldbid = substr($doublecldbid,0,-1);
				$updatecldbid = $mysqlcon->query("SELECT `cldbid`,`uuid`,`name`,`lastseen` FROM `$dbname`.`user` WHERE `cldbid` in ({$doublecldbid})")->fetchAll(PDO::FETCH_ASSOC);
				foreach($updatecldbid as $row) {
					if($mysqlcon->exec("UPDATE `$dbname`.`user` SET `cldbid`='{$maxcldbid}' WHERE `uuid`='{$row['uuid']}'") === false) {
						enter_logfile($cfg,1,"      Repair double client-database-ID failed (".$row['uuid']."): ".print_r($mysqlcon->errorInfo(), true));
					} else {
						enter_logfile($cfg,4,"      Repair double client-database-ID for ".$row['name']." (".$row['uuid']."); old ID ".$row['cldbid']."; set virtual ID $maxcldbid");
					}
					$maxcldbid++;
				}
			}
		} while ($doublecldbidarr != NULL);
	}

	function set_new_version($mysqlcon,$cfg,$dbname) {
		if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('version_current_using','{$cfg['version_latest_available']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) {
			enter_logfile($cfg,1,"  An error happens due updating the Ranksystem Database:".print_r($mysqlcon->errorInfo(), true));
			shutdown($mysqlcon,$cfg,1,"  Check the database connection and properties in other/dbconfig.php and check also the database permissions.");
		} else {
			$cfg['version_current_using'] = $cfg['version_latest_available'];
			enter_logfile($cfg,4,"  Database successfully updated!");
			return $cfg;
		}
	}

	function old_files($cfg) {
		$del_folder = array('icons/','libs/ts3_lib/Adapter/Blacklist/','libs/ts3_lib/Adapter/TSDNS/','libs/ts3_lib/Adapter/Update/','libs/fonts/');
		$del_files = array('install.php','libs/combined_stats.css','libs/combined_stats.js','webinterface/admin.php','libs/ts3_lib/Adapter/Blacklist/Exception.php','libs/ts3_lib/Adapter/TSDNS/Exception.php','libs/ts3_lib/Adapter/Update/Exception.php','libs/ts3_lib/Adapter/Blacklist.php','libs/ts3_lib/Adapter/TSDNS.php','libs/ts3_lib/Adapter/Update.php','languages/core_ar.php','languages/core_cz.php','languages/core_de.php','languages/core_en.php','languages/core_es.php','languages/core_fr.php','languages/core_it.php','languages/core_nl.php','languages/core_pl.php','languages/core_pt.php','languages/core_ro.php','languages/core_ru.php','webinterface/nav.php');
		function rmdir_recursive($folder,$cfg) {
			foreach(scandir($folder) as $file) {
				if ('.' === $file || '..' === $file) continue;
				if (is_dir($folder.$file)) {
					rmdir_recursive($folder.$file);
				} else {
					if(!unlink($folder.$file)) {
						enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: ".$folder.$file);
					}
				}
			}
			if(!rmdir($folder)) {
				enter_logfile($cfg,4,"Unnecessary folder, please delete it from your webserver: ".$folder);
			}
		}
				
		foreach($del_folder as $folder) {
			if(is_dir(substr(__DIR__,0,-4).$folder)) {
				rmdir_recursive(substr(__DIR__,0,-4).$folder,$cfg);
			}
		}
		foreach($del_files as $file) {
			if(is_file(substr(__DIR__,0,-4).$file)) {
				if(!unlink(substr(__DIR__,0,-4).$file)) {
					enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: ".$file);
				}
			}
		}
	}

	function check_writable($cfg,$mysqlcon) {
		enter_logfile($cfg,5,"  Check files permissions...");
		$counterr=0;
		try {
			$scandir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(substr(__DIR__,0,-4)));
			$files = array(); 
			foreach ($scandir as $object) {
				if(!strstr($object, '/.') && !strstr($object, '\.')) {
					if (!$object->isDir()) {
						if(!is_writable($object->getPathname())) {
							enter_logfile($cfg,3,"    File is not writeable ".$object);
							$counterr++;
						}
					} else {
						if(!is_writable($object->getPathname())) {
							enter_logfile($cfg,3,"    Folder is not writeable ".$object);
							$counterr++;
						}
					}
				}
			}
		} catch (Exception $e) {
			shutdown($mysqlcon,$cfg,1,"File Permissions Error: ".$e->getCode()." ".$e->getMessage());
			enter_logfile($cfg,3,"File Permissions Error: ".$e->getCode()." ".$e->getMessage());
		}
		if($counterr!=0) {
			shutdown($mysqlcon,$cfg,1,"Wrong file/folder permissions (see messages before!)! Check and correct the owner (chown) and access permissions (chmod)!");
		} else {
			enter_logfile($cfg,5,"  Check files permissions [done]");
		}
	}
	
	check_writable($cfg,$mysqlcon);
	old_files($cfg);
	check_double_cldbid($mysqlcon,$cfg,$dbname);

	if($cfg['version_current_using'] == $cfg['version_latest_available']) {
		enter_logfile($cfg,5,"  No newer version detected; Database check finished.");
	} else {
		enter_logfile($cfg,4,"  Update the Ranksystem Database to new version...");

		if(version_compare($cfg['version_current_using'], '1.3.1', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('reset_user_time', '0'),('reset_user_delete', '0'),('reset_group_withdraw', '0'),('reset_webspace_cache', '0'),('reset_usage_graph', '0'),('reset_stop_after', '0');") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.1] Added new job_check values.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.4', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_show_maxclientsline_switch', 0)") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.4] Added new config values.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.4] Adjusted table groups successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.4] Adjusted table user successfully.");
			}			
			
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='".time()."' WHERE `job_name`='last_update';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.4] Stored timestamp of last update successfully.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.7', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_fresh_installation', '0'),('webinterface_advanced_mode', '1')") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.7] Added new config values.");
			}
			
			if($mysqlcon->exec("DELETE FROM `$dbname`.`groups`;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.7] Empty table groups successfully.");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` ADD COLUMN `sortid` int(10) NOT NULL default '0', ADD COLUMN `type` tinyint(1) NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.7] Adjusted table groups successfully.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.8', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_api_keys', '');") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Added new config values.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `lastseen` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `boosttime` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `firstcon` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `grpsince` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `rank` smallint(5) UNSIGNED NOT NULL default '65535', MODIFY COLUMN `nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table user successfully (part 1).");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `count` DECIMAL(14,3) NOT NULL default '0', MODIFY COLUMN `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, MODIFY COLUMN `idle` DECIMAL(14,3) NOT NULL default '0', MODIFY COLUMN `platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, MODIFY COLUMN `nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, MODIFY COLUMN `version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table user successfully (part 2).");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_snapshot` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table user_snapshot successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `base64hash` char(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `count_week` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `count_month` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `idle_week` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `idle_month` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `active_week` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `active_month` mediumint(8) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table stats_user (part 1) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` DROP COLUMN `rank`, DROP COLUMN `battles_total`, DROP COLUMN `battles_won`, DROP COLUMN `battles_lost`, DROP COLUMN `achiev_time`, DROP COLUMN `achiev_connects`, DROP COLUMN `achiev_battles`, DROP COLUMN `achiev_time_perc`, DROP COLUMN `achiev_connects_perc`, DROP COLUMN `achiev_battles_perc`;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table stats_user (part 2) successfully.");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `base64hash` char(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table stats_user (part 3) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`server_usage` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `clients` smallint(5) UNSIGNED NOT NULL default '0', MODIFY COLUMN `channel` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table server_usage successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`job_check` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table job_check successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`admin_addtime` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table admin_addtime successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_iphash` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table user_iphash successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_versions` MODIFY COLUMN `count` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table stats_versions successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_platforms` MODIFY COLUMN `count` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table stats_platforms successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_nations` MODIFY COLUMN `nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `count` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table stats_nations successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` ADD COLUMN `ext` char(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table groups (part 1) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgid` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `icondate` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table groups (part 2) successfully.");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table groups (part 3) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`csrf_token` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table csrf_token successfully (part 1).");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`csrf_token` MODIFY COLUMN `sessionid` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table csrf_token successfully (part 2).");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`addon_assign_groups` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Adjusted table addon_assign_groups successfully.");
			}

			if($mysqlcon->exec("DELETE FROM `$dbname`.`groups`;") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.8] Empty table groups successfully.");
			}
			
			if($mysqlcon->exec("UPDATE `$dbname`.`user` SET `idle`=0 WHERE `idle`<0; UPDATE `$dbname`.`user` SET `count`=`idle` WHERE `count`<0;") === false) { } else {
				enter_logfile($cfg,6,"    [1.3.8] Fix for negative values in table user.");
			}
				
			if($mysqlcon->exec("UPDATE `$dbname`.`user_snapshot` SET `idle`=0 WHERE `idle`<0; UPDATE `$dbname`.`user_snapshot` SET `count`=`idle` WHERE `count`<0;") === false) { } else {
				enter_logfile($cfg,6,"    [1.3.8] Fix for negative values in table user_snapshot.");
			}
			
			$check_snapshot_convert = $mysqlcon->query("DESC `$dbname`.`user_snapshot`")->fetchAll(PDO::FETCH_ASSOC);
			if($check_snapshot_convert[0]['Field'] == 'timestamp' && $check_snapshot_convert[0]['Field'] != 'id') {
				
				if($mysqlcon->exec("DELETE `a` FROM `$dbname`.`user_snapshot` AS `a` CROSS JOIN(SELECT DISTINCT(`timestamp`) FROM `$dbname`.`user_snapshot` ORDER BY `timestamp` DESC LIMIT 1000 OFFSET 121) AS `b` WHERE `a`.`timestamp`=`b`.`timestamp`;") === false) { } else {
					enter_logfile($cfg,4,"    [1.3.8] Deleted old values out of the table user_snapshot.");
				}
				
				if($mysqlcon->exec("CREATE TABLE `$dbname`.`user_snapshot2` (`id` tinyint(3) UNSIGNED NOT NULL default '0',`cldbid` int(10) UNSIGNED NOT NULL default '0',`count` int(10) UNSIGNED NOT NULL default '0',`idle` int(10) UNSIGNED NOT NULL default '0',PRIMARY KEY (`id`,`cldbid`));") === false) { } else {
					enter_logfile($cfg,4,"    [1.3.8] Created new table user_snapshot2 successfully.");
					enter_logfile($cfg,4,"    [1.3.8]   Beginn with converting values.. This could take a while! Please do NOT stop the Bot!");
				}

				$maxvalues = $mysqlcon->query("SELECT COUNT(*) FROM `$dbname`.`user_snapshot`;")->fetch();
				$timestamps = $mysqlcon->query("SELECT DISTINCT(`timestamp`) FROM `$dbname`.`user_snapshot` ORDER BY `timestamp` ASC;")->fetchAll(PDO::FETCH_ASSOC);
				$user = $mysqlcon->query("SELECT `uuid`,`cldbid` FROM `$dbname`.`user`;")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);

				$ts2id = array();
				$count = 0;
				foreach($timestamps as $tstamp) {
					$count++;
					$ts2id[$tstamp['timestamp']] = $count;
				}

				$loops = $maxvalues[0] / 50000;
				for ($i = 0; $i <= $loops; $i++) {
					$offset = $i * 50000;
					$snapshot = $mysqlcon->query("SELECT * FROM `$dbname`.`user_snapshot` LIMIT 50000 OFFSET {$offset};")->fetchAll(PDO::FETCH_ASSOC);
					
					$sqlinsertvalues = '';
					$count = 0;
					foreach($snapshot as $entry) {
						if(isset($user[$entry['uuid']]) && $user[$entry['uuid']]['cldbid'] != NULL) {
							$snapshot[$count]['id'] = $ts2id[$entry['timestamp']];
							$snapshot[$count]['cldbid'] = $user[$entry['uuid']]['cldbid'];
							$sqlinsertvalues .= "(".$ts2id[$entry['timestamp']].",".$user[$entry['uuid']]['cldbid'].",".round($entry['count']).",".round($entry['idle'])."),";
							$count++;
						}
					}

					$sqlinsertvalues = substr($sqlinsertvalues, 0, -1);
					if ($mysqlcon->exec("INSERT INTO `$dbname`.`user_snapshot2` (`id`,`cldbid`,`count`,`idle`) VALUES {$sqlinsertvalues};") === false) {
						enter_logfile($cfg,1,"  Insert failed: ".print_r($mysqlcon->errorInfo(), true));
					}
					unset($snapshot, $sqlinsertvalues);
					if (($offset + 50000) > $maxvalues[0]) {
						$convertedvalus = $maxvalues[0];
					} else {
						$convertedvalus = $offset + 50000;
					}
					enter_logfile($cfg,4,"    [1.3.8]     Converted ".$convertedvalus." out of ".$maxvalues[0]." values.");
				}
				
				enter_logfile($cfg,4,"    [1.3.8]   Finished converting values");
				
				$lastsnapshot = $mysqlcon->query("SELECT MAX(`timestamp`) AS `timestamp` FROM `$dbname`.`user_snapshot`")->fetchAll(PDO::FETCH_ASSOC);
				
				if($mysqlcon->exec("DROP TABLE `$dbname`.`user_snapshot`;") === false) { } else {
					enter_logfile($cfg,4,"    [1.3.8] Dropped old table user_snapshot successfully.");
				}
				
				if($mysqlcon->exec("RENAME TABLE `$dbname`.`user_snapshot2` TO `$dbname`.`user_snapshot`;") === false) { } else {
					enter_logfile($cfg,4,"    [1.3.8] Renamed table user_snapshot2 to user_snapshot successfully.");
				}

				$currentid = count($timestamps);

				if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('last_snapshot_id', '{$currentid}'),('last_snapshot_time', '{$lastsnapshot[0]['timestamp']}');") === false) { } else {
					enter_logfile($cfg,4,"    [1.3.8] Added new job_check values (part 1).");
				}
				
				if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('update_groups', '0');") === false) { } else {
					enter_logfile($cfg,4,"    [1.3.8] Added new job_check values (part 2).");
				}
				
			} else {
				enter_logfile($cfg,4,"    [1.3.8] Converting user_snapshot already done. Did not started it again.");
			}
			
			foreach(scandir(substr(dirname(__FILE__),0,-4).'tsicons/') as $file) {
				if (in_array($file, array('.','..','check.png','placeholder.png','rs.png','servericon.png','100.png','200.png','300.png','500.png','600.png'))) continue;
				if(!unlink(substr(dirname(__FILE__),0,-4).'tsicons/'.$file)) {
					enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: tsicons/".$file);
				}
			}

			check_double_cldbid($mysqlcon,$cfg,$dbname);
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.9', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('get_avatars', '0'),('calc_donut_chars', '0'),('reload_trigger', '0');") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.9] Added new job_check values.");
			}
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.10', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` ADD COLUMN `last_calculated` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.10] Added new stats_user values.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `total_connections` MEDIUMINT(8) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.10] Adjusted table stats_user successfully.");
			}
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.11', '<')) {
			if($mysqlcon->exec("DELETE FROM `$dbname`.`admin_addtime`;") === false) { }
			if($mysqlcon->exec("DELETE FROM `$dbname`.`addon_assign_groups`;") === false) { }
			
			if($mysqlcon->exec("INSERT INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('assign_groups_excepted_groupids','');") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.11] Adjusted table addons_config successfully.");
			}

			if($mysqlcon->exec("CREATE INDEX `snapshot_id` ON `$dbname`.`user_snapshot` (`id`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `snapshot_cldbid` ON `$dbname`.`user_snapshot` (`cldbid`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `serverusage_timestamp` ON `$dbname`.`server_usage` (`timestamp`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_version` ON `$dbname`.`user` (`version`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_cldbid` ON `$dbname`.`user` (`cldbid` ASC,`uuid`,`rank`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_online` ON `$dbname`.`user` (`online`,`lastseen`)") === false) { }
		}

		if(version_compare($cfg['version_current_using'], '1.3.11', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('imprint_enabled', '0'),('imprint_address', 'Max Mustermann<br>Musterstraße 13<br>05172 Musterhausen<br>Germany'),('imprint_email', 'info@example.com'),('imprint_phone', '+49 171 1234567'),('imprint_notes', NULL),('imprint_privacy-policy', 'Add your own privacy policy here. (editable in the webinterface)');") === false) { } else {
				enter_logfile($cfg,4,"    [1.3.11] Added new imprint values.");
			}
		}
		$cfg = set_new_version($mysqlcon,$cfg,$dbname);
	}
	enter_logfile($cfg,5,"Check Ranksystem database for updates [done]");
	return $cfg;
}
?>