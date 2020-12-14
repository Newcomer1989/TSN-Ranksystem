<?PHP
$rsversion = '1.3.14';

require_once('other/_functions.php');
require_once('other/config.php');
start_session($cfg);
$lang = set_language(get_language($cfg));
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="tsicons/rs.png">
	<title>TS-N.NET Ranksystem</title>
	<link href="libs/combined_wi.css" rel="stylesheet">
	<script src="libs/combined_wi.js"></script>
</head>
<body>
	<div id="wrapper">
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-header">
				<a class="navbar-brand" href="index.php">TSN Ranksystem - <?PHP echo $lang['install'],' (',$rsversion,')'; ?></a>
			</div>
			<ul class="nav navbar-right top-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fas fa-globe-europe"></i>&nbsp;<b class="caret"></b></a>
					<ul class="dropdown-menu">
					<?PHP
					if(is_dir(__DIR__.'/languages/')) {
						foreach(scandir(__DIR__.'/languages/') as $file) {
							if ('.' === $file || '..' === $file || is_dir($file)) continue;
							$sep_lang = preg_split("/[._]/", $file);
							if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
								echo '<li><a href="?lang='.$sep_lang[1].'"><span class="flag-icon flag-icon-'.$sep_lang[3].'"></span>&nbsp;&nbsp;'.strtoupper($sep_lang[1]).' - '.$sep_lang[2].'</a></li>';
							}
						}
					}
					?>
					</ul>
				</li>
			</ul>
			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav side-nav">
					<?PHP
					if (!isset($_POST['install']) && !isset($_POST['confweb'])) {
						echo '<li class="active"><a>1. ',$lang['instdb'],'</a></li>';
					} else {
						echo '<li><a>1. ',$lang['instdb'],'</a></li>';
					}
					if (isset($_POST['install'])) {
						echo '<li class="active"><a>2. ',$lang['isntwiusrcr'],'</a></li>';
					} else {
						echo '<li><a>2. ',$lang['isntwiusrcr'],'</a></li>';
					}
					if (isset($_POST['confweb'])) {
						echo '<li class="active"><a class="active">3. ',$lang['isntwicfg2'],'</a></li>';
					} else {
						echo '<li><a>3. ',$lang['isntwicfg2'],'</a></li>';
					}
					?>
				</ul>
			</div>
		</nav>
<?PHP

