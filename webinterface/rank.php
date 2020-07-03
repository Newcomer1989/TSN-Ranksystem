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

if(($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups` ORDER BY `sortid`,`sgidname` ASC")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
	$err_msg = print_r($mysqlcon->errorInfo(), true);
	$err_lvl = 3;
}

if(!isset($groupslist) || $groupslist == NULL) {
	$err_msg = '<b>No servergroups found inside the Ranksystem cache!</b><br><br>Please connect the Ranksystem Bot to the TS server. The Ranksystem will download the servergroups when it is connected to the server.<br>Give it a few minutes and reload this page. The dropdown field should contain your groups after.';
	$err_lvl = 1;
}

if (isset($_POST['update_old']) && isset($db_csrf[$_POST['csrf_token']])) {
	if(empty($_POST['rankup_definition'])) {
		$grouparr_old = null;
	} else {
		foreach (explode(',', $_POST['rankup_definition']) as $entry) {
			list($time, $groupid) = explode('=>', $entry);
			$grouparr_old[$groupid] = $time;
		}
	}
	
	if(isset($groupslist) && $groupslist != NULL) {
		foreach($grouparr_old as $groupid => $time) {
			if(!isset($groupslist[$groupid]) && $groupid != NULL) {
				$err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wigrptime']).'<br>';
				$err_lvl = 3;
				$errcnf++;
			}
		}
	}
	
	if($_POST['rankup_definition'] == "") {
		$err_msg = "Saving of empty defintion prevented.<br><br>Your changes were <b>not</b> be saved!<br><br>You need at least one entry to be able to save the configuration!";
		$err_lvl = 3;
	} else {
		$cfg['rankup_definition'] = $_POST['rankup_definition'];

		if($errcnf == 0) {
			if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_definition','{$cfg['rankup_definition']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
				$err_msg = print_r($mysqlcon->errorInfo(), true);
				$err_lvl = 3;
			} else {
				$err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
				$err_lvl = NULL;
			}
		} else {
			$err_msg .= "<br>".$lang['errgrpid'];
		}
	
		$grouptimearr = explode(',', $_POST['rankup_definition']);
		foreach ($grouptimearr as $entry) {
			list($key, $value) = explode('=>', $entry);
			$addnewvalue1[$key] = $value;
			$cfg['rankup_definition'] = $addnewvalue1;
		}
	}
	
} elseif (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
	$rankup_definition = "";
	if(isset($_POST['rankuptime']) && isset($_POST['rankupgroup'])) {
		$rankupgroups = [];
		foreach($_POST['rankuptime'] as $key => $entry) {
			$servergroupId = isset($_POST["rankupgroup"][$key]) ? $_POST["rankupgroup"][$key] : 0;
			if(empty($servergroupId)) {
				$servergroupId = 0;
			}
			if(empty($entry)) {
				$entry = 0; 
			}
			$rankupgroups[] = "$entry=>$servergroupId";
		}
		$rankup_definition = implode(",", $rankupgroups);
		$grouparr = [];
		foreach(explode(',', $rankup_definition) as $entry) {
			list($time, $groupid) = explode('=>', $entry);
			$grouparr[$groupid] = $time;
		}
		
		$err_msg = '';
		$errcnf = 0;
		if(isset($groupslist) && $groupslist != NULL) {
			foreach($grouparr as $groupid => $time) {
				if((!isset($groupslist[$groupid]) && $groupid != NULL) || $groupid == 0) {
					$err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wigrptime']).'<br>';
					$err_lvl = 3;
					$errcnf++;
				}
			}
		}

		$cfg['rankup_definition'] = $rankup_definition;

		if($errcnf == 0) {
			if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_definition','{$cfg['rankup_definition']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
				$err_msg = print_r($mysqlcon->errorInfo(), true);
				$err_lvl = 3;
			} else {
				$err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
				$err_lvl = NULL;
			}
		} else {
			$err_msg .= "<br>".$lang['errgrpid'];
		}

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
	} else {
		$err_msg = $lang['errukwn'];
		$err_lvl = 3;
	}
} elseif(isset($_POST['update']) || isset($_POST['update_old'])) {
	echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
	rem_session_ts3($rspathhex);
	exit;
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				
				<form class="form-horizontal" data-toggle="validator" name="update" method="POST" id="new">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<span><?php echo $lang['stmy0002'],' ',$lang['wihlset']; ?></span>
								<div class="btn pull-right expertelement">
									<input id="switchexpert1" class="switch-animate" type="checkbox" data-size="mini" value="switchexpert1" data-label-text="<?php echo $lang['wigrpimp'] ?>" data-off-text="OFF">
								</div>
							</h1>
						</div>
					</div>
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-sm-12 pointer" data-toggle="modal" data-target="#wihladm0desc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="panel-body">
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
									<div class="form-group">
										<div class="col-sm-4">
											<b><?php echo $lang['wigrpt1'],' (',$lang['wigrptk'],')'; ?></b>
										</div>
										<div class="col-sm-5">
											<b><?php echo $lang['wigrpt2'] ?></b>
										</div>
										<div class="col-sm-2"></div>
										<div class="col-sm-1"></div>
									</div>
								<?PHP
								foreach($cfg['rankup_definition'] as $time => $sgroup) {
								?>
									<div class="form-group" name="rankupgroup">
										<div class="col-sm-4">
											<input type="text" class="form-control rankuptime" name="rankuptime[]" value="<?PHP echo $time; ?>">
										</div>
										<div class="col-sm-5">
											<select class="selectpicker show-tick form-control" data-live-search="true" name="rankupgroup[]">
											<?PHP
											foreach ($groupslist as $groupID => $groupParam) {
												if ($groupID == $sgroup) $selected=" selected"; else $selected="";
												if (isset($groupParam['iconid']) && $groupParam['iconid'] != 0) $iconid=$groupParam['iconid']; else $iconid="placeholder";
												if ($groupParam['type'] == 0 || $groupParam['type'] == 2) $disabled=" disabled"; else $disabled="";
												if ($groupParam['type'] == 0) $grouptype=" [TEMPLATE GROUP]"; else $grouptype="";
												if ($groupParam['type'] == 2) $grouptype=" [QUERY GROUP]";
												if ($groupID != 0) {
													echo '<option data-content="<img src=\'../tsicons/',$iconid,'.',$groupParam['ext'],'\' width=\'16\' height=\'16\'>&nbsp;&nbsp;',$groupParam['sgidname'],'&nbsp;<span class=\'text-muted small\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$selected,$disabled,'></option>';
												}
											}
											?>
											</select>
										</div>
										<div class="col-sm-1 text-center delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;"></i></div>
										<div class="col-sm-2"></div>
									</div>
								<?PHP
								} 
									?>
									<div class="form-group" id="addrankupgroup">
										<div class="col-sm-9"></div>
										<div class="col-sm-1 text-center">
											<span class="d-inline-block" ata-toggle="tooltip" title="Add new line">
												<button class="btn btn-primary" style="margin-top: 5px;" type="button"><i class="fas fa-plus"></i></button>
											</span>
										</div>
										<div class="col-sm-2"></div>
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
				
				<form class="form-horizontal hidden" data-toggle="validator" name="update_old" method="POST" id="old">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<span><?php echo $lang['stmy0002'],' ',$lang['wihlset']; ?></span>
								<div class="btn pull-right">
									<input id="switchexpert2" class="switch-animate" type="checkbox" checked data-size="mini" value="switchexpert2" data-label-text="<?php echo $lang['wigrpimp'] ?>" data-on-text="ON">
								</div>
							</h1>
						</div>
					</div>

					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-sm-12" data-toggle="modal" data-target="#wihladm1desc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
								<div class="panel-body">
								<div class="row">&nbsp;</div>
							</div>
								
							<div class="form-group required-field-block">
								<label class="col-sm-2 control-label"><?php echo $lang['wigrptime']; ?></label>
								<div class="col-sm-10">
									<textarea class="form-control required" data-pattern="^([0-9]{1,9}=>[0-9]{1,9},)*[0-9]{1,9}=>[0-9]{1,9}$" data-error="Wrong definition, please look at description for more details. No comma at ending!" rows="15" name="rankup_definition" maxlength="21588" required><?php $implode_definition = ''; foreach ($cfg['rankup_definition'] as $time => $sgroup) { $implode_definition .= $time."=>".$sgroup.","; } $implode_definition = substr($implode_definition, 0, -1); echo $implode_definition; ?></textarea>
									<div class="help-block with-errors"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">&nbsp;</div>
					<div class="row">
						<div class="text-center">
							<button type="submit" class="btn btn-primary" name="update_old"><?php echo $lang['wisvconf']; ?></button>
						</div>
					</div>
					<div class="row">&nbsp;</div>
				</form>
			</div>
		</div>
	</div>

<div class="modal fade" id="wihladm0desc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wigrptime']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wigrptime2desc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wihladm1desc" tabindex="-1">
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

$(".rankuptime").TouchSpin({
	min: 0,
	max: 999999999,
	verticalbuttons: true,
	prefix: 'Sec.:'
});
$("#addrankupgroup").click(function(){
	var $clone = $("div[name='rankupgroup']").last().clone();
	$clone.insertBefore("#addrankupgroup");
	$clone.find('.bootstrap-select').replaceWith(function() { return $('select', this); });
	$clone.find('select').selectpicker('val', '');
	$clone.find('.bootstrap-touchspin').replaceWith(function() { return $('input', this); });;
	$clone.find('input').TouchSpin({min: 0,max: 999999999,verticalbuttons: true,prefix: 'Sec.:'});
	$clone.find('input').trigger("touchspin.uponce");
	$('.delete').removeClass("hidden");
});
$(document).on("click", ".delete", function(){
	var $number = $('.delete').length;
	if($number == 1) {
		alert('Do not remove the last line! A definition without entries isn\'t valid!');
	} else if($number == 2) {
		$(this).parent().remove();
		$('.delete').addClass("hidden");
	} else {
		$(this).parent().remove();
	}
});
$(document).ready(function() {
	var $number = $('.delete').length;
	if($number == 1) {
		$('.delete').remove();
	}
});
$('#switchexpert1').on('switchChange.bootstrapSwitch', function() {
	document.getElementById("new").classList.add("hidden");
	document.getElementById("old").classList.remove("hidden");
	$('#switchexpert2').bootstrapSwitch('state', true, false);
});
$('#switchexpert2').on('switchChange.bootstrapSwitch', function() {
	document.getElementById("new").classList.remove("hidden");
	document.getElementById("old").classList.add("hidden");
	$('#switchexpert1').bootstrapSwitch('state', false, false);
});
</script>
</body>
</html>