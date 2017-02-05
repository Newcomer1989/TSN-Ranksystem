<?PHP
session_start();
require_once('../other/config.php');
require_once('../other/session.php');
require_once('../other/load_addons_config.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);
if($addons_config['assign_groups_active']['value'] != '1') {
	echo "addon is disabled";
	exit;
}

if(!isset($_SESSION['tsuid']) || isset($_SESSION['uuid_verified'])) {
	set_session_ts3($ts['voice'], $mysqlcon, $dbname, $language, $adminuuid);
}

$uuid = $_SESSION['tsuid'];
$dbdata = $mysqlcon->query("SELECT * FROM $dbname.user WHERE uuid='$uuid'");
$dbdata_fetched = $dbdata->fetchAll();
$cld_groups = explode(',', $dbdata_fetched[0]['cldgroup']);
$multiple_uuid = explode(',', substr($_SESSION['multiple'], 0, -1));
$disabled = '';
$allowed_groups_arr = array();

if(count($multiple_uuid) > 1 and !isset($_SESSION['uuid_verified'])) {
	$disabled = 1;
	$err_msg = sprintf($lang['stag0006'], '<a href="verify.php">', '</a>'); $err_lvl = 3;
} else {
	$dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups");
	$servergroups = $dbgroups->fetchAll(PDO::FETCH_ASSOC);
	foreach($servergroups as $servergroup) {
		$sqlhisgroup[$servergroup['sgid']] = $servergroup['sgidname'];
		if(file_exists('../icons/'.$servergroup['sgid'].'.png')) {
			$sqlhisgroup_file[$servergroup['sgid']] = true;
		} else {
			$sqlhisgroup_file[$servergroup['sgid']] = false;
		}
	}

	$allowed_groups_arr = explode(',', $addons_config['assign_groups_groupids']['value']);

	if(isset($_POST['update'])) {
		if(($sumentries = $mysqlcon->query("SELECT COUNT(*) FROM $dbname.addon_assign_groups WHERE uuid='$uuid'")->fetch(PDO::FETCH_NUM)) === false) {
			$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
		} else {
			if($sumentries[0] > 0) {
				$err_msg = $lang['stag0007']; $err_lvl = 3;
			} else {
				$set_groups = '';
				$count_limit = 0;
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
				if($set_groups != '' && $count_limit <= $addons_config['assign_groups_limit']['value']) {
					if ($mysqlcon->exec("INSERT INTO $dbname.addon_assign_groups SET uuid='$uuid', grpids='$set_groups'") === false) {
						$err_msg = print_r($mysqlcon->errorInfo(), true); $err_lvl = 3;
					} else {
						$err_msg = $lang['stag0008']; $err_lvl = NULL;
					}
				} elseif($count_limit > $addons_config['assign_groups_limit']['value']) {
					$err_msg = sprintf($lang['stag0009'], $addons_config['assign_groups_limit']['value']); $err_lvl = 3;
				} else {
					$err_msg = $lang['stag0010']; $err_lvl = 3;
				}
			}
		}
	}
}

require_once('nav.php');
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
										<?PHP if (isset($sqlhisgroup_file[$allowed_group]) && $sqlhisgroup_file[$allowed_group]===true) { ?>
										<label class="col-sm-5 control-label"><?php echo $sqlhisgroup[$allowed_group]; ?></label>
										<label class="col-sm-1 control-label"><img src="../icons/<?PHP echo $allowed_group; ?>.png" alt="groupicon"></label>
										<label class="col-sm-2 control-label"></label>
										<?PHP } else { ?>
										<label class="col-sm-5 control-label"><?php echo $sqlhisgroup[$allowed_group]; ?></label>
										<label class="col-sm-3 control-label"></label>
										<?PHP } ?>
										<div class="col-sm-2">
											<?PHP if(in_array($allowed_group, $cld_groups)) {
												echo '<input id="switch-animate" type="checkbox" checked data-size="mini" name="',$allowed_group,'" value="1">';
											} else {
												echo '<input id="switch-animate" type="checkbox" data-size="mini" name="',$allowed_group,'" value="1">';
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
</body>
</html>