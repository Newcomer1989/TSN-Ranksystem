<?PHP
require_once('other/config.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="icons/rs.png">
	<title>TS-N.NET Ranksystem</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="bootstrap/css/custom.css" rel="stylesheet">
	<link href="bootstrap/addons/sb-admin.css" rel="stylesheet">
	<link href="bootstrap/addons/switch-master/bootstrap-switch.min.css" rel="stylesheet">
	<link href="bootstrap/addons/touchspin-master/jquery.bootstrap-touchspin.css" rel="stylesheet">
	<link href="bootstrap/addons/select/bootstrap-select.min.css" rel="stylesheet">
	<link href="bootstrap/addons/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	<link href="bootstrap/flag_icon/css/flag-icon.min.css" rel="stylesheet">
	<script src="jquerylib/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="bootstrap/js/highlight.js"></script>
	<script src="bootstrap/js/main.js"></script>
	<script src="bootstrap/addons/switch-master/bootstrap-switch.min.js"></script>
	<script src="bootstrap/addons/touchspin-master/jquery.bootstrap-touchspin.js"></script>
	<script src="bootstrap/addons/select/bootstrap-select.min.js"></script>
	<script src="bootstrap/addons/show-password/bootstrap-show-password.min.js"></script>
	<script src="bootstrap/addons/validator/validator.min.js"></script>
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
				<a class="navbar-brand" href="index.php">TSN Ranksystem - <?PHP echo $lang['install']; ?></a>
			</div>
			<ul class="nav navbar-right top-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-globe"></i>&nbsp;<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li>
							<a href="?lang=ar"><span class="flag-icon flag-icon-arab"></span>&nbsp;&nbsp;AR - العربية</a>
						</li>
						<li>
							<a href="?lang=de"><span class="flag-icon flag-icon-de"></span>&nbsp;&nbsp;DE - Deutsch</a>
						</li>
						<li>
							<a href="?lang=en"><span class="flag-icon flag-icon-us"></span>&nbsp;&nbsp;EN - english</a>
						</li>
						<li>
							<a href="?lang=it"><span class="flag-icon flag-icon-it"></span>&nbsp;&nbsp;IT - italiano</a>
						</li>
						<li>
							<a href="?lang=ru"><span class="flag-icon flag-icon-ro"></span>&nbsp;&nbsp;RO - românesc</a>
						</li>
						<li>
							<a href="?lang=ru"><span class="flag-icon flag-icon-ru"></span>&nbsp;&nbsp;RU - русский</a>
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

if (isset($_POST['install'])) {
	unset($err_msg);
	$type=$_POST['dbtype'];
	$host=$_POST['dbhost'];
	$user=$_POST['dbuser'];
	$pass=$_POST['dbpass'];
	$dbname=$_POST['dbname'];
	if ($type == 'mysql') {
	$dboptions = array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		PDO::ATTR_PERSISTENT => true
	);
	} else {
		$dboptions = array();
	}
	$dbserver  = $type.':host='.$host.'; dbname='.$dbname;
	$dbserver2 = $type.':host='.$host;
	try {
		$mysqlcon = new PDO($dbserver, $user, $pass, $dboptions);
	} catch (PDOException $e) {
		try {
			$mysqlcon = new PDO($dbserver2, $user, $pass, $dboptions);
		} catch (PDOException $e) {
			$err_msg = $lang['dbconerr'].$e->getMessage(); $err_lvl = 1;
		}
	}
	
	if(!is_writeable('./other/dbconfig.php')) {
		$err_msg = $lang['isntwicfg'];
		$err_lvl = 2;
	}
	
	if($err_msg == NULL) {
		$newconfig='<?php
$db[\'type\']="'.$type.'";
$db[\'host\']="'.$host.'";
$db[\'user\']="'.$user.'";
$db[\'pass\']="'.$pass.'";
$db[\'dbname\']="'.$dbname.'";
	?>';
		$handle=fopen('./other/dbconfig.php','w');
		if(!fwrite($handle,$newconfig)) {
			$err_msg = $lang['isntwicfg'];
			$err_lvl = 2;
		} else {
			$count = 1;
			if(($mysqlcon->exec("DROP DATABASE $dbname")) === false) { }
			
			if($mysqlcon->exec("CREATE DATABASE $dbname") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.user (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY,cldbid bigint(10) NOT NULL default '0',count bigint(11) NOT NULL default '0',ip bigint(10) NOT NULL default '0',name text CHARACTER SET utf8 COLLATE utf8_unicode_ci,lastseen bigint(11) NOT NULL default '0',grpid bigint(10) NOT NULL default '0',nextup bigint(11) NOT NULL default '0',idle bigint(11) NOT NULL default '0',cldgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci,online int(1) NOT NULL default '0',boosttime bigint(11) NOT NULL default '0', rank bigint(11) NOT NULL default '0', platform text default NULL, nation text default NULL, version text default NULL, firstcon bigint(11) NOT NULL default '0', except int(1) NOT NULL default '0')") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.groups (sgid bigint(10) PRIMARY KEY,sgidname text CHARACTER SET utf8 COLLATE utf8_unicode_ci,iconid bigint(10) NOT NULL default '0',icondate bigint(11) NOT NULL default '0')") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.config (webuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci,webpass text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tshost text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tsquery int(5) NOT NULL default '0',tsvoice int(5) NOT NULL default '0',tsuser text CHARACTER SET utf8 COLLATE utf8_unicode_ci,tspass text CHARACTER SET utf8 COLLATE utf8_unicode_ci,language text CHARACTER SET utf8 COLLATE utf8_unicode_ci,queryname text CHARACTER SET utf8 COLLATE utf8_unicode_ci,queryname2 text CHARACTER SET utf8 COLLATE utf8_unicode_ci,grouptime text CHARACTER SET utf8 COLLATE utf8_unicode_ci,resetbydbchange int(1) NOT NULL default '0',msgtouser int(1) NOT NULL default '0',upcheck int(1) NOT NULL default '0',uniqueid text CHARACTER SET utf8 COLLATE utf8_unicode_ci,updateinfotime int(8) NOT NULL default '0',currvers text CHARACTER SET utf8 COLLATE utf8_unicode_ci,substridle int(1) NOT NULL default '0',exceptuuid text CHARACTER SET utf8 COLLATE utf8_unicode_ci,exceptgroup text CHARACTER SET utf8 COLLATE utf8_unicode_ci,dateformat text CHARACTER SET utf8 COLLATE utf8_unicode_ci,showexcld int(1) NOT NULL default '0',showcolcld int(1) NOT NULL default '0',showcoluuid int(1) NOT NULL default '0',showcoldbid int(1) NOT NULL default '0',showcolot int(1) NOT NULL default '0',showcolit int(1) NOT NULL default '0',showcolat int(1) NOT NULL default '0',showcolnx int(1) NOT NULL default '0',showcolsg int(1) NOT NULL default '0',showcolrg int(1) NOT NULL default '0',showcolls int(1) NOT NULL default '0',slowmode bigint(11) NOT NULL default '0',cleanclients int(1) NOT NULL default '0',cleanperiod bigint(11) NOT NULL default '0',showhighest int(1) NOT NULL default '0',boost text default NULL,showcolas int(1) NOT NULL default '0',defchid bigint(11) NOT NULL default '0',timezone varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci,logpath varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci, advancemode int(1) NOT NULL default '0', count_access int(2) NOT NULL default '0', last_access bigint(11) NOT NULL default '0', ignoreidle bigint(11) NOT NULL default '0', exceptcid text CHARACTER SET utf8 COLLATE utf8_unicode_ci, rankupmsg text CHARACTER SET utf8 COLLATE utf8_unicode_ci, boost_mode int(1) NOT NULL default '0', newversion varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.server_usage (timestamp bigint(11) NOT NULL default '0', clients bigint(11) NOT NULL default '0', channel bigint(11) NOT NULL default '0')") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
				if($mysqlcon->exec("CREATE INDEX serverusage_timestamp ON $dbname.server_usage (timestamp)") === false) {
					$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
					$count++;
				}
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.user_snapshot (timestamp bigint(11) NOT NULL default '0', uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci, count bigint(11) NOT NULL default '0', idle bigint(11) NOT NULL default '0')") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
				if($mysqlcon->exec("CREATE INDEX snapshot_timestamp ON $dbname.user_snapshot (timestamp)") === false) {
					$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
					$count++;
				}
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.stats_server (total_user bigint(11) NOT NULL default '0', total_online_time bigint(13) NOT NULL default '0', total_online_month bigint(11) NOT NULL default '0', total_online_week bigint(11) NOT NULL default '0', total_active_time bigint(11) NOT NULL default '0', total_inactive_time bigint(11) NOT NULL default '0', country_nation_name_1 varchar(3) NOT NULL default '0', country_nation_name_2 varchar(3) NOT NULL default '0', country_nation_name_3 varchar(3) NOT NULL default '0', country_nation_name_4 varchar(3) NOT NULL default '0', country_nation_name_5 varchar(3) NOT NULL default '0', country_nation_1 bigint(11) NOT NULL default '0', country_nation_2 bigint(11) NOT NULL default '0', country_nation_3 bigint(11) NOT NULL default '0', country_nation_4 bigint(11) NOT NULL default '0', country_nation_5 bigint(11) NOT NULL default '0', country_nation_other bigint(11) NOT NULL default '0', platform_1 bigint(11) NOT NULL default '0', platform_2 bigint(11) NOT NULL default '0', platform_3 bigint(11) NOT NULL default '0', platform_4 bigint(11) NOT NULL default '0', platform_5 bigint(11) NOT NULL default '0', platform_other bigint(11) NOT NULL default '0', version_name_1 varchar(35) NOT NULL default '0', version_name_2 varchar(35) NOT NULL default '0', version_name_3 varchar(35) NOT NULL default '0', version_name_4 varchar(35) NOT NULL default '0', version_name_5 varchar(35) NOT NULL default '0', version_1 bigint(11) NOT NULL default '0', version_2 bigint(11) NOT NULL default '0', version_3 bigint(11) NOT NULL default '0', version_4 bigint(11) NOT NULL default '0', version_5 bigint(11) NOT NULL default '0', version_other bigint(11) NOT NULL default '0', server_status int(1) NOT NULL default '0', server_free_slots bigint(11) NOT NULL default '0', server_used_slots bigint(11) NOT NULL default '0', server_channel_amount bigint(11) NOT NULL default '0', server_ping bigint(11) NOT NULL default '0', server_packet_loss float (4,4), server_bytes_down bigint(11) NOT NULL default '0', server_bytes_up bigint(11) NOT NULL default '0', server_uptime bigint(11) NOT NULL default '0', server_id bigint(11) NOT NULL default '0', server_name text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_pass int(1) NOT NULL default '0', server_creation_date bigint(11) NOT NULL default '0', server_platform text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_weblist text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_version text CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.stats_user (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, removed int(1) NOT NULL default '0', rank bigint(11) NOT NULL default '0', total_connections bigint(11) NOT NULL default '0', count_week bigint(11) NOT NULL default '0', count_month bigint(11) NOT NULL default '0', idle_week bigint(11) NOT NULL default '0', idle_month bigint(11) NOT NULL default '0', achiev_count bigint(11) NOT NULL default '0', achiev_time bigint(11) NOT NULL default '0', achiev_connects bigint(11) NOT NULL default '0', achiev_battles bigint(11) NOT NULL default '0', achiev_time_perc int(3) NOT NULL default '0', achiev_connects_perc int(3) NOT NULL default '0', achiev_battles_perc int(3) NOT NULL default '0', battles_total bigint(11) NOT NULL default '0', battles_won bigint(11) NOT NULL default '0', battles_lost bigint(11) NOT NULL default '0', client_description text CHARACTER SET utf8 COLLATE utf8_unicode_ci, base64hash varchar(58) CHARACTER SET utf8 COLLATE utf8_unicode_ci, client_total_up bigint(15) NOT NULL default '0', client_total_down bigint(15) NOT NULL default '0')") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}

			if($mysqlcon->exec("INSERT INTO $dbname.stats_server SET total_user='9999'") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.job_check (job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, timestamp bigint(11) NOT NULL default '0')") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("INSERT INTO $dbname.job_check (job_name) VALUES ('calc_user_limit'),('calc_user_lastscan'),('check_update'),('check_clean'),('get_version')") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($mysqlcon->exec("CREATE TABLE $dbname.job_log (id bigint(11) AUTO_INCREMENT PRIMARY KEY, timestamp bigint(11) NOT NULL default '0', job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci, status int(1) NOT NULL default '0', err_msg text CHARACTER SET utf8 COLLATE utf8_unicode_ci, runtime float (4,4))") === false) {
				$err_msg .= $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true).'<br>'; $err_lvl = 2;
				$count++;
			}
			
			if($count == 1) {
				$err_msg = sprintf($lang['instdbsuc'], $dbname); $err_lvl = NULL;
				$install_webuser = 1;
			}
		}
		fclose($handle);
	}
}

if(isset($_POST['confweb'])) {
	require_once('other/dbconfig.php');
	$user=$_POST['user'];
	$pass=password_hash($_POST['pass'], PASSWORD_DEFAULT);
	$logpath = addslashes(__DIR__."/logs/");
	$dbserver = $db['type'].':host='.$db['host'].';dbname='.$db['dbname'];
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
	if($err_lvl != 1) {
		if($mysqlcon->exec("INSERT INTO $dbname.config (webuser,webpass,tshost,tsquery,tsvoice,tsuser,language,queryname,queryname2,grouptime,resetbydbchange,msgtouser,upcheck,uniqueid,updateinfotime,currvers,exceptuuid,exceptgroup,dateformat,showexcld,showcolcld,showcoluuid,showcoldbid,showcolot,showcolit,showcolat,showcolnx,showcolsg,showcolrg,showcolls,slowmode,cleanclients,cleanperiod,showhighest,showcolas,defchid,timezone,logpath,ignoreidle,rankupmsg,newversion) VALUES ('$user','$pass','localhost','10011','9987','serveradmin','en','Ranksystem','RankSystem','31536000=>47,31536060=>50','1','1','1','xrTKhT/HDl4ea0WoFDQH2zOpmKg=,9odBYAU7z2E2feUz965sL0/Myom=','7200','1.1.0','xrTKhT/HDl4ea0WoFDQH2zOpmKg=','2,6','%a days, %h hours, %i mins, %s secs','1','1','1','1','1','1','1','1','1','1','1','0','1','86400','1','1','0','Europe/Berlin','$logpath','600','\\nHey, you got a rank up, cause you reached an activity of %s days, %s hours, %s minutes and %s seconds.','1.1.0')") === false) {
			$err_msg = $lang['isntwidbmsg'].$mysqlcon->errorCode()." ".print_r($mysqlcon->errorInfo(), true); $err_lvl = 2;
		} else {
			$err_msg .= $lang['isntwiusr'].'<br><br>';
			if(!unlink('./install.php')) {
				$err_msg .= sprintf($lang['isntwidel'],"<a href=\"webinterface\\\">/webinterface/</a>");
			}
			$install_finished = 1;
		}
	}
}

if (!isset($_POST['install']) && !isset($_POST['confweb'])) {
	unset($err_msg);
	unset($err_lvl);
	if(!is_writeable('./other/dbconfig.php')) {
		$err_msg = $lang['isntwicfg'];
		$err_lvl = 2;
	}
}
	
function error_handling($msg,$type = NULL) {
	switch ($type) {
		case NULL: echo '<div class="alert alert-success alert-dismissible">'; break;
		case 1: echo '<div class="alert alert-info alert-dismissible">'; break;
		case 2: echo '<div class="alert alert-warning alert-dismissible">'; break;
		case 3: echo '<div class="alert alert-danger alert-dismissible">'; break;
	}
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',$msg,'</div>';
}

if ((!isset($_POST['install']) && !isset($_POST['confweb'])) || $err_lvl == 1 || $err_lvl == 2) {
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['wihldb']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" name="install" method="POST">
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
											<option data-subtext="Firebird/Interbase 6" value="firebird">firebird</option>
											<option data-subtext="IBM DB2" value="ibm">ibm</option>
											<option data-subtext="IBM Informix Dynamic Server" value="informix">informix</option>
											<option data-subtext="MySQL 3.x/4.x/5.x [recommend]" value="mysql" selected>mysql</option>
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
											<input type="text" class="form-control" name="dbhost" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbnamedesc"><?php echo $lang['isntwidbname']; ?></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control" name="dbname" required>
											<div class="required-icon"><div class="text">*</div></div>
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
											<input type="text" class="form-control" name="dbuser" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbpassdesc"><?php echo $lang['isntwidbpass']; ?></label>
										<div class="col-sm-8 required-field-block">
											<input type="password" class="form-control" name="dbpass" data-toggle="password" data-placement="before" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" class="btn btn-primary" name="install"><?php echo $lang['instdb']; ?></button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</form>
			</div>
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
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
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
							<button type="submit" class="btn btn-primary" name="confweb"><?php echo $lang['isntwiusrcr']; ?></button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</form>
			</div>
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
	</div>
<?PHP
}
?>
</body>
</html>