<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="version" content="<?PHP echo $currvers; ?>">
	<link rel="icon" href="../icons/rs.png">
	<title>TS-N.NET Ranksystem</title>
	<link href="../libs/combined_wi.css?v=<?PHP echo $currvers; ?>" rel="stylesheet">
	<script src="../libs/combined_wi.js?v=<?PHP echo $currvers; ?>"></script>
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
				<a class="navbar-brand" href="index.php">TSN Ranksystem - Webinterface <?PHP echo $currvers;?></a>
				<?PHP if(isset($_SESSION['newversion']) && version_compare(substr($_SESSION['newversion'], 0, 5), substr($currvers, 0, 5), '>') && $_SESSION['newversion'] != '') {
					echo '<a class="navbar-brand" href="http://ts-n.net/ranksystem.php" target="_blank">'.$lang['winav9'].' ['.$_SESSION['newversion'].']</a>';
				} ?>
			</div>
			<?PHP if(basename($_SERVER['SCRIPT_NAME']) == "stats.php") { ?>
			<ul class="nav navbar-left top-nav">
				<li class="navbar-form navbar-right">
					<button onclick="window.open('../stats/list_rankup.php?admin=true','_blank'); return false;" class="btn btn-primary" name="adminlist">
						<i class="fa fa-fw fa-list"></i>&nbsp;<?PHP echo $lang['wihladm']; ?>
					</button>
				</li>
			</ul>
			<?PHP } ?>
			<ul class="nav navbar-right top-nav">
				<?PHP
				echo '<li><a href="http',(!empty($_SERVER['HTTPS'])?'s':''),'://',$_SERVER['SERVER_NAME'],substr(dirname($_SERVER['SCRIPT_NAME']),0,-12),'stats/"><i class="fa fa-fw fa-bar-chart"></i>&nbsp;',$lang['winav6'],'</a></li>';
				if(isset($_SESSION['username']) && $_SESSION['username'] == $webuser && $_SESSION['password'] == $webpass) { ?>
				<li>
					<a href="changepassword.php"><i class="fa fa-lock"></i>&nbsp;<?PHP echo $lang['pass2']; ?></a>
				</li>
				<li>
					<form class="navbar-form navbar-center" method="post">
						<div class="form-group">
							<button type="submit" name="logout" class="btn btn-primary"><?PHP echo $lang['wilogout']; ?>&nbsp;<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></button>
						</div>
					</form>
				</li>
				<?PHP } ?>
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
							<a href="?lang=fr"><span class="flag-icon flag-icon-fr"></span>&nbsp;&nbsp;FR - français</a>
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
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "ts.php" ? ' class="active">' : '>'); ?>
						<a href="ts.php"><i class="fa fa-fw fa-headphones"></i>&nbsp;<?PHP echo $lang['winav1']; ?></a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "db.php" ? ' class="active">' : '>'); ?>
						<a href="db.php"><i class="fa fa-fw fa-database"></i>&nbsp;<?PHP echo $lang['winav2']; ?></a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "core.php" ? ' class="active">' : '>'); ?>
						<a href="core.php"><i class="fa fa-fw fa-cogs"></i>&nbsp;<?PHP echo $lang['winav3']; ?></a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "other.php" ? ' class="active">' : '>'); ?>
						<a href="other.php"><i class="fa fa-fw fa-wrench"></i>&nbsp;<?PHP echo $lang['winav4']; ?></a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "msg.php" ? ' class="active">' : '>'); ?>
						<a href="msg.php"><i class="fa fa-fw fa-envelope"></i>&nbsp;<?PHP echo $lang['winav5']; ?></a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "stats.php" ? ' class="active">' : '>'); ?>
						<a href="stats.php"><i class="fa fa-fw fa-bar-chart"></i>&nbsp;<?PHP echo $lang['winav6']; ?></a>
					</li>
					<li class="divider"></li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "addon_assign_groups.php" ? ' class="active">' : '>'); ?>
						<a href="javascript:;" data-toggle="collapse" data-target="#addons"><i class="fa fa-fw fa-puzzle-piece"></i>&nbsp;<?PHP echo $lang['winav12']; ?>&nbsp;<i class="fa fa-fw fa-caret-down"></i></a>
						<ul id="addons" class="collapse">
							<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "addon_assign_groups.php" ? ' class="active">' : '>'); ?>
								<a href="addon_assign_groups.php"><?PHP echo $lang['stag0001']; ?></a>
							</li>
						</ul>
					</li>
					<li class="divider"></li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "admin.php" ? ' class="active">' : '>'); ?>
						<a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-users"></i>&nbsp;<?PHP echo $lang['winav7']; ?>&nbsp;<i class="fa fa-fw fa-caret-down"></i></a>
						<ul id="demo" class="collapse">
							<li>
								<a href="admin.php"><?PHP echo $lang['wihladm1']; ?></a>
							</li>
						</ul>
					</li>
					<li class="divider"></li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "bot.php" ? ' class="active">' : '>'); ?>
						<a href="bot.php"><i class="fa fa-fw fa-power-off"></i>&nbsp;<?PHP echo $lang['winav8']; ?></a>
					</li>
				</ul>
			</div>
		</nav>
<?PHP
if($adminuuid==NULL && $_SESSION['username'] == $webuser && !isset($err_msg)) {
	$err_msg = $lang['winav11']; $err_lvl = 3;
}

if(!isset($_SERVER['HTTPS']) && !isset($err_msg) || $_SERVER['HTTPS'] != "on" && !isset($err_msg)) {
	$host = "<a href=\"https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."\">";
	$err_msg = sprintf($lang['winav10'], $host,'</a>!<br>', '<br>'); $err_lvl = 2;
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