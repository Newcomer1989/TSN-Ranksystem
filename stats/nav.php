<?PHP
$job_check = $mysqlcon->query("SELECT * FROM `$dbname`.`job_check`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
if((time() - $job_check['last_update']['timestamp']) < 259200 && !isset($_SESSION[$rspathhex.'upinfomsg'])) {
	if(!isset($err_msg)) {
		$err_msg = '<i class="fa fa-fw fa-info-circle"></i>&nbsp;'.sprintf($lang['upinf2'], date("Y-m-d H:i",$job_check['last_update']['timestamp']), '<a href="//ts-n.net/ranksystem.php?changelog" target="_blank"><i class="fa fa-fw fa-book"></i>&nbsp;', '</a>'); $err_lvl = 1;
		$_SESSION[$rspathhex.'upinfomsg'] = 1;
	}
}
?>
<!DOCTYPE html>
<html lang="<?PHP echo $language; ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="version" content="<?PHP echo $currvers; ?>">
	<link rel="icon" href="../tsicons/rs.png">
	<title>TS-N.NET Ranksystem</title>
	<link href="../libs/combined_st.css?v=<?PHP echo $currvers; ?>" rel="stylesheet">
<?PHP
	$sitescript = basename($_SERVER['SCRIPT_NAME']);
	switch($sitescript) {
		case "index.php":
			?><script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/qbrm.js','../libs/statsindex.js'])</script><?PHP
			break;
		case "assign_groups.php":
			?><script src="../libs/qbh_bsw.js"></script><?PHP
			break;
		case "top_all.php":
			?><script src="../libs/qbrm.js"></script><?PHP
			break;
		case "top_month.php":
			?><script src="../libs/qbrm.js"></script><?PHP
			break;
		case "top_week.php":
			?><script src="../libs/qbrm.js"></script><?PHP
			break;
		case "verify.php":
			?><script src="../libs/qbh_bse.js"></script><?PHP
			break;
		case "list_rankup.php":
			?><script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"defer"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/qb.js'])</script><?PHP
			break;
		default:
			?><script src="../libs/qb.js"></script><?PHP
	}
	if(isset($shownav) && $shownav == 0) { ?>
	<style>
		body{margin-top:0px!important}
		.affix{top:0px!important;width:calc(100% - 50px)!important;position:fixed;color:#000;background-color:#fff!important;}
	</style>
<?PHP } ?>
</head>
<body>
	<div id="myModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?PHP echo $lang['stnv0001']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?PHP echo $servernews; ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div id="myModal2" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?PHP echo $lang['stnv0003']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?PHP echo $lang['stnv0004']; ?></p>
					<p><?PHP echo $lang['stnv0005']; ?></p>
				</div>
				<div class="modal-footer">
					<form method="post">
							<button class="btn btn-primary" type="submit" name="refresh"><?PHP echo $lang['stnv0006']; ?></button>
							<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="battlesystem" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?PHP echo $lang['stnv0007']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?PHP echo $lang['stnv0008']; ?></p>
					<p><?PHP echo $lang['stnv0009']; ?></p>
					<p><?PHP echo $lang['stnv0010']; ?></p>
					<p><?PHP echo $lang['stnv0011']; ?></p>
					<p><?PHP echo $lang['stnv0012']; ?></p>
					<p><?PHP echo $lang['stnv0013']; ?></p>
					<p><?PHP echo $lang['stnv0014']; ?></p>
					<p><?PHP echo $lang['stnv0015']; ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div id="myStatsModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?PHP echo $lang['stnv0016']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?PHP echo $lang['stnv0017']; ?></p>
					<p><?PHP echo $lang['stnv0018']; ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div id="infoModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?PHP echo $lang['stnv0019']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?PHP echo $lang['stnv0020']; ?></p>
					<p><?PHP echo $lang['stnv0021']; ?></p>
					<p><?PHP echo $lang['stnv0022']; ?></p>
					<p><?PHP echo $lang['stnv0023']; ?></p>
					<br>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div id="filteroptions" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Filter options - Search function</h4>
				</div>
				<div class="modal-body">
					<p><?PHP echo $lang['stnv0031']; ?></p>
					<p><?PHP echo $lang['stnv0032']; ?></p>
					<p><?PHP echo $lang['stnv0033']; ?></p>
					<p><?PHP echo $lang['stnv0034']; ?></p>
					<p><?PHP echo $lang['stnv0035']; ?></p>
					<p><br></p>
					<p><b>filter:excepted:</b></p>
					<p><?PHP echo $lang['stnv0036']; ?></p>
					<p><b>filter:nonexcepted:</b></p>
					<p><?PHP echo $lang['stnv0037']; ?></p>
					<p><b>filter:online:</b></p>
					<p><?PHP echo $lang['stnv0038']; ?></p>
					<p><b>filter:nononline:</b></p>
					<p><?PHP echo $lang['stnv0039']; ?></p>
					<p><b>filter:actualgroup:<i>GROUPID</i>:</b></p>
					<p><?PHP echo $lang['stnv0040']; ?></p>
					<p><b>filter:country:<i>TS3-COUNTRY-CODE</i>:</b></p>
					<p><?PHP echo $lang['stnv0042']; ?></p>
					<p><b>filter:lastseen:<i>OPERATOR</i>:<i>TIME</i>:</b></p>
					<p><?PHP echo $lang['stnv0041']; ?></p>
					<br>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
<?PHP
	if($shownav == 1) {
?>
	<div id="wrapper">
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-header">
				<a class="navbar-brand" href="index.php"><?PHP echo $lang['stnv0024']; ?></a>
			</div>
			<?PHP if(basename($_SERVER['SCRIPT_NAME']) == "list_rankup.php") { ?>
			<ul class="nav navbar-left top-nav">
				<li class="navbar-form navbar-left dropdown">
					<div class="btn-group">
						<a href="#filteroptions" data-toggle="modal" class="btn btn-primary">
							<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
						</a>
					</div>
				</li>
				<li class="navbar-form navbar-right dropdown">
					<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<?PHP echo $lang['stnv0025']; ?>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=50&amp;lang=$language&amp;search=$getstring"; ?>">50</a></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=100&amp;lang=$language&amp;search=$getstring"; ?>">100</a></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=250&amp;lang=$language&amp;search=$getstring"; ?>">250</a></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=500&amp;lang=$language&amp;search=$getstring"; ?>">500</a></li>
						<li role="separator" class="divider"></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=all&amp;lang=$language&amp;search=$getstring"; ?>"><?PHP echo $lang['stnv0026']; ?></a></li>
					</ul>
				</li>
				<li class="navbar-form navbar-right">
					<form method="post">
						<div class="form-group">
							<input class="form-control" type="text" name="usersuche" placeholder="Search"<?PHP if(isset($getstring)) echo ' value="'.$getstring.'"'; ?>>
						</div>
						<button class="btn btn-primary" type="submit" name="username"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
					</form>
				</li>
			</ul>
			<?PHP } ?>
			<ul class="nav navbar-right top-nav">
				<?PHP
				if((time() - $job_check['calc_user_lastscan']['timestamp']) > 600) { ?>
				<li class="navbar-form navbar-left">
					<span class="label label-warning"><?PHP echo $lang['stnv0027']; ?></span>
				</li>
				<?PHP } ?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><?PHP echo '&nbsp;&nbsp;' . $_SESSION[$rspathhex.'tsname'] ?>&nbsp;
					<b class="caret"></b></a><ul class="dropdown-menu">
						<?PHP
						if($_SESSION[$rspathhex.'tsname'] == "verification needed!" || $_SESSION[$rspathhex.'connected'] == 0) {
							echo '<li><a href="verify.php"><i class="fa fa-fw fa-key"></i>&nbsp;verificate here..</a></li>';
						}
						if(isset($_SESSION[$rspathhex.'admin']) && $_SESSION[$rspathhex.'admin'] == TRUE) {
							if($_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 80) {
								echo '<li><a href="//',$_SERVER['SERVER_NAME'],':',substr(dirname($_SERVER['SCRIPT_NAME']),0,-5),'webinterface/bot.php"><i class="fa fa-fw fa-wrench"></i>&nbsp;',$lang['wi'],'</a></li>';
							} else {
								echo '<li><a href="//',$_SERVER['SERVER_NAME'],':',$_SERVER['SERVER_PORT'],substr(dirname($_SERVER['SCRIPT_NAME']),0,-5),'webinterface/bot.php"><i class="fa fa-fw fa-wrench"></i>&nbsp;',$lang['wi'],'</a></li>';
							}
						} elseif ($_SESSION[$rspathhex.'connected'] == 0) {
							echo '<li><a href="ts3server://';
								if (($ts['host']=='localhost' || $ts['host']=='127.0.0.1') && strpos($_SERVER['HTTP_HOST'], 'www.') == 0) {
									echo preg_replace('/www\./','',$_SERVER['HTTP_HOST']);
								} elseif ($ts['host']=='localhost' || $ts['host']=='127.0.0.1') {
									echo $_SERVER['HTTP_HOST'];
								} else {
									echo $ts['host'];
								}
								echo ':'.$ts['voice'];
							echo '"><i class="fa fa-fw fa-headphones"></i>&nbsp;'.$lang['stnv0043'].'</a></li>';
						}
						?>
						<li>
							<a href="my_stats.php"><i class="fa fa-fw fa-user"></i>&nbsp;<?PHP echo $lang['stmy0001']; ?></a>
						</li>
						<li>
							<a href="#myModal" data-toggle="modal"><i class="fa fa-fw fa-envelope"></i>&nbsp;<?PHP echo $lang['stnv0001']; ?></a>
						</li>
					</ul>
				</li>
				<li>
					<div class="navbar-form navbar-center">
						<div class="btn-group">
							<a href="#myModal2" data-toggle="modal" class="btn btn-primary">
								<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
							</a>
						</div>
					</div>
				</li>
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
							<a href="?lang=en"><span class="flag-icon flag-icon-gb"></span>&nbsp;&nbsp;EN - english</a>
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
							<a href="?lang=ru"><span class="flag-icon flag-icon-ru"></span>&nbsp;&nbsp;RU - Pусский</a>
						</li>
						<li>
							<a href="?lang=pt"><span class="flag-icon flag-icon-ptbr"></span>&nbsp;&nbsp;PT - Português</a>
						</li>
					</ul>
				</li>
			</ul>
			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav side-nav">
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "index.php" ? ' class="active">' : '>'); ?>
						<a href="index.php"><i class="fa fa-fw fa-area-chart"></i>&nbsp;<?PHP echo $lang['stix0001']; ?></a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "my_stats.php" ? ' class="active">' : '>'); ?>
						<a href="my_stats.php"><i class="fa fa-fw fa-bar-chart-o"></i>&nbsp;<?PHP echo $lang['stmy0001']; ?></a>
					</li>
					<?PHP if($addons_config['assign_groups_active']['value'] == '1') {
							echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "assign_groups.php" ? ' class="active">' : '>'); ?>
							<a href="assign_groups.php"><i class="fa fa-fw fa-address-card-o"></i>&nbsp;<?PHP echo $lang['stag0001']; ?></a>
						<?PHP }	?>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "top_all.php" ? ' class="active">' : '>'); ?>
						<a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-trophy"></i>&nbsp;<?PHP echo $lang['sttw0001']; ?>&nbsp;<i class="fa fa-fw fa-caret-down"></i></a>
						<ul id="demo" class="collapse">
							<li>
								<a href="top_week.php"><?PHP echo $lang['sttw0002']; ?></a>
							</li>
							<li>
								<a href="top_month.php"><?PHP echo $lang['sttm0001']; ?></a>
							</li>
							<li>
								<a href="top_all.php"><?PHP echo $lang['stta0001']; ?></a>
							</li>
						</ul>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "list_rankup.php" ? ' class="active">' : '>'); ?>
						<a href="list_rankup.php"><i class="fa fa-fw fa-list-ul"></i>&nbsp;<?PHP echo $lang['stnv0029']; ?></a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "info.php" ? ' class="active">' : '>'); ?>
						<a href="info.php"><i class="fa fa-fw fa-info-circle"></i>&nbsp;<?PHP echo $lang['stnv0030']; ?></a>
					</li>
				</ul>
			</div>
		</nav>
<?PHP
	} else {
		echo '<div id="container">';
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
?>