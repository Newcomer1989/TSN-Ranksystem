<?PHP
require_once('_preload.php');

try {
	if($addons_config['assign_groups_active']['value'] != '1') {
		echo "addon is disabled";
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

	if(count($_SESSION[$rspathhex.'multiple']) > 1 and !isset($_SESSION[$rspathhex.'uuid_verified'])) {
		$disabled = 1;
		$err_msg = sprintf($lang['stag0006'], '<a href="verify.php">', '</a>'); $err_lvl = 3;
	} elseif ($_SESSION[$rspathhex.'connected'] == 0) {
		$err_msg = sprintf($lang['stag0015'], '<a href="verify.php">', '</a>'); $err_lvl = 3;
		$disabled = 1;
	} else {
		if(($sqlhisgroup = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
			$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
		}

		$allowed_groups_arr = explode(',', $addons_config['assign_groups_groupids']['value']);
		$excepted_groups_arr = explode(',', $addons_config['assign_groups_excepted_groupids']['value']);

		if(isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
			if(($sumentries = $mysqlcon->query("SELECT COUNT(*) FROM `$dbname`.`addon_assign_groups` WHERE `uuid`='$uuid'")->fetch(PDO::FETCH_NUM)) === false) {
				$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
			} else {
				if($sumentries[0] > 0) {
					$err_msg = $lang['stag0007']; $err_lvl = 3;
				} else {
					$set_groups = '';
					$count_limit = $excepted = 0;
					if(isset($excepted_groups_arr) && $excepted_groups_arr != '') {
						foreach($excepted_groups_arr as $excepted_group) {
							if(in_array($excepted_group, $cld_groups)) {
								$excepted++;
								$err_msg = sprintf($lang['stag0019'], $sqlhisgroup[$excepted_group]['sgidname'], $excepted_group);
								break;
							}
						}
					}
					foreach($allowed_groups_arr as $allowed_group) {
						if(in_array($allowed_group, $cld_groups)) {
							$count_limit++;
						}
						if(isset($_POST[$allowed_group]) && $_POST[$allowed_group] == 1 && !in_array($allowed_group, $cld_groups)) {
							$set_groups .= $allowed_group.',';
							array_push($cld_groups, $allowed_group);
							$count_limit++;
						}
						if(!isset($_POST[$allowed_group]) && in_array($allowed_group, $cld_groups)) {
							$set_groups .= '-'.$allowed_group.',';
							$position = array_search($allowed_group, $cld_groups);
							array_splice($cld_groups, $position, 1);
							$count_limit--;
						}
					}
					$set_groups = substr($set_groups, 0, -1);
					if($set_groups != '' && $count_limit <= $addons_config['assign_groups_limit']['value'] && $excepted == 0) {
						if ($mysqlcon->exec("INSERT INTO `$dbname`.`addon_assign_groups` SET `uuid`='$uuid',`grpids`='$set_groups'") === false) {
							$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
						} elseif($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`=1 WHERE `job_name`='reload_trigger'; ") === false) {
							$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
						} else {
							$err_msg = $lang['stag0008']; $err_lvl = NULL;
						}
					} elseif($count_limit > $addons_config['assign_groups_limit']['value']) {
						$err_msg = sprintf($lang['stag0009'], $addons_config['assign_groups_limit']['value']); $err_lvl = 3;
					} elseif($excepted > 0) {
						$err_lvl = 3;
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
							<div class="col-md-6">
							</div>
							<div class="col-md-3">
								<p class="text-right"><strong><?PHP echo $lang['stag0011'].$addons_config['assign_groups_limit']['value']; ?></strong></p>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="col-md-3">
							</div>
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<?PHP foreach($allowed_groups_arr as $allowed_group) { ?>
										<div class="form-group">
											<?PHP if (isset($sqlhisgroup[$allowed_group]['iconid']) && $sqlhisgroup[$allowed_group]['iconid'] != 0) { ?>
											<label class="col-sm-5 control-label"><?php echo $sqlhisgroup[$allowed_group]['sgidname']; ?></label>
											<label class="col-sm-1 control-label"><img src="../tsicons/<?PHP echo $sqlhisgroup[$allowed_group]['iconid'],'.',$sqlhisgroup[$allowed_group]['ext']; ?>" width="16" height="16" alt="missed_icon"></label>
											<label class="col-sm-2 control-label"></label>
											<?PHP } else { ?>
											<label class="col-sm-5 control-label"><?php echo $sqlhisgroup[$allowed_group]['sgidname']; ?></label>
											<label class="col-sm-3 control-label"></label>
											<?PHP } ?>
											<div class="col-sm-2">
												<?PHP if(in_array($allowed_group, $cld_groups)) {
													echo '<input type="checkbox" checked data-size="mini" name="',$allowed_group,'" value="1">';
												} else {
													echo '<input type="checkbox" data-size="mini" name="',$allowed_group,'" value="1">';
												} ?>
											</div>
										</div>
										<?PHP } ?>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" name="update" class="btn btn-primary"<?PHP if($disabled == 1) echo " disabled"; ?>><?PHP echo $lang['stag0012']; ?></button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</form>
				</div>
			</div>
		</div>
		<?PHP require_once('_footer.php'); ?>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>