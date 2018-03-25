<?PHP
session_start();

require_once('../other/config.php');
require_once('../other/csrf_handler.php');

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
    rem_session_ts3($rspathhex);
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

if (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $webuser || $_SESSION[$rspathhex.'password'] != $webpass || $_SESSION[$rspathhex.'clientip'] != getclientip()) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

require_once('nav.php');

if (isset($_POST['update']) && $_SESSION[$rspathhex.'username'] == $webuser && $_SESSION[$rspathhex.'password'] == $webpass && $_SESSION[$rspathhex.'clientip'] == getclientip()) {
	$tshost     = $_POST['tshost'];
    $tsquery    = $_POST['tsquery'];
    $tsvoice    = $_POST['tsvoice'];
    $tsuser     = $_POST['tsuser'];
    $tspass     = $_POST['tspass'];
    $queryname  = $_POST['queryname'];
    $queryname2 = $_POST['queryname2'];
    $defchid 	= $_POST['defchid'];
	$slowmode 	= $_POST['slowmode'];
	$avatar_delay= $_POST['avatar_delay'];
    if ($mysqlcon->exec("UPDATE $dbname.config set tshost='$tshost',tsquery='$tsquery',tsvoice='$tsvoice',tsuser='$tsuser',tspass='$tspass',queryname='$queryname',queryname2='$queryname2',slowmode='$slowmode',defchid='$defchid',avatar_delay='$avatar_delay'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><button
		type="submit" class="btn btn-primary" name="restart"><i class="fa fa-fw fa-refresh"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
		$err_lvl = NULL;
    }
	$ts['host']		= $_POST['tshost'];
	$ts['query']	= $_POST['tsquery'];
	$ts['voice']	= $_POST['tsvoice'];
	$ts['user']		= $_POST['tsuser'];
	$ts['pass']		= $_POST['tspass'];
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['wihlts']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" data-toggle="validator" name="update" method="POST">
					<?php echo $CSRF; ?>
					<div class="row">
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group required-field-block">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3hostdesc"><?php echo $lang['wits3host']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" data-pattern="^[^.]+[^:]*$" data-error="Do not enter the port inside this field. You should enter the port (e.g. 9987) inside the TS3-Voice-Port!" name="tshost" value="<?php echo $ts['host']; ?>" maxlength="64" required>
											<div class="required-icon"><div class="text">*</div></div>
											<div class="help-block with-errors"></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3querydesc"><?php echo $lang['wits3query']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8 required-field-block-spin">
											<input type="text" class="form-control" name="tsquery" value="<?php echo $ts['query']; ?>" required>
											<script>
											$("input[name='tsquery']").TouchSpin({
												min: 0,
												max: 65535,
												verticalbuttons: true,
												prefix: 'TCP:'
											});
											</script>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3voicedesc"><?php echo $lang['wits3voice']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8 required-field-block-spin">
											<input type="text" class="form-control" name="tsvoice" value="<?php echo $ts['voice']; ?>" required>
											<script>
											$("input[name='tsvoice']").TouchSpin({
												min: 0,
												max: 65535,
												verticalbuttons: true,
												prefix: 'UDP:'
											});
											</script>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3querusrdesc"><?php echo $lang['wits3querusr']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control" name="tsuser" value="<?php echo $ts['user']; ?>" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3querpwdesc"><?php echo $lang['wits3querpw']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8 required-field-block">
											<input type="password" class="form-control" name="tspass" id="tspass" value="<?php echo $ts['pass']; ?>" data-toggle="password" data-placement="before" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 ">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3qnmdesc"><?php echo $lang['wits3qnm']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control" name="queryname" value="<?php echo $queryname; ?>" maxlength="30" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3qnm2desc"><?php echo $lang['wits3qnm2']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control" name="queryname2" value="<?php echo $queryname2; ?>" maxlength="30" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3dchdesc"><?php echo $lang['wits3dch']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="defchid" value="<?php echo $defchid; ?>">
									<script>
									$("input[name='defchid']").TouchSpin({
										min: 0,
										max: 2147483647,
										verticalbuttons: true,
										prefix: 'ID:'
									});
									</script>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3smdesc"><?php echo $lang['wits3sm']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control" id="basic" name="slowmode">
									<?PHP
									echo '<option data-subtext="[recommended]" value="0"'; if($slowmode=="0") echo ' selected="selected"'; echo '>disabled (Realtime)</option>';
									echo '<option data-divider="true">&nbsp;</option>';
									echo '<option data-subtext="(0,2 seconds)" value="200000"'; if($slowmode=="200000") echo ' selected="selected"'; echo '>Low delay</option>';
									echo '<option data-subtext="(0,5 seconds)" value="500000"'; if($slowmode=="500000") echo ' selected="selected"'; echo '>Middle delay</option>';
									echo '<option data-subtext="(1,0 seconds)" value="1000000"'; if($slowmode=="1000000") echo ' selected="selected"'; echo '>High delay</option>';
									echo '<option data-subtext="(2,0 seconds)" value="2000000"'; if($slowmode=="2000000") echo ' selected="selected"'; echo '>Huge delay</option>';
									echo '<option data-subtext="(5,0 seconds)" value="5000000"'; if($slowmode=="5000000") echo ' selected="selected"'; echo '>Ultra delay</option>';
									?>
									</select>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wits3avatdesc"><?php echo $lang['wits3avat']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="avatar_delay" value="<?php echo $avatar_delay; ?>">
									<script>
									$("input[name='avatar_delay']").TouchSpin({
										min: 0,
										max: 65535,
										verticalbuttons: true,
										prefix: 'Sec.:'
									});
									</script>
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
				</form>
			</div>
		</div>
	</div>
<div class="modal fade" id="wits3hostdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3host']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3hostdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3querydesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3query']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3querydesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3voicedesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3voice']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3voicedesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3querusrdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3querusr']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3querusrdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3querpwdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3querpw']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3querpwdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3qnmdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3qnm']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3qnmdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3qnm2desc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3qnm2']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3qnm2desc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3dchdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3dch']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3dchdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3smdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3sm']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3smdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wits3avatdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wits3avat']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wits3avatdesc']; ?>
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