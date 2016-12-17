<?PHP
session_start();

require_once('../other/config.php');

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
    $_SESSION = array();
    session_destroy();
	if($_SERVER['HTTPS'] == "on") {
		header("Location: https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	} else {
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	}
	exit;
}

if (!isset($_SESSION['username']) || $_SESSION['username'] != $webuser || $_SESSION['password'] != $webpass || $_SESSION['clientip'] != getclientip()) {
	if($_SERVER['HTTPS'] == "on") {
		header("Location: https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	} else {
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	}
	exit;
}

require_once('nav.php');

if (isset($_POST['update']) && $_SESSION['username'] == $webuser && $_SESSION['password'] == $webpass && $_SESSION['clientip'] == getclientip()) {
	$rankupmsg	= addslashes($_POST['rankupmsg']);
	$servernews	= addslashes($_POST['servernews']);
	$nextupinfomsg1	= addslashes($_POST['nextupinfomsg1']);
	$nextupinfomsg2	= addslashes($_POST['nextupinfomsg2']);
	$nextupinfomsg3	= addslashes($_POST['nextupinfomsg3']);
	$nextupinfo = $_POST['nextupinfo'];
	if (isset($_POST['msgtouser'])) $msgtouser = 1; else $msgtouser = 0;
	if ($mysqlcon->exec("UPDATE $dbname.config set msgtouser='$msgtouser',rankupmsg='$rankupmsg',servernews='$servernews',nextupinfo='$nextupinfo',nextupinfomsg1='$nextupinfomsg1',nextupinfomsg2='$nextupinfomsg2',nextupinfomsg3='$nextupinfomsg3'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc']." ".$lang['wisvres'];
		$err_lvl = NULL;
    }
	$rankupmsg	= $_POST['rankupmsg'];
	$servernews	= $_POST['servernews'];
	$nextupinfomsg1	= $_POST['nextupinfomsg1'];
	$nextupinfomsg2	= $_POST['nextupinfomsg2'];
	$nextupinfomsg3	= $_POST['nextupinfomsg3'];
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['wihlmsg']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" name="update" method="POST">
					<div class="row">
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wimsgusrdesc"><?php echo $lang['wimsgusr']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($msgtouser == 1) {
												echo '<input id="switch-animate" type="checkbox" checked data-size="mini" name="msgtouser" value="',$msgtouser,'">';
											} else {
												echo '<input id="switch-animate" type="checkbox" data-size="mini" name="msgtouser" value="',$msgtouser,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wimsgmsgdesc"><?php echo $lang['wimsgmsg']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="5" name="rankupmsg" maxlength="500"><?php echo $rankupmsg; ?></textarea>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wimsgsndesc"><?php echo $lang['wimsgsn']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
									<div class="col-sm-8">
										<textarea class="form-control" rows="15" name="servernews" maxlength="5000"><?php echo $servernews; ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxinfodesc"><?php echo $lang['winxinfo']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<select class="selectpicker show-tick form-control" id="basic" name="nextupinfo">
											<?PHP
											echo '<option value="0"'; if($nextupinfo=="0") echo " selected=selected"; echo '>',$lang['winxmode1'],'</option>';
											echo '<option value="1"'; if($nextupinfo=="1") echo " selected=selected"; echo '>',$lang['winxmode2'],'</option>';
											echo '<option value="2"'; if($nextupinfo=="2") echo " selected=selected"; echo '>',$lang['winxmode3'],'</option>';
											?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxmsgdesc1"><?php echo $lang['winxmsg1']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="5" name="nextupinfomsg1" maxlength="500"><?php echo $nextupinfomsg1; ?></textarea>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxmsgdesc2"><?php echo $lang['winxmsg2']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="5" name="nextupinfomsg2" maxlength="500"><?php echo $nextupinfomsg2; ?></textarea>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#winxmsgdesc3"><?php echo $lang['winxmsg3']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="5" name="nextupinfomsg3" maxlength="500"><?php echo $nextupinfomsg3; ?></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" name="update" class="btn btn-primary"><?php echo $lang['wisvconf']; ?></button>
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
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
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
        <?php echo $lang['wimsgmsgdesc']; ?>
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
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
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
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
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