function install($type, $host, $user, $pass, $dbname, $lang, $mysqlcon, &$err_msg, &$err_lvl, &$install_webuser) {
	$newconfig='<?php
$db[\'type\']=\''.$type.'\';
$db[\'host\']=\''.$host.'\';
$db[\'user\']=\''.$user.'\';
$db[\'pass\']=\''.$pass.'\';
$db[\'dbname\']=\''.$dbname.'\';
?>';
	
	if(!is_writable('./other/dbconfig.php')) {
		$err_msg = $lang['isntwicfg'];
		$err_lvl = 2;
	} else {
		$count = 1;
		if(($mysqlcon->exec("DROP DATABASE `$dbname`")) === false) { }
		
		if($mysqlcon->exec("CREATE DATABASE `$dbname`") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}

		if($mysqlcon->exec("CREATE TABLE `$dbname`.`user` (`uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`cldbid` int(10) NOT NULL default '0',`count` DECIMAL(14,3) NOT NULL default '0',`name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,`lastseen` int(10) UNSIGNED NOT NULL default '0',`grpid` int(10) NOT NULL default '0',`nextup` int(10) NOT NULL default '0',`idle` DECIMAL(14,3) NOT NULL default '0',`cldgroup` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`online` tinyint(1) NOT NULL default '0',`boosttime` int(10) UNSIGNED NOT NULL default '0',`rank` smallint(5) UNSIGNED NOT NULL default '65535',`platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,`nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,`version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,`firstcon` int(10) UNSIGNED NOT NULL default '0',`except` tinyint(1) NOT NULL default '0',`grpsince` int(10) UNSIGNED NOT NULL default '0',`cid` int(10) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		} else {
			if($mysqlcon->exec("CREATE INDEX `user_version` ON `$dbname`.`user` (`version`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			if($mysqlcon->exec("CREATE INDEX `user_cldbid` ON `$dbname`.`user` (`cldbid` ASC,`uuid`,`rank`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			if($mysqlcon->exec("CREATE INDEX `user_online` ON `$dbname`.`user` (`online`,`lastseen`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`groups` (`sgid` int(10) UNSIGNED NOT NULL default '0' PRIMARY KEY,`sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,`iconid` bigint(10) NOT NULL default '0',`icondate` int(10) UNSIGNED NOT NULL default '0',`sortid` int(10) NOT NULL default '0',`type` tinyint(1) NOT NULL default '0',`ext` char(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`cfg_params` (`param` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `value` varchar(21588) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`server_usage` (`timestamp` int(10) UNSIGNED NOT NULL default '0',`clients` smallint(5) UNSIGNED NOT NULL default '0',`channel` smallint(5) UNSIGNED NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		} else {
			if($mysqlcon->exec("CREATE INDEX `serverusage_timestamp` ON `$dbname`.`server_usage` (`timestamp`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`user_snapshot` (`id` tinyint(3) UNSIGNED NOT NULL default '0',`cldbid` int(10) UNSIGNED NOT NULL default '0',`count` int(10) UNSIGNED NOT NULL default '0',`idle` int(10) UNSIGNED NOT NULL default '0',PRIMARY KEY (`id`,`cldbid`));") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		} else {
			if($mysqlcon->exec("CREATE INDEX `snapshot_id` ON `$dbname`.`user_snapshot` (`id`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			if($mysqlcon->exec("CREATE INDEX `snapshot_cldbid` ON `$dbname`.`user_snapshot` (`cldbid`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_server` (`total_user` int(10) NOT NULL default '0',`total_online_time` bigint(13) NOT NULL default '0',`total_online_month` bigint(11) NOT NULL default '0',`total_online_week` bigint(11) NOT NULL default '0',`total_active_time` bigint(11) NOT NULL default '0',`total_inactive_time` bigint(11) NOT NULL default '0',`country_nation_name_1` varchar(3) NOT NULL default '0',`country_nation_name_2` varchar(3) NOT NULL default '0',`country_nation_name_3` varchar(3) NOT NULL default '0',`country_nation_name_4` varchar(3) NOT NULL default '0',`country_nation_name_5` varchar(3) NOT NULL default '0',`country_nation_1` int(10) NOT NULL default '0',`country_nation_2` int(10) NOT NULL default '0',`country_nation_3` int(10) NOT NULL default '0',`country_nation_4` int(10) NOT NULL default '0',`country_nation_5` int(10) NOT NULL default '0',`country_nation_other` int(10) NOT NULL default '0',`platform_1` int(10) NOT NULL default '0',`platform_2` int(10) NOT NULL default '0',`platform_3` int(10) NOT NULL default '0',`platform_4` int(10) NOT NULL default '0',`platform_5` int(10) NOT NULL default '0',`platform_other` int(10) NOT NULL default '0',`version_name_1` varchar(35) NOT NULL default '0',`version_name_2` varchar(35) NOT NULL default '0',`version_name_3` varchar(35) NOT NULL default '0',`version_name_4` varchar(35) NOT NULL default '0',`version_name_5` varchar(35) NOT NULL default '0',`version_1` int(10) NOT NULL default '0',`version_2` int(10) NOT NULL default '0',`version_3` int(10) NOT NULL default '0',`version_4` int(10) NOT NULL default '0',`version_5` int(10) NOT NULL default '0',`version_other` int(10) NOT NULL default '0',`server_status` tinyint(1) NOT NULL default '0',`server_free_slots` smallint(5) NOT NULL default '0',`server_used_slots` smallint(5) NOT NULL default '0',`server_channel_amount` smallint(5) NOT NULL default '0',`server_ping` smallint(5) NOT NULL default '0',`server_packet_loss` float (4,4),`server_bytes_down` bigint(11) NOT NULL default '0',`server_bytes_up` bigint(11) NOT NULL default '0',`server_uptime` bigint(11) NOT NULL default '0',`server_id` smallint(5) NOT NULL default '0',`server_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,`server_pass` tinyint(1) NOT NULL default '0',`server_creation_date` bigint(11) NOT NULL default '0',`server_platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`server_weblist` tinyint(1) NOT NULL default '0',`server_version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`user_today` int(10) NOT NULL default '0',`user_week` int(10) NOT NULL default '0',`user_month` int(10) NOT NULL default '0',`user_quarter` int(10) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_user` (`uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`removed` tinyint(1) NOT NULL default '0',`total_connections` MEDIUMINT(8) UNSIGNED NOT NULL default '0',`count_week` mediumint(8) UNSIGNED NOT NULL default '0',`count_month` mediumint(8) UNSIGNED NOT NULL default '0',`idle_week` mediumint(8) UNSIGNED NOT NULL default '0',`idle_month` mediumint(8) UNSIGNED NOT NULL default '0',`client_description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,`base64hash` char(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,`client_total_up` bigint(15) NOT NULL default '0',`client_total_down` bigint(15) NOT NULL default '0',`active_week` mediumint(8) UNSIGNED NOT NULL default '0',`active_month` mediumint(8) UNSIGNED NOT NULL default '0',`last_calculated` int(10) UNSIGNED NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("INSERT INTO `$dbname`.`stats_server` SET `total_user`='9999'") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`admin_addtime` (`uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`timestamp` int(10) UNSIGNED NOT NULL default '0',`timecount` int(10) NOT NULL default '0', PRIMARY KEY (`uuid`,`timestamp`))") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`user_iphash` (`uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`iphash` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`ip` varchar(39) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`job_check` (`job_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`timestamp` int(10) UNSIGNED NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`) VALUES ('calc_user_limit'),('calc_user_lastscan'),('check_update'),('database_export'),('get_version'),('clean_db'),('clean_clients'),('calc_donut_chars'),('calc_server_stats'),('get_avatars'),('last_snapshot_id'),('last_snapshot_time'),('last_update'),('reload_trigger'),('reset_user_time'),('reset_user_delete'),('reset_group_withdraw'),('reset_webspace_cache'),('reset_usage_graph'),('reset_stop_after'),('runtime_check'),('update_groups')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}

		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_nations` (`nation` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`count` smallint(5) UNSIGNED NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_versions` (`version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`count` smallint(5) UNSIGNED NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_platforms` (`platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`count` smallint(5) UNSIGNED NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		} else {
			if($mysqlcon->exec("INSERT INTO `$dbname`.`stats_platforms` (`platform`,`count`) VALUES ('Windows',0),('Android',0),('OSX',0),('iOS',0),('Linux',0)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`addons_config` (`param` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci UNIQUE,`value` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("INSERT INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('assign_groups_active','0'),('assign_groups_name',''),('assign_groups_excepted_groupids',''),('assign_groups_groupids',''),('assign_groups_limit','')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`addon_assign_groups` (`uuid` char(28) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`grpids` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`csrf_token` (`token` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `timestamp` int(10) UNSIGNED NOT NULL default '0', `sessionid` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}

		if($count == 1) {
			$err_msg = sprintf($lang['instdbsuc'], $dbname); $err_lvl = NULL;
			$install_webuser = 1;
			
			$dbconfig = fopen('./other/dbconfig.php','w');
			if(!fwrite($dbconfig, $newconfig)) {
				$err_msg = $lang['isntwicfg'];
				$err_lvl = 2;
			}
			fclose($dbconfig);
		}
	}
}

if (isset($_POST['install'])) {
	unset($err_msg);
	if ($_POST['dbtype'] == 'mysql') {
		if(!in_array('pdo_mysql', get_loaded_extensions())) {
			unset($err_msg); $err_msg = sprintf($lang['insterr2'],'PHP MySQL','//php.net/manual/en/ref.pdo-mysql.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
		} else {
			$dboptions = array();
		}
	} else {
		$dboptions = array();
	}
	
	if(!isset($err_msg)) {
		$dbserver  = $_POST['dbtype'].':host='.$_POST['dbhost'].'; dbname='.$_POST['dbname'].';charset=utf8mb4';
		$dbserver2 = $_POST['dbtype'].':host='.$_POST['dbhost'].';charset=utf8mb4';
		$dbexists = 0;
		try {
			$mysqlcon = new PDO($dbserver, $_POST['dbuser'], $_POST['dbpass'], $dboptions);
			$dbexists = 1;
		} catch (PDOException $e) {
			try {
				$mysqlcon = new PDO($dbserver2, $_POST['dbuser'], $_POST['dbpass'], $dboptions);
			} catch (PDOException $e) {
				$err_msg = $lang['dbconerr'].$e->getMessage(); $err_lvl = 1;
			}
		}
		
		if(!is_writable('./other/dbconfig.php')) {
			$err_msg = $lang['isntwicfg'];
			$err_lvl = 2;
		}
	}
	
	if(!isset($err_msg)) {
		if(isset($_POST['installchecked'])) {
			install($_POST['dbtype'], $_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname'], $lang, $mysqlcon, $err_msg, $err_lvl, $install_webuser);
		} elseif($dbexists == 1) {
			$err_msg = sprintf($lang['insterr1'],$_POST['dbname']);
			$err_lvl = 2;
			$show_warning = 1;
		} else {
			install($_POST['dbtype'], $_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname'], $lang, $mysqlcon, $err_msg, $err_lvl, $install_webuser);
		}
	}
}

if(isset($_POST['confweb'])) {
	require_once('other/dbconfig.php');
	$user=$_POST['user'];
	$pass=password_hash($_POST['pass'], PASSWORD_DEFAULT);
	$logpath = addslashes(__DIR__."/logs/");
	$dbname = $db['dbname'];
	$dbserver = $db['type'].':host='.$db['host'].'; dbname=`'.$db['dbname'].'`;charset=utf8mb4';
	$dbserver2 = $db['type'].':host='.$db['host'];
	try {
		$mysqlcon = new PDO($dbserver, $db['user'], $db['pass']);
	} catch (PDOException $e) {
		try {
			$mysqlcon = new PDO($dbserver2, $db['user'], $db['pass']);
		} catch (PDOException $e) {
			$err_msg = $lang['dbconerr'].$e->getMessage(); $err_lvl = 1;
		}
	}
	if(!isset($err_lvl) || $err_lvl != 1) {
		$dateformat = $mysqlcon->quote("%a days, %h hours, %i mins, %s secs");
		$nextupinfomsg1 = $mysqlcon->quote("Your next rank up will be in %1\$s days, %2\$s hours, %3\$s minutes and %4\$s seconds. The next servergroup you will reach is [B]%5\$s[/B].");
		$nextupinfomsg2 = $mysqlcon->quote("You have already reached the highest rank.");
		$nextupinfomsg3 = $mysqlcon->quote("You are excepted from the Ranksystem. If you wish to rank contact an admin on the TS3 server.");
		$servernews = $mysqlcon->quote("<strong>Message</strong><br>This is an example Message.<br>Change this Message inside the webinterface.");
		$rankupmsg = $mysqlcon->quote('Hey, you reached a higher rank, since you already connected for %1$s days, %2$s hours and %3$s minutes to our TS3 server.[B]Keep it up![/B] ;-) ');
		if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('default_date_format', $dateformat), ('default_language', 'en'), ('default_session_sametime', 'Strict'), ('default_header_origin', ''), ('default_header_xss', '1; mode=block'), ('default_header_contenttyp', '1'), ('default_header_frame', 'SAMEORIGIN'), ('logs_path', '{$logpath}'), ('logs_timezone', 'Europe/Berlin'), ('logs_debug_level', '5'), ('logs_rotation_size', '5'), ('rankup_boost_definition', ''), ('rankup_clean_clients_period', '86400'), ('rankup_clean_clients_switch', '1'), ('rankup_client_database_id_change_switch', '0'), ('rankup_definition', '31536000=>7=>0'), ('rankup_excepted_channel_id_list', ''), ('rankup_excepted_group_id_list', ''), ('rankup_excepted_mode', '0'), ('rankup_excepted_unique_client_id_list', ''), ('rankup_hash_ip_addresses_mode', '2'), ('rankup_ignore_idle_time', '600'), ('rankup_message_to_user', $rankupmsg), ('rankup_message_to_user_switch', '1'), ('rankup_next_message_1', $nextupinfomsg1), ('rankup_next_message_2', $nextupinfomsg2), ('rankup_next_message_3', $nextupinfomsg3), ('rankup_next_message_mode', '1'), ('rankup_time_assess_mode', '0'), ('stats_api_keys', ''), ('stats_column_active_time_switch', '0'), ('stats_column_current_group_since_switch', '1'), ('stats_column_current_server_group_switch', '1'), ('stats_column_client_db_id_switch', '0'), ('stats_column_client_name_switch', '1'), ('stats_column_idle_time_switch', '1'), ('stats_column_last_seen_switch', '1'), ('stats_column_nation_switch', '0'), ('stats_column_next_rankup_switch', '1'), ('stats_column_next_server_group_switch', '1'), ('stats_column_online_time_switch', '1'), ('stats_column_platform_switch', '0'), ('stats_column_rank_switch', '1'), ('stats_column_unique_id_switch', '0'), ('stats_column_default_sort', 'lastseen'), ('stats_column_default_sort_2', 'rank'), ('stats_column_default_order', 'desc'), ('stats_column_default_order_2', 'asc'), ('stats_column_version_switch', '0'), ('stats_imprint_switch', '0'), ('stats_imprint_address', 'Max Mustermann<br>Musterstraße 13<br>05172 Musterhausen<br>Germany'), ('stats_imprint_address_url', 'https://site.url/imprint/'), ('stats_imprint_email', 'info@example.com'), ('stats_imprint_notes', NULL), ('stats_imprint_phone', '+49 171 1234567'), ('stats_imprint_privacypolicy', 'Add your own privacy policy here. (editable in the webinterface)'), ('stats_imprint_privacypolicy_url', 'https://site.url/privacy/'), ('stats_server_news', $servernews), ('stats_show_clients_in_highest_rank_switch', '1'), ('stats_show_excepted_clients_switch', '1'), ('stats_show_maxclientsline_switch', 0), ('stats_show_site_navigation_switch', '1'), ('stats_time_bronze','50'), ('stats_time_silver','100'), ('stats_time_gold','250'), ('stats_time_legend','500'), ('stats_connects_bronze','50'), ('stats_connects_silver','100'), ('stats_connects_gold','250'), ('stats_connects_legend','500'), ('teamspeak_avatar_download_delay', '0'), ('teamspeak_default_channel_id', '0'), ('teamspeak_host_address', '127.0.0.1'), ('teamspeak_query_command_delay', '0'), ('teamspeak_query_encrypt_switch', '0'), ('teamspeak_query_nickname', 'Ranksystem'), ('teamspeak_query_pass', ''), ('teamspeak_query_port', '10011'), ('teamspeak_query_user', 'serveradmin'), ('teamspeak_verification_channel_id', '0'), ('teamspeak_voice_port', '9987'), ('version_current_using', '{$rsversion}'), ('version_latest_available', '{$rsversion}'), ('version_update_channel', 'stable'), ('webinterface_access_count', '0'), ('webinterface_access_last', '0'), ('webinterface_admin_client_unique_id_list', ''), ('webinterface_advanced_mode', '0'), ('webinterface_fresh_installation', '1'), ('webinterface_pass', '{$pass}'), ('webinterface_user', '{$user}');") === false) {
			$err_msg = $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true); $err_lvl = 2;
		} else {
			$err_msg = $lang['isntwiusr'].'<br><br>';
			$err_msg = $lang['isntwiusr2'].'<br><br>';
			$err_msg .= sprintf($lang['isntwiconf'],"<a href=\"webinterface\\\">/webinterface/</a>").'<br><br>';
			if(!unlink('./install.php')) {
				$err_msg .= $lang['isntwidel'];
			}
			$install_finished = 1; $err_lvl = NULL;
		}
	}
}

if (!isset($_POST['install']) && !isset($_POST['confweb'])) {
	unset($err_msg);
	unset($err_lvl);
	$err_msg = '';
	if(!is_writable('./other/dbconfig.php')) {
		$err_msg = $lang['isntwicfg']; $err_lvl = 3;
	}

	$file_err_count=0;
	$file_err_msg = '';
	try {
		$scandir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
		$files = array(); 
		foreach ($scandir as $object) {
			if(!strstr($object, '/.') && !strstr($object, '\.')) {
				if (!$object->isDir()) {
					if(!is_writable($object->getPathname())) {
						$file_err_msg .= "File is not writeable ".$object."<br>";
						$file_err_count++;
					}
				} else {
					if(!is_writable($object->getPathname())) {
						$file_err_msg .= "Folder is not writeable ".$object."<br>";
						$file_err_count++;
					}
				}
			}
		}
	} catch (Exception $e) {
		$err_msg .= "File Permissions Error: ".$e->getCode()." ".$e->getMessage();
		$err_lvl = 3;
	}
	
	if($file_err_count!=0) {
		$err_msg = "<b>Wrong file/folder permissions!</b><br>You need to correct the owner and access permissions of the named files/folders!<br><br>The <u>owner</u> of all files and folders of the Ranksystem installation folder must be the user of your webserver (e.g.: www-data).<br>On Linux systems you may do something like this (linux shell command):<br>chown -R www-data:www-data ".__DIR__."<br><br>Also the <u>access permission</u> must be set, that the user of your webserver is able to read, write and execute files.<br>On Linux systems you may do something like this (linux shell command):<br>chmod -R 640 ".__DIR__."<br><br><br>List of concerned files/folders:<br>";
		$err_lvl = 3;
		$err_msg .= $file_err_msg;
	}

	if(!class_exists('PDO')) {
		$err_msg = sprintf($lang['insterr2'],'PHP PDO','//php.net/manual/en/book.pdo.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
	}
	if(version_compare(phpversion(), '5.5.0', '<')) {
		$err_msg = sprintf($lang['insterr4'],phpversion()); $err_lvl = 3;
	}
	if(!function_exists('simplexml_load_file')) {
		$err_msg = sprintf($lang['insterr2'],'PHP SimpleXML','//php.net/manual/en/book.simplexml.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
	}
	if(!in_array('curl', get_loaded_extensions())) {
		$err_msg = sprintf($lang['insterr2'],'PHP cURL','//php.net/manual/en/book.curl.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
	}
	if(!in_array('zip', get_loaded_extensions())) {
		$err_msg = sprintf($lang['insterr2'],'PHP Zip','//php.net/manual/en/book.zip.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
	}
	if(!in_array('mbstring', get_loaded_extensions())) {
		$err_msg = sprintf($lang['insterr2'],'PHP mbstring','//php.net/manual/en/book.mbstring.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
	}
	if(!in_array('openssl', get_loaded_extensions())) {
		unset($err_msg); $err_msg = sprintf($lang['insterr2'],'PHP OpenSSL','//php.net/manual/en/book.openssl.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	}
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		if(!in_array('com_dotnet', get_loaded_extensions())) {
			$err_msg = sprintf($lang['insterr2'],'PHP COM and .NET (Windows only)','//php.net/manual/en/book.com.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
		}
	}
	if(!function_exists('exec')) {
		unset($err_msg); $err_msg = sprintf($lang['insterr3'],'exec','//php.net/manual/en/book.exec.php',get_cfg_var('cfg_file_path')); $err_lvl = 3;
	} else {
		if ($err_msg == NULL) {
			require_once('other/phpcommand.php');
			exec("$phpcommand -v", $phpversioncheck);
			$output = '';
			foreach($phpversioncheck as $line) $output .= print_r($line, true).'<br>';
			if(empty($phpversioncheck) || strtoupper(substr($phpversioncheck[0], 0, 3)) != "PHP") {
				$err_msg .= sprintf($lang['chkphpcmd'], "\"other/phpcommand.php\"", "<u>\"other/phpcommand.php\"</u>", '<pre>'.$phpcommand.'</pre>', '<pre>'.$output.'</pre><br><br>', '<pre>php -v</pre>'); $err_lvl = 3;
			} else {
				$exploded = explode(' ',$phpversioncheck[0]);
				if($exploded[1] != phpversion()) {
					$err_msg .= sprintf($lang['chkphpmulti'], phpversion(), "<u>\"other/phpcommand.php\"</u>", $exploded[1], "\"other/phpcommand.php\"</u>", "\"other/phpcommand.php\"</u>", '<pre>'.$phpcommand.'</pre>');
					if(getenv('PATH')!='') {
						$err_msg .= "<br><br>".sprintf($lang['chkphpmulti2'], '<br>'.getenv('PATH')); $err_lvl = 2;
					}
				}
			}
		}
	}
	
	if($err_msg == '' && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
		$host = "<a href=\"https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF'])."/install.php", '/\\')."\">";
		$err_msg = sprintf($lang['winav10'], $host,'</a>!<br>', '<br>'); $err_lvl = 2;
	}
	
	if(!isset($err_lvl)) {
		unset($err_msg);
	}
}
	
if ((!isset($_POST['install']) && !isset($_POST['confweb'])) || $err_lvl == 1 || $err_lvl == 2 || $err_lvl == 3) {
	if(isset($show_warning)) {
		$dbhost = $_POST['dbhost'];
		$dbname = $_POST['dbname'];
		$dbuser = $_POST['dbuser'];
		$dbpass = $_POST['dbpass'];
	} elseif(isset($_GET["dbhost"]) && isset($_GET["dbname"]) && isset($_GET["dbuser"]) && isset($_GET["dbpass"])) {
		$dbhost = $_GET["dbhost"];
		$dbname = $_GET['dbname'];
		$dbuser = $_GET['dbuser'];
		$dbpass = $_GET['dbpass'];
	} else {
		$dbhost = "";
		$dbname = "";
		$dbuser = "";
		$dbpass = "";
	}
	?>
	<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						<?php echo $lang['winav2'],' ',$lang['wihlset']; ?>
					</h1>
				</div>
			</div>
			<form class="form-horizontal" data-toggle="validator" name="install" method="POST">
				<div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbtypedesc"><?php echo $lang['isntwidbtype']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="col-sm-8">
										<select class="selectpicker show-tick form-control required" id="basic" name="dbtype" required>
										<option disabled value=""> -- select database -- </option>
										<option data-subtext="Cubrid" value="cubrid">cubrid</option>
										<option data-subtext="FreeTDS / Microsoft SQL Server / Sybase" value="dblib">dblib</option>
										<option data-subtext="Firebird/Interbase" value="firebird">firebird</option>
										<option data-subtext="IBM DB2" value="ibm">ibm</option>
										<option data-subtext="IBM Informix Dynamic Server" value="informix">informix</option>
										<option data-subtext="MySQL [recommended]" value="mysql" selected>mysql (also mariadb)</option>
										<option data-subtext="Oracle Call Interface" value="oci">oci</option>
										<option data-subtext="ODBC v3 (IBM DB2, unixODBC und win32 ODBC)" value="odbc">odbc</option>
										<option data-subtext="PostgreSQL" value="pgsql">pgsql</option>
										<option data-subtext="SQLite 3 und SQLite 2" value="sqlite">sqlite</option>
										<option data-subtext="Microsoft SQL Server / SQL Azure" value="sqlsrv">sqlsrv</option>
										<option data-subtext="4D" value="4d">4d</option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbhostdesc"><?php echo $lang['isntwidbhost']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="col-sm-8 required-field-block">
										<input type="text" class="form-control required" name="dbhost" placeholder="localhost" value="<?php echo $dbhost; ?>" required>
									</div>
								</div>
								<div class="form-group required-field-block">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbnamedesc"><?php echo $lang['isntwidbname']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="col-sm-8 required-field-block">
										<input type="text" data-pattern="^([A-Za-z0-9$_]){1,64}$" data-error="Please do not use special characters or more then 64 characters inside the database name!" class="form-control required" name="dbname" placeholder="ts3_ranksystem" value="<?php echo $dbname; ?>" required>
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="panel panel-default">
							<div class="panel-body">
								<div class="form-group required-field-block">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbusrdesc"><?php echo $lang['isntwidbusr']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="col-sm-8 required-field-block">
										<input type="text" placeholder="<?php echo $lang['user'] ?>" data-pattern="^[^&quot;'\\-\s]+$" data-error="Please do not use one of the following special characters: ' \ &quot; - also no whitespace and do not use more then 64 characters inside the database user!" class="form-control required" name="dbuser" value="<?php echo $dbuser; ?>" maxlength="64" required>
										<div class="help-block with-errors"></div>
									</div>
								</div>
								<div class="form-group required-field-block">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbpassdesc"><?php echo $lang['isntwidbpass']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="col-sm-8 required-field-block">
										<div class="input-group">
											<span id="toggle-password2" class="input-group-addon" onclick="togglepwd()" style="cursor: pointer; pointer-events: all;"><svg class="svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z"></path></svg></span>
											<span id="toggle-password1" class="input-group-addon" onclick="togglepwd()" style="cursor: pointer; pointer-events: all; display: none;"><svg class="svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z"></path></svg></span>
											<input id="password" placeholder="<?php echo $lang['pass'] ?>" type="password" data-pattern="^[^&quot;'\\-\s]+$" data-error="Please do not use one of the following special characters: ' \ &quot; - also no whitespace and do not use more then 64 characters inside the database password!" class="form-control required" name="dbpass" value="<?php echo $dbpass; ?>" data-toggle="password" data-placement="before" maxlength="64" required>
										</div>
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">&nbsp;</div>
				<div class="row">
					<div class="text-center">
						<?PHP
						if(isset($err_lvl) && $err_lvl == 3) {
							echo "<button type=\"submit\" class=\"btn btn-primary\" name=\"install\" disabled>",$lang['instdb'],"</button>";
						} else {
							echo "<button type=\"submit\" class=\"btn btn-primary\" name=\"install\">",$lang['instdb'],"</button>";
						}
						if(isset($show_warning)) {
							echo '<input type="hidden" name="installchecked" value="">';
						}
						?>
					</div>
				</div>
				<div class="row">&nbsp;</div>
			</form>
		</div>
	</div>
	
	<div class="modal fade" id="isntwidbtypedesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbtype']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo sprintf($lang['isntwidbtypedesc'], '<a href="https://ts-ranksystem.com/#linux" target="_blank">https://ts-ranksystem.com/#linux</a>'); ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbhostdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbhost']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbhostdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbusrdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbusr']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbusrdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbpassdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbpass']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbpassdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbnamedesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbname']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbnamedesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwiusrdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwiusrcr']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwiusrdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
<?PHP
} elseif(isset($install_webuser)) {
?>
	<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						<?php echo $lang['isntwiusrh']; ?>
					</h1>
					<h4>
						<?php echo $lang['isntwiusrd']; ?>
					</h4>
				</div>
				<div class="row">&nbsp;</div>
				<div class="row">&nbsp;</div>
			</div>
			<form class="form-horizontal" name="confweb" method="POST">
				<div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwiusrdesc"><?php echo $lang['user']; ?></label>
									<div class="col-sm-8 required-field-block">
										<input type="text" placeholder="<?php echo $lang['user'] ?>" class="form-control required" name="user" maxlength="65536" required>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwiusrdesc"><?php echo $lang['pass']; ?></label>
									<div class="col-sm-8 required-field-block">
										<div class="input-group">
											<span id="toggle-password2" class="input-group-addon" onclick="togglepwd()" style="cursor: pointer; pointer-events: all;"><svg class="svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z"></path></svg></span>
											<span id="toggle-password1" class="input-group-addon" onclick="togglepwd()" style="cursor: pointer; pointer-events: all; display: none;"><svg class="svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z"></path></svg></span>
											<input id="password" placeholder="<?php echo $lang['pass'] ?>" type="password" class="form-control required" name="pass" data-toggle="password" maxlength="65536" required>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</div>
				</div>
				<div class="row">&nbsp;</div>
				<div class="row">
					<div class="text-center">
						<?PHP
						if(isset($err_lvl) && $err_lvl == 3) {
							echo "<button type=\"submit\" class=\"btn btn-primary\" name=\"confweb\" disabled>",$lang['isntwiusrcr'],"</button>";
						} else {
							echo "<button type=\"submit\" class=\"btn btn-primary\" name=\"confweb\">",$lang['isntwiusrcr'],"</button>";
						}
						?>
					</div>
				</div>
				<div class="row">&nbsp;</div>
			</form>
		</div>
	</div>
<?PHP
} elseif(isset($install_finished)) {
?>
	<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
		<div class="container-fluid">
			<div class="row">
			</div>
		</div>
	</div>
<?PHP
}
?>
<script>
$('form[data-toggle="validator"]').validator({
	custom: {
		pattern: function ($el) {
			var pattern = new RegExp($el.data('pattern'));
			return pattern.test($el.val());
		}
	},
	delay: 100,
	errors: {
		pattern: "There should be an error in your value, please check all could be right!"
	}
});
</script>
</body>
</html>