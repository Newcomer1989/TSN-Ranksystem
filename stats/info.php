<?PHP
session_start();
$starttime = microtime(true);

require_once('../other/config.php');
require_once('../other/session.php');

if(!isset($_SESSION['tsuid'])) {
	$hpclientip = ip2long($_SERVER['REMOTE_ADDR']);
	set_session_ts3($hpclientip, $ts['voice'], $mysqlcon, $dbname);
}
require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
			<div class="container-fluid">

				<!-- Page Heading -->
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
						<p>The <a href="http://ts-n.net/ranksystem.php" target="_blank">Ranksystem</a> was coded by <strong>Newcomer1989</strong> Copyright &copy; 2009-2016 <a href="http://ts-n.net/" target="_blank">TeamSpeak Sponsoring TS-N.NET</a>. All rights reserved.</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-warning"><?PHP echo $lang['stri0005']; ?></span></strong></h4>
						<p><?PHP echo $lang['stri0006']; ?></p>
						<p><?PHP echo $lang['stri0007']; ?></p>
						<p><?PHP echo $lang['stri0008']; ?></p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-danger"><?PHP echo $lang['stri0009']; ?></span></strong></h4>
						<p><?PHP echo $lang['stri0010']; ?></p>
						<p><a href="http://php.net/" target="_blank">PHP</a> - Copyright &copy; 2001-2016 the <a href="https://secure.php.net/credits.php" target="_blank">PHP Group</a></p><br>
						<p><?PHP echo $lang['stri0011']; ?></p>
						<p><a href="http://jquery.com/" target="_blank">jQuery v2.2.0</a> - Copyright &copy; 2016 The jQuery Foundation</p> 
						<p>jQuery Autocomplete plugin 1.1 - Copyright &copy; 2009 J&ouml;rn Zaefferer</p> 
						<p><a href="http://fontawesome.io" target="_blank">Font Awesome 4.2.0</a> - Copyright &copy; davegandy</p>
						<p><a href="http://flag-icon-css.lip.is/" target="_blank">flag-icon-css</a> - Copyright &copy; 2016 lipis</p>
						<p><a href="http://jquery.com/plugins/project/ajaxqueue" target="_blank">Ajax Queue Plugin</a> - Copyright &copy; 2013 Corey Frang</p> 
						<p><a href="http://planetteamspeak.com/" target="_blank">TeamSpeak 3 PHP Framework 1.1.23</a> - Copyright &copy; 2010 Planet TeamSpeak</p> 
						<p><a href="http://getbootstrap.com/" target="_blank">Bootstrap 3.3.6</a> - Copyright &copy; 2011-2016 Twitter, Inc.</p>
						<p><a href="http://morrisjs.github.io/morris.js/" target="_blank">morris.js</a> - Copyright &copy; 2013 Olly Smith</p>
						<p><a href="http://raphaeljs.com" target="_blank">Rapha&euml;l 2.1.4 - JavaScript Vector Library</a> - Copyright &copy; 2008-2012 Dmitry Baranovskiy</p>
						<p><a href="http://startbootstrap.com" target="_blank">SB Admin Bootstrap Admin Template</a> - Copyright &copy; 2013-2016 Blackrock Digital LLC.</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><span class="text-info"><?PHP echo $lang['stri0012']; ?></span></strong></h4>
						<p><?PHP echo $lang['stri0013']; ?></p>
						<p><?PHP echo $lang['stri0014']; ?></p>
						<p><?PHP echo $lang['stri0015']; ?></p>
						<br>
					</div>
				</div>
			</div>
			<!-- /.container-fluid -->
		</div>
		<!-- /#page-wrapper -->
	</div>
	<!-- /#wrapper -->
</body>
</html>