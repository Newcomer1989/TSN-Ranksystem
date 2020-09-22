<?PHP
require_once('_preload.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['imprint']; ?>
						</h1>
					</div>
				</div>
				<?PHP
				if (isset($cfg['stats_imprint_switch']) && $cfg['stats_imprint_switch'] == 1) {
					echo '
					<div class="row">
						<div class="col-lg-12">
							<h5><strong><span class="text-info">' . $lang['wiimpaddr'] . '</span></strong></h5>
							<p>' . $cfg['stats_imprint_address'] . '</p>
							<br>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h5><strong><span class="text-warning">' . $lang['wiimpemail'] . '</span></strong></h5>
							<p>' . $cfg['stats_imprint_email'] . '</p>
							<br>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h5><strong><span class="text-warning">' . $lang['wiimpphone'] . '</span></strong></h5>
							<p>' . $cfg['stats_imprint_phone'] . '</p>
							<br>
						</div>
					</div>';
					if ($cfg['stats_imprint_notes'] != NULL) {
						echo '
						<div class="row">
							<div class="col-lg-12">
								<h5><strong><span class="text-danger">' . $lang['wiimpnotes'] . '</span></strong></h5>
								<p>' . $cfg['stats_imprint_notes'] . '</p>
							</div>
						</div>';
					}
				} else {
					echo '
					<div class="row">
						<div class="col-lg-12">
							<h5><strong><span class="text-danger">' . $lang['module_disabled'] . '</span></strong></h5>
						</div>
					</div>';
				}
				?>
			</div>
		</div>
	</div>
<?PHP require_once('_footer.php'); ?>
</body>
</html>
