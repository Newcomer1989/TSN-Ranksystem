<?PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if(in_array('sha512', hash_algos())) {
	ini_set('session.hash_function', 'sha512');
}
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	ini_set('session.cookie_secure', 1);
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
$newcsrf = bin2hex(openssl_random_pseudo_bytes(32));

if (isset($_POST['update']) && $_SESSION[$rspathhex.'username'] == $webuser && $_SESSION[$rspathhex.'password'] == $webpass && $_SESSION[$rspathhex.'clientip'] == getclientip() && $_POST['csrf_token'] == $_SESSION[$rspathhex.'csrf_token']) {
	
	if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		enter_logfile($logpath,$timezone,1,"Select on DB failed for group check: ".print_r($mysqlcon->errorInfo(), true));
	}
	
	if(empty($_POST['boost'])) {
		$boostarr = null;
	} else {
		$boostarr = null;
		foreach (explode(',', $_POST['boost']) as $entry) {
			list($key, $value1, $value2) = explode('=>', $entry);
			$boostarr[$key] = array("group"=>$key,"factor"=>$value1,"time"=>$value2);
		}
	}
	if(empty($_POST['grouptime'])) {
		$grouparr = null;
	} else {
		foreach (explode(',', $_POST['grouptime']) as $entry) {
			list($time, $groupid) = explode('=>', $entry);
			$grouparr[$groupid] = $time;
		}
	}

	$err_msg = '';
	$errcnf = 0;
	if(isset($groupslist) && $groupslist != NULL) {
		foreach($grouparr as $groupid => $time) {
			if(!isset($groupslist[$groupid]) && $groupid != NULL) {
				$err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wigrptime']).'<br>';
				$err_lvl = 3;
				$errcnf++;
			}
		}
		foreach($boostarr as $groupid => $value) {
			if(!isset($groupslist[$groupid]) && $groupid != NULL) {
				$err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wiboost']).'<br>';
				$err_lvl = 3;
				$errcnf++;
			}
		}
		foreach(array_flip(explode(',', $_POST['exceptgroup'])) as $groupid => $value) {
			if(!isset($groupslist[$groupid]) && $groupid != NULL) {
				$err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wiexgrp']).'<br>';
				$err_lvl = 3;
				$errcnf++;
			}
		}
	}
	unset($groupslist);
	
	
	$substridle		= $_POST['substridle'];
	$exceptuuid 	= $_POST['exceptuuid'];
    $exceptgroup	= $_POST['exceptgroup'];
    $exceptcid		= $_POST['exceptcid'];
	$grouptime		= $_POST['grouptime'];
	$ignoreidle		= $_POST['ignoreidle'];
	$resetexcept	= $_POST['resetexcept'];
    if (isset($_POST['resetbydbchange'])) $resetbydbchange = 1; else $resetbydbchange = 0;
	if (isset($_POST['cleanclients'])) $cleanclients = 1; else $cleanclients = 0;
	$cleanperiod 	= $_POST['cleanperiod'];
    $boost          = $_POST['boost'];
	if($_POST['registercid'] == NULL) {
		$registercid = 0;
	} else {
		$registercid = $_POST['registercid'];
	}
	if($errcnf == 0) {
		if ($mysqlcon->exec("UPDATE `$dbname`.`config` SET `substridle`='$substridle',`exceptuuid`='$exceptuuid',`exceptgroup`='$exceptgroup',`exceptcid`='$exceptcid',`grouptime`='$grouptime',`ignoreidle`='$ignoreidle',`resetbydbchange`='$resetbydbchange',`cleanclients`='$cleanclients',`cleanperiod`='$cleanperiod',`boost`='$boost',`resetexcept`='$resetexcept',`registercid`='$registercid'") === false) {
			$err_msg = print_r($mysqlcon->errorInfo(), true);
			$err_lvl = 3;
		} else {
			$err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$newcsrf.'"><button
			type="submit" class="btn btn-primary" name="restart"><i class="fa fa-fw fa-refresh"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
			$err_lvl = NULL;
		}
	} else {
		$err_msg .= "<br>".$lang['errgrpid'];
	}
	$config['grouptime']	= $_POST['grouptime'];
	$config['exceptuuid'] 	= $_POST['exceptuuid'];
    $config['exceptgroup']	= $_POST['exceptgroup'];
    $config['exceptcid']	= $_POST['exceptcid'];
	$config['boost']		= $_POST['boost'];
}

