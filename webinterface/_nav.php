<?PHP
if(($job_check = $mysqlcon->query("SELECT * FROM `$dbname`.`job_check`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
} else {
	if((time() - $job_check['last_update']['timestamp']) < 259200 && !isset($_SESSION[$rspathhex.'upinfomsg'])) {
		if(!isset($err_msg)) {
			$err_msg = '<i class="fas fa-info-circle"></i><span class="item-margin">'.sprintf($lang['upinf2'], date("Y-m-d H:i",$job_check['last_update']['timestamp']), '</span><i class="fas fa-book"></i><span class="item-margin"><a href="//ts-ranksystem.com/?changelog" target="_blank">', '</a></span>'); $err_lvl = 1;
			$_SESSION[$rspathhex.'upinfomsg'] = 1;
		}
	}
}

if(!isset($_POST['start']) && !isset($_POST['stop']) && !isset($_POST['restart']) && isset($_SESSION[$rspathhex.'username']) && hash_equals($_SESSION[$rspathhex.'username'], $cfg['webinterface_user']) && hash_equals($_SESSION[$rspathhex.'password'], $cfg['webinterface_pass'])) {
	if (substr(php_uname(), 0, 7) == "Windows") {
		if (file_exists($GLOBALS['pidfile'])) {
			$pid = str_replace(array("\r", "\n"), '', file_get_contents($GLOBALS['pidfile']));
			exec("wmic process where \"processid=".$pid."\" get processid 2>nul", $result);
			if(isset($result[1]) && is_numeric($result[1])) {
				$botstatus = 1;
			} else {
				$botstatus = 0;
			}
		} else {
			$botstatus = 0;
		}
	} else {
		if (file_exists($GLOBALS['pidfile'])) {
			$check_pid = str_replace(array("\r", "\n"), '', file_get_contents($GLOBALS['pidfile']));
			$result = str_replace(array("\r", "\n"), '', shell_exec("ps ".$check_pid));
			if (strstr($result, $check_pid)) {
				$botstatus = 1;
			} else {
				$botstatus = 0;
			}
		} else {
			$botstatus = 0;
		}
	}
}

if(isset($_POST['switchexpert']) && isset($_SESSION[$rspathhex.'username']) && hash_equals($_SESSION[$rspathhex.'username'], $cfg['webinterface_user']) && hash_equals($_SESSION[$rspathhex.'password'], $cfg['webinterface_pass'])) {
	if ($_POST['switchexpert'] == "check") $cfg['webinterface_advanced_mode'] = 1; else $cfg['webinterface_advanced_mode'] = 0;

	if (($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_advanced_mode','{$cfg['webinterface_advanced_mode']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);")) === false) {
		print_r($mysqlcon->errorInfo(), true);
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}
}
?>
<!DOCTYPE html>
<html lang="<?PHP echo $cfg['default_language']; ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="version" content="<?PHP echo $cfg['version_current_using']; ?>">
	<link rel="icon" href="../tsicons/rs.png">
	<title>TSN Ranksystem - ts-ranksystem.com</title>
	<link href="../libs/combined_wi.css?v=<?PHP echo $cfg['version_current_using']; ?>" rel="stylesheet">
	<?PHP
	if ($GLOBALS['style'] != NULL && is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'WI.css')) {
		echo '<link href="../styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'WI.css?v='.$cfg['version_current_using'].'" rel="stylesheet">';
	}
	?>
	<script src="../libs/combined_wi.js?v=<?PHP echo $cfg['version_current_using']; ?>"></script>
	<script>
	$(function() {
		var timerid;
		$("ul.dropdown-menu").on("click", "[data-keepOpenOnClick]", function(e) {
			e.stopPropagation();
		});
		$('#switchexpert').on('switchChange.bootstrapSwitch', function() {
			$('.expertelement').each(function(i, obj) {
				$(this).toggleClass("hidden");
			});
			clearTimeout(timerid);
			timerid = setTimeout(function() { $('#autosubmit').submit(); }, 250);
		});
	});
	window.onload = function() {
		var expert = '<?PHP echo $cfg['webinterface_advanced_mode']; ?>';
		if(expert == 0) {
			$('.expertelement').each(function(i, obj) {
				$(this).toggleClass("hidden");
			});
		}
	$(".close-button").click(function(){
		$(this).closest("li").css("display","none");
        $.ajax({
            type: "POST",
            url: "../other/session_handling.php",
            data: {stats_news_html: "stats_news_html"}
        });
	})};
	</script>
	<?PHP
	if ($GLOBALS['style'] != NULL && is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'WI.js')) {
		echo '<script src="../styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'/WI.js?v='.$cfg['version_current_using'].'"></script>';
	}
	?>
<body>
	<div id="wrapper">
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">TSN Ranksystem - Webinterface <?PHP echo $cfg['version_current_using'];?></a>
				<?PHP if(isset($_SESSION[$rspathhex.'newversion']) && version_compare($_SESSION[$rspathhex.'newversion'], $cfg['version_current_using'], '>') && $_SESSION[$rspathhex.'newversion'] != '') {
					echo '<a class="navbar-brand" href="//ts-ranksystem.com/?changelog" target="_blank">'.$lang['winav9'].' ['.$_SESSION[$rspathhex.'newversion'].']</a>';
				} ?>
			</div>
			<?PHP if(basename($_SERVER['SCRIPT_NAME']) == "ranklist.php") { ?>
			<ul class="nav navbar-left top-nav">
				<li class="navbar-form navbar-right">
					<button onclick="window.open('../stats/list_rankup.php?admin=true','_blank'); return false;" class="btn btn-primary" name="adminlist">
						<i class="fas fa-list"></i><span class="item-margin"><?PHP echo $lang['wihladm']; ?></span>
					</button>
				</li>
			</ul>
			<?PHP } ?>
			<ul class="nav navbar-right top-nav">
				<?PHP
				if(isset($job_check['news_html']['timestamp']) && $job_check['news_html']['timestamp'] != 0 && isset($_SESSION[$rspathhex.'stats_news_html'])) { ?>
				<li class="navbar-form navbar-left">
					<span class="label label-primary"><?PHP echo $cfg['stats_news_html']; ?><button type="button" class="close close-button" aria-label="Close"><span aria-hidden="true">&times;</span></button></span>
				</li>
				<?PHP
				}
				if(isset($_SESSION[$rspathhex.'username']) && hash_equals($_SESSION[$rspathhex.'username'], $cfg['webinterface_user']) && hash_equals($_SESSION[$rspathhex.'password'], $cfg['webinterface_pass'])) { ?>
				<li class="dropdown">
					<a href="" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><span class="item-margin"><?PHP echo $_SESSION[$rspathhex.'username']; ?></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a>
					<ul class="dropdown-menu">
						<li class="navbar-form">
							<form method="post" id="autosubmit">
								<?PHP
								if($cfg['webinterface_advanced_mode'] == 1) {
									echo '<input name="switchexpert" value="0" type="hidden">';
									echo '<input class="switch-animate" id="switchexpert" name="switchexpert" value="checked" type="checkbox" data-size="mini" data-label-width="100" data-label-text="Expert Mode" checked>';
								} else {
									echo '<input class="switch-animate" id="switchexpert" name="switchexpert" value="check" type="checkbox" data-size="mini" data-label-width="100" data-label-text="Expert Mode">';
								}
								?>
							</form>
							<script>$("[id='switchexpert']").bootstrapSwitch();</script>
						</li>
						<li class="divider"></li>
						<?PHP if($_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 80) {
							echo '<li><a href="//',$_SERVER['SERVER_NAME'],substr(dirname($_SERVER['SCRIPT_NAME']),0,-12),'stats/"><i class="fas fa-chart-bar"></i><span class="item-margin">',$lang['winav6'],'</span></a></li>';
						} else {
							echo '<li><a href="//',$_SERVER['SERVER_NAME'],':',$_SERVER['SERVER_PORT'],substr(dirname($_SERVER['SCRIPT_NAME']),0,-12),'stats/"><i class="fas fa-chart-bar"></i><span class="item-margin">',$lang['winav6'],'</span></a></li>';
						} ?>
						<li>
							<a href="changepassword.php"><i class="fas fa-key"></i><span class="item-margin"><?PHP echo $lang['pass2']; ?></span></a>
						</li>
						<li class="divider"></li>
						<li>
							<form method="post" id="logout">
								<div class="form-group">
									<button type="submit" name="logout" class="btn btn-primary btn-sm btn-block"><span class="fas fa-sign-out-alt" aria-hidden="true"></span><span class="item-margin"><?PHP echo $lang['wilogout']; ?></span></button>
								</div>
							</form>
						</li>
					</ul>
				</li>
				<?PHP } elseif($_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 80) {
					echo '<li><a href="//',$_SERVER['SERVER_NAME'],substr(dirname($_SERVER['SCRIPT_NAME']),0,-12),'stats/"><i class="fas fa-chart-bar"></i><span class="item-margin">',$lang['winav6'],'</span></a></li>';
				} else {
					echo '<li><a href="//',$_SERVER['SERVER_NAME'],':',$_SERVER['SERVER_PORT'],substr(dirname($_SERVER['SCRIPT_NAME']),0,-12),'stats/"><i class="fas fa-chart-bar"></i><span class="item-margin">',$lang['winav6'],'</span></a></li>';
				} ?>
				<li class="dropdown">
					<?PHP
					$dropdownlist = '';
					if(is_dir($GLOBALS['langpath'])) {
						foreach(scandir($GLOBALS['langpath']) as $file) {
							if ('.' === $file || '..' === $file || is_dir($file)) continue;
							$sep_lang = preg_split("/[._]/", $file);
							if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
								if($_SESSION[$rspathhex.'language'] == $sep_lang[1]) {
									$dropdownfront = '<a href="" class="dropdown-toggle" data-toggle="dropdown"><span class="flag-icon flag-icon-'.$sep_lang[3].'"></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a><ul class="dropdown-menu">';
								}
								$dropdownlist .= '<li><a href="?lang='.$sep_lang[1].'"><span class="flag-icon flag-icon-'.$sep_lang[3].'"></span><span class="item-margin">'.strtoupper($sep_lang[1]).' - '.$sep_lang[2].'</span></a></li>';
							}
						}
					}
					echo $dropdownfront,$dropdownlist;
					?>
					</ul>
				</li>
			</ul>
			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav side-nav">
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "ts.php" ? ' class="active">' : '>'); ?>
						<a href="ts.php"><i class="fas fa-headset fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav1']; ?></span></a>
					</li>
					<?PHP 
					if ((array_key_exists('webinterface_fresh_installation', $cfg) && $cfg['webinterface_fresh_installation'] != 1) || !array_key_exists('webinterface_fresh_installation', $cfg)) {
						echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "db.php" ? ' class="active expertelement">' : ' class="expertelement">'); ?>
							<a href="db.php"><i class="fas fa-database fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav2']; ?></span></a>
						</li>
						<li>
							<a href="javascript:;" data-toggle="collapse" data-target="#rank"><i class="fas fa-hourglass-half fa-fw"></i><span class="item-margin"><?PHP echo $lang['stmy0002']; ?></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a>
							<?PHP echo '<ul id="rank" class="'.(in_array(basename($_SERVER['SCRIPT_NAME']), array("core.php", "rank.php", "boost.php", "except.php", "msg.php")) ? 'in collapse">' : 'collapse">'); ?>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "core.php" ? ' class="active">' : '>'); ?>
									<a href="core.php"><i class="fas fa-cogs fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav3']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "rank.php" ? ' class="active">' : '>'); ?>
									<a href="rank.php"><i class="fas fa-list-ol fa-fw"></i><span class="item-margin"><?PHP echo $lang['wigrptime']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "except.php" ? ' class="active">' : '>'); ?>
									<a href="except.php"><i class="fas fa-ban fa-fw"></i><span class="item-margin"><?PHP echo $lang['wiexcept']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "boost.php" ? ' class="active">' : '>'); ?>
									<a href="boost.php"><i class="fas fa-rocket fa-fw"></i><span class="item-margin"><?PHP echo $lang['wiboost']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "msg.php" ? ' class="active">' : '>'); ?>
									<a href="msg.php"><i class="fas fa-envelope fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav5']; ?></span></a>
								</li>
							</ul>
						</li>
						<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "other.php" ? ' class="active expertelement">' : ' class="expertelement">'); ?>
							<a href="other.php"><i class="fas fa-wrench fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav4']; ?></span></a>
						</li>
						<li>
							<a href="javascript:;" data-toggle="collapse" data-target="#stats"><i class="fas fa-chart-area fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav6']; ?></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a>
							<?PHP echo '<ul id="stats" class="'.(in_array(basename($_SERVER['SCRIPT_NAME']), array("stats.php", "ranklist.php", "imprint.php")) ? 'in collapse">' : 'collapse">'); ?>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "stats.php" ? ' class="active expertelement">' : ' class="expertelement">'); ?>
									<a href="stats.php"><i class="fas fa-chart-bar fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav13']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "ranklist.php" ? ' class="active">' : '>'); ?>
									<a href="ranklist.php"><i class="fas fa-list fa-fw"></i><span class="item-margin"><?PHP echo $lang['stnv0029']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "imprint.php" ? ' class="active">' : '>'); ?>
									<a href="imprint.php"><i class="fas fa-address-card fa-fw"></i><span class="item-margin"><?PHP echo $lang['imprint']; ?></span></a>
								</li>
							</ul>
						</li>
						<li class="divider"></li>
						<li>
							<a href="javascript:;" data-toggle="collapse" data-target="#admin"><i class="fas fa-users fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav7']; ?></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a>
							<?PHP echo '<ul id="admin" class="'.(in_array(basename($_SERVER['SCRIPT_NAME']), array("admin_mrgclient.php", "admin_addtime.php", "admin_remtime.php", "admin_delclient.php", "reset.php", "export.php")) ? 'in collapse">' : 'collapse">'); ?>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "admin_mrgclient.php" ? ' class="active">' : '>'); ?>
									<a href="admin_mrgclient.php"><i class="fas fa-code-fork fa-fw"></i><span class="item-margin"><?PHP echo $lang['wihladm500']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "admin_addtime.php" ? ' class="active">' : '>'); ?>
									<a href="admin_addtime.php"><i class="fas fa-plus fa-fw"></i><span class="item-margin"><?PHP echo $lang['wihladm1']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "admin_remtime.php" ? ' class="active">' : '>'); ?>
									<a href="admin_remtime.php"><i class="fas fa-minus fa-fw"></i><span class="item-margin"><?PHP echo $lang['wihladm2']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "admin_delclient.php" ? ' class="active">' : '>'); ?>
									<a href="admin_delclient.php"><i class="fas fa-times fa-fw"></i><span class="item-margin"><?PHP echo $lang['wihladm4']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "reset.php" ? ' class="active expertelement">' : ' class="expertelement">'); ?>
									<a href="reset.php"><i class="fas fa-sync fa-fw"></i><span class="item-margin"><?PHP echo $lang['wihladm3']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "export.php" ? ' class="active expertelement">' : ' class="expertelement">'); ?>
									<a href="export.php"><i class="fas fa-download fa-fw"></i><span class="item-margin"><?PHP echo $lang['wihladmex']; ?></span></a>
								</li>
							</ul>
						</li>
						<li class="divider"></li>
						<li>
							<a href="javascript:;" data-toggle="collapse" data-target="#addons"><i class="fas fa-puzzle-piece fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav12']; ?></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a>
							<?PHP echo '<ul id="addons" class="'.(in_array(basename($_SERVER['SCRIPT_NAME']), array("addon_assign_groups.php", "api.php", "addon_channelinfo_toplist.php")) ? 'in collapse">' : 'collapse">'); ?>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "api.php" ? ' class="active">' : '>'); ?>
									<a href="api.php"><i class="fas fa-microchip fa-fw"></i><span class="item-margin"><?PHP echo $lang['api']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "addon_assign_groups.php" ? ' class="active">' : '>'); ?>
									<a href="addon_assign_groups.php"><i class="fas fa-user-plus fa-fw"></i><span class="item-margin"><?PHP echo $lang['stag0001']; ?></span></a>
								</li>
								<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "addon_channelinfo_toplist.php" ? ' class="active">' : '>'); ?>
									<a href="addon_channelinfo_toplist.php"><i class="fas fa-trophy fa-fw"></i><span class="item-margin"><?PHP echo $lang['addonchtopl']; ?></span></a>
								</li>
							</ul>
						</li>
						<?PHP
					} ?>
					<li class="divider"></li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "bot.php" ? ' class="active">' : '>'); ?>
						<a href="bot.php"><i class="fas fa-power-off fa-fw"></i><span class="item-margin"><?PHP echo $lang['winav8']; ?></span></a>
					</li>
					<?PHP
					if(isset($botstatus)) {
						echo '<li class="divider"></li>';
						if($botstatus == 1) {
							echo '<li><div class="btn-group-justified alertbot alert-success" style="width:100%;"><i class="fas fa-check fa-fw"></i><span class="item-margin">'.$lang['boton'].'</span></div></li>';
						} else {
							echo '<li><div class="btn-group-justified alertbot alert-info" style="width:100%;"><i class="fas fa-times fa-fw"></i><span class="item-margin">'.$lang['botoff'].'</span>';
							if (file_exists($GLOBALS['autostart'])) {
								echo '<br><br><i class="fas fa-info-circle fa-fw"></i><span class="item-margin">',$lang['autooff'],'</span></div></li>';
							} else {
								echo '</div></li>';
							}
						}
					}
					?>
				</ul>
			</div>
		</nav>
<?PHP
if($cfg['webinterface_admin_client_unique_id_list'] == NULL && isset($_SESSION[$rspathhex.'username']) && hash_equals($_SESSION[$rspathhex.'username'], $cfg['webinterface_user']) && !isset($err_msg) && $cfg['webinterface_fresh_installation'] != 1) {
	$err_msg = $lang['winav11']; $err_lvl = 2;
}

if((!isset($_SERVER['HTTPS']) || isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on") && !isset($err_msg) && basename($_SERVER['SCRIPT_NAME']) == "index.php") {
	$host = "<a href=\"https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."\">";
	$err_msg = sprintf($lang['winav10'], $host,'</a>!<br>', '<br>'); $err_lvl = 2;
}
?>