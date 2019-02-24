<?PHP
function check_db($mysqlcon,$lang,$cfg,$dbname) {
	$cfg['version_latest_available'] = '1.2.12';
	enter_logfile($cfg,5,"Check Ranksystem database for updates...");
	
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
	
	function check_chmod($cfg,$lang) {
		if(substr(sprintf('%o', fileperms(substr(__DIR__,0,-4).'tsicons/')), -3, 1)!='7') {
			enter_logfile($cfg,2,sprintf($lang['isntwichm'],'tsicons'));
		}
		if(substr(sprintf('%o', fileperms($cfg['logs_path'])), -3, 1)!='7') {
			enter_logfile($cfg,2,sprintf($lang['isntwichm'],'logs'));
		}
		if(substr(sprintf('%o', fileperms(substr(__DIR__,0,-4).'avatars/')), -3, 1)!='7') {
			enter_logfile($cfg,2,sprintf($lang['isntwichm'],'avatars'));
		}
		if(substr(sprintf('%o', fileperms(substr(__DIR__,0,-4).'update/')), -3, 1)!='7') {
			enter_logfile($cfg,2,sprintf($lang['isntwichm'],'update'));
		}
	}

	function old_files($cfg) {
		if(is_file(substr(__DIR__,0,-4).'install.php')) {
			if(!unlink('install.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: install.php");
			}
		}
		if(is_dir(substr(__DIR__,0,-4).'icons/')) {
			if(!rmdir(substr(__DIR__,0,-4).'icons/')) {
				enter_logfile($cfg,4,"Unnecessary folder, please delete it from your webserver: icons/");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/combined_stats.css')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/combined_stats.css')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/combined_stats.css");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/combined_stats.js')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/combined_stats.js')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/combined_stats.js");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'webinterface/admin.php')) {
			if(!unlink(substr(__DIR__,0,-4).'webinterface/admin.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: webinterface/admin.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Blacklist/Exception.php')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Blacklist/Exception.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/ts3_lib/Adapter/Blacklist/Exception.php");
			}
		}
		if(is_dir(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Blacklist/')) {
			if(!rmdir(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Blacklist/')) {
				enter_logfile($cfg,4,"Unnecessary folder, please delete it from your webserver: libs/ts3_lib/Adapter/Blacklist/");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/TSDNS/Exception.php')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/TSDNS/Exception.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/ts3_lib/Adapter/TSDNS/Exception.php");
			}
		}
		if(is_dir(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/TSDNS/')) {
			if(!rmdir(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/TSDNS/')) {
				enter_logfile($cfg,4,"Unnecessary folder, please delete it from your webserver: libs/ts3_lib/Adapter/TSDNS/");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Update/Exception.php')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Update/Exception.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/ts3_lib/Adapter/Update/Exception.php");
			}
		}
		if(is_dir(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Update/')) {
			if(!rmdir(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Update/')) {
				enter_logfile($cfg,4,"Unnecessary folder, please delete it from your webserver: libs/ts3_lib/Adapter/Update/");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Blacklist.php')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Blacklist.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/ts3_lib/Adapter/Blacklist.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/TSDNS.php')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/TSDNS.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/ts3_lib/Adapter/TSDNS.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Update.php')) {
			if(!unlink(substr(__DIR__,0,-4).'libs/ts3_lib/Adapter/Update.php')) {
				enter_logfile($cfg,4,"Unnecessary file, please delete it from your webserver: libs/ts3_lib/Adapter/Update.php");
			}
		}
	}

	function check_writable($cfg) {
		enter_logfile($cfg,5,"  Check files permissions...");
		$counterr=0;
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
		if($counterr!=0) {
			enter_logfile($cfg,1,"Please check the files pemissions. Shutting down!\n\n");
			exit;
		} else {
			enter_logfile($cfg,5,"  Check files permissions [done]");
		}
	}

	if($cfg['version_current_using'] == $cfg['version_latest_available']) {
		enter_logfile($cfg,5,"  No newer version detected; Database check finished.");
		old_files($cfg);
		check_chmod($cfg,$lang);
		check_writable($cfg);
	} else {
		enter_logfile($cfg,4,"  Update the Ranksystem Database to new version...");
		if(version_compare($cfg['version_current_using'], '1.2.1', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_server` MODIFY COLUMN `server_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `server_platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `server_weblist` tinyint(1) NOT NULL default '0', MODIFY COLUMN `server_version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `total_user` int(10) NOT NULL default '0', MODIFY COLUMN `country_nation_1` int(10) NOT NULL default '0', MODIFY COLUMN `country_nation_2` int(10) NOT NULL default '0', MODIFY COLUMN `country_nation_3` int(10) NOT NULL default '0', MODIFY COLUMN `country_nation_4` int(10) NOT NULL default '0', MODIFY COLUMN `country_nation_5` int(10) NOT NULL default '0', MODIFY COLUMN `country_nation_other` int(10) NOT NULL default '0', MODIFY COLUMN `platform_1` int(10) NOT NULL default '0', MODIFY COLUMN `platform_2` int(10) NOT NULL default '0', MODIFY COLUMN `platform_3` int(10) NOT NULL default '0', MODIFY COLUMN `platform_4` int(10) NOT NULL default '0', MODIFY COLUMN `platform_5` int(10) NOT NULL default '0', MODIFY COLUMN `platform_other` int(10) NOT NULL default '0', MODIFY COLUMN `version_1` int(10) NOT NULL default '0', MODIFY COLUMN `version_2` int(10) NOT NULL default '0', MODIFY COLUMN `version_3` int(10) NOT NULL default '0', MODIFY COLUMN `version_4` int(10) NOT NULL default '0', MODIFY COLUMN `version_5` int(10) NOT NULL default '0', MODIFY COLUMN `version_other` int(10) NOT NULL default '0', MODIFY COLUMN `server_status` tinyint(1) NOT NULL default '0', MODIFY COLUMN `server_free_slots` smallint(5) NOT NULL default '0', MODIFY COLUMN `server_used_slots` smallint(5) NOT NULL default '0', MODIFY COLUMN `server_channel_amount` smallint(5) NOT NULL default '0', MODIFY COLUMN `server_ping` smallint(5) NOT NULL default '0', MODIFY COLUMN `server_id` smallint(5) NOT NULL default '0', MODIFY COLUMN `server_pass` tinyint(1) NOT NULL default '0'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table stats_server (part1) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_server` ADD (`user_today` int(10) NOT NULL default '0',`user_week` int(10) NOT NULL default '0',`user_month` int(10) NOT NULL default '0',`user_quarter` int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table stats_server (part2) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `removed` tinyint(1) NOT NULL default '0', MODIFY COLUMN `rank` int(10) NOT NULL default '0', MODIFY COLUMN `count_week` int(10) NOT NULL default '0', MODIFY COLUMN `count_month` int(10) NOT NULL default '0', MODIFY COLUMN `idle_week` int(10) NOT NULL default '0', MODIFY COLUMN `idle_month` int(10) NOT NULL default '0', MODIFY COLUMN `achiev_count` tinyint(1) NOT NULL default '0', MODIFY COLUMN `achiev_time` int(10) NOT NULL default '0', MODIFY COLUMN `achiev_connects` smallint(5) NOT NULL default '0', MODIFY COLUMN `achiev_battles` tinyint(3) NOT NULL default '0', MODIFY COLUMN `achiev_time_perc` tinyint(3) NOT NULL default '0', MODIFY COLUMN `achiev_connects_perc` tinyint(3) NOT NULL default '0', MODIFY COLUMN `achiev_battles_perc` tinyint(3) NOT NULL default '0', MODIFY COLUMN `battles_total` tinyint(3) NOT NULL default '0', MODIFY COLUMN `battles_won` tinyint(3) NOT NULL default '0', MODIFY COLUMN `battles_lost` tinyint(3) NOT NULL default '0', MODIFY COLUMN `total_connections` smallint(5) NOT NULL default '0', MODIFY COLUMN `client_description` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table stats_user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `cldbid` int(10) NOT NULL default '0', MODIFY COLUMN `count` int(10) NOT NULL default '0', MODIFY COLUMN `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `grpid` int(10) NOT NULL default '0', MODIFY COLUMN `nextup` int(10) NOT NULL default '0', MODIFY COLUMN `idle` int(10) NOT NULL default '0', MODIFY COLUMN `cldgroup` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `online` tinyint(1) NOT NULL default '0', MODIFY COLUMN `boosttime` int(10) NOT NULL default '0', MODIFY COLUMN `rank` int(10) NOT NULL default '0', MODIFY COLUMN `platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `nation` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `except` tinyint(1) NOT NULL default '0'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` MODIFY COLUMN `webuser` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `webpass` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `tshost` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `tsquery` smallint(5) NOT NULL default '0', MODIFY COLUMN `tsvoice` smallint(5) NOT NULL default '0', MODIFY COLUMN `tsuser` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `tspass` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `language` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `queryname` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `queryname2` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `grouptime` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `resetbydbchange` tinyint(1) NOT NULL default '0', MODIFY COLUMN `msgtouser` tinyint(1) NOT NULL default '0', MODIFY COLUMN `upcheck` tinyint(1) NOT NULL default '0', MODIFY COLUMN `uniqueid` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `updateinfotime` mediumint(6) NOT NULL default '0', MODIFY COLUMN `currvers` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `substridle` tinyint(1) NOT NULL default '0', MODIFY COLUMN `exceptuuid` varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `exceptgroup` varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `dateformat` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `showexcld` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolcld` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcoluuid` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcoldbid` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolot` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolit` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolat` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolnx` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolsg` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolrg` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showcolls` tinyint(1) NOT NULL default '0', MODIFY COLUMN `slowmode` mediumint(9) NOT NULL default '0', MODIFY COLUMN `cleanclients` tinyint(1) NOT NULL default '0', MODIFY COLUMN `cleanperiod` mediumint(9) NOT NULL default '0', MODIFY COLUMN `showhighest` tinyint(1) NOT NULL default '0', MODIFY COLUMN `boost` varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `showcolas` tinyint(1) NOT NULL default '0', MODIFY COLUMN `defchid` int(10) NOT NULL default '0', MODIFY COLUMN `advancemode` tinyint(1) NOT NULL default '0', MODIFY COLUMN `count_access` tinyint(2) NOT NULL default '0', MODIFY COLUMN `ignoreidle` smallint(5) NOT NULL default '0', MODIFY COLUMN `exceptcid` varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `rankupmsg` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `boost_mode` tinyint(1) NOT NULL default '0', MODIFY COLUMN `servernews` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `nextupinfo` tinyint(1) NOT NULL default '0', MODIFY COLUMN `nextupinfomsg1` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `nextupinfomsg2` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `nextupinfomsg3` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `shownav` tinyint(1) NOT NULL default '0', MODIFY COLUMN `showgrpsince` tinyint(1) NOT NULL default '0', MODIFY COLUMN `resetexcept` tinyint(1) NOT NULL default '0'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table config successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`server_usage` MODIFY COLUMN `clients` smallint(5) NOT NULL default '0', MODIFY COLUMN `channel` smallint(5) NOT NULL default '0'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table server_usage successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_snapshot` MODIFY COLUMN `count` int(10) NOT NULL default '0', MODIFY COLUMN `idle` int(10) NOT NULL default '0'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table user_snapshot successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgid` int(10) NOT NULL default '0' PRIMARY KEY, MODIFY COLUMN `sgidname` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Adjusted table groups successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_nations` (`nation` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`count` int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Created table stats_nations successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_versions` (`version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`count` int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Created table stats_versions successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_platforms` (`platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`count` int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.1] Created table stats_platforms successfully.");
			}
		}
		if(version_compare($cfg['version_current_using'], '1.2.2', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` ADD (`active_week` int(10) NOT NULL default '0',`active_month` int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.2] Adjusted table stats_user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` ADD (`avatar_delay` smallint(5) UNSIGNED NOT NULL default '0')") === false) { } else {
				if($mysqlcon->exec("UPDATE `$dbname`.`config` set `avatar_delay`='0'") === false) { } else {
					enter_logfile($cfg,4,"    [1.2.2] Adjusted table config (part 1) successfully.");
				}
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` MODIFY COLUMN `tsquery` smallint(5) UNSIGNED NOT NULL default '0'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.2] Adjusted table config (part 2) successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`addons_config` (`param` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci UNIQUE,`value` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				if($mysqlcon->exec("INSERT INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('assign_groups_active','0'),('assign_groups_groupids',''),('assign_groups_limit','')") === false) { } else {
					enter_logfile($cfg,4,"    [1.2.2] Created table addons_config successfully.");
				}
			}
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`addon_assign_groups` (`uuid` varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`grpids` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.2] Created table addon_assign_groups successfully.");
			}
		}
		if(version_compare($cfg['version_current_using'], '1.2.3', '<')) {
			if($mysqlcon->exec("DELETE FROM `$dbname`.`groups`") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.3] Cleaned table groups successfully. (cause new icon folder tsicons - redownload)");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` MODIFY COLUMN `tsvoice` smallint(5) UNSIGNED NOT NULL default '0'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.3] Adjusted table config successfully.");
			}
			if($mysqlcon->exec("CREATE INDEX `snapshot_timestamp` ON `$dbname`.`user_snapshot` (`timestamp`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.3] Recreated index on table user_snapshot successfully.");
			}
			if($mysqlcon->exec("CREATE INDEX `serverusage_timestamp` ON `$dbname`.`server_usage` (`timestamp`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.3] Recreated index on table server_usage successfully.");
			}
		}
		if(version_compare($cfg['version_current_using'], '1.2.4', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` MODIFY COLUMN `adminuuid` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Adjusted table config (part 1) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` ADD (`registercid` mediumint(8) UNSIGNED NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Adjusted table config (part 2) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` ADD (`cid` int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Adjusted table user successfully.");
			}
			if($mysqlcon->exec("CREATE INDEX `user_version` ON `$dbname`.`user` (`version`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Create index 'user_version' on table user successfully.");
			}
			if($mysqlcon->exec("CREATE INDEX `user_cldbid` ON `$dbname`.`user` (`cldbid` ASC,`uuid`,`rank`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Create index 'user_cldbid' on table user successfully.");
			}
			if($mysqlcon->exec("CREATE INDEX `user_online` ON `$dbname`.`user` (`online`,`lastseen`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Create index 'user_online' on table user successfully.");
			}
			if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`) VALUES ('clean_db'),('clean_clients'),('calc_server_stats'),('runtime_check'),('last_update')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Set new values to table job_check successfully.");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`job_check` WHERE `job_name`='check_clean'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Removed old value 'check_clean' from table job_check successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_snapshot` ADD PRIMARY KEY (`timestamp`,`uuid`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Added new primary key on table user_snapshot successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_nations` ADD PRIMARY KEY (`nation`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Added new primary key on table stats_nations successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_platforms` ADD PRIMARY KEY (`platform`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Added new primary key on table stats_platforms successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_versions` ADD PRIMARY KEY (`version`)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.4] Added new primary key on table stats_versions successfully.");
			}
		}
		if(version_compare($cfg['version_current_using'], '1.2.5', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.5] Adjusted table groups successfully.");
			}
		}
		if(version_compare($cfg['version_current_using'], '1.2.6', '<')) {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`) VALUES ('last_update')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.6] Set missed value to table job_check successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` DROP COLUMN `upcheck`, DROP COLUMN `uniqueid`, DROP COLUMN `updateinfotime`") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.6] Dropped old values from table config sucessfully.");
			}		
		}
		if(version_compare($cfg['version_current_using'], '1.2.7', '<')) {
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`admin_addtime` (`uuid` varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`timestamp` bigint(11) NOT NULL default '0',`timecount` int(10) NOT NULL default '0', PRIMARY KEY (`uuid`,`timestamp`))") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Created table admin_addtime successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` DROP COLUMN `ip`") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Dropped client ip from table user sucessfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` MODIFY COLUMN `timezone` varchar(35) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN `queryname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, MODIFY COLUMN `queryname2` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, MODIFY COLUMN `rankupmsg` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, MODIFY COLUMN `servernews` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, MODIFY COLUMN `nextupinfomsg1` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, MODIFY COLUMN `nextupinfomsg2` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, MODIFY COLUMN `nextupinfomsg3` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Adjusted table config (part 1) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` ADD (`iphash` tinyint(1) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Adjusted table config (part 2) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`groups` MODIFY COLUMN `sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Adjusted table groups successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_server` MODIFY COLUMN `server_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Adjusted table stats_server successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `client_description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Adjusted table stats_user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Adjusted table user successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`user_iphash` (`uuid` varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`iphash` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`ip` varchar(39) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.7] Created table user_iphash successfully.");
			}
		}
		if(version_compare($cfg['version_current_using'], '1.2.10', '<')) {
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` ADD (`tsencrypt` tinyint(1) NOT NULL default '0')") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.10] Adjusted table config successfully.");
			}
		}
		if(version_compare($cfg['version_current_using'], '1.2.11', '<')) {
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`csrf_token` (`token` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `timestamp` bigint(11) NOT NULL default '0', `sessionid` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.11] Created table csrf_token successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`config` DROP COLUMN `queryname2`") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.11] Dropped old value from table config successfully.");
			}
			if($mysqlcon->exec("DROP TABLE `$dbname`.`bak_stats_nations`") === false) { }
			if($mysqlcon->exec("DROP TABLE `$dbname`.`bak_stats_platforms`") === false) { }
			if($mysqlcon->exec("DROP TABLE `$dbname`.`bak_stats_versions`") === false) { }
			if($mysqlcon->exec("DROP TABLE `$dbname`.`bak_addon_assign_groups`") === false) { }
		}
		if(version_compare($cfg['version_current_using'], '1.2.12', '<')) {
			if($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='".time()."' WHERE `job_name`='last_update'") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Stored timestamp of last update successfully.");
			}
			if($mysqlcon->exec("DELETE FROM `$dbname`.`admin_addtime`") === false) { }
			if($mysqlcon->exec("DELETE FROM `$dbname`.`addon_assign_groups`") === false) { }
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_iphash` MODIFY COLUMN `uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `iphash` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Adjusted table user_iphash successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE `$dbname`.`cfg_params` (`param` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `value` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Created table cfg_params successfully.");
				$oldconfigs = $mysqlcon->query("SELECT * FROM `$dbname`.`config`")->fetch();
				if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('default_date_format', '".$oldconfigs['dateformat']."'), ('default_language', '".$oldconfigs['language']."'), ('logs_path', '".$oldconfigs['logpath']."'), ('logs_timezone', '".$oldconfigs['timezone']."'), ('rankup_boost_definition', '".$oldconfigs['boost']."'), ('rankup_clean_clients_period', '".$oldconfigs['cleanperiod']."'), ('rankup_clean_clients_switch', '".$oldconfigs['cleanclients']."'), ('rankup_client_database_id_change_switch', '".$oldconfigs['resetbydbchange']."'), ('rankup_definition', '".$oldconfigs['grouptime']."'), ('rankup_excepted_channel_id_list', '".$oldconfigs['exceptcid']."'), ('rankup_excepted_group_id_list', '".$oldconfigs['exceptgroup']."'), ('rankup_excepted_mode', '".$oldconfigs['resetexcept']."'), ('rankup_excepted_unique_client_id_list', '".$oldconfigs['exceptuuid']."'), ('rankup_hash_ip_addresses_mode', '".$oldconfigs['iphash']."'), ('rankup_ignore_idle_time', '".$oldconfigs['ignoreidle']."'), ('rankup_message_to_user', '".$oldconfigs['rankupmsg']."'), ('rankup_message_to_user_switch', '".$oldconfigs['msgtouser']."'), ('rankup_next_message_1', '".$oldconfigs['nextupinfomsg1']."'), ('rankup_next_message_2', '".$oldconfigs['nextupinfomsg2']."'), ('rankup_next_message_3', '".$oldconfigs['nextupinfomsg3']."'), ('rankup_next_message_mode', '".$oldconfigs['nextupinfo']."'), ('rankup_time_assess_mode', '".$oldconfigs['substridle']."'), ('stats_column_active_time_switch', '".$oldconfigs['showcolat']."'), ('stats_column_current_group_since_switch', '".$oldconfigs['showgrpsince']."'), ('stats_column_current_server_group_switch', '".$oldconfigs['showcolas']."'), ('stats_column_client_db_id_switch', '".$oldconfigs['showcoldbid']."'), ('stats_column_client_name_switch', '".$oldconfigs['showcolcld']."'), ('stats_column_idle_time_switch', '".$oldconfigs['showcolit']."'), ('stats_column_last_seen_switch', '".$oldconfigs['showcolls']."'), ('stats_column_next_rankup_switch', '".$oldconfigs['showcolnx']."'), ('stats_column_next_server_group_switch', '".$oldconfigs['showcolsg']."'), ('stats_column_online_time_switch', '".$oldconfigs['showcolot']."'), ('stats_column_rank_switch', '".$oldconfigs['showcolrg']."'), ('stats_column_unique_id_switch', '".$oldconfigs['showcoluuid']."'), ('stats_server_news', '".$oldconfigs['servernews']."'), ('stats_show_clients_in_highest_rank_switch', '".$oldconfigs['showhighest']."'), ('stats_show_excepted_clients_switch', '".$oldconfigs['showexcld']."'), ('stats_show_site_navigation_switch', '".$oldconfigs['shownav']."'), ('teamspeak_avatar_download_delay', '".$oldconfigs['avatar_delay']."'), ('teamspeak_default_channel_id', '".$oldconfigs['defchid']."'), ('teamspeak_host_address', '".$oldconfigs['tshost']."'), ('teamspeak_query_command_delay', '".$oldconfigs['slowmode']."'), ('teamspeak_query_encrypt_switch', '".$oldconfigs['tsencrypt']."'), ('teamspeak_query_nickname', '".$oldconfigs['queryname']."'), ('teamspeak_query_pass', '".$oldconfigs['tspass']."'), ('teamspeak_query_port', '".$oldconfigs['tsquery']."'), ('teamspeak_query_user', '".$oldconfigs['tsuser']."'), ('teamspeak_verification_channel_id', '".$oldconfigs['registercid']."'), ('teamspeak_voice_port', '".$oldconfigs['tsvoice']."'), ('version_current_using', '".$oldconfigs['currvers']."'), ('version_latest_available', '".$oldconfigs['newversion']."'), ('version_update_channel', '".$oldconfigs['upchannel']."'), ('webinterface_access_count', '".$oldconfigs['count_access']."'), ('webinterface_access_last', '".$oldconfigs['last_access']."'), ('webinterface_admin_client_unique_id_list', '".$oldconfigs['adminuuid']."'), ('webinterface_advanced_mode', '".$oldconfigs['advancemode']."'), ('webinterface_pass', '".$oldconfigs['webpass']."'), ('webinterface_user', '".$oldconfigs['webuser']."')") === false) { } else {
					enter_logfile($cfg,4,"    [1.2.12] Set new values to table cfg_params successfully.");
				}
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user` MODIFY COLUMN `uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Adjusted table user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`stats_user` MODIFY COLUMN `uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `base64hash` char(58) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Adjusted table stats_user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`user_snapshot` MODIFY COLUMN `uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Adjusted table user_snapshot successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`addon_assign_groups` MODIFY COLUMN `uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Adjusted table addon_assign_groups successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE `$dbname`.`admin_addtime` MODIFY COLUMN `uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($cfg,4,"    [1.2.12] Adjusted table admin_addtime successfully.");
			}
			if($mysqlcon->exec("CREATE INDEX `snapshot_timestamp` ON `$dbname`.`user_snapshot` (`timestamp`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `serverusage_timestamp` ON `$dbname`.`server_usage` (`timestamp`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_version` ON `$dbname`.`user` (`version`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_cldbid` ON `$dbname`.`user` (`cldbid` ASC,`uuid`,`rank`)") === false) { }
			if($mysqlcon->exec("CREATE INDEX `user_online` ON `$dbname`.`user` (`online`,`lastseen`)") === false) { }
		}
		$cfg = set_new_version($mysqlcon,$cfg,$dbname);
		old_files($cfg);
		check_chmod($cfg,$lang);
	}
	enter_logfile($cfg,5,"Check Ranksystem database for updates [done]");
	return $cfg;
}
?>