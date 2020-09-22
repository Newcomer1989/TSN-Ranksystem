<?PHP
require_once('_preload.php');

$sql_res = $mysqlcon->query("SELECT * FROM `$dbname`.`stats_versions` ORDER BY `count` DESC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['stna0005'],' - ',$lang['stna0002']; ?>
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
									<th><?PHP echo $lang['stna0005']; ?></th>
									<th><?PHP echo $lang['stix0060'],' ',$lang['stna0004']; ?></th>
									<th><?PHP echo $lang['stna0007']; ?></th>
								</tr>
<?PHP
$count = 0;
$sum_of_all = 0;
foreach ($sql_res as $country => $value) {
	$sum_of_all = $sum_of_all + $value['count'];
}
foreach ($sql_res as $version => $value) {
	$count++;
	echo '
	<tr>
		<td>',$count,'</td>
		<td>',$version,'</td>
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
	<?PHP require_once('_footer.php'); ?>
</body>
</html>