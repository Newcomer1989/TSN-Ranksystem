<?PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if(in_array('sha512', hash_algos())) {
	ini_set('session.hash_function', 'sha512');
}
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	ini_set('session.cookie_secure', 1);
	if(!headers_sent()) {
		header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload;");
	}
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

if (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $cfg['webinterface_user'] || $_SESSION[$rspathhex.'password'] != $cfg['webinterface_pass'] || $_SESSION[$rspathhex.'clientip'] != getclientip()) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

require_once('nav.php');
$csrf_token = bin2hex(openssl_random_pseudo_bytes(32));

if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
	if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	// Convert groups to string
	$rankup_definition = "";
	if(isset($_POST['rankuptime']) && isset($_POST['rankupgroup'])) {
		$rankupgroups = [];
		foreach ($_POST['rankuptime'] as $key => $entry) {
			$servergroupId = isset($_POST["rankupgroup"][$key]) ? $_POST["rankupgroup"][$key] : 0;
			if (empty($servergroupId)) {
				$servergroupId = 0;
			}
			if (empty($entry)) {
				$entry = 0; 
			}
			$rankupgroups[] = "$entry=>$servergroupId";
		}
		$rankup_definition = implode(",", $rankupgroups);
	}
	
	if(empty($_POST['rankup_boost_definition'])) {
		$cfg['rankup_boost_definition'] = NULL;
	} else {
		foreach (explode(',', $_POST['rankup_boost_definition']) as $entry) {
			list($key, $value1, $value2) = explode('=>', $entry);
			$addnewvalue2[$key] = array("group"=>$key,"factor"=>$value1,"time"=>$value2);
			$cfg['rankup_boost_definition'] = $addnewvalue2;
		}
	}
	if(empty($rankup_definition)) {
		$grouparr = null;
	} else {
		foreach (explode(',', $rankup_definition) as $entry) {
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
		if(isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != NULL) {
			foreach($cfg['rankup_boost_definition'] as $groupid => $value) {
				if(!isset($groupslist[$groupid]) && $groupid != NULL) {
					$err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wiboost']).'<br>';
					$err_lvl = 3;
					$errcnf++;
				}
			}
		}
		foreach(array_flip(explode(',', $_POST['rankup_excepted_group_id_list'])) as $groupid => $value) {
			if(!isset($groupslist[$groupid]) && $groupid != NULL) {
				$err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wiexgrp']).'<br>';
				$err_lvl = 3;
				$errcnf++;
			}
		}
	}
	unset($groupslist);

	$cfg['rankup_time_assess_mode'] = $_POST['rankup_time_assess_mode'];
	$cfg['rankup_excepted_mode'] = $_POST['rankup_excepted_mode'];
	$cfg['rankup_excepted_unique_client_id_list'] = $_POST['rankup_excepted_unique_client_id_list'];
    $cfg['rankup_excepted_group_id_list'] = $_POST['rankup_excepted_group_id_list'];
    $cfg['rankup_excepted_channel_id_list'] = $_POST['rankup_excepted_channel_id_list'];
	$cfg['rankup_definition'] = $rankup_definition;
	$cfg['rankup_boost_definition'] = $_POST['rankup_boost_definition'];
	$cfg['rankup_ignore_idle_time']	= $_POST['rankup_ignore_idle_time'];
	
    if (isset($_POST['rankup_client_database_id_change_switch'])) $cfg['rankup_client_database_id_change_switch'] = 1; else $cfg['rankup_client_database_id_change_switch'] = 0;
	if (isset($_POST['rankup_clean_clients_switch'])) $cfg['rankup_clean_clients_switch'] = 1; else $cfg['rankup_clean_clients_switch'] = 0;
	$cfg['rankup_clean_clients_period'] = $_POST['rankup_clean_clients_period'];
	if($_POST['teamspeak_verification_channel_id'] == NULL) {
		$cfg['teamspeak_verification_channel_id'] = 0;
	} else {
		$cfg['teamspeak_verification_channel_id'] = $_POST['teamspeak_verification_channel_id'];
	}
	if($errcnf == 0) {
	if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_time_assess_mode','{$cfg['rankup_time_assess_mode']}'),('rankup_excepted_mode','{$cfg['rankup_excepted_mode']}'),('rankup_excepted_unique_client_id_list','{$cfg['rankup_excepted_unique_client_id_list']}'),('rankup_excepted_group_id_list','{$cfg['rankup_excepted_group_id_list']}'),('rankup_excepted_channel_id_list','{$cfg['rankup_excepted_channel_id_list']}'),('rankup_definition','{$cfg['rankup_definition']}'),('rankup_ignore_idle_time','{$cfg['rankup_ignore_idle_time']}'),('rankup_client_database_id_change_switch','{$cfg['rankup_client_database_id_change_switch']}'),('rankup_clean_clients_switch','{$cfg['rankup_clean_clients_switch']}'),('rankup_clean_clients_period','{$cfg['rankup_clean_clients_period']}'),('teamspeak_verification_channel_id','{$cfg['teamspeak_verification_channel_id']}'),('rankup_boost_definition','{$cfg['rankup_boost_definition']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
			$err_msg = print_r($mysqlcon->errorInfo(), true);
			$err_lvl = 3;
		} else {
			$err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button
			type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
			$err_lvl = NULL;
		}
	} else {
		$err_msg .= "<br>".$lang['errgrpid'];
	}
	$cfg['rankup_excepted_unique_client_id_list'] = array_flip(explode(',', $cfg['rankup_excepted_unique_client_id_list']));
	$cfg['rankup_excepted_group_id_list'] = array_flip(explode(',', $cfg['rankup_excepted_group_id_list']));
	$cfg['rankup_excepted_channel_id_list'] = array_flip(explode(',', $cfg['rankup_excepted_channel_id_list']));
	if(empty($rankup_definition)) {
		$cfg['rankup_definition'] = NULL;
	} else {
		$grouptimearr = explode(',', $rankup_definition);
		foreach ($grouptimearr as $entry) {
			list($key, $value) = explode('=>', $entry);
			$addnewvalue1[$key] = $value;
			$cfg['rankup_definition'] = $addnewvalue1;
		}
	}
	if(empty($_POST['rankup_boost_definition'])) {
		$cfg['rankup_boost_definition'] = NULL;
	} else {
		foreach (explode(',', $_POST['rankup_boost_definition']) as $entry) {
			list($key, $value1, $value2) = explode('=>', $entry);
			$addnewvalue2[$key] = array("group"=>$key,"factor"=>$value1,"time"=>$value2);
			$cfg['rankup_boost_definition'] = $addnewvalue2;
		}
	}
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
						<h1 class="page-header"><?php echo $lang['winav3'],' ',$lang['wihlset']; ?></h1>
					</div>
				</div>
				<form class="form-horizontal" data-toggle="validator" name="update" method="POST">
				<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wisupidledesc"><?php echo $lang['wisupidle']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control basic" name="rankup_time_assess_mode">
									<?PHP
									echo '<option value="0"'; if($cfg['rankup_time_assess_mode']=="0") echo " selected=selected"; echo '>',$lang['wishcolot'],'</option>';
									echo '<option value="1"'; if($cfg['rankup_time_assess_mode']=="1") echo " selected=selected"; echo '>',$lang['wishcolat'],'</option>';
									?>
									</select>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexresdesc"><?php echo $lang['wiexres']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<select class="selectpicker show-tick form-control basic" name="rankup_excepted_mode">
											<?PHP
											echo '<option value="0"'; if($cfg['rankup_excepted_mode']=="0") echo " selected=selected"; echo '>',$lang['wiexres1'],'</option>';
											echo '<option value="1"'; if($cfg['rankup_excepted_mode']=="1") echo " selected=selected"; echo '>',$lang['wiexres2'],'</option>';
											echo '<option value="2"'; if($cfg['rankup_excepted_mode']=="2") echo " selected=selected"; echo '>',$lang['wiexres3'],'</option>';
											?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexuiddesc"><?php echo $lang['wiexuid']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" data-pattern="^([A-Za-z0-9\\\/\+]{27}=,)*([A-Za-z0-9\\\/\+]{27}=)$" data-error="Check all unique IDs are correct and your list do not ends with a comma!" rows="1" name="rankup_excepted_unique_client_id_list" maxlength="65535"><?php if(!empty($cfg['rankup_excepted_unique_client_id_list'])) echo implode(',',array_flip($cfg['rankup_excepted_unique_client_id_list'])); ?></textarea>
											<div class="help-block with-errors"></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexgrpdesc"><?php echo $lang['wiexgrp']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" data-pattern="^([0-9]{1,9},)*[0-9]{1,9}$" data-error="Only use digits separated with a comma! Also must the first and last value be digit!" rows="1" name="rankup_excepted_group_id_list" maxlength="65535"><?php if(!empty($cfg['rankup_excepted_group_id_list'])) echo implode(',',array_flip($cfg['rankup_excepted_group_id_list'])); ?></textarea>
											<div class="help-block with-errors"></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiexciddesc"><?php echo $lang['wiexcid']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<textarea class="form-control" data-pattern="^([0-9]{1,9},)*[0-9]{1,9}$" data-error="Only use digits separated with a comma! Also must the first and last value be digit!" rows="1" name="rankup_excepted_channel_id_list" maxlength="65535"><?php if(!empty($cfg['rankup_excepted_channel_id_list'])) echo implode(',',array_flip($cfg['rankup_excepted_channel_id_list'])); ?></textarea>
											<div class="help-block with-errors"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group required-field-block">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wigrptimedesc"><?php echo $lang['wigrptime']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<div class="row">
										<div class="col-sm-8">
											<b>Time in Seconds</b>
										</div>
										<div class="col-sm-4">
											<b>Servergroup ID</b>
										</div>
									</div>
									<?php
										foreach ($cfg['rankup_definition'] as $time => $sgroup) {
											echo '
											<div name="rankupgroup" class="row" style="margin-top: 5px;">
												<div class="col-sm-8">
													<input type="text" class="form-control rankuptime" name="rankuptime[]" value="'.$time.'">
												</div>
												<div class="col-sm-4">
													<input type="text" class="form-control" name="rankupgroup[]" value="'.$sgroup.'">
												</div>
											</div>
											';
										} 
									?>
									<button type="button" class="btn btn-primary" id="addrankupgroup" style="margin-top: 5px;">Add</button>
									<script>
										$(".rankuptime").TouchSpin({
											min: 0,
											max: 999999999,
											verticalbuttons: true,
											prefix: 'Sec.:'
										});

										$("#addrankupgroup").click(function(){
											var elm = $("div[name='rankupgroup']").first().clone();
											elm.find("input[type=text], textarea").val("0");
											elm.insertBefore("#addrankupgroup");
										});
									</script>
									<!-- </textarea> -->
									<div class="help-block with-errors"></div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiignidledesc"><?php echo $lang['wiignidle']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="rankup_ignore_idle_time" value="<?php echo $cfg['rankup_ignore_idle_time']; ?>">
									<script>
									$("input[name='rankup_ignore_idle_time']").TouchSpin({
										min: 0,
										max: 65535,
										verticalbuttons: true,
										prefix: 'Sec.:'
									});
									</script>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wichdbiddesc"><?php echo $lang['wichdbid']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-lg-8">
									<?PHP if ($cfg['rankup_client_database_id_change_switch'] == 1) {
											echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="rankup_client_database_id_change_switch" value="',$cfg['rankup_client_database_id_change_switch'],'">';
										} else {
											echo '<input class="switch-animate" type="checkbox" data-size="mini" name="rankup_client_database_id_change_switch" value="',$cfg['rankup_client_database_id_change_switch'],'">';
										} ?>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#cleancdesc"><?php echo $lang['cleanc']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
										<?PHP if ($cfg['rankup_clean_clients_switch'] == 1) {
											echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="rankup_clean_clients_switch" value="',$cfg['rankup_clean_clients_switch'],'">';
										} else {
											echo '<input class="switch-animate" type="checkbox" data-size="mini" name="rankup_clean_clients_switch" value="',$cfg['rankup_clean_clients_switch'],'">';
										} ?>
										</div>
									</div>
									<div class="row">&nbsp;</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#cleanpdesc"><?php echo $lang['cleanp']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" name="rankup_clean_clients_period" value="<?php echo $cfg['rankup_clean_clients_period']; ?>">
											<script>
											$("input[name='rankup_clean_clients_period']").TouchSpin({
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
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiboostdesc"><?php echo $lang['wiboost']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="col-sm-8">
									<textarea class="form-control" data-pattern="^([1-9][0-9]{0,9}=>[0-9]{0,9}\.?[0-9]+=>[1-9][0-9]{0,9},)*[1-9][0-9]{0,9}=>[0-9]{0,9}\.?[0-9]+=>[1-9][0-9]{0,9}$" data-error="Wrong definition, please look at description for more details. No comma at ending!" rows="5" name="rankup_boost_definition" maxlength="65535"><?php
									$implode_boost = '';
									if(isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != NULL) {
										foreach ($cfg['rankup_boost_definition'] as $r) {
											$implode_boost .= $r['group']."=>".$r['factor']."=>".$r['time'].",";
										}
										$implode_boost = substr($implode_boost, 0, -1);
									}
									echo $implode_boost;
									?></textarea>
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
	    <?php echo sprintf($lang['cleancdesc'], '<a href="https://ts-n.net/clientcleaner.php" target="_blank">https://ts-n.net/clientcleaner.php</a>'); ?>
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