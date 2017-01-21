<?PHP
function check_db($mysqlcon,$lang,$dbname,$timezone,$currvers,$logpath) {
	$newversion = '1.2.2';
	enter_logfile($logpath,$timezone,5,"Check Ranksystem database for updates.");
	
	function set_new_version($mysqlcon,$dbname,$timezone,$newversion,$logpath) {
		if($mysqlcon->exec("UPDATE $dbname.config set currvers='$newversion'") === false) {
			enter_logfile($logpath,$timezone,1,"  An error happens due updating the Ranksystem Database:".print_r($mysqlcon->errorInfo(), true));
			enter_logfile($logpath,$timezone,1,"  Check the database connection properties in other/dbconfig.php and check also the database permissions.");
			exit;
		} else {
			$currvers = $newversion;
			enter_logfile($logpath,$timezone,4,"  Database successfully updated!");
			return $currvers;
		}
	}
	
	function check_chmod($timezone,$logpath,$lang) {
		if(substr(sprintf('%o', fileperms(substr(__DIR__,0,-4).'icons/')), -3, 1)!='7') {
			enter_logfile($logpath,$timezone,2,sprintf($lang['isntwichm'],'icons'));
		}
		if(substr(sprintf('%o', fileperms($logpath)), -3, 1)!='7') {
			enter_logfile($logpath,$timezone,2,sprintf($lang['isntwichm'],'logs'));
		}
		if(substr(sprintf('%o', fileperms(substr(__DIR__,0,-4).'avatars/')), -3, 1)!='7') {
			enter_logfile($logpath,$timezone,2,sprintf($lang['isntwichm'],'avatars'));
		}
		if(substr(sprintf('%o', fileperms(substr(__DIR__,0,-4).'update/')), -3, 1)!='7') {
			enter_logfile($logpath,$timezone,2,sprintf($lang['isntwichm'],'update'));
		}
	}
	
	function check_config($mysqlcon,$dbname) {
		if(($dbdata = $mysqlcon->query("SELECT * FROM $dbname.config")) === false) { } else {
			if($dbdata->rowCount() > 1) {
				if($mysqlcon->exec("DELETE FROM $dbname.config WHERE webuser IS NULL") === false) { }
			}
			$config = $dbdata->fetchAll();
			if($config[0]['updateinfotime'] > 86400) {
				if($mysqlcon->exec("UPDATE $dbname.config SET updateinfotime='86400'") === false) { }
			}
		}
	}

	function old_files($timezone,$logpath) {
		if(is_file(substr(__DIR__,0,-4).'install.php')) {
			if(!unlink('install.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: install.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'list_rankup.php')) {
			if(!unlink(substr(__DIR__,0,-4).'list_rankup.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: list_rankup.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'lang.php')) {
			if(!unlink(substr(__DIR__,0,-4).'lang.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: lang.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'license.txt')) {
			if(!unlink(substr(__DIR__,0,-4).'license.txt')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: license.txt");
			}
		}
		if(is_dir(substr(__DIR__,0,-4).'jquerylib/')) {
			if(!rmdir(substr(__DIR__,0,-4).'jquerylib/')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary folder, please delete it from your webserver: jquerylib/");
			}
		}
		if(is_dir(substr(__DIR__,0,-4).'ts3_lib/')) {
			if(!rmdir(substr(__DIR__,0,-4).'ts3_lib/')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary folder, please delete it from your webserver: ts3_lib/");
			}
		}
		if(is_dir(substr(__DIR__,0,-4).'bootstrap/')) {
			if(!rmdir(substr(__DIR__,0,-4).'bootstrap/')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary folder, please delete it from your webserver: bootstrap/");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'other/webinterface_list.php')) {
			if(!unlink(substr(__DIR__,0,-4).'other/webinterface_list.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: other/webinterface_list.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'other/webinterface_login.php')) {
			if(!unlink(substr(__DIR__,0,-4).'other/webinterface_login.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: other/webinterface_login.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'webinterface.php')) {
			if(!unlink(substr(__DIR__,0,-4).'webinterface.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: webinterface.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'other/style.css.php')) {
			if(!unlink(substr(__DIR__,0,-4).'other/style.css.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: other/style.css.php");
			}
		}
	if(is_file(substr(__DIR__,0,-4).'other/search.php')) {
			if(!unlink(substr(__DIR__,0,-4).'other/search.php')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: other/search.php");
			}
		}
		if(is_file(substr(__DIR__,0,-4).'server-news')) {
			if(!unlink(substr(__DIR__,0,-4).'server-news')) {
				enter_logfile($logpath,$timezone,4,"Unnecessary file, please delete it from your webserver: server-news");
			}
		}
	}
	
	function check_writable($timezone,$logpath) {
		enter_logfile($logpath,$timezone,5,"  Check files permissions...");
		$counterr=0;
		$scandir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(substr(__DIR__,0,-4)));
		$files = array(); 
		foreach ($scandir as $object) {
			if(!strstr($object, '/.') && !strstr($object, '\.')) {
				if (!$object->isDir()) {
					if(!is_writable($object->getPathname())) {
						enter_logfile($logpath,$timezone,3,"    File is not writeable ".$object);
						$counterr++;
					}
				} else {
					if(!is_writable($object->getPathname())) {
						enter_logfile($logpath,$timezone,3,"    Folder is not writeable ".$object);
						$counterr++;
					}
				}
			}
		}
		if($counterr!=0) {
			enter_logfile($logpath,$timezone,1,"Please check the files pemissions. Shutting down!\n\n");
			exit;
		} else {
			enter_logfile($logpath,$timezone,5,"  Check files permissions [done]");
		}
	}
	
	if($currvers==$newversion) {
		enter_logfile($logpath,$timezone,5,"  No newer version detected; Database check finished.");
		old_files($timezone,$logpath);
		check_chmod($timezone,$logpath,$lang);
		check_config($mysqlcon,$dbname);
		check_writable($timezone,$logpath);
	} elseif($currvers=="0.13-beta") {
		enter_logfile($logpath,$timezone,4,"  Update the Ranksystem Database to version 1.0.1");
		
		$errcount=1;
		
		if($mysqlcon->exec("ALTER TABLE $dbname.user ADD (boosttime bigint(11) NOT NULL default '0', rank bigint(11) NOT NULL default '0', platform text default NULL, nation text default NULL, version text default NULL, firstcon bigint(11) NOT NULL default '0', except int(1) NOT NULL default '0')") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"user\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (boost text default NULL, showcolas int(1) NOT NULL default '0', defchid bigint(11) NOT NULL default '0', timezone varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci, logpath varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"config\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		$logpath = addslashes(__DIR__."/logs/");
		if($mysqlcon->exec("ALTER TABLE $dbname.config MODIFY slowmode bigint(11) NOT NULL default '0'") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"config\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
			if($mysqlcon->exec("UPDATE $dbname.config set defchid='0', timezome='Europe/Berlin', slowmode='0', logpath='$logpath'") === false) {
				enter_logfile($logpath,$timezone,1,"DB Update Error: table \"config\" ".print_r($mysqlcon->errorInfo(), true));
				$errcount++;
			}
		}
		
		if($mysqlcon->exec("ALTER TABLE $dbname.groups ADD (icondate bigint(11) NOT NULL default '0')") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"groups\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
			if($mysqlcon->exec("CREATE TABLE $dbname.server_usage (timestamp bigint(11) NOT NULL default '0', clients bigint(11) NOT NULL default '0', channel bigint(11) NOT NULL default '0')") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"server_usage\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("CREATE TABLE $dbname.user_snapshot (timestamp bigint(11) NOT NULL default '0', uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci, count bigint(11) NOT NULL default '0', idle bigint(11) NOT NULL default '0')") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"user_snapshot\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("CREATE INDEX snapshot_timestamp ON $dbname.user_snapshot (timestamp)") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"snapshot_timestamp\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("CREATE INDEX serverusage_timestamp ON $dbname.server_usage (timestamp)") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"snapshot_timestamp\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("CREATE TABLE $dbname.stats_server (total_user bigint(11) NOT NULL default '0', total_online_time bigint(13) NOT NULL default '0', total_online_month bigint(11) NOT NULL default '0', total_online_week bigint(11) NOT NULL default '0', total_active_time bigint(11) NOT NULL default '0', total_inactive_time bigint(11) NOT NULL default '0', country_nation_name_1 varchar(3) NOT NULL default '0', country_nation_name_2 varchar(3) NOT NULL default '0', country_nation_name_3 varchar(3) NOT NULL default '0', country_nation_name_4 varchar(3) NOT NULL default '0', country_nation_name_5 varchar(3) NOT NULL default '0', country_nation_1 bigint(11) NOT NULL default '0', country_nation_2 bigint(11) NOT NULL default '0', country_nation_3 bigint(11) NOT NULL default '0', country_nation_4 bigint(11) NOT NULL default '0', country_nation_5 bigint(11) NOT NULL default '0', country_nation_other bigint(11) NOT NULL default '0', platform_1 bigint(11) NOT NULL default '0', platform_2 bigint(11) NOT NULL default '0', platform_3 bigint(11) NOT NULL default '0', platform_4 bigint(11) NOT NULL default '0', platform_5 bigint(11) NOT NULL default '0', platform_other bigint(11) NOT NULL default '0', version_name_1 varchar(35) NOT NULL default '0', version_name_2 varchar(35) NOT NULL default '0', version_name_3 varchar(35) NOT NULL default '0', version_name_4 varchar(35) NOT NULL default '0', version_name_5 varchar(35) NOT NULL default '0', version_1 bigint(11) NOT NULL default '0', version_2 bigint(11) NOT NULL default '0', version_3 bigint(11) NOT NULL default '0', version_4 bigint(11) NOT NULL default '0', version_5 bigint(11) NOT NULL default '0', version_other bigint(11) NOT NULL default '0', server_status int(1) NOT NULL default '0', server_free_slots bigint(11) NOT NULL default '0', server_used_slots bigint(11) NOT NULL default '0', server_channel_amount bigint(11) NOT NULL default '0', server_ping bigint(11) NOT NULL default '0', server_packet_loss float (4,4), server_bytes_down bigint(11) NOT NULL default '0', server_bytes_up bigint(11) NOT NULL default '0', server_uptime bigint(11) NOT NULL default '0', server_id bigint(11) NOT NULL default '0', server_name text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_pass int(1) NOT NULL default '0', server_creation_date bigint(11) NOT NULL default '0', server_platform text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_weblist text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_version text CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"stats_server\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("CREATE TABLE $dbname.stats_user (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, removed int(1) NOT NULL default '0', rank bigint(11) NOT NULL default '0', total_connections bigint(11) NOT NULL default '0', count_week bigint(11) NOT NULL default '0', count_month bigint(11) NOT NULL default '0', idle_week bigint(11) NOT NULL default '0', idle_month bigint(11) NOT NULL default '0', achiev_count bigint(11) NOT NULL default '0', achiev_time bigint(11) NOT NULL default '0', achiev_connects bigint(11) NOT NULL default '0', achiev_battles bigint(11) NOT NULL default '0', achiev_time_perc int(3) NOT NULL default '0', achiev_connects_perc int(3) NOT NULL default '0', achiev_battles_perc int(3) NOT NULL default '0', battles_total bigint(11) NOT NULL default '0', battles_won bigint(11) NOT NULL default '0', battles_lost bigint(11) NOT NULL default '0', client_description text CHARACTER SET utf8 COLLATE utf8_unicode_ci, base64hash varchar(58) CHARACTER SET utf8 COLLATE utf8_unicode_ci, client_total_up bigint(15) NOT NULL default '0', client_total_down bigint(15) NOT NULL default '0')") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"stats_user\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("INSERT INTO $dbname.stats_server SET total_user='9999'") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"stats_server\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("CREATE TABLE $dbname.job_check (job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, timestamp bigint(11) NOT NULL default '0')") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"job_check\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
		
		if($mysqlcon->exec("INSERT INTO $dbname.job_check (job_name) VALUES ('calc_user_limit'),('calc_user_lastscan'),('check_update'),('check_clean')") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"job_check\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
	
		if($mysqlcon->exec("CREATE TABLE $dbname.job_log (id bigint(11) AUTO_INCREMENT PRIMARY KEY, timestamp bigint(11) NOT NULL default '0', job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci, status int(1) NOT NULL default '0', err_msg text CHARACTER SET utf8 COLLATE utf8_unicode_ci, runtime float (4,4))") === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"job_log\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		}
	
		if(($lastscan = $mysqlcon->query("SELECT timestamp FROM $dbname.lastscan")) === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"lastscan\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		} else {
			$timestampls = $lastscan->fetchAll();
			$calc_user_lastscan = $timestampls[0]['timestamp'];
			if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$calc_user_lastscan' WHERE job_name='calc_user_lastscan'") === false) {
				enter_logfile($logpath,$timezone,1,"DB Update Error: table \"job_check\" ".print_r($mysqlcon->errorInfo(), true));
				$errcount++;
			} elseif($mysqlcon->exec("DROP TABLE $dbname.lastscan") === false) {
				enter_logfile($logpath,$timezone,1,"DB Update Error: table \"lastscan\" ".print_r($mysqlcon->errorInfo(), true));
				$errcount++;
			}
		}
	
		if(($lastupdate = $mysqlcon->query("SELECT timestamp FROM $dbname.upcheck")) === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"upcheck\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		} else {
			$timestampuc = $lastupdate->fetchAll();
			$check_update = $timestampuc[0]['timestamp'];
			if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$check_update' WHERE job_name='check_update'") === false) {
				enter_logfile($logpath,$timezone,1,"DB Update Error: table \"job_check\" ".print_r($mysqlcon->errorInfo(), true));
				$errcount++;
			} elseif($mysqlcon->exec("DROP TABLE $dbname.upcheck") === false) {
				enter_logfile($logpath,$timezone,1,"DB Update Error: table \"upcheck\" ".print_r($mysqlcon->errorInfo(), true));
				$errcount++;
			}
		}
	
		if(($lastclean = $mysqlcon->query("SELECT timestamp FROM $dbname.cleanclients")) === false) {
			enter_logfile($logpath,$timezone,1,"DB Update Error: table \"upcheck\" ".print_r($mysqlcon->errorInfo(), true));
			$errcount++;
		} else {
			$timestamplc = $lastclean->fetchAll();
			$check_clean = $timestampls[0]['timestamp'];
			if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$check_clean' WHERE job_name='check_clean'") === false) {
				enter_logfile($logpath,$timezone,1,"DB Update Error: table \"upcheck\" ".print_r($mysqlcon->errorInfo(), true));
				$errcount++;
			} elseif($mysqlcon->exec("DROP TABLE $dbname.cleanclients") === false) {
				enter_logfile($logpath,$timezone,1,"DB Update Error: table \"upcheck\" ".print_r($mysqlcon->errorInfo(), true));
				$errcount++;
			}
		}
		
		if ($errcount == 1) {
			$currvers = set_new_version($mysqlcon,$dbname,$timezone,$newversion,$logpath);
			check_chmod($timezone,$logpath,$lang);
			check_chmod($timezone);
		} else {
			enter_logfile($logpath,$timezone,1,"An error happens due updating the Ranksystem Database!");
			enter_logfile($logpath,$timezone,1,"Check the database connection properties in other/dbconfig.php and check also the database permissions.");
			exit;
		}
	} else {
		enter_logfile($logpath,$timezone,4,"  Update the Ranksystem Database to new version...");
		if(version_compare($currvers, '1.0.2', '<=')) {
			if($mysqlcon->exec("CREATE INDEX serverusage_timestamp ON $dbname.server_usage (timestamp)") === false) { }
			if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (advancemode int(1) NOT NULL default '0', count_access int(2) NOT NULL default '0', last_access bigint(11) NOT NULL default '0', ignoreidle bigint(11) NOT NULL default '0', exceptcid text CHARACTER SET utf8 COLLATE utf8_unicode_ci, rankupmsg text CHARACTER SET utf8 COLLATE utf8_unicode_ci, boost_mode int(1) NOT NULL default '0', newversion varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else { 
				enter_logfile($logpath,$timezone,4,"    [1.1.0] Adjusted table config successfully.");
			}
			if($mysqlcon->exec("UPDATE $dbname.config set ignoreidle='600', rankupmsg='\\nHey, you got a rank up, cause you reached an activity of %s days, %s hours, %s minutes and %s seconds.', newversion='1.1.0'") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.1.0] Set default values to new fields in table config.");
			}
			if($mysqlcon->exec("INSERT INTO $dbname.job_check (job_name) VALUES ('get_version')") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.1.0] Set new values to table job_check.");
			}
			if(($password = $mysqlcon->query("SELECT webpass FROM $dbname.config")) === false) { }
			$password = $password->fetchAll();
			if(strlen($password[0]['webpass']) != 60) {
				$newwebpass = password_hash($password[0]['webpass'], PASSWORD_DEFAULT);
				if($mysqlcon->exec("UPDATE $dbname.config set webpass='$newwebpass'") === false) { } else {
					enter_logfile($logpath,$timezone,4,"    [1.1.0] Encrypted password for the webinterface and wrote hash to database.");
				}
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.config DROP COLUMN showexgrp, DROP COLUMN showgen, DROP COLUMN bgcolor, DROP COLUMN hdcolor, DROP COLUMN txcolor, DROP COLUMN hvcolor, DROP COLUMN ifcolor, DROP COLUMN wncolor, DROP COLUMN sccolor") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.1.0] Delete old configs, which are no more needed.");
			}
		}
		if(version_compare($currvers, '1.1.0', '<=')) {
			if($mysqlcon->exec("ALTER TABLE $dbname.user CHANGE ip clientip bigint(11) NOT NULL default '0'") === false) { }
			if($mysqlcon->exec("ALTER TABLE $dbname.user ADD ip VARBINARY(16) DEFAULT NULL") === false) { } else { 
				enter_logfile($logpath,$timezone,4,"    [1.1.1] Adjusted table user successfully.");
			}
			if(($dbuserdata = $mysqlcon->query("SELECT uuid,clientip FROM $dbname.user")) === false) { }
			$uuids = $dbuserdata->fetchAll();
			foreach($uuids as $uuid) {
				$sqlhis[$uuid['uuid']] = array(
					"uuid" => $uuid['uuid'],
					"ip" => $mysqlcon->quote(inet_pton(long2ip($uuid['clientip'])), ENT_QUOTES)
				);
			}
			foreach ($sqlhis as $updatearr) {
				$allupdateuuid = $allupdateuuid . "'" . $updatearr['uuid'] . "',";
				$allupdateip = $allupdateip . "WHEN '" . $updatearr['uuid'] . "' THEN " . $updatearr['ip'] . " ";
			}
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			if($mysqlcon->exec("UPDATE $dbname.user set ip = CASE uuid $allupdateip END WHERE uuid IN ($allupdateuuid)") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.1.1] Converted client IP successfully for IPv6 support.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.user DROP COLUMN clientip") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.1.1] Delete unconverted IP(v4), which are no more needed.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (servernews text CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else { 
				$servernews = $mysqlcon->quote(file_get_contents('../server-news'));
				if($mysqlcon->exec("UPDATE $dbname.config set servernews='$servernews')") === false) { } else {
					enter_logfile($logpath,$timezone,4,"    [1.1.1] Adjusted table config successfully.");
				}
			}
			if($mysqlcon->exec("DROP TABLE $dbname.job_log") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.1.1] Drop table job_log, which is no more needed.");
			}
		}
		if(version_compare($currvers, '1.1.1', '<=')) {
			if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (adminuuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci, nextupinfo int(1) NOT NULL default '0', nextupinfomsg1 text CHARACTER SET utf8 COLLATE utf8_unicode_ci, nextupinfomsg2 text CHARACTER SET utf8 COLLATE utf8_unicode_ci, nextupinfomsg3 text CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				$nextupinfomsg1 = $mysqlcon->quote("Your next rank up will be in %1\$s days, %2\$s hours, %3\$s minutes and %4\$s seconds. The next servergroup you will reach is [B]%5\$s[/B].");
				$nextupinfomsg2 = $mysqlcon->quote("You have already reached the highest rank.");
				$nextupinfomsg3 = $mysqlcon->quote("You are excepted from the Ranksystem. If you wish to rank contact an admin on the TS3 server.");
				if($mysqlcon->exec("UPDATE $dbname.config set nextupinfo='1',  nextupinfomsg1=$nextupinfomsg1, nextupinfomsg2=$nextupinfomsg2, nextupinfomsg3=$nextupinfomsg3") === false) { } else {
					enter_logfile($logpath,$timezone,4,"    [1.1.2] Adjusted table config (part1) successfully.");
				}
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (shownav int(1) NOT NULL default '0', showgrpsince int(1) NOT NULL default '0')") === false) { } else {
				if($mysqlcon->exec("UPDATE $dbname.config set shownav='1', showgrpsince='1'") === false) { } else {
					enter_logfile($logpath,$timezone,4,"    [1.1.2] Adjusted table config (part2) successfully.");
				}
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.user ADD (grpsince bigint(11) NOT NULL default '0')") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.1.2] Adjusted table user successfully.");
			}
		}
		if(version_compare($currvers, '1.1.2', '<=')) {
			enter_logfile($logpath,$timezone,4,"    [1.1.3] No database changes needed.");
		}
		if(version_compare($currvers, '1.1.3', '<=')) {
			if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (resetexcept int(1) NOT NULL default '0', upchannel varchar(20) NOT NULL default '0')") === false) { } else {
				if($mysqlcon->exec("UPDATE $dbname.config set upchannel='version'") === false) { } else {
					enter_logfile($logpath,$timezone,4,"    [1.2.0] Adjusted table config successfully.");
				}
			}
		}
		if(version_compare($currvers, '1.2.0', '<=')) {
			if($mysqlcon->exec("ALTER TABLE $dbname.stats_server MODIFY COLUMN server_name varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN server_platform varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN server_weblist tinyint(1) NOT NULL default '0', MODIFY COLUMN server_version varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN total_user int(10) NOT NULL default '0', MODIFY COLUMN country_nation_1 int(10) NOT NULL default '0', MODIFY COLUMN country_nation_2 int(10) NOT NULL default '0', MODIFY COLUMN country_nation_3 int(10) NOT NULL default '0', MODIFY COLUMN country_nation_4 int(10) NOT NULL default '0', MODIFY COLUMN country_nation_5 int(10) NOT NULL default '0', MODIFY COLUMN country_nation_other int(10) NOT NULL default '0', MODIFY COLUMN platform_1 int(10) NOT NULL default '0', MODIFY COLUMN platform_2 int(10) NOT NULL default '0', MODIFY COLUMN platform_3 int(10) NOT NULL default '0', MODIFY COLUMN platform_4 int(10) NOT NULL default '0', MODIFY COLUMN platform_5 int(10) NOT NULL default '0', MODIFY COLUMN platform_other int(10) NOT NULL default '0', MODIFY COLUMN version_1 int(10) NOT NULL default '0', MODIFY COLUMN version_2 int(10) NOT NULL default '0', MODIFY COLUMN version_3 int(10) NOT NULL default '0', MODIFY COLUMN version_4 int(10) NOT NULL default '0', MODIFY COLUMN version_5 int(10) NOT NULL default '0', MODIFY COLUMN version_other int(10) NOT NULL default '0', MODIFY COLUMN server_status tinyint(1) NOT NULL default '0', MODIFY COLUMN server_free_slots smallint(5) NOT NULL default '0', MODIFY COLUMN server_used_slots smallint(5) NOT NULL default '0', MODIFY COLUMN server_channel_amount smallint(5) NOT NULL default '0', MODIFY COLUMN server_ping smallint(5) NOT NULL default '0', MODIFY COLUMN server_id smallint(5) NOT NULL default '0', MODIFY COLUMN server_pass tinyint(1) NOT NULL default '0'") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table stats_server (part1) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.stats_server ADD (user_today int(10) NOT NULL default '0', user_week int(10) NOT NULL default '0', user_month int(10) NOT NULL default '0', user_quarter int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table stats_server (part2) successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.stats_user MODIFY COLUMN removed tinyint(1) NOT NULL default '0', MODIFY COLUMN rank int(10) NOT NULL default '0', MODIFY COLUMN count_week int(10) NOT NULL default '0', MODIFY COLUMN count_month int(10) NOT NULL default '0', MODIFY COLUMN idle_week int(10) NOT NULL default '0', MODIFY COLUMN idle_month int(10) NOT NULL default '0', MODIFY COLUMN achiev_count tinyint(1) NOT NULL default '0', MODIFY COLUMN achiev_time int(10) NOT NULL default '0', MODIFY COLUMN achiev_connects smallint(5) NOT NULL default '0', MODIFY COLUMN achiev_battles tinyint(3) NOT NULL default '0', MODIFY COLUMN achiev_time_perc tinyint(3) NOT NULL default '0', MODIFY COLUMN achiev_connects_perc tinyint(3) NOT NULL default '0', MODIFY COLUMN achiev_battles_perc tinyint(3) NOT NULL default '0', MODIFY COLUMN battles_total tinyint(3) NOT NULL default '0', MODIFY COLUMN battles_won tinyint(3) NOT NULL default '0', MODIFY COLUMN battles_lost tinyint(3) NOT NULL default '0', MODIFY COLUMN total_connections smallint(5) NOT NULL default '0', MODIFY COLUMN client_description varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table stats_user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.user MODIFY COLUMN cldbid int(10) NOT NULL default '0', MODIFY COLUMN count int(10) NOT NULL default '0', MODIFY COLUMN name varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN grpid int(10) NOT NULL default '0', MODIFY COLUMN nextup int(10) NOT NULL default '0', MODIFY COLUMN idle int(10) NOT NULL default '0', MODIFY COLUMN cldgroup varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN online tinyint(1) NOT NULL default '0', MODIFY COLUMN boosttime int(10) NOT NULL default '0', MODIFY COLUMN rank int(10) NOT NULL default '0', MODIFY COLUMN platform varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN nation varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN version varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN except tinyint(1) NOT NULL default '0'") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.config MODIFY COLUMN webuser varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN webpass varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN tshost varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN tsquery smallint(5) NOT NULL default '0', MODIFY COLUMN tsvoice smallint(5) NOT NULL default '0', MODIFY COLUMN tsuser varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN tspass varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN language char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN queryname varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN queryname2 varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN grouptime varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN resetbydbchange tinyint(1) NOT NULL default '0', MODIFY COLUMN msgtouser tinyint(1) NOT NULL default '0', MODIFY COLUMN upcheck tinyint(1) NOT NULL default '0', MODIFY COLUMN uniqueid varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN updateinfotime mediumint(6) NOT NULL default '0', MODIFY COLUMN currvers varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN substridle tinyint(1) NOT NULL default '0', MODIFY COLUMN exceptuuid varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN exceptgroup varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN dateformat varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN showexcld tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolcld tinyint(1) NOT NULL default '0', MODIFY COLUMN showcoluuid tinyint(1) NOT NULL default '0', MODIFY COLUMN showcoldbid tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolot tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolit tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolat tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolnx tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolsg tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolrg tinyint(1) NOT NULL default '0', MODIFY COLUMN showcolls tinyint(1) NOT NULL default '0', MODIFY COLUMN slowmode mediumint(9) NOT NULL default '0', MODIFY COLUMN cleanclients tinyint(1) NOT NULL default '0', MODIFY COLUMN cleanperiod mediumint(9) NOT NULL default '0', MODIFY COLUMN showhighest tinyint(1) NOT NULL default '0', MODIFY COLUMN boost varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN showcolas tinyint(1) NOT NULL default '0', MODIFY COLUMN defchid int(10) NOT NULL default '0', MODIFY COLUMN advancemode tinyint(1) NOT NULL default '0', MODIFY COLUMN count_access tinyint(2) NOT NULL default '0', MODIFY COLUMN ignoreidle smallint(5) NOT NULL default '0', MODIFY COLUMN exceptcid varchar(999) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN rankupmsg varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN boost_mode tinyint(1) NOT NULL default '0', MODIFY COLUMN servernews varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN nextupinfo tinyint(1) NOT NULL default '0', MODIFY COLUMN nextupinfomsg1 varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN nextupinfomsg2 varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN nextupinfomsg3 varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci, MODIFY COLUMN shownav tinyint(1) NOT NULL default '0', MODIFY COLUMN showgrpsince tinyint(1) NOT NULL default '0', MODIFY COLUMN resetexcept tinyint(1) NOT NULL default '0'") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table config successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.server_usage MODIFY COLUMN clients smallint(5) NOT NULL default '0', MODIFY COLUMN channel smallint(5) NOT NULL default '0'") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table server_usage successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.user_snapshot MODIFY COLUMN count int(10) NOT NULL default '0', MODIFY COLUMN idle int(10) NOT NULL default '0'") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table user_snapshot successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.groups MODIFY COLUMN sgid int(10) NOT NULL default '0' PRIMARY KEY, MODIFY COLUMN sgidname varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Adjusted table groups successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE $dbname.stats_nations (nation varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci, count int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Created table stats_nations successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE $dbname.stats_versions (version varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci, count int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Created table stats_versions successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE $dbname.stats_platforms (platform varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci, count int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.1] Created table stats_platforms successfully.");
			}
		}
		if(version_compare($currvers, '1.2.1', '<=')) {
			if($mysqlcon->exec("ALTER TABLE $dbname.stats_user ADD (active_week int(10) NOT NULL default '0', active_month int(10) NOT NULL default '0')") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.2] Adjusted table stats_user successfully.");
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (avatar_delay smallint(5) UNSIGNED NOT NULL default '0')") === false) { } else {
				if($mysqlcon->exec("UPDATE $dbname.config set avatar_delay='0'") === false) { } else {
					enter_logfile($logpath,$timezone,4,"    [1.2.2] Adjusted table config (part 1) successfully.");
				}
			}
			if($mysqlcon->exec("ALTER TABLE $dbname.config MODIFY COLUMN tsquery smallint(5) UNSIGNED NOT NULL default '0'") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.2] Adjusted table config (part 2) successfully.");
			}
			if($mysqlcon->exec("CREATE TABLE $dbname.addons_config (param varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci UNIQUE, value varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				if($mysqlcon->exec("INSERT INTO $dbname.addons_config (param,value) VALUES ('assign_groups_active','0'),('assign_groups_groupids',''),('assign_groups_limit','')") === false) { } else {
					enter_logfile($logpath,$timezone,4,"    [1.2.2] Created table addons_config successfully.");
				}
			}
			if($mysqlcon->exec("CREATE TABLE $dbname.addon_assign_groups (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci, grpids varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) { } else {
				enter_logfile($logpath,$timezone,4,"    [1.2.2] Created table addon_assign_groups successfully.");
			}
		}
		$currvers = set_new_version($mysqlcon,$dbname,$timezone,$newversion,$logpath);
		old_files($timezone,$logpath);
		check_chmod($timezone,$logpath,$lang);
		check_config($mysqlcon,$dbname);
	}
	return $currvers;
}
?>