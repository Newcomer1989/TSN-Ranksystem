<?PHP
require_once('_preload.php');

try {
	if(is_dir($GLOBALS['langpath'])) {
		foreach(scandir($GLOBALS['langpath']) as $file) {
			if ('.' === $file || '..' === $file || is_dir($file)) continue;
			$sep_lang = preg_split("/[._]/", $file);
			if(isset($sep_lang[0]) && $sep_lang[0] == 'nations' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[2]) && strtolower($sep_lang[2]) == 'php') {
				if(strtolower($cfg['default_language']) == strtolower($sep_lang[1])) {
					require_once($GLOBALS['langpath'].'nations_'.$sep_lang[1].'.php');
					$required_nations = 1;
					break;
				}
			}
		}
		if(!isset($required_nations)) {
			require_once($GLOBALS['langpath'].'nations_en.php');
		}
	}

	$sql_res = $mysqlcon->query("SELECT * FROM `$dbname`.`stats_nations` ORDER BY `count` DESC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	?>
			<div id="page-wrapper" class="stats_nations">
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
							<div class="table-responsive" id="nations">
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
		echo '<tr><td>',$count,'</td><td><span class="';
		if(strtoupper($country) == 'XX' || $country == NULL) {
			echo 'fas fa-question-circle';
		} else {
			echo 'flag-icon flag-icon-',strtolower($country);
		}
		echo '"></span><span class="item-margin">',$country,'</span></td><td><a href="list_rankup.php?sort=rank&order=desc&search=filter:country:',$country,':">';
		if(isset($nation[$country])) echo $nation[$country];
		echo '</td><td>',$value['count'],'</td><td>',number_format(round(($value['count'] * 100 / $sum_of_all), 1), 1),' %</td></tr>';
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
		<?PHP require_once('_footer.php'); ?>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>