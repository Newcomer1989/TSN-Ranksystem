<?PHP
session_start();
require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if($language == "ar") {
	require_once('../languages/nations_en.php');
} elseif($language == "de") {
	require_once('../languages/nations_de.php');
} elseif($language == "en") {
	require_once('../languages/nations_en.php');
} elseif($language == "it") {
	require_once('../languages/nations_it.php');
} elseif($language == "ro") {
	require_once('../languages/nations_en.php');
} elseif($language == "ru") {
	require_once('../languages/nations_ru.php');
}

if(!isset($_SESSION['tsuid'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}


$sql = $mysqlcon->query("SELECT * FROM $dbname.stats_nations ORDER BY count DESC");
$sql_res = $sql->fetchAll();

require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['stna0001'],' - ',$lang['stna0002']; ?>
						</h1>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="table-responsive">
							<table class="table table-bordered table-hover">
								<tbody>
								<tr>
									<th>#</th>
									<th><?PHP echo $lang['stna0003']; ?></th>
									<th><?PHP echo $lang['stna0001']; ?></th>
									<th><?PHP echo $lang['stix0060'],' ',$lang['stna0004']; ?></th>
								</tr>
<?PHP
$count = 0;
foreach ($sql_res as $line) {
	$count++;
	echo '
	<tr>
		<td>',$count,'</td>
		<td><span class="flag-icon flag-icon-',strtolower($line['nation']),'"></span>&nbsp;&nbsp;',$line['nation'],'</td>
		<td><a href="list_rankup.php?sort=rank&order=desc&search=filter:country:',$line['nation'],':">',$nation[$line['nation']],'</td>
		<td>',$line['count'],'</td>
	</tr>';
}
?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>  
		</div>
	</div>
</body>
</html>