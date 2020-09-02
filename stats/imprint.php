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
$sql_res = $mysqlcon->query("SELECT `msg` FROM `$dbname`.`imprint` WHERE `text`=`adress`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
$stats_user = $mysqlcon->query("SELECT `adress`,`email`,`phone`,`active_month`,`last_calculated` FROM `$dbname`.`stats_user` WHERE `uuid`='".$_SESSION[$rspathhex.'tsuid']."'")->fetch();
require_once('nav.php');
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
				if ($cfg['imprint_enabled'] == 1) {
    				  echo '<div class="row">
					  			<div class="col-lg-12">
								  <h5><strong><span class="text-info">' . $lang['imprint_address'] . '</span></strong></h5>
						  		  <p>' . $cfg['imprint_address'] . '</p>
								  <br>
								</div>
				  			</div>
				  			<div class="row">
					  			<div class="col-lg-12">
								  <h5><strong><span class="text-warning">' . $lang['imprint_email'] . '</span></strong></h5>
								  <p>' . $cfg['imprint_email'] . '</p>
								  <br>
			  				    </div>
				  			</div>
						    <div class="row">
					  			<div class="col-lg-12">
								  <h5><strong><span class="text-warning">' . $lang['imprint_phone'] . '</span></strong></h5>
								  <p>' . $cfg['imprint_phone'] . '</p>
								  <br>
							    </div>
							</div>'
							if ($cfg['imprint_notes'] != NULL) {
							  echo '<div class="row">
					  				  <div class="col-lg-12">
						  		  		<h5><strong><span class="text-danger">' . $lang['imprint_notes'] . '</span></strong></h5>
						  		  		<p>' . $cfg['imprint_notes'] . '</p>
					  				  </div>
							  		</div>';
							};
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
