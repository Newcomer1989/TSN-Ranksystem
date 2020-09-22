<?PHP
require_once('_preload.php');

$sql_res = $mysqlcon->query("SELECT * FROM `$dbname`.`stats_platforms` ORDER BY `count` DESC")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['stna0006'],' - ',$lang['stna0002']; ?>
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
									<th><?PHP echo $lang['stna0006']; ?></th>
									<th><?PHP echo $lang['stix0060'],' ',$lang['stna0004']; ?></th>
									<th><?PHP echo $lang['stna0007']; ?></th>
								</tr>
<?PHP
$count = 0;
$sum_of_all = 0;
foreach ($sql_res as $country => $value) {
	$sum_of_all = $sum_of_all + $value['count'];
}
foreach ($sql_res as $platform => $value) {
	$count++;
	echo '
	<tr>
		<td>',$count,'</td>
		<td>',$platform,'</td>
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