<?php
require_once '_preload.php';

try {
    require_once '_nav.php';

    if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
        $err_lvl = 3;
    }

    if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC)) === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
        $err_lvl = 3;
    }

    if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
        $cfg['rankup_message_to_user'] = addslashes($_POST['rankup_message_to_user']);
        $cfg['rankup_next_message_1'] = addslashes($_POST['rankup_next_message_1']);
        $cfg['rankup_next_message_2'] = addslashes($_POST['rankup_next_message_2']);
        $cfg['rankup_next_message_3'] = addslashes($_POST['rankup_next_message_3']);
        $cfg['rankup_next_message_mode'] = $_POST['rankup_next_message_mode'];
        if (isset($_POST['rankup_message_to_user_switch'])) {
            $cfg['rankup_message_to_user_switch'] = 1;
        } else {
            $cfg['rankup_message_to_user_switch'] = 0;
        }
        if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_message_to_user_switch','{$cfg['rankup_message_to_user_switch']}'),('rankup_message_to_user','{$cfg['rankup_message_to_user']}'),('rankup_next_message_mode','{$cfg['rankup_next_message_mode']}'),('rankup_next_message_1','{$cfg['rankup_next_message_1']}'),('rankup_next_message_2','{$cfg['rankup_next_message_2']}'),('rankup_next_message_3','{$cfg['rankup_next_message_3']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
            $err_msg = print_r($mysqlcon->errorInfo(), true);
            $err_lvl = 3;
        } else {
            $err_msg = $lang['wisvsuc'].' '.sprintf($lang['wisvres'], '<span class="item-margin"><form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button
			type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i><span class="item-margin">'.$lang['wibot7'].'</span></button></form></span>');
            $err_lvl = null;
        }
        $cfg['rankup_message_to_user'] = $_POST['rankup_message_to_user'];
        $cfg['rankup_next_message_1'] = $_POST['rankup_next_message_1'];
        $cfg['rankup_next_message_2'] = $_POST['rankup_next_message_2'];
        $cfg['rankup_next_message_3'] = $_POST['rankup_next_message_3'];
    } elseif (isset($_POST['update'])) {
        echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
        rem_session_ts3();
        exit;
    }
    ?>
			<div id="page-wrapper" class="webinterface_msg">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['winav5'],' ',$lang['wihlset']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
						<div class="row">
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wimsgusrdesc"><?php echo $lang['wimsgusr']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?php if ($cfg['rankup_message_to_user_switch'] == 1) {
												    echo '<input id="switch-animate" type="checkbox" checked data-size="mini" name="rankup_message_to_user_switch" value="',$cfg['rankup_message_to_user_switch'],'">';
												} else {
												    echo '<input id="switch-animate" type="checkbox" data-size="mini" name="rankup_message_to_user_switch" value="',$cfg['rankup_message_to_user_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wimsgmsgdesc"><?php echo $lang['wimsgmsg']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<textarea class="form-control" rows="5" name="rankup_message_to_user" maxlength="21588"><?php echo $cfg['rankup_message_to_user']; ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxinfodesc"><?php echo $lang['winxinfo']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control" id="basic" name="rankup_next_message_mode">
												<?php
												echo '<option data-icon="fas fa-ban" value="0"';
    if ($cfg['rankup_next_message_mode'] == '0') {
        echo ' selected=selected';
    } echo '><span class="item-margin">',$lang['winxmode1'],'</span></option>';
    echo '<option data-icon="fas fa-clipboard-check" value="1"';
    if ($cfg['rankup_next_message_mode'] == '1') {
        echo ' selected=selected';
    } echo '><span class="item-margin">',$lang['winxmode2'],'</span></option>';
    echo '<option data-icon="fas fa-clipboard-list" value="2"';
    if ($cfg['rankup_next_message_mode'] == '2') {
        echo ' selected=selected';
    } echo '><span class="item-margin">',$lang['winxmode3'],'</span></option>';
    ?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxmsgdesc1"><?php echo $lang['winxmsg1']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<textarea class="form-control" rows="5" name="rankup_next_message_1" maxlength="21588"><?php echo $cfg['rankup_next_message_1']; ?></textarea>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxmsgdesc2"><?php echo $lang['winxmsg2']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<textarea class="form-control" rows="5" name="rankup_next_message_2" maxlength="21588"><?php echo $cfg['rankup_next_message_2']; ?></textarea>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxmsgdesc3"><?php echo $lang['winxmsg3']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<textarea class="form-control" rows="5" name="rankup_next_message_3" maxlength="21588"><?php echo $cfg['rankup_next_message_3']; ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i><span class="item-margin"><?php echo $lang['wisvconf']; ?></span></button>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</form>
				</div>
			</div>
		</div>
		
	<div class="modal fade" id="wimsgusrdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wimsgusr']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wimsgusrdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wimsgmsgdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wimsgmsg']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo sprintf($lang['wimsgmsgdesc'], '<a href="https://ts-n.net/lexicon.php?showid=97#lexindex" target="_blank">https://ts-n.net/lexicon.php?showid=97#lexindex</a>'); ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="winxinfodesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['winxinfo']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['winxinfodesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="winxmsgdesc1" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['winxmsg1']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['winxmsgdesc1']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="winxmsgdesc2" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['winxmsg2']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['winxmsgdesc2']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
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
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<script>
	$("[name='rankup_message_to_user_switch']").bootstrapSwitch();
	</script>
	</body>
	</html>
	<?php
} catch(Throwable $ex) {
}
?>