<?PHP
session_start();

require_once('../other/config.php');
require_once('../other/load_addons_config.php');
require_once('../other/csrf_handler.php');

$addons_config = load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath);

function getclientip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP']))
		return $_SERVER['HTTP_CLIENT_IP'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED']))
		return $_SERVER['HTTP_X_FORWARDED'];
	elseif(!empty($_SERVER['HTTP_FORWARDED_FOR']))
		return $_SERVER['HTTP_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_FORWARDED']))
		return $_SERVER['HTTP_FORWARDED'];
	elseif(!empty($_SERVER['REMOTE_ADDR']))
		return $_SERVER['REMOTE_ADDR'];
	else
		return false;
}

if (isset($_POST['logout'])) {
	echo "logout";
    rem_session_ts3($rspathhex);
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

if (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $webuser || $_SESSION[$rspathhex.'password'] != $webpass || $_SESSION[$rspathhex.'clientip'] != getclientip()) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

require_once('nav.php');

if(!isset($_POST['number']) || $_POST['number'] == "yes") {
	$_SESSION[$rspathhex.'showexcepted'] = "yes";
	$filter = " AND except='0'";
} else {
	$_SESSION[$rspathhex.'showexcepted'] = "no";
	$filter = "";
}
$assign_groups_active = 0;

if (isset($_POST['update']) && $_SESSION[$rspathhex.'username'] == $webuser && $_SESSION[$rspathhex.'password'] == $webpass && $_SESSION[$rspathhex.'clientip'] == getclientip()) {
	$assign_groups_limit 		= $_POST['assign_groups_limit'];
	$assign_groups_groupids 	= $_POST['assign_groups_groupids'];
	if (isset($_POST['assign_groups_active'])) $assign_groups_active = 1;
	if ($mysqlcon->exec("UPDATE $dbname.addons_config SET value = CASE param WHEN 'assign_groups_active' THEN '$assign_groups_active' WHEN 'assign_groups_limit' THEN '$assign_groups_limit' WHEN 'assign_groups_groupids' THEN '$assign_groups_groupids' END WHERE param IN ('assign_groups_active','assign_groups_groupids','assign_groups_limit')") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc'];
		$err_lvl = NULL;
    }
	$addons_config['assign_groups_groupids']['value'] = $_POST['assign_groups_groupids'];
	$addons_config['assign_groups_limit']['value'] = $_POST['assign_groups_limit'];
	$addons_config['assign_groups_active']['value'] = $assign_groups_active;
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
				<form class="form-horizontal" data-toggle="validator" name="update" method="POST">
				<?php echo $CSRF; ?>
				<div class="form-horizontal">
					<div class="row">
						<div class="col-md-3">
						</div>
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0014"><?php echo $lang['stag0013']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
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
									<div class="form-group required-field-block">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0003"><?php echo $lang['stag0002']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" data-pattern="^([0-9]+,)*[0-9]+$" data-error="Wrong definition, please look at description for more details. No comma at ending!" rows="5" name="assign_groups_groupids" maxlength="5000" required><?php echo $addons_config['assign_groups_groupids']['value']; ?></textarea>
											<div class="required-icon"><div class="text">*</div></div>
											<div class="help-block with-errors"></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0005"><?php echo $lang['stag0004']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
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
							<button type="submit" class="btn btn-primary" name="update"><?php echo $lang['wisvconf']; ?></button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</div>
				</form>
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
<script>
$('form[data-toggle="validator"]').validator({
	custom: {
		pattern: function ($el) {
			var pattern = new RegExp($el.data('pattern'));
			return pattern.test($el.val());
		}
	},
	delay: 100,
	errors: {
		pattern: "There should be an error in your value, please check all could be right!"
	}
});
</script>
</body>
</html>