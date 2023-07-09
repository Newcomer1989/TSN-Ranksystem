<?PHP
function check_db($mysqlcon,$lang,&$cfg,$dbname) {
	$cfg['version_latest_available'] = '1.3.22';
	enter_logfile(5,"Check Ranksystem database for updates...");

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
						enter_logfile(1,"      Repair double client-database-ID failed (".$row['uuid']."): ".print_r($mysqlcon->errorInfo(), true));
					} else {
						enter_logfile(4,"      Repair double client-database-ID for ".$row['name']." (".$row['uuid']."); old ID ".$row['cldbid']."; set virtual ID $maxcldbid");
					}
					$maxcldbid++;
				}
			}
		} while ($doublecldbidarr != NULL);
	}

	function set_new_version($mysqlcon,$cfg,$dbname) {
		if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('version_current_using','{$cfg['version_latest_available']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) {
			enter_logfile(1,"  An error happens due updating the Ranksystem Database:".print_r($mysqlcon->errorInfo(), true));
			shutdown($mysqlcon,1,"  Check the database connection and properties in configs/dbconfig.php and check also the database permissions.");
		} else {
			$cfg['version_current_using'] = $cfg['version_latest_available'];
			enter_logfile(4,"  Database successfully updated!");
			return $cfg;
		}
	}

	function old_files($cfg) {
		$del_folder = array('icons/','libs/ts3_lib/Adapter/Blacklist/','libs/ts3_lib/Adapter/TSDNS/','libs/ts3_lib/Adapter/Update/','libs/fonts/');
		$del_files = array('install.php','libs/combined_stats.css','libs/combined_stats.js','webinterface/admin.php','libs/ts3_lib/Adapter/Blacklist/Exception.php','libs/ts3_lib/Adapter/TSDNS/Exception.php','libs/ts3_lib/Adapter/Update/Exception.php','libs/ts3_lib/Adapter/Blacklist.php','libs/ts3_lib/Adapter/TSDNS.php','libs/ts3_lib/Adapter/Update.php','languages/core_ar.php','languages/core_cz.php','languages/core_de.php','languages/core_en.php','languages/core_es.php','languages/core_fr.php','languages/core_it.php','languages/core_nl.php','languages/core_pl.php','languages/core_pt.php','languages/core_ro.php','languages/core_ru.php','webinterface/nav.php','stats/nav.php','other/session.php');
		function rmdir_recursive($folder,$cfg) {
			foreach(scandir($folder) as $file) {
				if ('.' === $file || '..' === $file) continue;
				if (is_dir($folder.$file)) {
					rmdir_recursive($folder.$file);
				} else {
					if(!unlink($folder.$file)) {
						enter_logfile(4,"Unnecessary file, please delete it from your webserver: ".$folder.$file);
					}
				}
			}
			if(!rmdir($folder)) {
				enter_logfile(4,"Unnecessary folder, please delete it from your webserver: ".$folder);
			}
		}
				
		foreach($del_folder as $folder) {
			if(is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.$folder)) {
				rmdir_recursive(dirname(__DIR__).DIRECTORY_SEPARATOR.$folder,$cfg);
			}
		}
		foreach($del_files as $file) {
			if(is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.$file)) {
				if(!unlink(dirname(__DIR__).DIRECTORY_SEPARATOR.$file)) {
					enter_logfile(4,"Unnecessary file, please delete it from your webserver: ".$file);
				}
			}
		}
	}

	function check_writable($cfg,$mysqlcon) {
		enter_logfile(5,"  Check files permissions...");
		$counterr=0;
		try {
			$scandir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__)));
			$files = array(); 
			foreach ($scandir as $object) {
				if(!strstr($object, '/.') && !strstr($object, '\.')) {
					if (!$object->isDir()) {
						if(!is_writable($object->getPathname())) {
							enter_logfile(3,"    File is not writeable ".$object);
							$counterr++;
						}
					} else {
						if(!is_writable($object->getPathname())) {
							enter_logfile(3,"    Folder is not writeable ".$object);
							$counterr++;
						}
					}
				}
			}
		} catch (Exception $e) {
			shutdown($mysqlcon,1,"File Permissions Error: ".$e->getCode()." ".$e->getMessage());
			enter_logfile(3,"File Permissions Error: ".$e->getCode()." ".$e->getMessage());
		}
		if($counterr!=0) {
			shutdown($mysqlcon,1,"Wrong file/folder permissions (see messages before!)! Check and correct the owner (chown) and access permissions (chmod)!");
		} else {
			enter_logfile(5,"  Check files permissions [done]");
		}
	}
	
	check_writable($cfg,$mysqlcon);
	old_files($cfg);
	check_double_cldbid($mysqlcon,$cfg,$dbname);

	if($cfg['version_current_using'] == $cfg['version_latest_available']) {
		enter_logfile(5,"  No newer version detected; Database check finished.");
	} else {
		enter_logfile(4,"  Update the Ranksystem Database to new version...");
		
		if(version_compare($cfg['version_current_using'], '1.3.0', '<')) {
			shutdown($mysqlcon,1,"Your Ranksystem version is below 1.3.0. Please download the current version from the official page and install a new Ranksystem instead or contact the Ranksystem support.");
		}

		if(version_compare($cfg['version_current_using'], '1.3.1', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('reset_user_time', '0'),('reset_user_delete', '0'),('reset_group_withdraw', '0'),('reset_webspace_cache', '0'),('reset_usage_graph', '0'),('reset_stop_after', '0');") === false) { } else {
				enter_logfile(4,"    [1.3.1] Added new job_check values.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.4', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_show_maxclientsline_switch', 0)") === false) { } else {
				enter_logfile(4,"    [1.3.4] Added new config values.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.4] Adjusted table groups successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.4] Adjusted table user successfully.");
			}			
			
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='".time()."' WHERE `job_name`='last_update';") === false) { } else {
				enter_logfile(4,"    [1.3.4] Stored timestamp of last update successfully.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.7', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_fresh_installation', '0'),('webinterface_advanced_mode', '1')") === false) { } else {
				enter_logfile(4,"    [1.3.7] Added new config values.");
			}
			
			if($mysqlcon->exec("DELETE FROM `$dbname`.`groups`;") === false) { } else {
				enter_logfile(4,"    [1.3.7] Empty table groups successfully.");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` ADD COLUMN `sortid` int(10) NOT NULL default '0', ADD COLUMN `type` tinyint(1) NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.7] Adjusted table groups successfully.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.8', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_api_keys', '');") === false) { } else {
				enter_logfile(4,"    [1.3.8] Added new config values.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `lastseen` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `boosttime` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `firstcon` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `grpsince` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `rank` smallint(5) UNSIGNED NOT NULL default '65535', MODIFY COLUMN `nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table user successfully (part 1).");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `count` DECIMAL(14,3) NOT NULL default '0', MODIFY COLUMN `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, MODIFY COLUMN `idle` DECIMAL(14,3) NOT NULL default '0', MODIFY COLUMN `platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, MODIFY COLUMN `nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, MODIFY COLUMN `version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table user successfully (part 2).");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_snapshot` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table user_snapshot successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `base64hash` char(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `count_week` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `count_month` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `idle_week` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `idle_month` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `active_week` mediumint(8) UNSIGNED NOT NULL default '0', MODIFY COLUMN `active_month` mediumint(8) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table stats_user (part 1) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` DROP COLUMN `rank`, DROP COLUMN `battles_total`, DROP COLUMN `battles_won`, DROP COLUMN `battles_lost`, DROP COLUMN `achiev_time`, DROP COLUMN `achiev_connects`, DROP COLUMN `achiev_battles`, DROP COLUMN `achiev_time_perc`, DROP COLUMN `achiev_connects_perc`, DROP COLUMN `achiev_battles_perc`;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table stats_user (part 2) successfully.");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `base64hash` char(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table stats_user (part 3) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`server_usage` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `clients` smallint(5) UNSIGNED NOT NULL default '0', MODIFY COLUMN `channel` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table server_usage successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`job_check` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table job_check successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`admin_addtime` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table admin_addtime successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_iphash` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table user_iphash successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_versions` MODIFY COLUMN `count` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table stats_versions successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_platforms` MODIFY COLUMN `count` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table stats_platforms successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_nations` MODIFY COLUMN `nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `count` smallint(5) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table stats_nations successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` ADD COLUMN `ext` char(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table groups (part 1) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgid` int(10) UNSIGNED NOT NULL default '0', MODIFY COLUMN `icondate` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table groups (part 2) successfully.");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table groups (part 3) successfully.");
			}

			if($mysqlcon->exec("ALTER TABLE `$dbname`.`csrf_token` MODIFY COLUMN `timestamp` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table csrf_token successfully (part 1).");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`csrf_token` MODIFY COLUMN `sessionid` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table csrf_token successfully (part 2).");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`addon_assign_groups` MODIFY COLUMN `uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Adjusted table addon_assign_groups successfully.");
			}

			if($mysqlcon->exec("DELETE FROM `$dbname`.`groups`;") === false) { } else {
				enter_logfile(4,"    [1.3.8] Empty table groups successfully.");
			}
			
			if($mysqlcon->exec("UPDATE `$dbname`.`user` SET `idle`=0 WHERE `idle`<0; UPDATE `$dbname`.`user` SET `count`=`idle` WHERE `count`<0;") === false) { } else {
				enter_logfile(6,"    [1.3.8] Fix for negative values in table user.");
			}
				
			if($mysqlcon->exec("UPDATE `$dbname`.`user_snapshot` SET `idle`=0 WHERE `idle`<0; UPDATE `$dbname`.`user_snapshot` SET `count`=`idle` WHERE `count`<0;") === false) { } else {
				enter_logfile(6,"    [1.3.8] Fix for negative values in table user_snapshot.");
			}
			
			$check_snapshot_convert = $mysqlcon->query("DESC `$dbname`.`user_snapshot`")->fetchAll(PDO::FETCH_ASSOC);
			if($check_snapshot_convert[0]['Field'] == 'timestamp' && $check_snapshot_convert[0]['Field'] != 'id') {
				
				if($mysqlcon->exec("DELETE `a` FROM `$dbname`.`user_snapshot` AS `a` CROSS JOIN(SELECT DISTINCT(`timestamp`) FROM `$dbname`.`user_snapshot` ORDER BY `timestamp` DESC LIMIT 1000 OFFSET 121) AS `b` WHERE `a`.`timestamp`=`b`.`timestamp`;") === false) { } else {
					enter_logfile(4,"    [1.3.8] Deleted old values out of the table user_snapshot.");
				}
				
				if($mysqlcon->exec("CREATE TABLE `$dbname`.`user_snapshot2` (`id` tinyint(3) UNSIGNED NOT NULL default '0',`cldbid` int(10) UNSIGNED NOT NULL default '0',`count` int(10) UNSIGNED NOT NULL default '0',`idle` int(10) UNSIGNED NOT NULL default '0',PRIMARY KEY (`id`,`cldbid`));") === false) { } else {
					enter_logfile(4,"    [1.3.8] Created new table user_snapshot2 successfully.");
					enter_logfile(4,"    [1.3.8]   Beginn with converting values.. This could take a while! Please do NOT stop the Bot!");
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
					if ($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`user_snapshot2` (`id`,`cldbid`,`count`,`idle`) VALUES {$sqlinsertvalues};") === false) {
						enter_logfile(1,"  Insert failed: ".print_r($mysqlcon->errorInfo(), true));
					}
					unset($snapshot, $sqlinsertvalues);
					if (($offset + 50000) > $maxvalues[0]) {
						$convertedvalus = $maxvalues[0];
					} else {
						$convertedvalus = $offset + 50000;
					}
					enter_logfile(4,"    [1.3.8]     Converted ".$convertedvalus." out of ".$maxvalues[0]." values.");
				}
				
				enter_logfile(4,"    [1.3.8]   Finished converting values");
				
				$lastsnapshot = $mysqlcon->query("SELECT MAX(`timestamp`) AS `timestamp` FROM `$dbname`.`user_snapshot`")->fetchAll(PDO::FETCH_ASSOC);
				
				if($mysqlcon->exec("DROP TABLE `$dbname`.`user_snapshot`;") === false) { } else {
					enter_logfile(4,"    [1.3.8] Dropped old table user_snapshot successfully.");
				}
				
				if($mysqlcon->exec("RENAME TABLE `$dbname`.`user_snapshot2` TO `$dbname`.`user_snapshot`;") === false) { } else {
					enter_logfile(4,"    [1.3.8] Renamed table user_snapshot2 to user_snapshot successfully.");
				}

				$currentid = count($timestamps);

				if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('last_snapshot_id', '{$currentid}'),('last_snapshot_time', '{$lastsnapshot[0]['timestamp']}');") === false) { } else {
					enter_logfile(4,"    [1.3.8] Added new job_check values (part 1).");
				}
				
				if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('update_groups', '0');") === false) { } else {
					enter_logfile(4,"    [1.3.8] Added new job_check values (part 2).");
				}
				
			} else {
				enter_logfile(4,"    [1.3.8] Converting user_snapshot already done. Did not started it again.");
			}
			
			foreach(scandir(substr(dirname(__FILE__),0,-4).'tsicons/') as $file) {
				if (in_array($file, array('.','..','check.png','placeholder.png','rs.png','servericon.png','100.png','200.png','300.png','500.png','600.png'))) continue;
				if(!unlink(substr(dirname(__FILE__),0,-4).'tsicons/'.$file)) {
					enter_logfile(4,"Unnecessary file, please delete it from your webserver: tsicons/".$file);
				}
			}

			check_double_cldbid($mysqlcon,$cfg,$dbname);
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.9', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('get_avatars', '0'),('calc_donut_chars', '0'),('reload_trigger', '0');") === false) { } else {
				enter_logfile(4,"    [1.3.9] Added new job_check values.");
			}
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.10', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` ADD COLUMN `last_calculated` int(10) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.10] Added new stats_user values.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `total_connections` MEDIUMINT(8) UNSIGNED NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.10] Adjusted table stats_user successfully.");
			}
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.11', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('assign_groups_excepted_groupids','');") === false) { } else {
				enter_logfile(4,"    [1.3.11] Adjusted table addons_config successfully.");
			}

			if($mysqlcon->exec("CREATE INDEX `snapshot_id` ON `$dbname`.`user_snapshot` (`id`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `snapshot_cldbid` ON `$dbname`.`user_snapshot` (`cldbid`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `serverusage_timestamp` ON `$dbname`.`server_usage` (`timestamp`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_version` ON `$dbname`.`user` (`version`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_cldbid` ON `$dbname`.`user` (`cldbid` ASC,`uuid`,`rank`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_online` ON `$dbname`.`user` (`online`,`lastseen`)") === false) { }
		}

		if(version_compare($cfg['version_current_using'], '1.3.12', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_imprint_switch', '0'),('stats_imprint_address', 'Max Mustermann<br>Musterstraße 13<br>05172 Musterhausen<br>Germany'),('stats_imprint_address_url', 'https://site.url/imprint/'), ('stats_imprint_email', 'info@example.com'),('stats_imprint_phone', '+49 171 1234567'),('stats_imprint_notes', NULL),('stats_imprint_privacypolicy', 'Add your own privacy policy here. (editable in the webinterface)'),('stats_imprint_privacypolicy_url', 'https://site.url/privacy/');") === false) { } else {
				enter_logfile(4,"    [1.3.12] Added new imprint values.");
			}
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.13', '<')) {
			if($mysqlcon->exec("UPDATE `$dbname`.`user` SET `idle`=0 WHERE `idle`<0; UPDATE `$dbname`.`user` SET `count`=`idle` WHERE `count`<0; UPDATE `$dbname`.`user` SET `count`=`idle` WHERE `count`<`idle`;") === false) { }
			if($mysqlcon->exec("UPDATE `$dbname`.`user_snapshot` SET `idle`=0 WHERE `idle`<0; UPDATE `$dbname`.`user_snapshot` SET `count`=`idle` WHERE `count`<0; UPDATE `$dbname`.`user_snapshot` SET `count`=`idle` WHERE `count`<`idle`;") === false) { }

			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('database_export', '0'),('update_groups', '0') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`);") === false) { } else {
				enter_logfile(4,"    [1.3.13] Added new job_check values.");
			}

			try {
				if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_fresh_installation', '0'),('stats_column_nation_switch', '0'),('stats_column_version_switch', '0'),('stats_column_platform_switch', '0');") === false) { }
			} catch (Exception $e) { }
			
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('default_session_sametime', 'Strict'),('default_header_origin', ''),('default_header_xss', '1; mode=block'),('default_header_contenttyp', '1'),('default_header_frame', '') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);") === false) { } else {
				enter_logfile(4,"    [1.3.13] Added new cfg_params values.");
			}

			if($mysqlcon->exec("UPDATE `$dbname`.`user` SET `nation`='XX' WHERE `nation`='';") === false) { } else {
				enter_logfile(4,"    [1.3.13] Updated table user.");
			}

			try {
				if($mysqlcon->exec("DROP INDEX `snapshot_id` ON `$dbname`.`user_snapshot` (`id`)") === false) { } else {
					enter_logfile(4,"    [1.3.13] Dropped unneeded Index snapshot_id on table user_snapshot.");
				}
				if($mysqlcon->exec("DROP INDEX `snapshot_cldbid` ON `$dbname`.`user_snapshot` (`cldbid`)") === false) { } else {
					enter_logfile(4,"    [1.3.13] Dropped unneeded Index snapshot_cldbid on table user_snapshot.");
				}
			} catch (Exception $e) { }
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.14', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_column_default_sort_2', 'rank'),('stats_column_default_order_2', 'asc');") === false) { } else {
				enter_logfile(4,"    [1.3.14] Added new cfg_params values.");
			}

			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('assign_groups_name','');") === false) { } else {
				enter_logfile(4,"    [1.3.14] Added new addons_config values.");
			}
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.16', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('calc_user_removed', '0') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`);") === false) { } else {
				enter_logfile(4,"    [1.3.16] Added new job_check values.");
			}
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.18', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('update_channel', '0') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`);") === false) { } else {
				enter_logfile(4,"    [1.3.18] Added new job_check values.");
			}
			
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('default_cmdline_sec_switch', '1');") === false) { } else {
				enter_logfile(4,"    [1.3.18] Added new cfg_params values.");
			}

			if($mysqlcon->exec("CREATE TABLE IF NOT EXISTS `$dbname`.`channel` (`cid` int(10) UNSIGNED NOT NULL default '0',`pid` int(10) UNSIGNED NOT NULL default '0',`channel_order` int(10) UNSIGNED NOT NULL default '0',`channel_name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,PRIMARY KEY (`cid`));") === false) { } else {
				enter_logfile(4,"    [1.3.18] Created new table channel successfully.");
			}
			
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`addons_config` MODIFY COLUMN `value` varchar(16000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.18] Adjusted table addons_config successfully.");
			}

			$channelinfo_desc = $mysqlcon->quote('[CENTER][B][SIZE=15]User Toplist (last week)[/SIZE][/B][/CENTER]

[SIZE=11][B]1st[/B]     [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_1}]{$CLIENT_NICKNAME_1}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_1} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_1}]{$CLIENT_CURRENT_CHANNEL_NAME_1}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_1|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_1}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_1}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_1}[/SIZE]

