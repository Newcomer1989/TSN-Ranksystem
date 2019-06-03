<?PHP
require_once('other/config.php');
$rsversion = '1.2.12';
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
	<script>
	$(function() {
		$('.required-icon').tooltip({
			placement: 'left',
			title: 'Required field'
		});
	});
	$(function() {
        $('#password').password().on('show.bs.password', function(e) {
            $('#eventLog').text('On show event');
            $('#methods').prop('checked', true);
        }).on('hide.bs.password', function(e) {
				$('#eventLog').text('On hide event');
				$('#methods').prop('checked', false);
			});
        $('#methods').click(function() {
            $('#password').password('toggle');
        });
    });
	</script>
</head>
<body>
	<div id="wrapper">
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-header">
				<a class="navbar-brand" href="index.php">TSN Ranksystem - <?PHP echo $lang['install'],' (',$rsversion,')'; ?></a>
			</div>
			<ul class="nav navbar-right top-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-globe"></i>&nbsp;<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li>
							<a href="?lang=ar"><span class="flag-icon flag-icon-arab"></span>&nbsp;&nbsp;AR - العربية</a>
						</li>
						<li>
							<a href="?lang=cz"><span class="flag-icon flag-icon-cz"></span>&nbsp;&nbsp;CZ - čeština</a>
						</li>
						<li>
							<a href="?lang=de"><span class="flag-icon flag-icon-de"></span>&nbsp;&nbsp;DE - Deutsch</a>
						</li>
						<li>
							<a href="?lang=en"><span class="flag-icon flag-icon-us"></span>&nbsp;&nbsp;EN - English</a>
						</li>
						<li>
							<a href="?lang=es"><span class="flag-icon flag-icon-es"></span>&nbsp;&nbsp;ES - español</a>
						</li>
						<li>
							<a href="?lang=fr"><span class="flag-icon flag-icon-fr"></span>&nbsp;&nbsp;FR - français</a>
						</li>
						<li>
							<a href="?lang=it"><span class="flag-icon flag-icon-it"></span>&nbsp;&nbsp;IT - Italiano</a>
						</li>
						<li>
							<a href="?lang=nl"><span class="flag-icon flag-icon-nl"></span>&nbsp;&nbsp;NL - Nederlands</a>
						</li>
						<li>
							<a href="?lang=pl"><span class="flag-icon flag-icon-pl"></span>&nbsp;&nbsp;PL - polski</a>
						</li>
						<li>
							<a href="?lang=ro"><span class="flag-icon flag-icon-ro"></span>&nbsp;&nbsp;RO - Română</a>
						</li>
						<li>
							<a href="?lang=ru"><span class="flag-icon flag-icon-ru"></span>&nbsp;&nbsp;RU - Русский</a>
						</li>
						<li>
							<a href="?lang=pt"><span class="flag-icon flag-icon-ptbr"></span>&nbsp;&nbsp;PT - Português</a>
						</li>
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
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`user` (`uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`cldbid` int(10) NOT NULL default '0',`count` int(10) NOT NULL default '0',`name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,`lastseen` bigint(11) NOT NULL default '0',`grpid` int(10) NOT NULL default '0',`nextup` int(10) NOT NULL default '0',`idle` int(10) NOT NULL default '0',`cldgroup` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`online` tinyint(1) NOT NULL default '0',`boosttime` int(10) NOT NULL default '0',`rank` int(10) NOT NULL default '0',`platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`nation` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`firstcon` bigint(11) NOT NULL default '0',`except` tinyint(1) NOT NULL default '0',`grpsince` bigint(11) NOT NULL default '0',`cid` int(10) NOT NULL default '0')") === false) {
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
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`groups` (`sgid` int(10) NOT NULL default '0' PRIMARY KEY,`sgidname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,`iconid` bigint(10) NOT NULL default '0',`icondate` bigint(11) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`cfg_params` (`param` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `value` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`server_usage` (`timestamp` bigint(11) NOT NULL default '0',`clients` smallint(5) NOT NULL default '0',`channel` smallint(5) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		} else {
			if($mysqlcon->exec("CREATE INDEX `serverusage_timestamp` ON `$dbname`.`server_usage` (`timestamp`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`user_snapshot` (`timestamp` bigint(11) NOT NULL default '0',`uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`count` int(10) NOT NULL default '0',`idle` int(10) NOT NULL default '0',PRIMARY KEY (`timestamp`,`uuid`))") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		} else {
			if($mysqlcon->exec("CREATE INDEX `snapshot_timestamp` ON `$dbname`.`user_snapshot` (`timestamp`)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_server` (`total_user` int(10) NOT NULL default '0',`total_online_time` bigint(13) NOT NULL default '0',`total_online_month` bigint(11) NOT NULL default '0',`total_online_week` bigint(11) NOT NULL default '0',`total_active_time` bigint(11) NOT NULL default '0',`total_inactive_time` bigint(11) NOT NULL default '0',`country_nation_name_1` varchar(3) NOT NULL default '0',`country_nation_name_2` varchar(3) NOT NULL default '0',`country_nation_name_3` varchar(3) NOT NULL default '0',`country_nation_name_4` varchar(3) NOT NULL default '0',`country_nation_name_5` varchar(3) NOT NULL default '0',`country_nation_1` int(10) NOT NULL default '0',`country_nation_2` int(10) NOT NULL default '0',`country_nation_3` int(10) NOT NULL default '0',`country_nation_4` int(10) NOT NULL default '0',`country_nation_5` int(10) NOT NULL default '0',`country_nation_other` int(10) NOT NULL default '0',`platform_1` int(10) NOT NULL default '0',`platform_2` int(10) NOT NULL default '0',`platform_3` int(10) NOT NULL default '0',`platform_4` int(10) NOT NULL default '0',`platform_5` int(10) NOT NULL default '0',`platform_other` int(10) NOT NULL default '0',`version_name_1` varchar(35) NOT NULL default '0',`version_name_2` varchar(35) NOT NULL default '0',`version_name_3` varchar(35) NOT NULL default '0',`version_name_4` varchar(35) NOT NULL default '0',`version_name_5` varchar(35) NOT NULL default '0',`version_1` int(10) NOT NULL default '0',`version_2` int(10) NOT NULL default '0',`version_3` int(10) NOT NULL default '0',`version_4` int(10) NOT NULL default '0',`version_5` int(10) NOT NULL default '0',`version_other` int(10) NOT NULL default '0',`server_status` tinyint(1) NOT NULL default '0',`server_free_slots` smallint(5) NOT NULL default '0',`server_used_slots` smallint(5) NOT NULL default '0',`server_channel_amount` smallint(5) NOT NULL default '0',`server_ping` smallint(5) NOT NULL default '0',`server_packet_loss` float (4,4),`server_bytes_down` bigint(11) NOT NULL default '0',`server_bytes_up` bigint(11) NOT NULL default '0',`server_uptime` bigint(11) NOT NULL default '0',`server_id` smallint(5) NOT NULL default '0',`server_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,`server_pass` tinyint(1) NOT NULL default '0',`server_creation_date` bigint(11) NOT NULL default '0',`server_platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`server_weblist` tinyint(1) NOT NULL default '0',`server_version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`user_today` int(10) NOT NULL default '0',`user_week` int(10) NOT NULL default '0',`user_month` int(10) NOT NULL default '0',`user_quarter` int(10) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_user` (`uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`removed` tinyint(1) NOT NULL default '0',`rank` int(10) NOT NULL default '0',`total_connections` smallint(5) NOT NULL default '0',`count_week` int(10) NOT NULL default '0',`count_month` int(10) NOT NULL default '0',`idle_week` int(10) NOT NULL default '0',`idle_month` int(10) NOT NULL default '0',`achiev_count` tinyint(1) NOT NULL default '0',`achiev_time` int(10) NOT NULL default '0',`achiev_connects` smallint(5) NOT NULL default '0',`achiev_battles` tinyint(3) NOT NULL default '0',`achiev_time_perc` tinyint(3) NOT NULL default '0',`achiev_connects_perc` tinyint(3) NOT NULL default '0',`achiev_battles_perc` tinyint(3) NOT NULL default '0',`battles_total` tinyint(3) NOT NULL default '0',`battles_won` tinyint(3) NOT NULL default '0',`battles_lost` tinyint(3) NOT NULL default '0',`client_description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,`base64hash` char(58) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`client_total_up` bigint(15) NOT NULL default '0',`client_total_down` bigint(15) NOT NULL default '0',`active_week` int(10) NOT NULL default '0',`active_month` int(10) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("INSERT INTO `$dbname`.`stats_server` SET `total_user`='9999'") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`admin_addtime` (`uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`timestamp` bigint(11) NOT NULL default '0',`timecount` int(10) NOT NULL default '0', PRIMARY KEY (`uuid`,`timestamp`))") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`user_iphash` (`uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`iphash` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,`ip` varchar(39) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`job_check` (`job_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`timestamp` bigint(11) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`) VALUES ('calc_user_limit'),('calc_user_lastscan'),('check_update'),('get_version'),('clean_db'),('clean_clients'),('calc_server_stats'),('runtime_check'),('last_update')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_nations` (`nation` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`count` int(10) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_versions` (`version` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`count` int(10) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`stats_platforms` (`platform` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`count` int(10) NOT NULL default '0')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`addons_config` (`param` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci UNIQUE,`value` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("INSERT INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('assign_groups_active','0'),('assign_groups_groupids',''),('assign_groups_limit','')") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`addon_assign_groups` (`uuid` char(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,`grpids` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
			$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
			$count++;
		}
		
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`csrf_token` (`token` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, `timestamp` bigint(11) NOT NULL default '0', `sessionid` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
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
			unset($err_msg); $err_msg .= "<br>".$lang['insterr9']; $err_lvl = 3;
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
		
		if(!is_writeable('./other/dbconfig.php')) {
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
		
		if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('cc_deletiontime', '2592000‬'), ('cc_query_nickname', 'ClientCleaner'), ('cc_slowmode', '0'), ('default_date_format', $dateformat), ('default_language', 'en'), ('logs_path', '{$logpath}'), ('logs_timezone', 'Europe/Berlin'), ('rankup_boost_definition', ''), ('rankup_clean_clients_period', '86400'), ('rankup_clean_clients_switch', '1'), ('rankup_client_database_id_change_switch', '0'), ('rankup_definition', '31536000=>47,31536060=>50'), ('rankup_excepted_channel_id_list', ''), ('rankup_excepted_group_id_list', '2,6'), ('rankup_excepted_mode', '0'), ('rankup_excepted_unique_client_id_list', 'xrTKhT/HDL4ea0WoFDQH2zOpmKg='), ('rankup_hash_ip_addresses_mode', '2'), ('rankup_ignore_idle_time', '600'), ('rankup_message_to_user', $rankupmsg), ('rankup_message_to_user_switch', '1'), ('rankup_next_message_1', $nextupinfomsg1), ('rankup_next_message_2', $nextupinfomsg2), ('rankup_next_message_3', $nextupinfomsg3), ('rankup_next_message_mode', '1'), ('rankup_time_assess_mode', '0'), ('simulate_mode', '1'), ('stats_column_active_time_switch', '0'), ('stats_column_current_group_since_switch', '1'), ('stats_column_current_server_group_switch', '1'), ('stats_column_client_db_id_switch', '0'), ('stats_column_client_name_switch', '1'), ('stats_column_idle_time_switch', '1'), ('stats_column_last_seen_switch', '1'), ('stats_column_next_rankup_switch', '1'), ('stats_column_next_server_group_switch', '1'), ('stats_column_online_time_switch', '1'), ('stats_column_rank_switch', '1'), ('stats_column_unique_id_switch', '0'), ('stats_server_news', $servernews), ('stats_show_clients_in_highest_rank_switch', '1'), ('stats_show_excepted_clients_switch', '1'), ('stats_show_site_navigation_switch', '1'), ('teamspeak_avatar_download_delay', '0'), ('teamspeak_default_channel_id', '0'), ('teamspeak_host_address', '127.0.0.1'), ('teamspeak_query_command_delay', '0'), ('teamspeak_query_encrypt_switch', '0'), ('teamspeak_query_nickname', 'Ranksystem'), ('teamspeak_query_pass', ''), ('teamspeak_query_port', '10011'), ('teamspeak_query_user', 'serveradmin'), ('teamspeak_verification_channel_id', '0'), ('teamspeak_voice_port', '9987'), ('version_current_using', '{$rsversion}'), ('version_latest_available', '{$rsversion}'), ('version_update_channel', 'stable'), ('webinterface_access_count', '0'), ('webinterface_access_last', '0'), ('webinterface_admin_client_unique_id_list', ''), ('webinterface_advanced_mode', '0'), ('webinterface_pass', '{$pass}'), ('webinterface_user', '{$user}');") === false) {
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
	if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") {
		$host = "<a href=\"https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF'])."/install.php", '/\\')."\">";
		$err_msg = sprintf($lang['winav10'], $host,'</a>!<br>', '<br>'); $err_lvl = 2;
	}
	if(!is_writeable('./other/dbconfig.php')) {
		unset($err_msg); $err_msg = $lang['isntwicfg']; $err_lvl = 3;
	}
	if(substr(sprintf('%o', fileperms('./avatars/')), -4)!='0777') {
		unset($err_msg); $err_msg = sprintf($lang['isntwichm'],"avatars"); $err_lvl = 3;
	}
	if(substr(sprintf('%o', fileperms('./tsicons/')), -4)!='0777') {
		unset($err_msg); $err_msg = sprintf($lang['isntwichm'],"tsicons"); $err_lvl = 3;
	}
	if(substr(sprintf('%o', fileperms('./logs/')), -4)!='0777') {
		unset($err_msg); $err_msg = sprintf($lang['isntwichm'],"logs"); $err_lvl = 3;
	}
	if(substr(sprintf('%o', fileperms('./update/')), -4)!='0777') {
		unset($err_msg); $err_msg = sprintf($lang['isntwichm'],"update"); $err_lvl = 3;
	}
	if(!class_exists('PDO')) {
		unset($err_msg); $err_msg = $lang['insterr2']; $err_lvl = 3;
	}
	if(!function_exists('exec')) {
		unset($err_msg); $err_msg = $lang['insterr3']; $err_lvl = 3;
	}
	if(version_compare(phpversion(), '5.5.0', '<')) {
		unset($err_msg); $err_msg = sprintf($lang['insterr4'],phpversion()); $err_lvl = 3;
	}
	if(!function_exists('simplexml_load_file')) {
		unset($err_msg); $err_msg = $lang['insterr5']; $err_lvl = 3;
	}
	if(!in_array('curl', get_loaded_extensions())) {
		unset($err_msg); $err_msg = $lang['insterr6']; $err_lvl = 3;
	}
	if(!in_array('zip', get_loaded_extensions())) {
		unset($err_msg); $err_msg = $lang['insterr7']; $err_lvl = 3;
	}
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		if(!in_array('com_dotnet', get_loaded_extensions())) {
			unset($err_msg); $err_msg = $lang['insterr8']; $err_lvl = 3;
		}
	}
	if(!isset($err_lvl)) {
		unset($err_msg);
	}
}
	
function error_handling($lang,$msg,$type = NULL) {
	switch ($type) {
		case NULL: echo '<div class="alert alert-success alert-dismissible">'; break;
		case 1: echo '<div class="alert alert-info alert-dismissible">'; break;
		case 2: echo '<div class="alert alert-warning alert-dismissible">'; break;
		case 3: echo '<div class="alert alert-danger alert-dismissible">'; break;
	}
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="',$lang['stnv0002'],'"><span aria-hidden="true">&times;</span></button>',$msg,'</div>';
}

if ((!isset($_POST['install']) && !isset($_POST['confweb'])) || $err_lvl == 1 || $err_lvl == 2 || $err_lvl == 3) {
	if(isset($show_warning)) {
		$dbhost = $_POST['dbhost'];
		$dbname = $_POST['dbname'];
		$dbuser = $_POST['dbuser'];
		$dbpass = $_POST['dbpass'];
	} else {
		$dbhost = "localhost";
		$dbname = "ts3_ranksystem";
		$dbuser = "";
		$dbpass = "";
	}
	?>
	<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($lang, $err_msg, $err_lvl); ?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						<?php echo $lang['wihldb']; ?>
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
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbtypedesc"><?php echo $lang['isntwidbtype']; ?></label>
									<div class="col-sm-8">
										<select class="selectpicker show-tick form-control" id="basic" name="dbtype" required>
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
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbhostdesc"><?php echo $lang['isntwidbhost']; ?></label>
									<div class="col-sm-8 required-field-block">
										<input type="text" class="form-control" name="dbhost" value="<?php echo $dbhost; ?>" required>
										<div class="required-icon"><div class="text">*</div></div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbnamedesc"><?php echo $lang['isntwidbname']; ?></label>
									<div class="col-sm-8 required-field-block">
										<input type="text" data-pattern="^([A-Za-z0-9$_]){1,64}$" data-error="Please do not use special characters or more then 64 characters inside the database name!" class="form-control" name="dbname" value="<?php echo $dbname; ?>" required>
										<div class="required-icon"><div class="text">*</div></div>
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="panel panel-default">
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbusrdesc"><?php echo $lang['isntwidbusr']; ?></label>
									<div class="col-sm-8 required-field-block">
										<input type="text" data-pattern="^[^&quot;'\\-\s]+$" data-error="Please do not use one of the following special characters: ' \ &quot; - also no whitespace and do not use more then 64 characters inside the database user!" class="form-control" name="dbuser" value="<?php echo $dbuser; ?>" maxlength="64" required>
										<div class="required-icon"><div class="text">*</div></div>
										<div class="help-block with-errors"></div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbpassdesc"><?php echo $lang['isntwidbpass']; ?></label>
									<div class="col-sm-8 required-field-block">
										<input type="password" data-pattern="^[^&quot;'\\-\s]+$" data-error="Please do not use one of the following special characters: ' \ &quot; - also no whitespace and do not use more then 64 characters inside the database password!" class="form-control" name="dbpass" value="<?php echo $dbpass; ?>" data-toggle="password" data-placement="before" required>
										<div class="required-icon"><div class="text">*</div></div>
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
			<?php echo $lang['isntwidbtypedesc']; ?>
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
	<?PHP if(isset($err_msg)) error_handling($lang, $err_msg, $err_lvl); ?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						<?php echo $lang['isntwiusrh']; ?>
					</h1>
				</div>
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
										<input type="text" class="form-control" name="user" required>
										<div class="required-icon"><div class="text">*</div></div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwiusrdesc"><?php echo $lang['pass']; ?></label>
									<div class="col-sm-8 required-field-block">
										<input type="password" class="form-control" name="pass" data-toggle="password" data-placement="before" required>
										<div class="required-icon"><div class="text">*</div></div>
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
	<?PHP if(isset($err_msg)) error_handling($lang, $err_msg, $err_lvl); ?>
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
