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
$starttime = microtime(true);

require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);

if(!isset($_SESSION[$rspathhex.'tsuid'])) {
	set_session_ts3($mysqlcon,$cfg,$lang,$dbname);
}
require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?PHP echo $lang['privacy-policy']; ?>
						</h1>
					</div>
                </div>
				<?PHP
				if ($cfg['imprint_enabled'] == 1) {
    				  echo '<div class="row">
					  			<div class="col-lg-12">
								  ' . $cfg['imprint_privacy-policy'] .'
								</div>
				  			</div>';
				  } else {
					  echo '<div class="row">
								<div class="col-lg-12">
								  <h5><strong><span class="text-danger">' . $lang['module_disabled'] . '</span></strong></h5>
				  				</div>
							</div>';
				  }
				?>
			</div>
		</div>
	</div>
	<?PHP require_once('footer.php'); ?>
</body>
</html>