<?PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if(in_array('sha512', hash_algos())) {
	ini_set('session.hash_function', 'sha512');
}
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	ini_set('session.cookie_secure', 1);
	header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload;");
}
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
    rem_session_ts3($rspathhex);
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

if (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $webuser || $_SESSION[$rspathhex.'password'] != $webpass || $_SESSION[$rspathhex.'clientip'] != getclientip()) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

if (isset($_POST['update']) && $_POST['csrf_token'] != $_SESSION[$rspathhex.'csrf_token']) {
	echo $lang['errcsrf'];
	rem_session_ts3($rspathhex);
	exit;
}

require_once('nav.php');

if (isset($_POST['update']) && $_SESSION[$rspathhex.'username'] == $webuser && $_SESSION[$rspathhex.'password'] == $webpass && $_SESSION[$rspathhex.'clientip'] == getclientip() && $_POST['csrf_token'] == $_SESSION[$rspathhex.'csrf_token']) {

	if (isset($_POST['showexcld'])) $showexcld = 1; else $showexcld = 0;
	if (isset($_POST['showcolrg'])) $showcolrg = 1; else $showcolrg = 0;
	if (isset($_POST['showcolcld'])) $showcolcld = 1; else $showcolcld = 0;
	if (isset($_POST['showcoluuid'])) $showcoluuid = 1; else $showcoluuid = 0;
	if (isset($_POST['showcoldbid'])) $showcoldbid = 1; else $showcoldbid = 0;
	if (isset($_POST['showcolls'])) $showcolls = 1; else $showcolls = 0;
	if (isset($_POST['showcolot'])) $showcolot = 1; else $showcolot = 0;
	if (isset($_POST['showcolit'])) $showcolit = 1; else $showcolit = 0;
	if (isset($_POST['showcolat'])) $showcolat = 1; else $showcolat = 0;
	if (isset($_POST['showcolas'])) $showcolas = 1; else $showcolas = 0;
	if (isset($_POST['showcolnx'])) $showcolnx = 1; else $showcolnx = 0;
	if (isset($_POST['showcolsg'])) $showcolsg = 1; else $showcolsg = 0;
	if (isset($_POST['showhighest'])) $showhighest = 1; else $showhighest = 0;
	if (isset($_POST['showgrpsince'])) $showgrpsince = 1; else $showgrpsince = 0;
	if (isset($_POST['shownav'])) $shownav = 1; else $shownav = 0;
	if ($mysqlcon->exec("UPDATE `$dbname`.`config` SET `showexcld`='$showexcld',`showcolrg`='$showcolrg',`showcolcld`='$showcolcld',`showcoluuid`='$showcoluuid',`showcoldbid`='$showcoldbid',`showcolls`='$showcolls',`showcolot`='$showcolot',`showcolit`='$showcolit',`showcolat`='$showcolat',`showcolas`='$showcolas',`showcolnx`='$showcolnx',`showcolsg`='$showcolsg',`showhighest`='$showhighest',`showgrpsince`='$showgrpsince',`shownav`='$shownav'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc'];
		$err_lvl = NULL;
    }
}

$_SESSION[$rspathhex.'csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['wihlsty']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" name="update" method="POST">
				<input type="hidden" name="csrf_token" value="<?PHP echo $_SESSION[$rspathhex.'csrf_token']; ?>">
					<div class="row">
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolrgdesc"><?php echo $lang['wishcolrg']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolrg == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolrg" value="',$showcolrg,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolrg" value="',$showcolrg,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolclddesc"><?php echo $lang['wishcolcld']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolcld == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolcld" value="',$showcolcld,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolcld" value="',$showcolcld,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoluuiddesc"><?php echo $lang['wishcoluuid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcoluuid == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcoluuid" value="',$showcoluuid,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcoluuid" value="',$showcoluuid,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldbiddesc"><?php echo $lang['wishcoldbid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcoldbid == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcoldbid" value="',$showcoldbid,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcoldbid" value="',$showcoldbid,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcollsdesc"><?php echo $lang['wishcolls']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolls == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolls" value="',$showcolls,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolls" value="',$showcolls,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolotdesc"><?php echo $lang['wishcolot']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolot == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolot" value="',$showcolot,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolot" value="',$showcolot,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolitdesc"><?php echo $lang['wishcolit']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolit == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolit" value="',$showcolit,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolit" value="',$showcolit,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolatdesc"><?php echo $lang['wishcolat']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolat == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolat" value="',$showcolat,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolat" value="',$showcolat,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolasdesc"><?php echo $lang['wishcolas']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolas == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolas" value="',$showcolas,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolas" value="',$showcolas,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolgsdesc"><?php echo $lang['wishcolgs']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showgrpsince == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showgrpsince" value="',$showgrpsince,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showgrpsince" value="',$showgrpsince,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolnxdesc"><?php echo $lang['wishcolnx']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolnx == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolnx" value="',$showcolnx,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolnx" value="',$showcolnx,'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolsgdesc"><?php echo $lang['wishcolsg']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($showcolsg == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showcolsg" value="',$showcolsg,'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showcolsg" value="',$showcolsg,'">';
											} ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishexclddesc"><?php echo $lang['wishexcld']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<?PHP if ($showexcld == 1) {
										echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showexcld" value="',$showexcld,'">';
									} else {
										echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showexcld" value="',$showexcld,'">';
									} ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishhiclddesc"><?php echo $lang['wishhicld']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<?PHP if ($showhighest == 1) {
										echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="showhighest" value="',$showhighest,'">';
									} else {
										echo '<input class="switch-animate" type="checkbox" data-size="mini" name="showhighest" value="',$showhighest,'">';
									} ?>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishnavdesc"><?php echo $lang['wishnav']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<?PHP if ($shownav == 1) {
										echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="shownav" value="',$shownav,'">';
									} else {
										echo '<input class="switch-animate" type="checkbox" data-size="mini" name="shownav" value="',$shownav,'">';
									} ?>
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
	
<div class="modal fade" id="wishexclddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishexcld']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishexclddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolrgdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolrg']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolrgdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolclddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolcld']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolclddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcoluuiddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcoluuid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcoluuiddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcoldbiddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcoldbid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcoldbiddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcollsdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolls']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcollsdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolotdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolot']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolotdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolitdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolit']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolitdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolatdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolat']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolatdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolasdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolas']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolasdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolgsdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolgs']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolgsdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolnxdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolnx']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolnxdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishcolsgdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishcolsg']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishcolsgdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wishhiclddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wishhicld']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wishhiclddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
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
</body>
</html>
