<?PHP
session_start();
require_once('../other/config.php');
require_once('../other/session.php');

if($language == "de") {
	require_once('../languages/nations_de.php');
} elseif($language == "en") {
	require_once('../languages/nations_en.php');
}

if(!isset($_SESSION['tsuid'])) {
	$hpclientip = ip2long($_SERVER['REMOTE_ADDR']);
	set_session_ts3($hpclientip, $ts['voice'], $mysqlcon, $dbname);
}

function human_readable_size($bytes) {
	$size = array(' B',' KiB',' MiB',' GiB',' TiB',' PiB',' EiB',' ZiB',' YiB');
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.2f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

$sql = $mysqlcon->query("SELECT * FROM $dbname.stats_server");
$sql_res = $sql->fetchAll();

$server_usage_sql = $mysqlcon->query("SELECT * FROM $dbname.server_usage ORDER BY(timestamp) DESC LIMIT 0, 47");
$server_usage_sql_res = $server_usage_sql->fetchAll();

if(isset($_GET['usage'])) {
	if ($_GET["usage"] == 'week') {
		$usage = 'week';
	} elseif ($_GET["usage"] == 'month') {
		$usage = 'month';
	} else {
		$usage = 'day';
	}
} else {
	$usage = 'day';
}
require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
			<div class="container-fluid">

				<!-- Page Heading -->
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
										<div class="huge"><?PHP echo $sql_res[0]['total_user'] ?></div>
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
										<div class="huge"><?PHP echo round(($sql_res[0]['total_online_time'] / 86400)). ' <small>'.$lang['days'].'</small>';?></div>
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
										<div class="huge"><?PHP echo round(($sql_res[0]['total_online_month'] / 86400)). ' <small>'.$lang['days'].'</small>';?></div>
										<div><?PHP echo ($sql_res[0]['total_online_month'] == 0 ? $lang['stix0048'] : $lang['stix0049']) ?></div>
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
										<div class="huge"><?PHP echo round(($sql_res[0]['total_online_week'] / 86400)). ' <small>'.$lang['days'].'</small>';?></div>
										<div><?PHP echo ($sql_res[0]['total_online_week'] == 0 ? $lang['stix0048'] : $lang['stix0050']) ?></div>
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
				<!-- /.row -->
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
				<!-- /.row -->

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
						</div>
					</div>
				</div>
				<!-- /.row -->
				<div class="row">
					<div class="col-lg-6">
						<h2><?PHP echo $lang['stix0020']; ?></h2>
						<div class="table-responsive">
							<table class="table table-bordered table-hover">
								<tbody>
									<tr>
										<td><?PHP echo $lang['stix0023']; ?></td>
										<td><?PHP echo ($sql_res[0]['server_status'] == 1 || $sql_res[0]['server_status'] == 3) ? '<span class="text-success">'.$lang['stix0024'].'</span>' : '<span class="text-danger">'.$lang['stix0025'].'</span>'; ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0026']; ?></td>
										<td><?PHP echo ($sql_res[0]['server_status'] == 0) ? '0' :  $sql_res[0]['server_used_slots'] , ' / ' ,($sql_res[0]['server_used_slots'] + $sql_res[0]['server_free_slots']); ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0027']; ?></td>
										<td><?PHP echo $sql_res[0]['server_channel_amount']; ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0028']; ?></td>
										<td><?PHP echo ($sql_res[0]['server_status'] == 0) ? '-' : $sql_res[0]['server_ping'] . ' ms'; ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0029']; ?></td>
										<td><?PHP echo human_readable_size($sql_res[0]['server_bytes_down']); ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0030']; ?></td>
										<td><?PHP echo human_readable_size($sql_res[0]['server_bytes_up']); ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0031']; ?></td>
										<td><?PHP echo ($sql_res[0]['server_status'] == 0) ? '-&nbsp;&nbsp;&nbsp;(<i>'.$lang['stix0032'].'&nbsp;'.(new DateTime("@0"))->diff(new DateTime("@".$sql_res[0]['server_uptime']))->format($timeformat).')</i>' : $lang['stix0033']; ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0034']; ?></td>
										<td><?PHP echo ($sql_res[0]['server_status'] == 0) ? '-' : $sql_res[0]['server_packet_loss'] * 100 .' %'; ?></td>
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
										<td><?PHP echo (file_exists("../icons/servericon.png") ? $sql_res[0]['server_name'] .'<div class="pull-right"><img src="../icons/servericon.png" alt="servericon"></div>' : $sql_res[0]['server_name']) ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0037']; ?></td>
										<td><a href="ts3server://<?PHP echo ($ts['host']=='localhost' ? $_SERVER['HTTP_HOST'] : $ts['host']).':'.$ts['voice']; ?>"><?PHP echo ($ts['host']=='localhost' ? $_SERVER['HTTP_HOST'] : $ts['host']).':'.$ts['voice']; ?></a></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0038']; ?></td>
										<td><?PHP echo ($sql_res[0]['server_pass'] == '0') ? $lang['stix0039'] : $lang['stix0040'] ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0041']; ?></td>
										<td><?PHP echo $sql_res[0]['server_id'] ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0042']; ?></td>
										<td><?PHP echo $sql_res[0]['server_platform'] ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0043']; ?></td>
										<td><?PHP echo substr($sql_res[0]['server_version'], 0, strpos($sql_res[0]['server_version'], ' ')); ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0044']; ?></td>
										<td><?PHP echo date('d/m/Y', $sql_res[0]['server_creation_date']) ?></td>
									</tr>
									<tr>
										<td><?PHP echo $lang['stix0045']; ?></td>
										<td><?PHP echo ($sql_res[0]['server_weblist'] == 1) ? '<a href="https://www.planetteamspeak.com/serverlist/result/server/ip/' .($ts['host']=='localhost' ? $_SERVER['HTTP_HOST'] : $ts['host']).':'.$ts['voice'] .'" target="_blank">'.$lang['stix0046'].'</a>' : $lang['stix0047'] ?></td>
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
		var totalSeconds = <?PHP echo $sql_res[0]['server_uptime'] ?>;
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
			{label: "Active Time (in Days)", value: <?PHP echo round(($sql_res[0]['total_active_time'] / 86400)); ?>},
			{label: "Inactive Time (in Days)", value: <?PHP echo round(($sql_res[0]['total_inactive_time'] / 86400)); ?>},
		  ]
		});
		Morris.Donut({
			element: 'client-version-donut',
			data: [
			   {label: "<?PHP echo $sql_res[0]['version_name_1'] ?>", value: <?PHP echo $sql_res[0]['version_1'] ?>},
			   {label: "<?PHP echo $sql_res[0]['version_name_2'] ?>", value: <?PHP echo $sql_res[0]['version_2'] ?>},
			   {label: "<?PHP echo $sql_res[0]['version_name_3'] ?>", value: <?PHP echo $sql_res[0]['version_3'] ?>},
			   {label: "<?PHP echo $sql_res[0]['version_name_4'] ?>", value: <?PHP echo $sql_res[0]['version_4'] ?>},
			   {label: "<?PHP echo $sql_res[0]['version_name_5'] ?>", value: <?PHP echo $sql_res[0]['version_5'] ?>},
			   {label: "Others", value: <?PHP echo $sql_res[0]['version_other'] ?>},
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
				{label: "<?PHP echo isset($nation[$sql_res[0]['country_nation_name_1']]) ? $nation[$sql_res[0]['country_nation_name_1']] : "unkown"; ?>", value: <?PHP echo isset($sql_res[0]['country_nation_1']) ? $sql_res[0]['country_nation_1'] : "0" ?>},
				{label: "<?PHP echo isset($nation[$sql_res[0]['country_nation_name_2']]) ? $nation[$sql_res[0]['country_nation_name_2']] : "unkown"; ?>", value: <?PHP echo isset($sql_res[0]['country_nation_2']) ? $sql_res[0]['country_nation_2'] : "0"; ?>},
				{label: "<?PHP echo isset($nation[$sql_res[0]['country_nation_name_3']]) ? $nation[$sql_res[0]['country_nation_name_3']] : "unkown"; ?>", value: <?PHP echo isset($sql_res[0]['country_nation_3']) ? $sql_res[0]['country_nation_3'] : "0"; ?>},
				{label: "<?PHP echo isset($nation[$sql_res[0]['country_nation_name_4']]) ? $nation[$sql_res[0]['country_nation_name_4']] : "unkown"; ?>", value: <?PHP echo isset($sql_res[0]['country_nation_4']) ? $sql_res[0]['country_nation_4'] : "0"; ?>},
				{label: "<?PHP echo isset($nation[$sql_res[0]['country_nation_name_5']]) ? $nation[$sql_res[0]['country_nation_name_5']] : "unkown"; ?>", value: <?PHP echo isset($sql_res[0]['country_nation_5']) ? $sql_res[0]['country_nation_5'] : "0"; ?>},
				{label: "Others", value: <?PHP echo $sql_res[0]['country_nation_other'] ?>}
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
				{label: "Windows", value: <?PHP echo $sql_res[0]['platform_1'] ?>},
				{label: "Linux", value: <?PHP echo $sql_res[0]['platform_3'] ?>},
				{label: "Android", value: <?PHP echo $sql_res[0]['platform_4'] ?>},
				{label: "iOS", value: <?PHP echo $sql_res[0]['platform_2'] ?>},
				{label: "OS X", value: <?PHP echo $sql_res[0]['platform_5'] ?>},
				{label: "Others", value: <?PHP echo $sql_res[0]['platform_other'] ?>},
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
				$trash_string = $mysqlcon->query("SET @a:=0");
				if($usage == 'week') { 
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients, u1.channel FROM (SELECT @a:=@a+1,mod(@a,4) AS test,timestamp,clients,channel FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' ORDER BY u2.timestamp DESC LIMIT 672");
				} elseif ($usage == 'month') {
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients, u1.channel FROM (SELECT @a:=@a+1,mod(@a,16) AS test,timestamp,clients,channel FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' ORDER BY u2.timestamp DESC LIMIT 2880");
				} else {
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients, u1.channel FROM (SELECT timestamp,clients,channel FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp ORDER BY u2.timestamp DESC LIMIT 96");
				}
				$server_usage = $server_usage->fetchAll(PDO::FETCH_ASSOC);
				foreach($server_usage as $chart_value) {
					$chart_time = date('Y-m-d H:i:s',$chart_value['timestamp']);
					$channel = $chart_value['channel'] - $chart_value['clients'];
					$chart_data = $chart_data . '{ y: \''.$chart_time.'\', a: '.$chart_value['clients'].', b: '.$channel.', c: '. $chart_value['channel'].' }, ';
				}
				$chart_data = substr($chart_data, 0, -2);
				echo $chart_data;
			?>
		  ],
		  xkey: 'y',
		  ykeys: ['a', 'b'],
		  hideHover: 'auto',
		  hoverCallback:  
				function (index, options, content, row) {
					return "<b>" + row.y + "</b><br><div class='morris-hover-point' style='color:#2677B5'>Clients: " + row.a + "</div><div class='morris-hover-point' style='color:#868F96'>Channel: " + (row.b + row.a) + "</div>";
				} ,
		  labels: ['Clients', 'Channel']
		});
	</script>
</body>
</html>