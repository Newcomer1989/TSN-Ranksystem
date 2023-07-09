<?php
require_once '_preload.php';

try {
    ?>
			<div id="page-wrapper" class="stats_privacy_policy">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['privacy']; ?>
							</h1>
						</div>
					</div>
					<?php if ($cfg['stats_imprint_switch'] == 1) { ?>
						<div class="row">
							<div class="col-lg-12">
								<?php echo $cfg['stats_imprint_privacypolicy']; ?>
							</div>
						</div>
					<?php } else { ?>
						<div class="row">
							<div class="col-lg-12">
								<h5><strong><span class="text-danger"><?php echo $lang['module_disabled']; ?></span></strong></h5>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php require_once '_footer.php'; ?>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>