<?PHP
require_once('_preload.php');

try {
	require_once('_nav.php');

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
		$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	function get_status($lang, $job_check, $check = NULL) {
		$err_msg = "<b>".$lang['wihladmex']."</b>: ";
		switch($job_check['database_export']['timestamp']) {
			case 1:
				if($check == 1) {
					$err_msg .= $lang['wihladmrs16']."<br>"; break;
				} else {
					$err_msg .= $lang['wihladmrs1']."<br>"; break;
				}
			case 2:
				$err_msg .= "<span class=\"alert-info\">".$lang['wihladmrs2']."</span><br>"; break;
			case 3:
				$err_msg .= "<span class=\"alert-danger\">".$lang['wihladmrs3']."</span><br>"; break;
			case 4:
				$err_msg .= "<span class=\"alert-success\">".$lang['wihladmrs4']."</span><br>"; break;
			default:
				$err_msg .= "<span class=\"alert-secondary\"><i>".$lang['wihladmrs0']."</i></span><br>";
		}

		return $err_msg;
	}

	if($job_check['database_export']['timestamp'] != 0) {
		$err_msg = '<b>'.$lang['wihladmrs'].":</b><br><br><pre>"; $err_lvl = 2;
		$err_msg .= get_status($lang, $job_check);

		if(in_array($job_check['database_export']['timestamp'], ["0","3","4"], true)) {
			$err_msg .= '</pre><br>';
			if($job_check['database_export']['timestamp'] == 4) {
				$err_msg .= "Exported file successfully.";
				if(version_compare(phpversion(), '7.2', '>=') && version_compare(phpversion("zip"), '1.2.0', '>=')) {
					$err_msg .= "<br><u>".sprintf($lang['wihladmex2'], "</u>")."<br><pre>".$cfg['teamspeak_query_pass']."</pre>";
				}
			}
			$err_msg .= '<br>'.sprintf($lang['wihladmrs9'], '<form class="btn-group" name="confirm" action="export.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-success btn-sm" name="confirm"><i class="fas fa-check"></i>&nbsp;', '</button></form>');
		} else {
			$err_msg .= '</pre><br>'.sprintf($lang['wihladmrs7'], '<form class="btn-group" name="refresh" action="export.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary btn-sm" name="refresh"><i class="fas fa-sync"></i>&nbsp;', '</button></form>').'<br><br>'.$lang['wihladmrs8'].'<br><br>'.sprintf($lang['wihladmrs17'], '<form class="btn-group" name="cancel" action="export.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-danger btn-sm" name="cancel"><i class="fas fa-times"></i>&nbsp;', '</button></form>');
		}
	}

	if (isset($_POST['confirm']) && isset($db_csrf[$_POST['csrf_token']])) {
		if(in_array($job_check['database_export']['timestamp'], ["0","3","4"], true)) {
			if ($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('database_export','0') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
				$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
				$err_lvl = 3;
			} else {
				$err_msg = $lang['wihladmrs10'];
				$err_lvl = NULL;
			}
		} else {
			$err_msg = $lang['errukwn'];
			$err_lvl = 3;
		}
	} elseif (isset($_POST['cancel']) && isset($db_csrf[$_POST['csrf_token']])) {
		if(in_array($job_check['database_export']['timestamp'], ["0","1","2","4"], true)) {
			if ($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('database_export','3') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
				$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
				$err_lvl = 3;
			} else {
				$err_msg = $lang['wihladmrs18'];
				$err_lvl = NULL;
			}
		} else {
			$err_msg = $lang['errukwn'];
			$err_lvl = 3;
		}
	} elseif (isset($_POST['delete']) && isset($db_csrf[$_POST['csrf_token']])) {
		if(substr($_POST['delete'],0,10) == "db_export_" && unlink($cfg['logs_path'].$_POST['delete'])) {
			$err_msg = sprintf($lang['wihladmex3'], $_POST['delete']);
			$err_lvl = NULL;
		} else {
			$err_msg = sprintf($lang['wihladmex4'], $_POST['delete']);
			$err_lvl = 3;
		}
	} elseif (isset($_POST['download']) && isset($db_csrf[$_POST['csrf_token']])) {
		$err_msg = "download request: ".$_POST['download'];
		$err_lvl = 3;
	} elseif (isset($_POST['export']) && isset($db_csrf[$_POST['csrf_token']])) {
		if ($mysqlcon->exec("INSERT INTO `$dbname`.`job_check` (`job_name`,`timestamp`) VALUES ('database_export','1') ON DUPLICATE KEY UPDATE `timestamp`=VALUES(`timestamp`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
			$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
			$err_lvl = 3;
		} else {
			$err_msg = '<b>'.$lang['wihladmex1'].'</b><br><br>'.sprintf($lang['wihladmrs7'], '<form class="btn-group" name="refresh" action="export.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary btn-sm" name="refresh"><i class="fas fa-sync"></i>&nbsp;', '</button></form>').'<br><br>'.$lang['wihladmrs8'];
			if(($snapshot = $mysqlcon->query("SELECT COUNT(*) AS `count` from `$dbname`.`user_snapshot`")->fetch()) === false) { } else {
				$est_time = round($snapshot['count'] * 0.00005) + 5;
				$dtF = new \DateTime('@0');
				$dtT = new \DateTime("@$est_time");
				$est_time = $dtF->diff($dtT)->format($cfg['default_date_format']);
				$err_msg .= '<br><br>'.$lang['wihladmrs11'].': '.$est_time.'.<br>';
			}
			$err_lvl = NULL;
		}
	} elseif(isset($_POST['update'])) {
		echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
		rem_session_ts3();
		exit;
	}
	?>
			<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['wihladmex']; ?>
							</h1>
						</div>
					</div>
					<div class="form-horizontal">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-sm-12 pointer" data-toggle="modal" data-target="#wihladmexdesc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="panel-body">
										<div class="row">&nbsp;</div>
										<div class="row">&nbsp;</div>
										<div class="form-group">
											<div class="col-sm-6">
												<b><?php echo "File" ?></b>
											</div>
											<div class="col-sm-1">
												<b><?php echo "Filesize" ?></b>
											</div>
											<div class="col-sm-3">
												<b><?php echo "MD5" ?></b>
											</div>
											<div class="col-sm-1"></div>
											<div class="col-sm-1"></div>
										</div>
										<div class="form-group" name="filegroup">
										<?PHP
										foreach(scandir($cfg['logs_path']) as $file) {
											if ('.' === $file || '..' === $file) continue;
											if (is_dir($cfg['logs_path'].$file)) continue;
											if(substr($file, 0, 10) != 'db_export_') continue;
										?>
											<div class="col-sm-6">
												<?PHP echo $cfg['logs_path'].$file; ?>
											</div>
											<div class="col-sm-1">
												<?PHP echo human_readable_size(filesize($cfg['logs_path'].$file),$lang); ?>
											</div>
											<div class="col-sm-3">
												<?PHP echo md5_file($cfg['logs_path'].$file); ?>
											</div>
											<div class="col-sm-1 text-center delete">
												<form id="<?PHP echo $file.'dow' ?>" method="POST">
													<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
													<input type="hidden" name="download" value="<?PHP echo $file; ?>">
													<?PHP if(in_array($job_check['database_export']['timestamp'], ["0","4"], true)) { ?>
														<a href="download_file.php?csrf_token=<?PHP echo $csrf_token; ?>&file=<?PHP echo $file ?>">
														<span onclick="document.getElementById('<?PHP echo $file.'dow' ?>').submit();" style="cursor: pointer; pointer-events: all;">
														<svg class="svg-inline--fa fa-download fa-w-16" style="margin-top: 10px;cursor: pointer;" title="download file" aria-labelledby="svg-inline--fa-title-D8LEkIGcdqdt" data-prefix="fas" data-icon="download" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-D8LEkIGcdqdt"><?PHP echo $lang['wihladmex5']; ?></title><path fill="currentColor" d="M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z" style="--darkreader-inline-fill:currentColor;" data-darkreader-inline-fill=""></path></svg>
														</span>
														</a>
													<?PHP } ?>
												</form>
											</div>
											<div class="col-sm-1 text-center delete">
												<form id="<?PHP echo $file.'del' ?>" method="POST">
													<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
													<input type="hidden" name="delete" value="<?PHP echo $file ?>">
													<?PHP if(in_array($job_check['database_export']['timestamp'], ["0","4"], true)) { ?>
														<span onclick="document.getElementById('<?PHP echo $file.'del' ?>').submit();" style="cursor: pointer; pointer-events: all;">
															<svg class="svg-inline--fa fa-trash fa-w-14" style="margin-top: 10px;cursor: pointer;" title="delete file" onclick="javascript:this.form.submit();" aria-labelledby="svg-inline--fa-title-gtKCZkgszs1S" data-prefix="fas" data-icon="trash" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><title id="svg-inline--fa-title-gtKCZkgszs1S"><?PHP echo $lang['wihladmex6']; ?></title><path fill="currentColor" d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z" style="--darkreader-inline-fill:currentColor;" data-darkreader-inline-fill=""></path></svg>
														</span>
													<?PHP } ?>
												</form>
											</div>
										<?PHP
										}
										?>
										</div>
									</div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="row">
								<form name="post" id="post" method="POST">
								<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
								<div class="text-center">
									<button type="submit" class="btn btn-primary" name="export"><?php echo $lang['wihladmex7']; ?></button>
								</div>
								</form>
							</div>
							<div class="row">&nbsp;</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	<div class="modal fade" id="wihladmexdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wihladm0']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wihladmexdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	</body>
	</html>
	<?PHP
} catch(Throwable $ex) { }
?>