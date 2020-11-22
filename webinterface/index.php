<?PHP
require_once('_preload.php');

try {
	if(!class_exists('PDO')) {
		$err_msg = sprintf($lang['insterr2'],'PHP PDO','//php.net/manual/en/book.pdo.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	}
	if(version_compare(PHP_VERSION, '5.5.0', '<')) {
		$err_msg = sprintf($lang['insterr4'],PHP_VERSION); $err_lvl = 3; $dis_login = 1;
	}
	if(!function_exists('simplexml_load_file')) {
		$err_msg = sprintf($lang['insterr2'],'PHP SimpleXML','//php.net/manual/en/book.simplexml.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	}
	if(!in_array('curl', get_loaded_extensions())) {
		$err_msg = sprintf($lang['insterr2'],'PHP cURL','//php.net/manual/en/book.curl.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	}
	if(!in_array('zip', get_loaded_extensions())) {
		$err_msg = sprintf($lang['insterr2'],'PHP Zip','//php.net/manual/en/book.zip.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	}
	if(!in_array('mbstring', get_loaded_extensions())) {
		$err_msg = sprintf($lang['insterr2'],'PHP mbstring','//php.net/manual/en/book.mbstring.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	}
	if(!in_array('openssl', get_loaded_extensions())) {
		$err_msg = sprintf($lang['insterr2'],'PHP OpenSSL','//php.net/manual/en/book.openssl.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	}
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		if(!in_array('com_dotnet', get_loaded_extensions())) {
			unset($err_msg); $err_msg = sprintf($lang['insterr2'],'PHP COM extension (php_com_dotnet.dll)','//php.net/manual/en/book.com.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
		}
	}

	if(file_exists($cfg['logs_path'].'ranksystem.log') && !is_writable($cfg['logs_path'].'ranksystem.log')) {
		$err_msg = sprintf($lang['chkfileperm'], '<pre>chown -R www-data:www-data '.$cfg['logs_path'].'</pre><br>', '<pre>chmod 0740 '.$cfg['logs_path'].'ranksystem.log</pre><br><br>', '<pre>'.$cfg['logs_path'].'ranksystem.log</pre>');
		$err_lvl = 3; $dis_login = 0;
	}

	if(!is_writable($cfg['logs_path'])) {
		$err_msg = sprintf($lang['chkfileperm'], '<pre>chown -R www-data:www-data '.$cfg['logs_path'].'</pre><br>', '<pre>chmod 0740 '.$cfg['logs_path'].'</pre><br><br>', '<pre>'.$cfg['logs_path'].'</pre>');
		$err_lvl = 3; $dis_login = 0;
	}

	if(!function_exists('exec')) {
		unset($err_msg); $err_msg = sprintf($lang['insterr3'],'exec','//php.net/manual/en/book.exec.php',get_cfg_var('cfg_file_path')); $err_lvl = 3; $dis_login = 1;
	} else {
		exec("$phpcommand -v", $phpversioncheck);
		$output = '';
		foreach($phpversioncheck as $line) $output .= print_r($line, true).'<br>';
		if(empty($phpversioncheck) || strtoupper(substr($phpversioncheck[0], 0, 3)) != "PHP") {
			$err_msg = sprintf($lang['chkphpcmd'], "\"other/phpcommand.php\"", "<u>\"other/phpcommand.php\"</u>", '<pre>'.$phpcommand.'</pre>', '<pre>'.$output.'</pre><br><br>', '<pre>php -v</pre>');
			$err_lvl = 3; $dis_login = 1;
		} else {
			$exploded = explode(' ',$phpversioncheck[0]);
			if($exploded[1] != phpversion()) {
				$err_msg = sprintf($lang['chkphpmulti'], phpversion(), "<u>\"other/phpcommand.php\"</u>", $exploded[1], "\"other/phpcommand.php\"</u>", "\"other/phpcommand.php\"</u>", '<pre>'.$phpcommand.'</pre>');
				if(getenv('PATH')!='') {
					$err_msg .= "<br><br>".sprintf($lang['chkphpmulti2'], '<br>'.getenv('PATH'));
				}			
				$err_lvl = 2;
			}
		}
	}

	if(!isset($err_msg) && version_compare(PHP_VERSION, '7.2.0', '<')) {
		$err_msg = "Your PHP Version: (".PHP_VERSION.") is outdated and no longer supported. Please update it!";
		$err_lvl = 2;
	}

	if(($cfg['webinterface_access_last'] + 1) >= time()) {
		$waittime = $cfg['webinterface_access_last'] + 2 - time();
		$err_msg = sprintf($lang['errlogin2'],$waittime);
		$err_lvl = 3;
	} elseif ($cfg['webinterface_access_count'] >= 10) {
		enter_logfile($cfg,3,sprintf($lang['brute'], getclientip()));
		$err_msg = $lang['errlogin3'];
		$err_lvl = 3;
		$bantime = time() + 299;
		if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_access_last','{$bantime}'),('webinterface_access_count','0') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) { }
	} elseif (isset($_POST['username']) && $_POST['username'] == $cfg['webinterface_user'] && password_verify($_POST['password'], $cfg['webinterface_pass'])) {
		$_SESSION[$rspathhex.'username'] = $cfg['webinterface_user'];
		$_SESSION[$rspathhex.'password'] = $cfg['webinterface_pass'];
		$_SESSION[$rspathhex.'clientip'] = getclientip();
		$_SESSION[$rspathhex.'newversion'] = $cfg['version_latest_available'];
		enter_logfile($cfg,6,sprintf($lang['brute2'], getclientip()));
		if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_access_count','0') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) { }
		header("Location: $prot://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/bot.php");
		exit;
	} elseif(isset($_POST['username'])) {
		$nowtime = time();
		enter_logfile($cfg,5,sprintf($lang['brute1'], getclientip(), $_POST['username']));
		$cfg['webinterface_access_count']++;
		if($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('webinterface_access_last','{$nowtime}'),('webinterface_access_count','{$cfg['webinterface_access_count']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)") === false) { }
		$err_msg = $lang['errlogin'];
		$err_lvl = 3;
	}

	if(isset($_SESSION[$rspathhex.'username']) && $_SESSION[$rspathhex.'username'] == $cfg['webinterface_user'] && $_SESSION[$rspathhex.'password'] == $cfg['webinterface_pass']) {
		header("Location: $prot://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/bot.php");
		exit;
	}

	require_once('_nav.php');
	?>
			<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div id="login-overlay" class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
							  <h4 class="modal-title" id="myModalLabel"><?PHP echo $lang['isntwiusrh']; ?></h4>
							</div>
							<div class="modal-body">
								<div class="row">
									<div class="col-xs-12">
										<form id="loginForm" method="POST">
											<div class="form-group">
												<label class="control-label"><?PHP echo $lang['user']; ?>:</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fas fa-user"></i></span>
													<input type="text" class="form-control" name="username" placeholder="<?PHP echo $lang['user']; ?>" maxlength="21588" autofocus>
												</div>
											</div>
											<div class="form-group">
												<label class="control-label"><?PHP echo $lang['pass']; ?>:</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fas fa-lock"></i></span>
													<input type="password" class="form-control" name="password" placeholder="<?PHP echo $lang['pass']; ?>" maxlength="21588">
												</div>
											</div>
											<br>
											<p>
												<?PHP
												if(isset($dis_login) && $dis_login == 1) {
													echo '<button type="submit" class="btn btn-success btn-block" disabled>',$lang['login'],'</button>';
												} else {
													echo '<button type="submit" class="btn btn-success btn-block">',$lang['login'],'</button>';
												}
												?>
											</p>
											<p class="small text-right">
												<a href="resetpassword.php"><?PHP echo $lang['pass5']; ?></a>
											</p>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>