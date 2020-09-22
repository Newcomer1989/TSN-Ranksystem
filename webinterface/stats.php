<?PHP
require_once('_preload.php');
require_once('_nav.php');

if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
	if (isset($_POST['stats_show_site_navigation_switch'])) $cfg['stats_show_site_navigation_switch'] = 1; else $cfg['stats_show_site_navigation_switch'] = 0;
	if (isset($_POST['teamspeak_verification_channel_id'])) $cfg['teamspeak_verification_channel_id'] = $_POST['teamspeak_verification_channel_id']; else $cfg['teamspeak_verification_channel_id'] = 0;
	$cfg['stats_show_maxclientsline_switch'] = $_POST['stats_show_maxclientsline_switch'];
	$cfg['stats_time_bronze'] = $_POST['stats_time_bronze'];
	$cfg['stats_time_silver'] = $_POST['stats_time_silver'];
	$cfg['stats_time_gold'] = $_POST['stats_time_gold'];
	$cfg['stats_time_legend'] = $_POST['stats_time_legend'];
	$cfg['stats_connects_bronze'] = $_POST['stats_connects_bronze'];
	$cfg['stats_connects_silver'] = $_POST['stats_connects_silver'];
	$cfg['stats_connects_gold'] = $_POST['stats_connects_gold'];
	$cfg['stats_connects_legend'] = $_POST['stats_connects_legend'];
	$cfg['stats_server_news'] = addslashes($_POST['stats_server_news']);

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_show_site_navigation_switch','{$cfg['stats_show_site_navigation_switch']}'),('stats_show_maxclientsline_switch','{$cfg['stats_show_maxclientsline_switch']}'),('stats_time_bronze','{$cfg['stats_time_bronze']}'),('stats_time_silver','{$cfg['stats_time_silver']}'),('stats_time_gold','{$cfg['stats_time_gold']}'),('stats_time_legend','{$cfg['stats_time_legend']}'),('stats_connects_bronze','{$cfg['stats_connects_bronze']}'),('stats_connects_silver','{$cfg['stats_connects_silver']}'),('stats_connects_gold','{$cfg['stats_connects_gold']}'),('stats_connects_legend','{$cfg['stats_connects_legend']}'),('teamspeak_verification_channel_id','{$cfg['teamspeak_verification_channel_id']}'),('stats_server_news','{$cfg['stats_server_news']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc'];
		$err_lvl = NULL;
    }
	$cfg['stats_server_news'] = $_POST['stats_server_news'];
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
							<?php echo $lang['winav6'],' ',$lang['wihlset']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" name="update" method="POST">
				<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="row">
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wisttidesc"><?php echo $lang['achieve'],' ',$lang['stmy0019']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_time_bronze" value="<?php echo $cfg['stats_time_bronze']; ?>">
											<script>
											$("input[name='stats_time_bronze']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: 'Hour(s):'
											});
											</script>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wisttidesc"><?php echo $lang['achieve'],' ',$lang['stmy0017']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_time_silver" value="<?php echo $cfg['stats_time_silver']; ?>">
											<script>
											$("input[name='stats_time_silver']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: 'Hour(s):'
											});
											</script>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wisttidesc"><?php echo $lang['achieve'],' ',$lang['stmy0015']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_time_gold" value="<?php echo $cfg['stats_time_gold']; ?>">
											<script>
											$("input[name='stats_time_gold']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: 'Hour(s):'
											});
											</script>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wisttidesc"><?php echo $lang['achieve'],' ',$lang['stmy0012']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_time_legend" value="<?php echo $cfg['stats_time_legend']; ?>">
											<script>
											$("input[name='stats_time_legend']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: 'Hour(s):'
											});
											</script>
										</div>
									</div>
									<div class="row">&nbsp;</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wistcodesc"><?php echo $lang['achieve'],' ',$lang['stmy0028']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_connects_bronze" value="<?php echo $cfg['stats_connects_bronze']; ?>">
											<script>
											$("input[name='stats_connects_bronze']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: '#'
											});
											</script>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wistcodesc"><?php echo $lang['achieve'],' ',$lang['stmy0027']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_connects_silver" value="<?php echo $cfg['stats_connects_silver']; ?>">
											<script>
											$("input[name='stats_connects_silver']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: '#'
											});
											</script>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wistcodesc"><?php echo $lang['achieve'],' ',$lang['stmy0026']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_connects_gold" value="<?php echo $cfg['stats_connects_gold']; ?>">
											<script>
											$("input[name='stats_connects_gold']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: '#'
											});
											</script>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wistcodesc"><?php echo $lang['achieve'],' ',$lang['stmy0024']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="stats_connects_legend" value="<?php echo $cfg['stats_connects_legend']; ?>">
											<script>
											$("input[name='stats_connects_legend']").TouchSpin({
												min: 0,
												max: 2147483647,
												verticalbuttons: true,
												prefix: '#'
											});
											</script>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishnavdesc"><?php echo $lang['wishnav']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<?PHP if ($cfg['stats_show_site_navigation_switch'] == 1) {
										echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_show_site_navigation_switch" value="',$cfg['stats_show_site_navigation_switch'],'">';
									} else {
										echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_show_site_navigation_switch" value="',$cfg['stats_show_site_navigation_switch'],'">';
									} ?>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiverifydesc"><?php echo $lang['wiverify']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="teamspeak_verification_channel_id" value="<?php echo $cfg['teamspeak_verification_channel_id']; ?>">
									<script>
									$("input[name='teamspeak_verification_channel_id']").TouchSpin({
										min: 0,
										max: 16777215,
										verticalbuttons: true,
										prefix: 'ID:'
									});
									</script>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishmaxdesc"><?php echo $lang['wishmax']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control basic" name="stats_show_maxclientsline_switch">
									<?PHP
									echo '<option data-subtext="[default]" value="0"'; if($cfg['stats_show_maxclientsline_switch']=="0") echo " selected=selected"; echo '>',$lang['wishmax0'],'</option>';
									echo '<option value="1"'; if($cfg['stats_show_maxclientsline_switch']=="1") echo " selected=selected"; echo '>',$lang['wishmax1'],'</option>';
									echo '<option value="2"'; if($cfg['stats_show_maxclientsline_switch']=="2") echo " selected=selected"; echo '>',$lang['wishmax2'],'</option>';
									echo '<option value="3"'; if($cfg['stats_show_maxclientsline_switch']=="3") echo " selected=selected"; echo '>',$lang['wishmax3'],'</option>';
									?>
									</select>
								</div>
							</div>
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wimsgsndesc"><?php echo $lang['wimsgsn']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="col-sm-8">
										<textarea class="form-control" rows="15" name="stats_server_news" maxlength="21588"><?php echo $cfg['stats_server_news']; ?></textarea>
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
				</form>
			</div>
		</div>
	</div>
	
<div class="modal fade" id="wishnavdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishnav']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishnavdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishmaxdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishmax']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishmaxdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wisttidesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['achieve']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wisttidesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wistcodesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['achieve']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wistcodesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiverifydesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiverify']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiverifydesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wimsgsndesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wimsgsn']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wimsgsndesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
</body>
</html>