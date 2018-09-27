<?PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if(in_array('sha512', hash_algos())) {
	ini_set('session.hash_function', 'sha512');
}
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	ini_set('session.cookie_secure', 1);
	if(!headers_sent()) {
		header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload;");
	}
}
session_start();
require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if($language == "ar") {
	require_once('../languages/nations_en.php');
} elseif($language == "cz") {
	require_once('../languages/nations_en.php');
} elseif($language == "de") {
	require_once('../languages/nations_de.php');
} elseif($language == "en") {
	require_once('../languages/nations_en.php');
} elseif($language == "fr") {
	require_once('../languages/nations_fr.php');
} elseif($language == "it") {
	require_once('../languages/nations_it.php');
} elseif($language == "nl") {
	require_once('../languages/nations_en.php');
} elseif($language == "pl") {
	require_once('../languages/nations_pl.php');
} elseif($language == "ro") {
	require_once('../languages/nations_en.php');
} elseif($language == "ru") {
	require_once('../languages/nations_ru.php');
} elseif($language == "pt") {
	require_once('../languages/nations_pt.php');
}

if(!isset($_SESSION[$rspathhex.'tsuid'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}

function human_readable_size($bytes,$lang) {
	$size = array($lang['size_byte'],$lang['size_kib'],$lang['size_mib'],$lang['size_gib'],$lang['size_tib'],$lang['size_pib'],$lang['size_eib'],$lang['size_zib'],$lang['size_yib']);
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}

if(($sql_res = $mysqlcon->query("SELECT * FROM `$dbname`.`stats_server`")->fetch()) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
}

require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['stix0001']; ?>
							<a href="#infoModal" data-toggle="modal" class="btn btn-primary">
								<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
							</a>
						</h1>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-users fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP echo $sql_res['total_user'] ?></div>
										<div><?PHP echo $lang['stix0002']; ?></div>
									</div>
								</div>
							</div>
							<a href="list_rankup.php">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0003']; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-clock-o fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP if(round(($sql_res['total_online_time'] / 86400)) == 1) { echo sprintf($lang['day'], round(($sql_res['total_online_time'] / 86400))); } else { echo sprintf($lang['days'], round(($sql_res['total_online_time'] / 86400))); } ?></div>
										<div><?PHP echo $lang['stix0004']; ?></div>
									</div>
								</div>
							</div>
							<a href="top_all.php">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0005']; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-clock-o fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP if(round(($sql_res['total_online_month'] / 86400)) == 1) { echo sprintf($lang['day'], round(($sql_res['total_online_month'] / 86400))); } else { echo sprintf($lang['days'], round(($sql_res['total_online_month'] / 86400))); } ?></div>
										<div><?PHP if($sql_res['total_online_month'] == 0) { echo $lang['stix0048']; } else { echo $lang['stix0049']; } ?></div>
									</div>
								</div>
							</div>
							<a href="top_month.php">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0006']; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-clock-o fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP if(round(($sql_res['total_online_week'] / 86400)) == 1) { echo sprintf($lang['day'], round(($sql_res['total_online_week'] / 86400))); } else { echo sprintf($lang['days'], round(($sql_res['total_online_week'] / 86400))); } ?></div>
										<div><?PHP if ($sql_res['total_online_week'] == 0) { echo $lang['stix0048']; } else { echo $lang['stix0050']; } ?></div>
									</div>
								</div>
							</div>
							<a href="top_week.php">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0007']; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-9">
										<h3 class="panel-title"><i class="fa fa-bar-chart-o"></i>&nbsp;<?PHP echo $lang['stix0008']; ?></h3>
									</div>
									<div class="col-xs-3">
										<div class="btn-group pull-right">
										  <select class="form-control" id="period">
											<option value="day"><?PHP echo $lang['stix0013']; ?></option>
											<option value="week"><?PHP echo $lang['stix0014']; ?></option>
											<option value="month"><?PHP echo $lang['stix0015']; ?></option>
											<option value="3month"><?PHP echo $lang['stix0064']; ?></option>
										  </select>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-body">
								<div id="serverusagechart"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title"><i class="fa fa-long-arrow-right"></i>&nbsp;<?PHP echo $lang['stix0016']; ?></h3>
							</div>
							<div class="panel-body">
								<div id="time-gap-donut"></div>
							</div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="panel panel-green">
							<div class="panel-heading">
								<h3 class="panel-title"><i class="fa fa-long-arrow-right"></i>&nbsp;<?PHP echo $lang['stix0017']; ?></h3>
							</div>
							<div class="panel-body">
								<div id="client-version-donut"></div>
							</div>
							<a href="versions.php">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0061']; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<h3 class="panel-title"><i class="fa fa-long-arrow-right"></i>&nbsp;<?PHP echo $lang['stix0018']; ?></h3>
							</div>
							<div class="panel-body">
								<div id="user-descent-donut"></div>
							</div>
							<a href="nations.php">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0062']; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="panel panel-red">
							<div class="panel-heading">
								<h3 class="panel-title"><i class="fa fa-long-arrow-right"></i>&nbsp;<?PHP echo $lang['stix0019']; ?></h3>
							</div>
							<div class="panel-body">
								<div id="user-platform-donut"></div>
							</div>
							<a href="platforms.php">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0063']; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-users fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP echo $sql_res['user_today']; ?></div>
										<div><?PHP echo $lang['stix0060'],' ',$lang['stix0055']; ?></div>
									</div>
								</div>
							</div>
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:%3e:<?PHP echo time()-86400; ?>:">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0059'],' (',$lang['stix0055'],')'; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-users fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP echo $sql_res['user_week']; ?></div>
										<div><?PHP echo $lang['stix0060'],' ',sprintf($lang['stix0056'], '7'); ?></div>
									</div>
								</div>
							</div>
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:%3e:<?PHP echo time()-604800; ?>:">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0059'],' (',sprintf($lang['stix0056'], '7'),')'; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-users fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP echo $sql_res['user_month']; ?></div>
										<div><?PHP echo $lang['stix0060'],' ',sprintf($lang['stix0056'], '30'); ?></div>
									</div>
								</div>
							</div>
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:%3e:<?PHP echo time()-2592000; ?>:">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0059'],' (',sprintf($lang['stix0056'], '30'),')'; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
					<div class="col-lg-3 col-md-6">
						<div class="panel panel-red">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
										<i class="fa fa-users fa-5x"></i>
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?PHP echo $sql_res['user_quarter']; ?></div>
										<div><?PHP echo $lang['stix0060'],' ',sprintf($lang['stix0056'], '90'); ?></div>
									</div>
								</div>
							</div>
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:%3e:<?PHP echo time()-7776000; ?>:">
								<div class="panel-footer">
									<span class="pull-left"><?PHP echo $lang['stix0059'],' (',sprintf($lang['stix0056'], '90'),')'; ?></span>
									<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<h2><?PHP echo $lang['stix0020']; ?></h2>
						<div class="table-responsive">
							<table class="table table-bordered table-hover">
								<tbody>
									<tr>
										<td><?PHP echo $lang['stix0023']; ?></td>
										<td><?PHP if($sql_res['server_status'] == 1 || $sql_res['server_status'] == 3) { echo '<span class="text-success">'.$lang['stix0024'].'</span>'; } else { echo '<span class="text-danger">'.$lang['stix0025'].'</span>'; } ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0026']; ?></td>
										<td><?PHP if($sql_res['server_status'] == 0) { echo '0'; } else { echo $sql_res['server_used_slots'] , ' / ' ,($sql_res['server_used_slots'] + $sql_res['server_free_slots']); } ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0027']; ?></td>
										<td><?PHP echo $sql_res['server_channel_amount']; ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0028']; ?></td>
										<td><?PHP if($sql_res['server_status'] == 0) { echo '-';} else { echo $sql_res['server_ping'] . ' ' . $lang['time_ms'];} ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0029']; ?></td>
										<td><?PHP echo human_readable_size($sql_res['server_bytes_down'],$lang); ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0030']; ?></td>
										<td><?PHP echo human_readable_size($sql_res['server_bytes_up'],$lang); ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0031']; ?></td>
										<td><?PHP $serveruptime = new DateTime("@".$sql_res['server_uptime']); if ($sql_res['server_status'] == 0) { echo '-&nbsp;&nbsp;&nbsp;(<i>'.$lang['stix0032'].'&nbsp;'.(new DateTime("@0"))->diff($serveruptime)->format($timeformat).')</i>'; } else { echo $lang['stix0033']; } ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0034']; ?></td>
										<td><?PHP if($sql_res['server_status'] == 0) { echo '-'; } else { echo $sql_res['server_packet_loss'] * 100 ,' %';} ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="col-lg-6">
						<h2><?PHP echo $lang['stix0035']; ?></h2>
						<div class="table-responsive">
							<table class="table table-bordered table-hover">
								<tbody>
									<tr>
										<td><?PHP echo $lang['stix0036']; ?></td>
										<td><?PHP if(file_exists("../tsicons/servericon.png")) { 
										$img_content = file_get_contents("../tsicons/servericon.png");
										echo $sql_res['server_name'] .'<div class="pull-right"><img src="data:',mime_content_type("../tsicons/servericon.png"),';base64,'.base64_encode($img_content).'" width="16" height="16" alt="servericon"></div>';
										} else { echo $sql_res['server_name']; } ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0037']; ?></td>
										<td><a href="ts3server://<?PHP
										if (($ts['host']=='localhost' || $ts['host']=='127.0.0.1') && strpos($_SERVER['HTTP_HOST'], 'www.') == 0) {
											echo preg_replace('/www\./','',$_SERVER['HTTP_HOST']);
										} elseif ($ts['host']=='localhost' || $ts['host']=='127.0.0.1') {
											echo $_SERVER['HTTP_HOST'];
										} else {
											echo $ts['host'];
										}
										echo ':'.$ts['voice']; ?>">
										<?PHP
										if (($ts['host']=='localhost' || $ts['host']=='127.0.0.1') && strpos($_SERVER['HTTP_HOST'], 'www.') == 0) {
											echo preg_replace('/www\./','',$_SERVER['HTTP_HOST']);
										} elseif ($ts['host']=='localhost' || $ts['host']=='127.0.0.1') {
											echo $_SERVER['HTTP_HOST'];
										} else {
											echo $ts['host'];
										}
										echo ':'.$ts['voice']; ?></a></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0038']; ?></td>
										<td><?PHP if($sql_res['server_pass'] == '0')  {echo $lang['stix0039']; } else { echo $lang['stix0040']; } ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0041']; ?></td>
										<td><?PHP echo $sql_res['server_id'] ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0042']; ?></td>
										<td><?PHP echo $sql_res['server_platform'] ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0043']; ?></td>
										<td><?PHP echo substr($sql_res['server_version'], 0, strpos($sql_res['server_version'], ' ')); ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0044']; ?></td>
										<td><?PHP if($sql_res['server_creation_date']==0) { echo $lang['stix0051']; } else { echo date('d/m/Y', $sql_res['server_creation_date']);} ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0045']; ?></td>
										<td><?PHP if ($sql_res['server_weblist'] == 1) { echo '<a href="https://www.planetteamspeak.com/serverlist/result/server/ip/'; if($ts['host']=='localhost' || $ts['host']=='127.0.0.1') { echo $_SERVER['HTTP_HOST'];} else { echo $ts['host']; } echo ':'.$ts['voice'] .'" target="_blank" rel="noopener noreferrer">'.$lang['stix0046'].'</a>'; } else { echo $lang['stix0047']; } ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>  
		</div>
	</div>
<input type="hidden" id="sut" value="<?PHP echo $sql_res['server_uptime']; ?>">
<input type="hidden" id="tsn1" value="<?PHP echo $lang['stix0053']; ?>">
<input type="hidden" id="tsn2" value="<?PHP echo $lang['stix0054']; ?>">
<input type="hidden" id="tsn3" value="<?PHP echo $lang['stix0052']; ?>">
<input type="hidden" id="tsn4" value="<?PHP echo round(($sql_res['total_active_time'] / 86400)); ?>">
<input type="hidden" id="tsn5" value="<?PHP echo round(($sql_res['total_inactive_time'] / 86400)); ?>">
<input type="hidden" id="tsn6" value="<?PHP echo $sql_res['version_name_1']; ?>">
<input type="hidden" id="tsn7" value="<?PHP echo $sql_res['version_name_2']; ?>">
<input type="hidden" id="tsn8" value="<?PHP echo $sql_res['version_name_3']; ?>">
<input type="hidden" id="tsn9" value="<?PHP echo $sql_res['version_name_4']; ?>">
<input type="hidden" id="tsn10" value="<?PHP echo $sql_res['version_name_5']; ?>">
<input type="hidden" id="tsn11" value="<?PHP echo $sql_res['version_1']; ?>">
<input type="hidden" id="tsn12" value="<?PHP echo $sql_res['version_2']; ?>">
<input type="hidden" id="tsn13" value="<?PHP echo $sql_res['version_3']; ?>">
<input type="hidden" id="tsn14" value="<?PHP echo $sql_res['version_4']; ?>">
<input type="hidden" id="tsn15" value="<?PHP echo $sql_res['version_5']; ?>">
<input type="hidden" id="tsn16" value="<?PHP echo $sql_res['version_other']; ?>">

<?PHP
if (isset($nation[$sql_res['country_nation_name_1']])) {
	echo '<input type="hidden" id="tsn17" value="',$sql_res['country_nation_name_1'],'"><input type="hidden" id="tsn22" value="',$sql_res['country_nation_1'],'">';
} else {
	echo '<input type="hidden" id="tsn17" value="unkown"><input type="hidden" id="tsn22" value="0">';
}
if (isset($nation[$sql_res['country_nation_name_2']])) {
	echo '<input type="hidden" id="tsn18" value="',$sql_res['country_nation_name_2'],'"><input type="hidden" id="tsn23" value="',$sql_res['country_nation_2'],'">';
} else {
	echo '<input type="hidden" id="tsn18" value="unkown"><input type="hidden" id="tsn23" value="0">';
}
if (isset($nation[$sql_res['country_nation_name_3']])) {
	echo '<input type="hidden" id="tsn19" value="',$sql_res['country_nation_name_3'],'"><input type="hidden" id="tsn24" value="',$sql_res['country_nation_3'],'">';
} else {
	echo '<input type="hidden" id="tsn19" value="unkown"><input type="hidden" id="tsn24" value="0">';
}
if (isset($nation[$sql_res['country_nation_name_4']])) {
	echo '<input type="hidden" id="tsn20" value="',$sql_res['country_nation_name_4'],'"><input type="hidden" id="tsn25" value="',$sql_res['country_nation_4'],'">';
} else {
	echo '<input type="hidden" id="tsn20" value="unkown"><input type="hidden" id="tsn25" value="0">';
}
if (isset($nation[$sql_res['country_nation_name_5']])) {
	echo '<input type="hidden" id="tsn21" value="',$sql_res['country_nation_name_5'],'"><input type="hidden" id="tsn26" value="',$sql_res['country_nation_5'],'">';
} else {
	echo '<input type="hidden" id="tsn21" value="unkown"><input type="hidden" id="tsn26" value="0">';
}
?>
<input type="hidden" id="tsn27" value="<?PHP echo $sql_res['country_nation_other']; ?>">
<input type="hidden" id="tsn28" value="<?PHP echo $sql_res['platform_1']; ?>">
<input type="hidden" id="tsn29" value="<?PHP echo $sql_res['platform_2']; ?>">
<input type="hidden" id="tsn30" value="<?PHP echo $sql_res['platform_3']; ?>">
<input type="hidden" id="tsn31" value="<?PHP echo $sql_res['platform_4']; ?>">
<input type="hidden" id="tsn32" value="<?PHP echo $sql_res['platform_5']; ?>">
<input type="hidden" id="tsn33" value="<?PHP echo $sql_res['platform_other']; ?>">
</body>
</html>