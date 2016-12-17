<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="version" content="<?PHP echo $currvers; ?>">
	<link rel="icon" href="../icons/rs.png">
	<title>TS-N.NET Ranksystem</title>
	<link href="../libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="../libs/bootstrap/addons/sb-admin.css" rel="stylesheet">
	<link href="../libs/bootstrap/addons/morris/morris.css" rel="stylesheet">
	<link href="../libs/bootstrap/addons/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	<link href="../libs/bootstrap/flag_icon/css/flag-icon.min.css" rel="stylesheet">
	<link href="../libs/bootstrap/css/custom.css" rel="stylesheet">
	<script src="../libs/jquery/jquery.min.js"></script>
	<script src="../libs/bootstrap/js/bootstrap.min.js"></script>
	<script src="../libs/bootstrap/addons/morris/raphael.min.js"></script>
	<script src="../libs/bootstrap/addons/morris/morris.min.js"></script>
<?PHP
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
				$lastscan = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='calc_user_lastscan'");
				$lastscan = $lastscan->fetchAll();
				if((time() - $lastscan[0]['timestamp']) > 600) { ?>
				<li class="navbar-form navbar-left">
					<span class="label label-warning"><?PHP echo $lang['stnv0027']; ?></span>
				</li>
				<?PHP } ?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><?PHP echo '&nbsp;&nbsp;' .($_SESSION['connected'] == 0 ? $lang['stnv0028'] : $_SESSION['tsname']); ?>&nbsp;<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<?PHP
						if(isset($_SESSION['admin'])) {
							echo '<li><a href="http',(!empty($_SERVER['HTTPS'])?'s':''),'://',$_SERVER['SERVER_NAME'],substr(dirname($_SERVER['SCRIPT_NAME']),0,-5),'webinterface/ts.php"><i class="fa fa-fw fa-wrench"></i>&nbsp;',$lang['wi'],'</a></li>';
						} elseif ($_SESSION['connected'] == 0) {
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
						echo (!isset($_SESSION['tsname']) ? ' ' : '<li><a href="my_stats.php"><i class="fa fa-fw fa-user"></i>&nbsp;'.$lang['stmy0001'].'</a></li>');
						?>
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
							<a href="?lang=de"><span class="flag-icon flag-icon-de"></span>&nbsp;&nbsp;DE - Deutsch</a>
						</li>
						<li>
							<a href="?lang=en"><span class="flag-icon flag-icon-gb"></span>&nbsp;&nbsp;EN - english</a>
						</li>
						<li>
							<a href="?lang=it"><span class="flag-icon flag-icon-it"></span>&nbsp;&nbsp;IT - italiano</a>
						</li>
						<li>
							<a href="?lang=it"><span class="flag-icon flag-icon-nl"></span>&nbsp;&nbsp;NL - Nederlands</a>
						</li>
						<li>
							<a href="?lang=ro"><span class="flag-icon flag-icon-ro"></span>&nbsp;&nbsp;RO - românesc</a>
						</li>
						<li>
							<a href="?lang=ru"><span class="flag-icon flag-icon-ru"></span>&nbsp;&nbsp;RU - русский</a>
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
						<?PHP if($_SESSION['connected'] == 0) {
							echo '<a href="#myStatsModal" data-toggle="modal"><i class="fa fa-fw fa-exclamation-triangle"></i>&nbsp;*',$lang['stmy0001'],'</a>';
						} else {
							echo '<a href="my_stats.php"><i class="fa fa-fw fa-bar-chart-o"></i>&nbsp;',$lang['stmy0001'],'</a>';
						}?>
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