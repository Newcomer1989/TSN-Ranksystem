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

$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);

if(is_dir(substr(__DIR__,0,-5).'languages/')) {
	foreach(scandir(substr(__DIR__,0,-5).'languages/') as $file) {
		if ('.' === $file || '..' === $file || is_dir($file)) continue;
		$sep_lang = preg_split("/[._]/", $file);
		if(isset($sep_lang[0]) && $sep_lang[0] == 'nations' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[2]) && strtolower($sep_lang[2]) == 'php') {
			if(strtolower($cfg['default_language']) == strtolower($sep_lang[1])) {
				require_once('../languages/nations_'.$sep_lang[1].'.php');
				$required_nations = 1;
				break;
			}
		}
	}
	if(!isset($required_nations)) {
		require_once('../languages/nations_en.php');
	}
}

if(!isset($_SESSION[$rspathhex.'tsuid'])) {
	set_session_ts3($mysqlcon,$cfg,$lang,$dbname);
}

$sql_res = $mysqlcon->query("SELECT * FROM `$dbname`.`stats_nations` ORDER BY `count` DESC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
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
									<th><?PHP echo $lang['stna0007']; ?></th>
								</tr>
<?PHP
$count = 0;
$sum_of_all = 0;
foreach ($sql_res as $country => $value) {
	$sum_of_all = $sum_of_all + $value['count'];
}
foreach ($sql_res as $country => $value) {
	$count++;
	echo '
	<tr>
		<td>',$count,'</td>
		<td><span class="flag-icon flag-icon-',strtolower($country),'"></span>&nbsp;&nbsp;',$country,'</td>
		<td><a href="list_rankup.php?sort=rank&order=desc&search=filter:country:',$country,':">',$nation[$country],'</td>
		<td>',$value['count'],'</td>
		<td>',number_format(round(($value['count'] * 100 / $sum_of_all), 1), 1),' %</td>
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
	<?PHP require_once('footer.php'); ?>
</body>
</html>