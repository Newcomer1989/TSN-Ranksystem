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
							<?PHP echo $lang['stri0001']; ?>
						</h1>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-info"><?PHP echo $lang['stri0002']; ?></span></strong></h4>
						<p><?PHP echo $lang['stri0003']; ?></p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-success"><?PHP echo $lang['stri0004']; ?></span></strong></h4>
						<p>The <a href="//ts-ranksystem.com" target="_blank" rel="noopener noreferrer">Ranksystem</a> was coded by <strong>Newcomer1989</strong> Copyright &copy; 2009-2019 powered by <a href="//ts-n.net/" target="_blank" rel="noopener noreferrer">TS-N.NET</a></p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-warning"><?PHP echo $lang['stri0005']; ?></span></strong></h4>
						<p><?PHP echo $lang['stri0006']; ?></p>
						<p><?PHP echo $lang['stri0007']; ?></p>
						<p><?PHP echo $lang['stri0023']; ?></p>
						<p><?PHP echo sprintf($lang['stri0008'], 'https://ts-ranksystem.com/#download'); ?></p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-danger"><?PHP echo $lang['stri0009'];?></span></strong></h4>
						<p><?PHP echo $lang['stri0010']; ?></p>
						<p><a href="//php.net/" target="_blank" rel="noopener noreferrer">PHP</a> - Copyright &copy; 2001-2019 the <a href="//secure.php.net/credits.php" target="_blank" rel="noopener noreferrer">PHP Group</a></p><br>
						<p><?PHP echo $lang['stri0011']; ?></p>
						<p><a href="//jquery.com/" target="_blank" rel="noopener noreferrer">jQuery v3.4.0</a> - Copyright &copy; 2019 The jQuery Foundation</p> 
						<p><a href="//fontawesome.com" target="_blank" rel="noopener noreferrer">Font Awesome 5.7.2</a> - Copyright &copy; davegandy</p>
						<p><a href="//flag-icon-css.lip.is/" target="_blank" rel="noopener noreferrer">flag-icon-css 2.8.0</a> - Copyright &copy; 2016 lipis</p>
						<p><a href="//planetteamspeak.com/" target="_blank" rel="noopener noreferrer">TeamSpeak 3 PHP Framework 1.1.33</a> - Copyright &copy; 2010-2018 Planet TeamSpeak</p> 
						<p><a href="//getbootstrap.com/" target="_blank" rel="noopener noreferrer">Bootstrap 3.3.7</a> - Copyright &copy; 2011-2019 Twitter, Inc.</p>
						<p><a href="//morrisjs.github.io/morris.js/" target="_blank" rel="noopener noreferrer">morris.js 0.5.1</a> - Copyright &copy; 2013 Olly Smith</p>
						<p><a href="//raphaeljs.com" target="_blank" rel="noopener noreferrer">Rapha&euml;l 2.2.1 - JavaScript Vector Library</a> - Copyright &copy; 2008-2012 Dmitry Baranovskiy</p>
						<p><a href="//startbootstrap.com" target="_blank" rel="noopener noreferrer">SB Admin Bootstrap Admin Template</a> - Copyright &copy; 2013-2016 Blackrock Digital LLC.</p>
						<p><a href="//github.com/Bttstrp/bootstrap-switch" target="_blank" rel="noopener noreferrer">Bootstrap Switch 3.3.2</a> - Copyright &copy; 2013-2015 Mattia Larentis</p>
						<p><a href="//www.virtuosoft.eu/code/bootstrap-touchspin" target="_blank" rel="noopener noreferrer">Bootstrap TouchSpin 3.1.2</a> - Copyright &copy; 2013-2016 István Ujj-Mészáros</p>
						<p><a href="//developer.snapappointments.com/bootstrap-select" target="_blank" rel="noopener noreferrer">bootstrap-select v1.13.0-beta</a> - Copyright &copy; 2012-2018 SnapAppointments, LLC</p>
						<p><a href="//wenzhixin.net.cn/" target="_blank" rel="noopener noreferrer">Bootstrap Show Password 1.0.3</a> - Copyright &copy; 2014 zhixin wen</p>
						<p><a href="//github.com/1000hz/bootstrap-validator" target="_blank" rel="noopener noreferrer">Bootstrap Validator</a> - Copyright &copy; 2016 Cina Saffary</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-info"><?PHP echo $lang['stri0012']; ?></span></strong></h4>
						<p><?PHP echo $lang['stri0021']; ?></p>
						<p><?PHP echo $lang['stri0022']; ?></p>
						<p><?PHP echo $lang['stri0013']; ?></p>
						<p><?PHP echo $lang['stri0014']; ?></p>
						<p><?PHP echo $lang['stri0015']; ?></p>
						<p><?PHP echo $lang['stri0016']; ?></p>
						<p><?PHP echo $lang['stri0017']; ?></p>
						<p><?PHP echo $lang['stri0018']; ?></p>
						<p><?PHP echo $lang['stri0019']; ?></p>
						<p><?PHP echo $lang['stri0020']; ?></p>
						<p><?PHP echo $lang['stri0024']; ?></p>
						<p><?PHP echo $lang['stri0025']; ?></p>
						<p><?PHP echo $lang['stri0026']; ?></p>
						<br>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>