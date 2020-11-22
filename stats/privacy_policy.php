<?PHP
require_once('_preload.php');

try {
	?>
			<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?PHP echo $lang['privacy']; ?>
							</h1>
						</div>
					</div>
					<?PHP if ($cfg['stats_imprint_switch'] == 1) { ?>
						<div class="row">
							<div class="col-lg-12">
								<?PHP echo $cfg['stats_imprint_privacypolicy']; ?>
							</div>
						</div>
					<?PHP } else { ?>
						<div class="row">
							<div class="col-lg-12">
								<h5><strong><span class="text-danger"><?PHP echo $lang['module_disabled']; ?></span></strong></h5>
							</div>
						</div>
					<?PHP } ?>
				</div>
			</div>
		</div>
		<?PHP require_once('_footer.php'); ?>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>