[SIZE=11][B]2nd[/B]    [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_2}]{$CLIENT_NICKNAME_2}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_2} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_2}]{$CLIENT_CURRENT_CHANNEL_NAME_2}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_2|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_2}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_2}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_2}[/SIZE]

[SIZE=11][B]3rd[/B]     [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_3}]{$CLIENT_NICKNAME_3}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_3} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_3}]{$CLIENT_CURRENT_CHANNEL_NAME_3}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_3|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_3}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_3}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_3}[/SIZE]

[SIZE=10][B]4th[/B]       [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_4}]{$CLIENT_NICKNAME_4}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_4} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_4}]{$CLIENT_CURRENT_CHANNEL_NAME_4}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_4|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_4}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_4}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_4}[/SIZE]

[SIZE=10][B]5th[/B]       [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_5}]{$CLIENT_NICKNAME_5}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_5} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_5}]{$CLIENT_CURRENT_CHANNEL_NAME_5}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_5|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_5}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_5}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_5}[/SIZE]

[SIZE=10][B]6th[/B]       [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_6}]{$CLIENT_NICKNAME_6}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_6} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_6}]{$CLIENT_CURRENT_CHANNEL_NAME_6}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_6|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_6}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_6}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_6}[/SIZE]

[SIZE=10][B]7th[/B]       [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_7}]{$CLIENT_NICKNAME_7}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_7} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_7}]{$CLIENT_CURRENT_CHANNEL_NAME_7}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_7|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_7}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_7}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_7}[/SIZE]

