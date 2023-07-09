<?php
require_once '_preload.php';

try {
    ?>
			<div id="page-wrapper" class="stats_info">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['stri0001']; ?>
							</h1>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2><strong><span class="text-info"><?php echo $lang['stri0002']; ?></span></strong></h2>
							<p><?php echo $lang['stri0003']; ?></p>
							<br>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2><strong><span class="text-success"><?php echo $lang['stri0004']; ?></span></strong></h2>
							<p>The <a href="//ts-ranksystem.com" target="_blank" rel="noopener noreferrer">Ranksystem</a> was coded by <strong>Newcomer1989</strong> Copyright &copy; 2009-2023 powered by <a href="//ts-n.net/" target="_blank" rel="noopener noreferrer">TS-N.NET</a></p>
							<br>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2><strong><span class="text-warning"><?php echo $lang['stri0005']; ?></span></strong></h2>
							<p><?php echo $lang['stri0006']; ?></p>
							<p><?php echo $lang['stri0007']; ?></p>
							<p><?php echo $lang['stri0023']; ?></p>
							<p><?php echo sprintf($lang['stri0008'], '//ts-ranksystem.com/#download'); ?></p>
							<br>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2><strong><span class="text-danger"><?php echo $lang['stri0009']; ?></span></strong></h2>
							<p><?php echo $lang['stri0010']; ?></p>
							<p><a href="//php.net/" target="_blank" rel="noopener noreferrer">PHP</a> - Copyright &copy; 2001-2023 the <a href="//secure.php.net/credits.php" target="_blank" rel="noopener noreferrer">PHP Group</a></p><br>
							<p><?php echo $lang['stri0011']; ?></p>
							<p><a href="//jquery.com" target="_blank" rel="noopener noreferrer">jQuery v3.6.2</a> - Copyright &copy; 2020 The jQuery Foundation</p> 
							<p><a href="//fontawesome.com" target="_blank" rel="noopener noreferrer">Font Awesome 5.15.1</a> - Copyright &copy; Fonticons, Inc.</p>
							<p><a href="//flagicons.lipis.dev/" target="_blank" rel="noopener noreferrer">flag-icon-css 3.5.0</a> - Copyright &copy; 2020 flag-icons</p>
							<p><a href="//planetteamspeak.com" target="_blank" rel="noopener noreferrer">TeamSpeak 3 PHP Framework 1.1.33</a> - Copyright &copy; 2010-2018 Planet TeamSpeak</p> 
							<p><a href="//getbootstrap.com" target="_blank" rel="noopener noreferrer">Bootstrap 3.4.1</a> - Copyright &copy; 2011-2019 Twitter, Inc.</p>
							<p><a href="//morrisjs.github.io/morris.js" target="_blank" rel="noopener noreferrer">morris.js 0.5.1</a> - Copyright &copy; 2013 Olly Smith</p>
							<p><a href="//raphaeljs.com" target="_blank" rel="noopener noreferrer">Rapha&euml;l 2.2.1 - JavaScript Vector Library</a> - Copyright &copy; 2008-2012 Dmitry Baranovskiy</p>
							<p><a href="//startbootstrap.com" target="_blank" rel="noopener noreferrer">SB Admin Bootstrap Admin Template</a> - Copyright &copy; 2013-2016 Blackrock Digital LLC.</p>
							<p><a href="//github.com/Bttstrp/bootstrap-switch" target="_blank" rel="noopener noreferrer">Bootstrap Switch 3.3.4</a> - Copyright &copy; 2013-2015 Mattia Larentis</p>
							<p><a href="//www.virtuosoft.eu/code/bootstrap-touchspin" target="_blank" rel="noopener noreferrer">Bootstrap TouchSpin 3.1.2</a> - Copyright &copy; 2013-2016 István Ujj-Mészáros</p>
							<p><a href="//developer.snapappointments.com/bootstrap-select" target="_blank" rel="noopener noreferrer">bootstrap-select v1.13.14</a> - Copyright &copy; 2012-2020 SnapAppointments, LLC</p>
							<p><a href="//wenzhixin.net.cn" target="_blank" rel="noopener noreferrer">Bootstrap Show Password 1.0.3</a> - Copyright &copy; 2014 zhixin wen</p>
							<p><a href="//github.com/1000hz/bootstrap-validator" target="_blank" rel="noopener noreferrer">Bootstrap Validator</a> - Copyright &copy; 2016 Cina Saffary</p>
							<p><a href="//www.smarty.net/" target="_blank" rel="noopener noreferrer">Smarty PHP Template Engine 4.1.0</a> - Copyright &copy; 2002-2022 New Digital Group, Inc.</p>
							<br>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2><strong><span class="text-info"><?php echo $lang['stri0012']; ?></span></strong></h2>
							<p><?php echo sprintf($lang['stri0021'], '<a href="//hdf-multigaming.de" target="_blank">Shad86</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0022'], '<a href="//magicbroccoli.de" target="_blank">mightyBroccoli</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0013'], 'sergey, <a href="//vk.com/akhachirov" target="_blank">Arselopster</a>, <a href="//vk.com/zheez" target="_blank">DeviantUser</a> & <a href="//goodgame.by/" target="_blank">kidi</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0014'], 'Benjamin Frost -'); ?></p>
							<p><?php echo sprintf($lang['stri0015'], '<a href="//hydrake.eu/" target="_blank">ZanK</a> & jacopomozzy -'); ?></p>
							<p><?php echo sprintf($lang['stri0016'], '<a href="//iraqgaming.net/" target="_blank">DeStRoYzR</a> & Jehad -'); ?></p>
							<p><?php echo sprintf($lang['stri0017'], '<a href="//whitecs.ro/" target="_blank">SakaLuX</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0018'], '<a href="//r4p3.net/members/0x0539.5476/" target="_blank">0x0539</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0019'], 'Quentinti -'); ?></p>
							<p><?php echo sprintf($lang['stri0020'], '<a href="mailto://celso@esbsb.com.br" target="_blank">Pasha</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0024'], '<a href="//zasivarna.cloud" target="_blank">KeviN</a> & <a href="//github.com/Stetinac" target="_blank">Stetinac</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0025'], '<a href="//github.com/DoktorekOne" target="_blank">DoktorekOne</a> & <a href="//toster.dev/" target="_blank">toster234</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0026'], '<a href="//foro.gameflix.es" target="_blank">JavierlechuXD</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0027'], '<a href="//warriortigers.hu" target="_blank">ExXeL</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0028'], '<a href="//grezhost.com" target="_blank">G. FARZALIYEV</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0029'], '<a href="//nick-slowinski.de" target="_blank">Nick Slowinski</a> -'); ?></p>
							<p><?php echo sprintf($lang['stri0030'], '<a href="//terrabot.de" target="_blank">JimmyNail</a> -'); ?></p>
							<br>
						</div>
					</div>
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