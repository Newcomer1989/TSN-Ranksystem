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
	$cfg['imprint_address']	= addslashes($_POST['imprint_address']);
	$cfg['imprint_email'] = addslashes($_POST['imprint_email']);
	$cfg['imprint_phone'] = addslashes($_POST['imprint_phone']);
	$cfg['imprint_notes'] = addslashes($_POST['imprint_notes']);
	$cfg['imprint_privacy-policy'] = addslashes($_POST['imprint_privacy-policy']);
	if (isset($_POST['imprint_enabled'])) $cfg['imprint_enabled'] = 1; else $cfg['imprint_enabled'] = 0;
	if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('imprint_enabled','{$cfg['imprint_enabled']}'),('imprint_address','{$cfg['imprint_address']}'),('imprint_email','{$cfg['imprint_email']}'),('imprint_phone','{$cfg['imprint_phone']}'),('imprint_notes','{$cfg['imprint_notes']}'),('imprint_privacy-policy','{$cfg['imprint_privacy-policy']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres']);
		$err_lvl = NULL;
    }
	$cfg['imprint_address'] = $_POST['imprint_address'];
	$cfg['imprint_email'] = $_POST['imprint_email'];
	$cfg['imprint_phone'] = $_POST['imprint_phone'];
	$cfg['imprint_notes'] = $_POST['imprint_notes'];
	$cfg['imprint_privacy-policy'] = $_POST['imprint_privacy-policy'];
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
							<?php echo $lang['imprint'],' & ',$lang['privacy-policy']; ?>
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
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpswitchdesc"><?php echo $lang['wiimpswitch']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['imprint_enabled'] == 1) {
												echo '<input id="switch-animate" type="checkbox" checked data-size="mini" name="imprint_enabled" value="',$cfg['imprint_enabled'],'">';
											} else {
												echo '<input id="switch-animate" type="checkbox" data-size="mini" name="imprint_enabled" value="',$cfg['imprint_enabled'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpaddrdesc"><?php echo $lang['imprint_address']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="4" name="imprint_address" maxlength="21588"><?php echo $cfg['imprint_address']; ?></textarea>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpemaildesc"><?php echo $lang['imprint_email']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="email" name="imprint_email" class="form-control" value='<?php echo $cfg["imprint_email"]; ?>'>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpphonedesc"><?php echo $lang['imprint_phone']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="tel" name="imprint_phone" class="form-control" value='<?php echo $cfg["imprint_phone"]; ?>'>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpnotesdesc"><?php echo $lang['imprint_notes']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="5" name="imprint_notes" maxlength="21588"><?php echo $cfg['imprint_notes']; ?></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiimpprivacydesc"><?php echo $lang['privacy-policy']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="15" name="imprint_privacy-policy" maxlength="21588"><?php echo $cfg['imprint_privacy-policy']; ?></textarea>
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
				</form>
			</div>
		</div>
	</div>
	
<div class="modal fade" id="wiimpswitchdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiimpswitch']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiimpswitchdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiimpaddrdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['imprint_address']; ?></h4>
      </div>
      <div class="modal-body">
	    <?php echo sprintf($lang['wiimpaddrdesc'], '<a href="https://ts-n.net/lexicon.php?showid=97#lexindex" target="_blank">https://ts-n.net/lexicon.php?showid=97#lexindex</a>'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiimpemaildesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['imprint_email']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiimpemaildesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiimpphonedesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['imprint_phone']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiimpphonedesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiimpnotesdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['imprint_notes']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiimpnotesdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiimpprivacydesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['privacy-policy']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiimpprivacydesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="winxmsgdesc3" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['winxmsg3']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['winxmsgdesc3']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
</body>
</html>