$_SESSION[$rspathhex.'csrf_token'] = $newcsrf;
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header"><?php echo $lang['wihlcfg']; ?></h1>
					</div>
				</div>
				<form class="form-horizontal" data-toggle="validator" name="update" method="POST">
				<input type="hidden" name="csrf_token" value="<?PHP echo $_SESSION[$rspathhex.'csrf_token']; ?>">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wisupidledesc"><?php echo $lang['wisupidle']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control" id="basic" name="substridle">
									<?PHP
									echo '<option value="0"'; if($substridle=="0") echo " selected=selected"; echo '>',$lang['wishcolot'],'</option>';
									echo '<option value="1"'; if($substridle=="1") echo " selected=selected"; echo '>',$lang['wishcolat'],'</option>';
									?>
									</select>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexresdesc"><?php echo $lang['wiexres']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<select class="selectpicker show-tick form-control" id="basic" name="resetexcept">
											<?PHP
											echo '<option value="0"'; if($resetexcept=="0") echo " selected=selected"; echo '>',$lang['wiexres1'],'</option>';
											echo '<option value="1"'; if($resetexcept=="1") echo " selected=selected"; echo '>',$lang['wiexres2'],'</option>';
											echo '<option value="2"'; if($resetexcept=="2") echo " selected=selected"; echo '>',$lang['wiexres3'],'</option>';
											?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexuiddesc"><?php echo $lang['wiexuid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" data-pattern="^([A-Za-z0-9\\\/\+]{27}=,)*([A-Za-z0-9\\\/\+]{27}=)$" data-error="Check all unique IDs are correct and your list do not ends with a comma!" rows="1" name="exceptuuid" maxlength="999"><?php echo $config['exceptuuid']; ?></textarea>
											<div class="help-block with-errors"></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexgrpdesc"><?php echo $lang['wiexgrp']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" data-pattern="^([0-9]{1,9},)*[0-9]{1,9}$" data-error="Only use digits separated with a comma! Also must the first and last value be digit!" rows="1" name="exceptgroup" maxlength="999"><?php echo $config['exceptgroup']; ?></textarea>
											<div class="help-block with-errors"></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexciddesc"><?php echo $lang['wiexcid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" data-pattern="^([0-9]{1,9},)*[0-9]{1,9}$" data-error="Only use digits separated with a comma! Also must the first and last value be digit!" rows="1" name="exceptcid" maxlength="999"><?php echo $config['exceptcid']; ?></textarea>
											<div class="help-block with-errors"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group required-field-block">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wigrptimedesc"><?php echo $lang['wigrptime']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<textarea class="form-control" data-pattern="^([0-9]{1,9}=>[0-9]{1,9},)*[0-9]{1,9}=>[0-9]{1,9}$" data-error="Wrong definition, please look at description for more details. No comma at ending!" rows="5" name="grouptime" maxlength="5000" required><?php echo $config['grouptime']; ?></textarea>
									<div class="required-icon"><div class="text">*</div></div>
									<div class="help-block with-errors"></div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiignidledesc"><?php echo $lang['wiignidle']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="ignoreidle" value="<?php echo $ignoreidle; ?>">
									<script>
									$("input[name='ignoreidle']").TouchSpin({
										min: 0,
										max: 65535,
										verticalbuttons: true,
										prefix: 'Sec.:'
									});
									</script>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wichdbiddesc"><?php echo $lang['wichdbid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-lg-8">
									<?PHP if ($resetbydbchange == 1) {
											echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="resetbydbchange" value="',$resetbydbchange,'">';
										} else {
											echo '<input class="switch-animate" type="checkbox" data-size="mini" name="resetbydbchange" value="',$resetbydbchange,'">';
										} ?>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#cleancdesc"><?php echo $lang['cleanc']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
										<?PHP if ($cleanclients == 1) {
											echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="cleanclients" value="',$cleanclients,'">';
										} else {
											echo '<input class="switch-animate" type="checkbox" data-size="mini" name="cleanclients" value="',$cleanclients,'">';
										} ?>
										</div>
									</div>
									<div class="row">&nbsp;</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#cleanpdesc"><?php echo $lang['cleanp']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="cleanperiod" value="<?php echo $cleanperiod; ?>">
											<script>
											$("input[name='cleanperiod']").TouchSpin({
												min: 0,
												max: 9223372036854775807,
												verticalbuttons: true,
												prefix: 'Sec.:'
											});
											</script>
										</div>
									</div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiverifydesc"><?php echo $lang['wiverify']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="registercid" value="<?php echo $registercid; ?>">
									<script>
									$("input[name='registercid']").TouchSpin({
										min: 0,
										max: 16777215,
										verticalbuttons: true,
										prefix: 'ID:'
									});
									</script>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiboostdesc"><?php echo $lang['wiboost']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<textarea class="form-control" data-pattern="^([1-9][0-9]{0,9}=>[0-9]{0,9}=>[1-9][0-9]{0,9},)*[1-9][0-9]{0,9}=>[0-9]{0,9}=>[1-9][0-9]{0,9}$" data-error="Wrong definition, please look at description for more details. No comma at ending!" rows="5" name="boost" maxlength="999"><?php echo $config['boost']; ?></textarea>
									<div class="help-block with-errors"></div>
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
	
<div class="modal fade" id="wisupidledesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wisupidle']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wisupidledesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiexresdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiexres']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiexresdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiexuiddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiexuid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiexuiddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiexgrpdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiexgrp']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiexgrpdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiexciddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiexcid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiexciddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wigrptimedesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wigrptime']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wigrptimedesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiignidledesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiignidle']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiignidledesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wichdbiddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wichdbid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wichdbiddesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cleancdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['cleanc']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['cleancdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cleanpdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['cleanp']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['cleanpdesc']; ?>
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
<div class="modal fade" id="wiboostdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiboost']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiboostdesc']; ?>
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