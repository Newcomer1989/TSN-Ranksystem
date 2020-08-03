<?PHP
require_once('_preload.php');
require_once('_nav.php');

require_once('../other/load_addons_config.php');
$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);

if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups` ORDER BY `sortid`,`sgidname` ASC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

$assign_groups_active = 0;
if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']]) && isset($_POST['assign_groups_active']) && !isset($_POST['assign_groups_groupids'])) {
	$err_msg = $lang['stag0010'];
	$err_lvl = 3;
} elseif (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
	$assign_groups_limit = $_POST['assign_groups_limit'];
	$assign_groups_groupids	= '';
	if (isset($_POST['assign_groups_groupids']) && $_POST['assign_groups_groupids'] != NULL) {
		foreach ($_POST['assign_groups_groupids'] as $group) {
			$assign_groups_groupids .= $group.',';
		}
	}
	$assign_groups_groupids = substr($assign_groups_groupids, 0, -1);
	if (isset($_POST['assign_groups_active'])) $assign_groups_active = 1;
	if ($mysqlcon->exec("UPDATE `$dbname`.`addons_config` SET `value` = CASE `param` WHEN 'assign_groups_active' THEN '{$assign_groups_active}' WHEN 'assign_groups_limit' THEN '{$assign_groups_limit}' WHEN 'assign_groups_groupids' THEN '{$assign_groups_groupids}' END WHERE `param` IN ('assign_groups_active','assign_groups_groupids','assign_groups_limit')") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc'];
		$err_lvl = NULL;
    }
	$addons_config['assign_groups_groupids']['value'] = $assign_groups_groupids;
	$addons_config['assign_groups_limit']['value'] = $_POST['assign_groups_limit'];
	$addons_config['assign_groups_active']['value'] = $assign_groups_active;
} elseif(isset($_POST['update'])) {
	echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
	rem_session_ts3($rspathhex);
	exit;
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['stag0001']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" name="update" method="POST">
				<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
				<div class="form-horizontal">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-sm-12 pointer" data-toggle="modal" data-target="#stag0001desc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="panel-body">
								</div>
							</div>
						</div>
						<div class="col-md-3">
						</div>
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0014"><?php echo $lang['stag0013']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
										<?PHP if ($addons_config['assign_groups_active']['value'] == '1') {
											echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="assign_groups_active" value="',$assign_groups_active,'">';
										} else {
											echo '<input class="switch-animate" type="checkbox" data-size="mini" name="assign_groups_active" value="',$assign_groups_active,'">';
										} ?>
										</div>
									</div>
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0003"><?php echo $lang['stag0002']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<select class="selectpicker form-control" data-live-search="true" data-actions-box="true" multiple name="assign_groups_groupids[]">
											<?PHP
											$assign_groups_groupids = explode(',', $addons_config['assign_groups_groupids']['value']);
											foreach ($groupslist as $groupID => $groupParam) {
												if (in_array($groupID, $assign_groups_groupids)) $selected=" selected"; else $selected="";
												if (isset($groupParam['iconid']) && $groupParam['iconid'] != 0) $iconid=$groupParam['iconid']."."; else $iconid="placeholder.png";
												if ($groupParam['type'] == 0 || $groupParam['type'] == 2) $disabled=" disabled"; else $disabled="";
												if ($groupParam['type'] == 0) $grouptype=" [TEMPLATE GROUP]"; else $grouptype="";
												if ($groupParam['type'] == 2) $grouptype=" [QUERY GROUP]";
												if ($groupID != 0) {
													echo '<option data-content="&nbsp;&nbsp;<img src=\'../tsicons/',$iconid,$groupParam['ext'],'\' width=\'16\' height=\'16\'>&nbsp;&nbsp;',$groupParam['sgidname'],'&nbsp;<span class=\'text-muted small\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$selected,$disabled,'></option>';
												}
											}
											?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0005"><?php echo $lang['stag0004']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="assign_groups_limit" value="<?php echo $addons_config['assign_groups_limit']['value']; ?>">
											<script>
											$("input[name='assign_groups_limit']").TouchSpin({
												min: 1,
												max: 65534,
												verticalbuttons: true,
												prefix: 'No.'
											});
											</script>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i>&nbsp;<?php echo $lang['wisvconf']; ?></button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</div>
				</form>
			</div>
		</div>
	</div>
<div class="modal fade" id="stag0001desc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['stag0001']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['stag0001desc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="stag0003" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['stag0002']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['stag0003']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="stag0005" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['stag0004']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['stag0005']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="stag0014" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['stag0013']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['stag0014']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
</body>
</html>