[SIZE=10][B]8th[/B]       [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_8}]{$CLIENT_NICKNAME_8}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_8} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_8}]{$CLIENT_CURRENT_CHANNEL_NAME_8}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_8|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_8}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_8}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_8}[/SIZE]

[SIZE=10][B]9th[/B]       [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_9}]{$CLIENT_NICKNAME_9}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_9} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_9}]{$CLIENT_CURRENT_CHANNEL_NAME_9}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_9|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_9}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_9}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_9}[/SIZE]

[SIZE=10][B]10th[/B]     [URL=client://0/{$CLIENT_UNIQUE_IDENTIFIER_10}]{$CLIENT_NICKNAME_10}[/URL][/SIZE][SIZE=7] {if {$CLIENT_ONLINE_STATUS_10} === \'Online\'}[COLOR=GREEN](Online)[/COLOR]
currently in channel [URL=channelid://{$CLIENT_CURRENT_CHANNEL_ID_10}]{$CLIENT_CURRENT_CHANNEL_NAME_10}[/URL]{else}[COLOR=RED](Offline)[/COLOR]
last seen  {$CLIENT_LAST_SEEN_10|date_format:"%d.%m.%Y %H:%M:%S"}{/if}[/SIZE]
[SIZE=8]Last week active: {$CLIENT_ACTIVE_TIME_LAST_WEEK_10}; reached Servergroup: [IMG]https://domain.com/ranksystem/{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_10}[/IMG] {$CLIENT_CURRENT_RANK_GROUP_NAME_10}[/SIZE]


[SIZE=6]Updated: {$LAST_UPDATE_TIME}[/SIZE]', ENT_QUOTES);
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('channelinfo_toplist_active','0'),('channelinfo_toplist_desc',{$channelinfo_desc}),('channelinfo_toplist_lastdesc',''),('channelinfo_toplist_delay','600'),('channelinfo_toplist_channelid','0'),('channelinfo_toplist_modus','1'),('channelinfo_toplist_lastupdate','0') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);") === false) {
				enter_logfile(2,"    [1.3.18] Error on updating new addons_config values: ".print_r($mysqlcon->errorInfo(), true));
			} else {
				enter_logfile(4,"    [1.3.18] Updated new addons_config values.");
			}

			if($mysqlcon->exec("DELETE FROM `$dbname`.`admin_addtime`;") === false) { }
			if($mysqlcon->exec("DELETE FROM `$dbname`.`addon_assign_groups`;") === false) { }
		}
		
		if(version_compare($cfg['version_current_using'], '1.3.19', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`addons_config` MODIFY COLUMN `value` varchar(16000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") === false) { } else {
				enter_logfile(4,"    [1.3.19] Adjusted table addons_config successfully.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.20', '<')) {
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('clean_user_iphash', '0') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`);") === false) { } else {
				enter_logfile(4,"    [1.3.20] Added new job_check values.");
			}
		}

		if(version_compare($cfg['version_current_using'], '1.3.22', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_server` MODIFY COLUMN `version_name_1` varchar(254) NOT NULL default '0',MODIFY COLUMN `version_name_2` varchar(254) NOT NULL default '0',MODIFY COLUMN `version_name_3` varchar(254) NOT NULL default '0',MODIFY COLUMN `version_name_4` varchar(254) NOT NULL default '0',MODIFY COLUMN `version_name_5` varchar(254) NOT NULL default '0';") === false) { } else {
				enter_logfile(4,"    [1.3.22] Adjusted table stats_server successfully.");
			}

			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('default_style', '0'),('stats_column_online_day_switch', '0'),('stats_column_idle_day_switch', '0'),('stats_column_active_day_switch', '0'),('stats_column_online_week_switch', '0'),('stats_column_idle_week_switch', '0'),('stats_column_active_week_switch', '0'),('stats_column_online_month_switch', '0'),('stats_column_idle_month_switch', '0'),('stats_column_active_month_switch', '0'),('teamspeak_chatcommand_prefix', '!'),('rankup_excepted_remove_group_switch', '0');") === false) { } else {
				enter_logfile(4,"    [1.3.22] Added new cfg_params values.");
			}
			$cfg['rankup_excepted_remove_group_switch'] = '0';
			$cfg['teamspeak_chatcommand_prefix'] = '!';
			
			if(($check_new_columns = $mysqlcon->query("SHOW COLUMNS FROM `$dbname`.`stats_user` WHERE `field`='count_day'")->fetchAll(PDO::FETCH_ASSOC)) === false) { } else {
				if(!isset($check_new_columns[0]['Field'])) {
					if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` ADD COLUMN `count_day` mediumint(8) UNSIGNED NOT NULL default '0', ADD COLUMN `idle_day` mediumint(8) UNSIGNED NOT NULL default '0', ADD COLUMN `active_day` mediumint(8) UNSIGNED NOT NULL default '0';") === false) { } else {
						enter_logfile(4,"    [1.3.22] Adjusted table stats_user successfully.");
					} 
				}
			}
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('news_html', '".time()."'),('news_bb', '".time()."') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`);") === false) { } else {
				enter_logfile(4,"    [1.3.22] Added new job_check values.");
			}
			
			if($mysqlcon->exec("INSERT IGNORE INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_news_html', '0'),('teamspeak_news_bb', '0') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);") === false) { } else {
				enter_logfile(4,"    [1.3.22] Added new cfg_params values (part 2).");
				$cfg['stats_news_html'] = 'New Feature <a href="https://ts-ranksystem.com#voting" target="_blank">VOTING!</a> for the Ranksystem';
				$cfg['teamspeak_news_bb'] = 'New Feature [URL=https://ts-ranksystem.com#voting]VOTING![/URL] for the Ranksystem';
			}

			if($mysqlcon->exec("DELETE FROM `$dbname`.`admin_addtime`;") === false) { }
			if($mysqlcon->exec("DELETE FROM `$dbname`.`addon_assign_groups`;") === false) { }

			try {
				if($mysqlcon->exec("CREATE INDEX `snapshot_id` ON `$dbname`.`user_snapshot` (`id`)") === false) { }
				if($mysqlcon->exec("CREATE INDEX `snapshot_cldbid` ON `$dbname`.`user_snapshot` (`cldbid`)") === false) { }
				if($mysqlcon->exec("CREATE INDEX `serverusage_timestamp` ON `$dbname`.`server_usage` (`timestamp`)") === false) { }
				if($mysqlcon->exec("CREATE INDEX `user_version` ON `$dbname`.`user` (`version`)") === false) { }
				if($mysqlcon->exec("CREATE INDEX `user_cldbid` ON `$dbname`.`user` (`cldbid` ASC,`uuid`,`rank`)") === false) { }
				if($mysqlcon->exec("CREATE INDEX `user_online` ON `$dbname`.`user` (`online`,`lastseen`)") === false) { }
			} catch (Exception $e) { }
			
			$updatedone = TRUE;
		}

		$cfg = set_new_version($mysqlcon,$cfg,$dbname);
	}
	enter_logfile(5,"Check Ranksystem database for updates [done]");
	if(isset($updatedone) && $updatedone === TRUE) return TRUE;
	return FALSE;
}
?>
