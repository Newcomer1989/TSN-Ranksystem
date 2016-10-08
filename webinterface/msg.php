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
	if (isset($_POST['msgtouser'])) $msgtouser = 1; else $msgtouser = 0;
	if ($mysqlcon->exec("UPDATE $dbname.config set msgtouser='$msgtouser',rankupmsg='$rankupmsg',servernews='$servernews'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo());
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc']." ".$lang['wisvres'];
		$err_lvl = NULL;
    }
	$rankupmsg	= $_POST['rankupmsg'];
	$servernews	= $_POST['servernews'];
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

								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wimsgsndesc"><?php echo $lang['wimsgsn']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows="15" name="servernews"><?php echo $servernews; ?></textarea>
										</div>
									</div>
								</div>

						</div>
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
											<textarea class="form-control" rows="5" name="rankupmsg"><?php echo $rankupmsg; ?></textarea>
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
</div><div class="modal fade" id="wimsgsndesc" tabindex="-1">
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