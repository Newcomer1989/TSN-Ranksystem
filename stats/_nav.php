<?php
$job_check = $mysqlcon->query("SELECT * FROM `$dbname`.`job_check`")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
if ((time() - $job_check['last_update']['timestamp']) < 259200 && ! isset($_SESSION[$rspathhex.'upinfomsg'])) {
    if (! isset($err_msg)) {
        $err_msg = '<i class="fas fa-info-circle"></i><span class="item-margin">'.sprintf($lang['upinf2'], date('Y-m-d H:i', $job_check['last_update']['timestamp']), '</span><a href="//ts-ranksystem.com/#changelog" target="_blank"><i class="fas fa-book"></i><span class="item-margin">', '</span></a>');
        $err_lvl = 1;
        $_SESSION[$rspathhex.'upinfomsg'] = 1;
    }
}

if (isset($_POST['username'])) {
    $_GET['search'] = $_POST['usersuche'];
    $_GET['seite'] = 1;
}
$filter = $searchstring = null;
if (isset($_GET['search']) && $_GET['search'] != '') {
    $getstring = htmlspecialchars($_GET['search']);
}
if (isset($getstring) && strstr($getstring, 'filter:excepted:')) {
    if (str_replace('filter:excepted:', '', $getstring) != '') {
        $searchstring = str_replace('filter:excepted:', '', $getstring);
    }
    $filter .= " AND `except` IN ('2','3')";
} elseif (isset($getstring) && strstr($getstring, 'filter:nonexcepted:')) {
    if (str_replace('filter:nonexcepted:', '', $getstring) != '') {
        $searchstring = str_replace('filter:nonexcepted:', '', $getstring);
    }
    $filter .= " AND `except` IN ('0','1')";
} else {
    if (isset($getstring)) {
        $searchstring = $getstring;
    } else {
        $searchstring = '';
    }
    if ($cfg['stats_show_excepted_clients_switch'] == 0) {
        $filter .= " AND `except` IN ('0','1')";
    }
}
if (isset($getstring) && strstr($getstring, 'filter:online:')) {
    $searchstring = preg_replace('/filter\:online\:/', '', $searchstring);
    $filter .= " AND `online`='1'";
} elseif (isset($getstring) && strstr($getstring, 'filter:nononline:')) {
    $searchstring = preg_replace('/filter\:nononline\:/', '', $searchstring);
    $filter .= " AND `online`='0'";
}
if (isset($getstring) && strstr($getstring, 'filter:actualgroup:')) {
    preg_match('/filter\:actualgroup\:(.*)\:/', $searchstring, $grpvalue);
    $searchstring = preg_replace('/filter\:actualgroup\:(.*)\:/', '', $searchstring);
    $filter .= " AND `grpid`='".$grpvalue[1]."'";
}
if (isset($getstring) && strstr($getstring, 'filter:country:')) {
    preg_match('/filter\:country\:(.*)\:/', $searchstring, $grpvalue);
    $searchstring = preg_replace('/filter\:country\:(.*)\:/', '', $searchstring);
    $filter .= " AND `nation`='".$grpvalue[1]."'";
}
if (isset($getstring) && strstr($getstring, 'filter:lastseen:')) {
    preg_match('/filter\:lastseen\:(.*)\:(.*)\:/', $searchstring, $seenvalue);
    $searchstring = preg_replace('/filter\:lastseen\:(.*)\:(.*)\:/', '', $searchstring);
    if (isset($seenvalue[2]) && is_numeric($seenvalue[2])) {
        $lastseen = $seenvalue[2];
    } elseif (isset($seenvalue[2])) {
        $r = date_parse_from_format('Y-m-d H-i', $seenvalue[2]);
        $lastseen = mktime($r['hour'], $r['minute'], $r['second'], $r['month'], $r['day'], $r['year']);
    } else {
        $lastseen = 0;
    }
    if (isset($seenvalue[1]) && ($seenvalue[1] == '&lt;' || $seenvalue[1] == '<')) {
        $operator = '<';
    } elseif (isset($seenvalue[1]) && ($seenvalue[1] == '&gt;' || $seenvalue[1] == '>')) {
        $operator = '>';
    } elseif (isset($seenvalue[1]) && $seenvalue[1] == '!=') {
        $operator = '!=';
    } else {
        $operator = '=';
    }
    $filter .= ' AND `lastseen`'.$operator."'".$lastseen."'";
}
$searchstring = htmlspecialchars_decode($searchstring);

