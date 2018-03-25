<?PHP
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

if(($sql_res = $mysqlcon->query("SELECT * FROM $dbname.stats_server; SET @a:=0")->fetch()) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
}

if(isset($_GET['usage'])) {
	if ($_GET["usage"] == 'week') {
		$usage = 'week';
	} elseif ($_GET["usage"] == 'month') {
		$usage = 'month';
	} elseif ($_GET["usage"] == 'year') {
		$usage = 'year';
	} else {
		$usage = 'day';
	}
} else {
	$usage = 'day';
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
										<div class="huge"><?PHP echo sprintf($lang['days'], round(($sql_res['total_online_time'] / 86400))); ?></div>
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
										<div class="huge"><?PHP echo sprintf($lang['days'], round(($sql_res['total_online_month'] / 86400))) ?></div>
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
										<div class="huge"><?PHP echo sprintf($lang['days'], round(($sql_res['total_online_week'] / 86400))) ?></div>
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
										<h3 class="panel-title"><i class="fa fa-bar-chart-o"></i>&nbsp;<?PHP echo $lang['stix0008']; ?>&nbsp;<i><?PHP if($usage == 'week') { echo $lang['stix0009']; } elseif ($usage == 'month') { echo $lang['stix0010']; } else { echo $lang['stix0011']; } ?></i></h3>
									</div>
									<div class="col-xs-3">
										<div class="btn-group dropup pull-right">
										  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<?PHP echo $lang['stix0012']; ?>&nbsp;&nbsp;<span class="caret"></span>
										  </button>
										  <ul class="dropdown-menu">
											<li><a href=<?PHP echo "\"?usage=day\">",$lang['stix0013']; ?></a></li>
											<li><a href=<?PHP echo "\"?usage=week\">",$lang['stix0014']; ?></a></li>
											<li><a href=<?PHP echo "\"?usage=month\">",$lang['stix0015']; ?></a></li>
										  </ul>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-body">
								<div id="server-usage-chart"></div>
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
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:&gt;:<?PHP echo time()-86400; ?>:">
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
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:&gt;:<?PHP echo time()-604800; ?>:">
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
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:&gt;:<?PHP echo time()-2592000; ?>:">
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
							<a href="list_rankup.php?sort=lastseen&order=desc&search=filter:lastseen:&gt;:<?PHP echo time()-7776000; ?>:">
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
										echo $sql_res['server_name'] .'<div class="pull-right"><img src="data:image;',mime_content_type("../tsicons/servericon.png"),';base64,'.base64_encode($img_content).'" alt="servericon"></div>';
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
										<td><?PHP if ($sql_res['server_weblist'] == 1) { echo '<a href="https://www.planetteamspeak.com/serverlist/result/server/ip/'; if($ts['host']=='localhost' || $ts['host']=='127.0.0.1') { echo $_SERVER['HTTP_HOST'];} else { echo $ts['host']; } echo ':'.$ts['voice'] .'" target="_blank">'.$lang['stix0046'].'</a>'; } else { echo $lang['stix0047']; } ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>  
			<!-- /.container-fluid -->

		</div>
		<!-- /#page-wrapper -->

	</div>
	<!-- /#wrapper -->
	<!-- Scripts -->
	<script type="text/javascript">
		var daysLabel = document.getElementById("days");
		var hoursLabel = document.getElementById("hours");
		var minutesLabel = document.getElementById("minutes");
		var secondsLabel = document.getElementById("seconds");
		var totalSeconds = <?PHP echo $sql_res['server_uptime'] ?>;
		setInterval(setTime, 1000);
		function setTime()
		{
			++totalSeconds;
			secondsLabel.innerHTML = pad(totalSeconds%60);
			minutesLabel.innerHTML = pad(parseInt(totalSeconds/60)%60);
			hoursLabel.innerHTML = pad(parseInt(totalSeconds/3600)%24)
			daysLabel.innerHTML = pad(parseInt(totalSeconds/86400))
		}
		function pad(val)
		{
			var valString = val + "";
			if(valString.length < 2)
			{
				return "0" + valString;
			}
			else
			{
				return valString;
			}
		}
	</script>
	<script>
		Morris.Donut({
		  element: 'time-gap-donut',
		  data: [
			{label: "<?PHP echo $lang['stix0053']?>", value: <?PHP echo round(($sql_res['total_active_time'] / 86400)); ?>},
			{label: "<?PHP echo $lang['stix0054']?>", value: <?PHP echo round(($sql_res['total_inactive_time'] / 86400)); ?>},
		  ]
		});
		Morris.Donut({
			element: 'client-version-donut',
			data: [
			   {label: "<?PHP echo $sql_res['version_name_1'] ?>", value: <?PHP echo $sql_res['version_1'] ?>},
			   {label: "<?PHP echo $sql_res['version_name_2'] ?>", value: <?PHP echo $sql_res['version_2'] ?>},
			   {label: "<?PHP echo $sql_res['version_name_3'] ?>", value: <?PHP echo $sql_res['version_3'] ?>},
			   {label: "<?PHP echo $sql_res['version_name_4'] ?>", value: <?PHP echo $sql_res['version_4'] ?>},
			   {label: "<?PHP echo $sql_res['version_name_5'] ?>", value: <?PHP echo $sql_res['version_5'] ?>},
			   {label: "<?PHP echo $lang['stix0052']?>", value: <?PHP echo $sql_res['version_other'] ?>},
			],
			colors: [
				'#5cb85c',
				'#73C773',
				'#8DD68D',
				'#AAE6AA',
				'#C9F5C9',
				'#E6FFE6'
		  ]
		});
		Morris.Donut({
			element: 'user-descent-donut', data: [
				{label: "<?PHP if (isset($nation[$sql_res['country_nation_name_1']])) { echo $nation[$sql_res['country_nation_name_1']]; } else { echo "unkown";} ?>", value: <?PHP if ( isset($sql_res['country_nation_1'])) { echo $sql_res['country_nation_1']; } else { echo "0";} ?>},
				{label: "<?PHP if (isset($nation[$sql_res['country_nation_name_2']])) { echo $nation[$sql_res['country_nation_name_2']]; } else { echo "unkown";} ?>", value: <?PHP if ( isset($sql_res['country_nation_2'])) { echo $sql_res['country_nation_2']; } else { echo "0";} ?>},
				{label: "<?PHP if (isset($nation[$sql_res['country_nation_name_3']])) { echo $nation[$sql_res['country_nation_name_3']]; } else { echo "unkown";} ?>", value: <?PHP if ( isset($sql_res['country_nation_3'])) { echo $sql_res['country_nation_3']; } else { echo "0";} ?>},
				{label: "<?PHP if (isset($nation[$sql_res['country_nation_name_4']])) { echo $nation[$sql_res['country_nation_name_4']]; } else { echo "unkown";} ?>", value: <?PHP if ( isset($sql_res['country_nation_4'])) { echo $sql_res['country_nation_4']; } else { echo "0";} ?>},
				{label: "<?PHP if (isset($nation[$sql_res['country_nation_name_5']])) { echo $nation[$sql_res['country_nation_name_5']]; } else { echo "unkown";} ?>", value: <?PHP if ( isset($sql_res['country_nation_5'])) { echo $sql_res['country_nation_5']; } else { echo "0";} ?>},
				{label: "<?PHP echo $lang['stix0052']?>", value: <?PHP echo $sql_res['country_nation_other'] ?>}
			],
			colors: [
				'#f0ad4e',
				'#ffc675',
				'#fecf8d',
				'#ffdfb1',
				'#fce8cb',
				'#fdf3e5'
			]
		});
		Morris.Donut({
			element: 'user-platform-donut',
			data: [
				{label: "Windows", value: <?PHP echo $sql_res['platform_1'] ?>},
				{label: "Linux", value: <?PHP echo $sql_res['platform_3'] ?>},
				{label: "Android", value: <?PHP echo $sql_res['platform_4'] ?>},
				{label: "iOS", value: <?PHP echo $sql_res['platform_2'] ?>},
				{label: "OS X", value: <?PHP echo $sql_res['platform_5'] ?>},
				{label: "<?PHP echo $lang['stix0052']?>", value: <?PHP echo $sql_res['platform_other'] ?>},
			],
			colors: [
				'#d9534f',
				'#FF4040',
				'#FF5050',
				'#FF6060',
				'#FF7070',
				'#FF8080'
		  ]
		});
		Morris.Area({
		  element: 'server-usage-chart',
		  data: [
			<?PHP
				$chart_data = '';
				if($usage == 'week') { 
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients, u1.channel FROM (SELECT @a:=@a+1,mod(@a,2) AS test,timestamp,clients,channel FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' ORDER BY u2.timestamp DESC LIMIT 336")->fetchAll(PDO::FETCH_ASSOC);
				} elseif ($usage == 'month') {
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients, u1.channel FROM (SELECT @a:=@a+1,mod(@a,4) AS test,timestamp,clients,channel FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' ORDER BY u2.timestamp DESC LIMIT 720")->fetchAll(PDO::FETCH_ASSOC);
				} elseif ($usage == 'year') {
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients, u1.channel FROM (SELECT @a:=@a+1,mod(@a,64) AS test,timestamp,clients,channel FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' ORDER BY u2.timestamp DESC LIMIT 548")->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$server_usage = $mysqlcon->query("SELECT timestamp, clients, channel FROM $dbname.server_usage ORDER BY timestamp DESC LIMIT 96")->fetchAll(PDO::FETCH_ASSOC);
				}
				foreach($server_usage as $chart_value) {
					$chart_data .= '{ y: \''.date('Y-m-d H:i',$chart_value['timestamp']).'\', a: '.$chart_value['clients'].', b: '.($chart_value['channel'] - $chart_value['clients']).', c: '. $chart_value['channel'].' }, ';
				}
				echo substr($chart_data, 0, -2);
			?>
		  ],
		  xkey: 'y',
		  ykeys: ['a', 'b'],
		  hideHover: 'auto',
		  hoverCallback:  
				function (index, options, content, row) {
					return "<b>" + row.y + "</b><br><div class='morris-hover-point text-primary'>Clients: " + row.a + "</div><div class='morris-hover-point text-muted'>Channel: " + (row.b + row.a) + "</div>";
				} ,
		  labels: ['Clients', 'Channel']
		});
	</script>
</body>
</html>