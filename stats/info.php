<?PHP
session_start();
$starttime = microtime(true);

require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

if(!isset($_SESSION['tsuid'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}
require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							Despre Tracker
						</h1>
					</div>
				</div>
				<div class="row">
					
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-success">Cine s-a muncit pentru tracker?</span></strong></h4>
						<p>FragCS <a href="http://fragcs.ro" target="_blank">Traker</a> scris din plictiseaza de <strong>BIT</strong> Copyright &copy; 2017 v1.0 BETA </p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-warning">Cand a fost lansat?</span></strong></h4>
						<p>Versiunea BETA 18.02.2016</p>
						
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-danger">Cum si in ce a fost codat Traker-ul?</span></strong></h4>
						<p>A fost scris in</p>
						<p><a href="//php.net/" target="_blank">PHP</a> - Copyright &copy; 2001-2016 the <a href="//secure.php.net/credits.php" target="_blank">PHP Group</a></p><br>
						<p>Si am mai folosit</p>
						<p><a href="//jquery.com/" target="_blank">jQuery v3.1.1</a> - Copyright &copy; 2016 The jQuery Foundation</p> 
						<p><a href="//fontawesome.io" target="_blank">Font Awesome 4.7.0</a> - Copyright &copy; davegandy</p>
						<p><a href="//flag-icon-css.lip.is/" target="_blank">flag-icon-css 2.8.0</a> - Copyright &copy; 2016 lipis</p>
						<p><a href="//planetteamspeak.com/" target="_blank">TeamSpeak 3 PHP Framework 1.1.24</a> - Copyright &copy; 2010 Planet TeamSpeak</p> 
						<p><a href="//getbootstrap.com/" target="_blank">Bootstrap 3.3.7</a> - Copyright &copy; 2011-2016 Twitter, Inc.</p>
						<p><a href="//morrisjs.github.io/morris.js/" target="_blank">morris.js 0.5.0</a> - Copyright &copy; 2013 Olly Smith</p>
						<p><a href="//raphaeljs.com" target="_blank">Rapha&euml;l 2.2.1 - JavaScript Vector Library</a> - Copyright &copy; 2008-2012 Dmitry Baranovskiy</p>
						<p><a href="//startbootstrap.com" target="_blank">SB Admin Bootstrap Admin Template</a> - Copyright &copy; 2013-2016 Blackrock Digital LLC.</p>
						<p><a href="//www.bootstrap-switch.org" target="_blank">Bootstrap Switch 3.3.2</a> - Copyright &copy; 2013-2015 Mattia Larentis</p>
						<p><a href="//www.virtuosoft.eu/code/bootstrap-touchspin" target="_blank">Bootstrap TouchSpin 3.1.2</a> - Copyright &copy; 2013-2016 István Ujj-Mészáros</p>
						<p><a href="//silviomoreto.github.io/bootstrap-select" target="_blank">bootstrap-select 1.11.2</a> - Copyright &copy; 2013-2015 Silvio Moreto a.o.</p>
						<p><a href="//wenzhixin.net.cn/" target="_blank">Bootstrap Show Password 1.0.3</a> - Copyright &copy; 2014 zhixin wen</p>
						<br>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</body>
</html>