if (isset($getstring)) {
    $dbdata_full = $mysqlcon->prepare("SELECT COUNT(*) FROM `$dbname`.`user` WHERE (`uuid` LIKE :searchvalue OR `cldbid` LIKE :searchvalue OR `name` LIKE :searchvalue)$filter");
    $dbdata_full->bindValue(':searchvalue', '%'.$searchstring.'%', PDO::PARAM_STR);
    $dbdata_full->execute();
    $sumentries = $dbdata_full->fetch(PDO::FETCH_NUM);
    $getstring = rawurlencode($getstring);
} else {
    $getstring = '';
    $sumentries = $mysqlcon->query("SELECT COUNT(*) FROM `$dbname`.`user`")->fetch(PDO::FETCH_NUM);
}

if (! isset($_GET['seite'])) {
    $seite = 1;
} else {
    $_GET['seite'] = preg_replace('/\D/', '', $_GET['seite']);
    if ($_GET['seite'] > 0) {
        $seite = $_GET['seite'];
    } else {
        $seite = 1;
    }
}
$adminlogin = 0;
$sortarr = array_flip(['active', 'cldbid', 'count', 'grpid', 'grpsince', 'idle', 'lastseen', 'name', 'nation', 'nextup', 'platform', 'rank', 'uuid', 'version', 'count_day', 'count_week', 'count_month', 'idle_day', 'idle_week', 'idle_month', 'active_day', 'active_week', 'active_month']);

if (isset($_GET['sort']) && isset($sortarr[$_GET['sort']])) {
    $keysort = $_GET['sort'];
} else {
    $keysort = $cfg['stats_column_default_sort'];
}
if (isset($_GET['order']) && $_GET['order'] == 'desc') {
    $keyorder = 'desc';
} elseif (isset($_GET['order']) && $_GET['order'] == 'asc') {
    $keyorder = 'asc';
} else {
    $keyorder = $cfg['stats_column_default_order'];
}

if (isset($_GET['admin'])) {
    if (hash_equals($_SESSION[$rspathhex.'username'], $cfg['webinterface_user']) && hash_equals($_SESSION[$rspathhex.'password'], $cfg['webinterface_pass']) && hash_equals($_SESSION[$rspathhex.'clientip'], getclientip())) {
        $adminlogin = 1;
    }
}

