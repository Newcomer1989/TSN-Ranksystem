<?PHP
require_once('_preload.php');

try {
	if($addons_config['assign_groups_active']['value'] != '1') {
		echo '<pre><h3><span class="text-danger"><strong>This addon is (currently) disabled!</strong></span></h3></pre>';
		exit;
	}

	if(isset($_SESSION[$rspathhex.'tsuid'])) {
		$uuid = $_SESSION[$rspathhex.'tsuid'];
	} else {
		$uuid = "no_uuid_found";
	}
	if(($dbdata = $mysqlcon->query("SELECT `cldgroup` FROM `$dbname`.`user` WHERE `uuid`='$uuid'")->fetch()) === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
	}
	$cld_groups = array();
	if(isset($dbdata['cldgroup']) && $dbdata['cldgroup'] != '') {
		$cld_groups = explode(',', $dbdata['cldgroup']);
	}

	$disabled = '';
	$allowed_groups_arr = array();

	$csrf_token = bin2hex(openssl_random_pseudo_bytes(32));

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}
	
	if(($sqlhisgroup = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
	}

	if(count($_SESSION[$rspathhex.'multiple']) > 1 and !isset($_SESSION[$rspathhex.'uuid_verified'])) {
		$disabled = 1;
		$err_msg = sprintf($lang['stag0006'], '<a href="verify.php">', '</a>'); $err_lvl = 3;
	} elseif ($_SESSION[$rspathhex.'connected'] == 0) {
		$err_msg = sprintf($lang['stag0015'], '<a href="verify.php">', '</a>'); $err_lvl = 3;
		$disabled = 1;
	} else {
		

		$name = explode(';',$addons_config['assign_groups_name']['value']);
		$alwgr = explode(';',$addons_config['assign_groups_groupids']['value']);
		$limit = explode(';',$addons_config['assign_groups_limit']['value']);
		$excgr = explode(';',$addons_config['assign_groups_excepted_groupids']['value']);

		if(isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
			if(($sumentries = $mysqlcon->query("SELECT COUNT(*) FROM `$dbname`.`addon_assign_groups` WHERE `uuid`='$uuid'")->fetch(PDO::FETCH_NUM)) === false) {
				$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			} else {
				if($sumentries[0] > 0) {
					$err_msg = $lang['stag0007']; $err_lvl = 3;
				} else {
					$set_groups = $err_msg = '';
					$limit_raised = $excepted = 0;

					foreach($alwgr as $rowid => $value) {
						$count_limit = $changed_group = 0;

						$allowed_groups_arr = explode(',', $alwgr[$rowid]);
						$excepted_groups_arr = explode(',', $excgr[$rowid]);

						foreach($allowed_groups_arr as $allowed_group) {
							if(in_array($allowed_group, $cld_groups)) {
								$count_limit++;
							}
							if(isset($_POST[$allowed_group]) && $_POST[$allowed_group] == 1 && !in_array($allowed_group, $cld_groups)) {
								$set_groups .= $allowed_group.',';
								array_push($cld_groups, $allowed_group);
								$count_limit++;
								$changed_group++;
							}
							if(!isset($_POST[$allowed_group]) && in_array($allowed_group, $cld_groups)) {
								$set_groups .= '-'.$allowed_group.',';
								$position = array_search($allowed_group, $cld_groups);
								array_splice($cld_groups, $position, 1);
								$count_limit--;
								$changed_group++;
							}
						}

						if(isset($excepted_groups_arr) && $excepted_groups_arr != '') {
							foreach($excepted_groups_arr as $excepted_group) {
								if(in_array($excepted_group, $cld_groups) && $changed_group != 0) {
									$excepted++;
									if($err_msg != '') {
										$err_msg .= '#####';
										$err_lvl .= '#3';
									} else {
										$err_lvl = 3;
									}
									$err_msg .= "<strong>".$name[$rowid]."</strong><br>".sprintf($lang['stag0019'], $sqlhisgroup[$excepted_group]['sgidname'], $excepted_group);
									break;
								}
							}
						}

						if($set_groups != '' && $count_limit > $limit[$rowid]) {
							if($err_msg != '') {
								$err_msg .= '#####';
								$err_lvl .= '#3';
							} else {
								$err_lvl = 3;
							}
							$err_msg .= "<strong>".$name[$rowid]."</strong><br>".sprintf($lang['stag0009'], $limit[$rowid]);
							$limit_raised = 1;
						}
					}
					$set_groups = substr($set_groups, 0, -1);

					if($set_groups != '' && $limit_raised == 0 && $excepted == 0) {
						if ($mysqlcon->exec("INSERT INTO `$dbname`.`addon_assign_groups` SET `uuid`='$uuid',`grpids`='$set_groups'; DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}") === false) {
							$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
						} elseif($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`=1 WHERE `job_name`='reload_trigger'; ") === false) {
							$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
						} else {
							$err_msg = $lang['stag0008']; $err_lvl = NULL;
						}
					} elseif($limit_raised != 0) {
						#message above generated
					} elseif($excepted > 0) {
						#message above generated
					} else {
						$err_msg = $lang['stag0010']; $err_lvl = 3;
					}
				}
			}
		} elseif(isset($_POST['update'])) {
			echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
			rem_session_ts3();
			exit;
		}
	}
	?>
			<div id="page-wrapper">
			<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?PHP echo $lang['stag0001']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
						<div class="row">
							<?PHP
							$alwgr = explode(';',$addons_config['assign_groups_groupids']['value']);
							$limit = explode(';',$addons_config['assign_groups_limit']['value']);
							$excgr = explode(';',$addons_config['assign_groups_excepted_groupids']['value']);
							if(isset($addons_config['assign_groups_name']['value'])) {
								$name = explode(';',$addons_config['assign_groups_name']['value']);
							} else {
								$name = '';
							}
							$exception_count = $forcount = 0;
							$output = array();
							foreach($alwgr as $rowid => $value) {
								$output[$forcount]['output'] = '';
								$allowed_groups_arr = explode(',', $alwgr[$rowid]);
								$excepted_groups_arr = explode(',', $excgr[$rowid]);
								if(isset($excepted_groups_arr) && $excepted_groups_arr != '') {
									foreach($excepted_groups_arr as $excepted_group) {
										if(in_array($excepted_group, $cld_groups)) {
											$output[$forcount]['except'] = 1;
											$excepted_group = "<strong>".$name[$rowid]."</strong><br>".sprintf($lang['stag0019'], $sqlhisgroup[$excepted_group]['sgidname'], $excepted_group);
											$exception_count++;
											break;
										}
									}
								}
								$output[$forcount]['output'] .= '<div class="panel panel-primary"><div class="panel-heading"><div class="row"><div class="col-xs-9 text-left"><h2>'.$name[$rowid].'</h2></div><div class="col-xs-12 text-right"><strong>'.$lang['stag0011'].$limit[$rowid].'</strong></div></div></div><div class="panel-body"><div class="col-md-12">';
								foreach($allowed_groups_arr as $allowed_group) {
									$output[$forcount]['output'] .= '<div class="form-group">';
									if (isset($sqlhisgroup[$allowed_group]['iconid']) && $sqlhisgroup[$allowed_group]['iconid'] != 0) {
										$output[$forcount]['output'] .= '<label class="col-sm-5 control-label">'.$sqlhisgroup[$allowed_group]['sgidname'].'</label><label class="col-sm-1 control-label"><img src="../tsicons/'.$sqlhisgroup[$allowed_group]['iconid'].'.'.$sqlhisgroup[$allowed_group]['ext'].'" width="16" height="16" alt="missed_icon"></label><label class="col-sm-2 control-label"></label>';
									} else {
										$output[$forcount]['output'] .= '<label class="col-sm-5 control-label">'.$sqlhisgroup[$allowed_group]['sgidname'].'</label><label class="col-sm-3 control-label"></label>';
									}
									$output[$forcount]['output'] .= '<div class="col-sm-2">';
									if(in_array($allowed_group, $cld_groups)) {
										$output[$forcount]['output'] .= '<input type="checkbox" checked data-size="mini" name="'.$allowed_group.'" value="1">';
									} else {
										$output[$forcount]['output'] .= '<input type="checkbox" data-size="mini" name="'.$allowed_group.'" value="1">';
									}
									$output[$forcount]['output'] .= '</div></div>';
								}
								$output[$forcount]['output'] .= '</div></div></div></div>';
								$forcount++;
							}

							foreach($output as $value) {
								if(isset($value['except']) && $value['except'] == 1) {
									echo '<div class="hidden">';
								} elseif(count($alwgr) == 1 || (count($alwgr) - $exception_count) == 1) {
									echo '<div class="col-md-3"></div><div class="col-md-6">';
								} else {
									echo '<div class="col-md-6">';
								}
								echo $value['output'];
							}
							?>
						</div>
						<div class="row">&nbsp;</div>
						<?PHP
						if($exception_count >= count($alwgr)) {
							echo '<pre><h3><span class="text-danger">',$excepted_group,'</span></h3></pre>';
						} else {
						?>
						<div class="row">
							<div class="text-center">
								<button type="submit" name="update" class="btn btn-primary"<?PHP if($disabled == 1) echo " disabled"; ?>><?PHP echo $lang['stag0012']; ?></button>
							</div>
						</div>
						<?PHP } ?>
						<div class="row">&nbsp;</div>
					</form>
				</div>
			</div>
		</div>
	<?PHP
	require_once('_footer.php');
	echo "<script>";
	foreach($alwgr as $rowid => $value) {
		$allowed_groups_arr = explode(',', $alwgr[$rowid]);
		foreach($allowed_groups_arr as $allowed_group) {
			if($disabled == 1) {
				echo '$("[name=\'',$allowed_group,'\']").bootstrapSwitch({disabled:true});';
				
			} else {
				echo '$("[name=\'',$allowed_group,'\']").bootstrapSwitch();';
			}
		}
	}
	?>
	</script>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>