if (! isset($_GET['user'])) {
    $user_pro_seite = 25;
} elseif ($_GET['user'] == 'all') {
    if ($sumentries[0] > 1000) {
        $user_pro_seite = 1000;
    } else {
        $user_pro_seite = $sumentries[0];
    }
} else {
    $_GET['user'] = preg_replace('/\D/', '', $_GET['user']);
    if ($_GET['user'] > 1000) {
        $user_pro_seite = 1000;
    } elseif ($_GET['user'] > 0) {
        $user_pro_seite = $_GET['user'];
    } else {
        $user_pro_seite = 25;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $cfg['default_language']; ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="version" content="<?php echo $cfg['version_current_using']; ?>">
	<link rel="icon" href="../tsicons/rs.png">
	<title>TSN Ranksystem - ts-ranksystem.com</title>
	<link href="../libs/combined_st.css?v=<?php echo $cfg['version_current_using']; ?>" rel="stylesheet">
<?php
    if ($GLOBALS['style'] != null && is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'ST.css')) {
        echo '<link href="../styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'ST.css?v='.$cfg['version_current_using'].'" rel="stylesheet">';
    }
    switch(basename($_SERVER['SCRIPT_NAME'])) {
        case 'index.php':
            ?><script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/qbrm.js?v=<?php echo $cfg['version_current_using']; ?>','../libs/statsindex.js?v=<?php echo $cfg['version_current_using']; ?>','../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
            break;
        case 'assign_groups.php':
            ?><script src="../libs/qbh_bsw.js?v=<?php echo $cfg['version_current_using']; ?>"></script>
			<script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"defer"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
            break;
        case 'top_all.php':
            ?><script src="../libs/qbrm.js?v=<?php echo $cfg['version_current_using']; ?>"></script>
			<script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"defer"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
            break;
        case 'top_month.php':
            ?><script src="../libs/qbrm.js?v=<?php echo $cfg['version_current_using']; ?>"></script>
			<script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"defer"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
            break;
        case 'top_week.php':
            ?><script src="../libs/qbrm.js?v=<?php echo $cfg['version_current_using']; ?>"></script>
			<script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"defer"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
            break;
        case 'verify.php':
            ?><script src="../libs/qbh_bse.js?v=<?php echo $cfg['version_current_using']; ?>"></script>
			<script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"defer"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
            break;
        case 'list_rankup.php':
            ?><script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/qb.js?v=<?php echo $cfg['version_current_using']; ?>','../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
            break;
        default:
            ?><script src="../libs/qb.js?v=<?php echo $cfg['version_current_using']; ?>"></script>
			<script>!function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"defer"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",['../libs/fa.js?v=<?php echo $cfg['version_current_using']; ?>'])</script><?php
    } ?>
	<script>
	window.onload=function(){$(".close-button").click(function(){
		$(this).closest("li").css("display","none");
        $.ajax({
            type: "POST",
            url: "../other/session_handling.php",
            data: {stats_news_html: "stats_news_html"}
        });
	})};
	</script>
	<?php if ($GLOBALS['style'] != null && is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'ST.js')) {
	    echo '<script src="../styles'.DIRECTORY_SEPARATOR.$GLOBALS['style'].DIRECTORY_SEPARATOR.'/ST.js?v='.$cfg['version_current_using'].'"></script>';
	}
    if (isset($cfg['stats_show_site_navigation_switch']) && $cfg['stats_show_site_navigation_switch'] == 0) { ?>
<?php } ?>
</head>
<body>
	<div id="myModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $lang['stnv0001']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?php echo $cfg['stats_server_news']; ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div id="myModal2" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $lang['stnv0003']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?php echo $lang['stnv0004']; ?></p>
					<p><?php echo $lang['stnv0005']; ?></p>
				</div>
				<div class="modal-footer">
					<form method="post">
							<button class="btn btn-primary" type="submit" name="refresh"><?php echo $lang['stnv0006']; ?></button>
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="myStatsModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $lang['stnv0016']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?php echo $lang['stnv0017']; ?></p>
					<p><?php echo $lang['stnv0018']; ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
	<div id="infoModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php echo $lang['stnv0019']; ?></h4>
				</div>
				<div class="modal-body">
					<p><?php echo $lang['stnv0020']; ?></p>
					<p><?php echo $lang['stnv0021']; ?></p>
					<p><?php echo $lang['stnv0022']; ?></p>
					<p><?php echo $lang['stnv0023']; ?></p>
					<br>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
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
					<p><?php echo $lang['stnv0031']; ?></p>
					<p><?php echo $lang['stnv0032']; ?></p>
					<p><?php echo $lang['stnv0033']; ?></p>
					<p><?php echo $lang['stnv0034']; ?></p>
					<p><?php echo $lang['stnv0035']; ?></p>
					<p><br></p>
					<p><b>filter:excepted:</b></p>
					<p><?php echo $lang['stnv0036']; ?></p>
					<p><b>filter:nonexcepted:</b></p>
					<p><?php echo $lang['stnv0037']; ?></p>
					<p><b>filter:online:</b></p>
					<p><?php echo $lang['stnv0038']; ?></p>
					<p><b>filter:nononline:</b></p>
					<p><?php echo $lang['stnv0039']; ?></p>
					<p><b>filter:actualgroup:<i>GROUPID</i>:</b></p>
					<p><?php echo $lang['stnv0040']; ?></p>
					<p><b>filter:country:<i>TS3-COUNTRY-CODE</i>:</b></p>
					<p><?php echo $lang['stnv0042']; ?></p>
					<p><b>filter:lastseen:<i>OPERATOR</i>:<i>TIME</i>:</b></p>
					<p><?php echo $lang['stnv0041']; ?></p>
					<br>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
	if ($cfg['stats_show_site_navigation_switch'] == 1) {
	    ?>
	<div id="wrapper">
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-header">
				<a class="navbar-brand" href="index.php"><?php echo $lang['stnv0024']; ?></a>
			</div>
			<?php if (basename($_SERVER['SCRIPT_NAME']) == 'list_rankup.php') { ?>
			<ul class="nav navbar-left top-nav">
				<li class="navbar-form navbar-left dropdown">
					<div class="btn-group">
						<a href="#filteroptions" data-toggle="modal" class="btn btn-primary">
							<span class="fas fa-info-circle" aria-hidden="true"></span>
						</a>
					</div>
				</li>
				<li class="navbar-form navbar-right dropdown" title="<?php echo $lang['stnv0025'] ?>">
					<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<?php echo $user_pro_seite; ?>
						<span class="item-margin"><i class="fas fa-caret-down"></i></span>
					</button>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
						<li role="presentation"><a role="menuitem" href="<?php echo "?sort=$keysort&amp;order=$keyorder&amp;user=50&amp;lang={$cfg['default_language']}&amp;search=$getstring"; ?>">50</a></li>
						<li role="presentation"><a role="menuitem" href="<?php echo "?sort=$keysort&amp;order=$keyorder&amp;user=100&amp;lang={$cfg['default_language']}&amp;search=$getstring"; ?>">100</a></li>
						<li role="presentation"><a role="menuitem" href="<?php echo "?sort=$keysort&amp;order=$keyorder&amp;user=250&amp;lang={$cfg['default_language']}&amp;search=$getstring"; ?>">250</a></li>
						<li role="presentation"><a role="menuitem" href="<?php echo "?sort=$keysort&amp;order=$keyorder&amp;user=500&amp;lang={$cfg['default_language']}&amp;search=$getstring"; ?>">500</a></li>
						<?php if (isset($sumentries[0]) && $sumentries[0] > 1000) { ?>
							<li role="presentation"><a role="menuitem" href="<?php echo "?sort=$keysort&amp;order=$keyorder&amp;user=1000&amp;lang={$cfg['default_language']}&amp;search=$getstring"; ?>">1000</a></li>
						<?php } else { ?>
							<li role="separator" class="divider"></li>
							<li role="presentation"><a role="menuitem" href="<?php echo "?sort=$keysort&amp;order=$keyorder&amp;user=all&amp;lang={$cfg['default_language']}&amp;search=$getstring"; ?>"><?php echo $lang['stnv0026']; ?></a></li>
						<?php } ?>
					</ul>
				</li>
				<li class="navbar-form navbar-right">
					<form method="post">
						<div class="form-group">
							<input class="form-control" type="text" name="usersuche" placeholder="Search"<?php if (isset($getstring)) {
							    echo ' value="'.rawurldecode($getstring).'"';
							} ?>>
						</div>
						<button class="btn btn-primary" type="submit" name="username"><span class="fas fa-search" aria-hidden="true"></span></button>
					</form>
				</li>
			</ul>
			<?php } ?>
			<ul class="nav navbar-right top-nav">
				<?php
                if (isset($job_check['news_html']['timestamp']) && $job_check['news_html']['timestamp'] != 0 && isset($_SESSION[$rspathhex.'stats_news_html'])) { ?>
				<li class="navbar-form navbar-left">
					<span class="label label-primary"><?php echo $cfg['stats_news_html']; ?><button type="button" class="close close-button" aria-label="Close"><span aria-hidden="true">&times;</span></button></span>
				</li>
				<?php }
                if ((time() - $job_check['calc_user_lastscan']['timestamp']) > 600) { ?>
				<li class="navbar-form navbar-left">
					<span class="label label-warning"><?php echo $lang['stnv0027']; ?></span>
				</li>
				<?php } ?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><?php
                    echo '<span class="item-margin"></span>';
	    if (isset($_SESSION[$rspathhex.'tsname'])) {
	        echo $_SESSION[$rspathhex.'tsname'];
	    }
	    ?><span class="item-margin"><i class="fas fa-caret-down fa-fw"></i></span></a>
					<ul class="dropdown-menu">
						<?php
	        if (isset($_SESSION[$rspathhex.'tsname']) && $_SESSION[$rspathhex.'tsname'] == $lang['stag0016'] || isset($_SESSION[$rspathhex.'tsname']) && $_SESSION[$rspathhex.'tsname'] == 'verification needed (multiple)!' || isset($_SESSION[$rspathhex.'connected']) && $_SESSION[$rspathhex.'connected'] == 0 || ! isset($_SESSION[$rspathhex.'connected'])) {
	            echo '<li><a href="verify.php"><i class="fas fa-key fa-fw"></i><span class="item-margin">'.$lang['stag0017'].'</span></a></li>';
	        }
	        if (isset($_SESSION[$rspathhex.'admin']) && $_SESSION[$rspathhex.'admin'] == true) {
	            if ($_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 80) {
	                echo '<li><a href="//',$_SERVER['SERVER_NAME'],':',substr(dirname($_SERVER['SCRIPT_NAME']), 0, -5),'webinterface/bot.php"><i class="fas fa-wrench fa-fw"></i><span class="item-margin">',$lang['wi'],'</span></a></li>';
	            } else {
	                echo '<li><a href="//',$_SERVER['SERVER_NAME'],':',$_SERVER['SERVER_PORT'],substr(dirname($_SERVER['SCRIPT_NAME']), 0, -5),'webinterface/bot.php"><i class="fas fa-wrench fa-fw"></i><span class="item-margin">',$lang['wi'],'</span></a></li>';
	            }
	        } elseif (isset($_SESSION[$rspathhex.'connected']) && $_SESSION[$rspathhex.'connected'] == 0 || ! isset($_SESSION[$rspathhex.'connected'])) {
	            echo '<li><a href="ts3server://';
	            if (($cfg['teamspeak_host_address'] == 'localhost' || $cfg['teamspeak_host_address'] == '127.0.0.1') && strpos($_SERVER['HTTP_HOST'], 'www.') == 0) {
	                echo preg_replace('/www\./', '', $_SERVER['HTTP_HOST']);
	            } elseif ($cfg['teamspeak_host_address'] == 'localhost' || $cfg['teamspeak_host_address'] == '127.0.0.1') {
	                echo $_SERVER['HTTP_HOST'];
	            } else {
	                echo $cfg['teamspeak_host_address'];
	            }
	            echo ':'.$cfg['teamspeak_voice_port'];
	            echo '"><i class="fas fa-headset fa-fw"></i><span class="item-margin">'.$lang['stnv0043'].'</span></a></li>';
	        }
	    ?>
						<li>
							<a href="#myModal2" data-toggle="modal"><i class="fas fa-sync fa-fw"></i><span class="item-margin">Refresh Session</span></a>
						</li>
						<li>
							<a href="my_stats.php"><i class="fas fa-chart-bar fa-fw"></i><span class="item-margin"><?php echo $lang['stmy0001']; ?></span></a>
						</li>
						<li>
							<a href="#myModal" data-toggle="modal"><i class="fas fa-envelope fa-fw"></i><span class="item-margin"><?php echo $lang['stnv0001']; ?></span></a>
						</li>
					</ul>
				</li>
				<li class="dropdown">
					<?php
                    $dropdownlist = $dropdownfront = '';
	    if (! isset($_SESSION[$rspathhex.'language'])) {
	        $_SESSION[$rspathhex.'language'] = get_language();
	    }
	    if (is_dir($GLOBALS['langpath'])) {
	        foreach (scandir($GLOBALS['langpath']) as $file) {
	            if ('.' === $file || '..' === $file || is_dir($file)) {
	                continue;
	            }
	            $sep_lang = preg_split('/[._]/', $file);
	            if (isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
	                if (isset($_SESSION[$rspathhex.'language']) && $_SESSION[$rspathhex.'language'] == $sep_lang[1]) {
	                    $dropdownfront .= '<a href="" class="dropdown-toggle" data-toggle="dropdown"><span class="flag-icon flag-icon-'.$sep_lang[3].'"></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a><ul class="dropdown-menu">';
	                }
	                $dropdownlist .= '<li><a href="?lang='.$sep_lang[1].'"><span class="flag-icon flag-icon-'.$sep_lang[3].'"></span><span class="item-margin">'.strtoupper($sep_lang[1]).' - '.$sep_lang[2].'<span></a></li>';
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
					<?php echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'index.php' ? ' class="active">' : '>'); ?>
						<a href="index.php"><i class="fas fa-chart-area fa-fw"></i><span class="item-margin"><?php echo $lang['stix0001']; ?></span></a>
					</li>
					<?php echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'my_stats.php' ? ' class="active">' : '>'); ?>
						<a href="my_stats.php"><i class="fas fa-chart-bar fa-fw"></i><span class="item-margin"><?php echo $lang['stmy0001']; ?></span></a>
					</li>
					<?php if ($addons_config['assign_groups_active']['value'] == '1') {
					    echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'assign_groups.php' ? ' class="active">' : '>'); ?>
							<a href="assign_groups.php"><i class="fas fa-address-card fa-fw"></i><span class="item-margin"><?php echo $lang['stag0001']; ?></span></a>
						<?php }	?>
					</li>
					<li>
						<a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fas fa-trophy fa-fw"></i><span class="item-margin"><?php echo $lang['sttw0001']; ?></span><span class="item-margin"><i class="fas fa-caret-down"></i></span></a>
						<?php echo '<ul id="demo" class="'.(basename($_SERVER['SCRIPT_NAME']) == 'top_week.php' || basename($_SERVER['SCRIPT_NAME']) == 'top_month.php' || basename($_SERVER['SCRIPT_NAME']) == 'top_all.php' ? 'in collapse">' : 'collapse">'); ?>
							<?php echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'top_week.php' ? ' class="active">' : '>'); ?>
								<a href="top_week.php"><span class="item-margin"><?php echo $lang['sttw0002']; ?></span></a>
							</li>
							<?php echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'top_month.php' ? ' class="active">' : '>'); ?>
								<a href="top_month.php"><span class="item-margin"><?php echo $lang['sttm0001']; ?></span></a>
							</li>
							<?php echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'top_all.php' ? ' class="active">' : '>'); ?>
								<a href="top_all.php"><span class="item-margin"><?php echo $lang['stta0001']; ?></span></a>
							</li>
						</ul>
					</li>
					<?php echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'list_rankup.php' ? ' class="active">' : '>'); ?>
						<a href="list_rankup.php"><i class="fas fa-list-ul fa-fw"></i><span class="item-margin"><?php echo $lang['stnv0029']; ?></span></a>
					</li>
					<?php echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == 'info.php' ? ' class="active">' : '>'); ?>
						<a href="info.php"><i class="fas fa-info-circle fa-fw"></i><span class="item-margin"><?php echo $lang['stnv0030']; ?></span></a>
					</li>
				</ul>
			</div>
		</nav>
<?php
	} else {
	    echo '<div id="container">';
	}
?>