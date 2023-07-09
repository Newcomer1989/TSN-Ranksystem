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

    if (($groupslist = $mysqlcon->query("SELECT * FROM `$dbname`.`groups` ORDER BY `sortid`,`sgidname` ASC")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC)) === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
        $err_lvl = 3;
    }

    if (! isset($groupslist) || $groupslist == null) {
        $err_msg = '<b>No servergroups found inside the Ranksystem cache!</b><br><br>Please connect the Ranksystem Bot to the TS server. The Ranksystem will download the servergroups when it is connected to the server.<br>Give it a few minutes and reload this page. The dropdown field should contain your groups after.';
        $err_lvl = 1;
    }

    if (isset($_POST['update_old']) && isset($db_csrf[$_POST['csrf_token']])) {
        if (empty($_POST['rankup_definition'])) {
            $grouparr_old = null;
        } else {
            foreach (explode(',', $_POST['rankup_definition']) as $entry) {
                list($time, $groupid, $keepflag) = explode('=>', $entry);
                if ($keepflag == null) {
                    $keepflag = 0;
                }
                $grouparr_old[$time] = ['time'=>$time, 'group'=>$groupid, 'keep'=>$keepflag];
                $cfg['rankup_definition'] = $grouparr_old;
            }
        }

        $errcnf = 0;
        if (isset($groupslist) && $groupslist != null) {
            if (isset($cfg['rankup_definition']) && $cfg['rankup_definition'] != null) {
                foreach ($cfg['rankup_definition'] as $time => $value) {
                    if (! isset($groupslist[$value['group']]) && $value['group'] != null) {
                        if (! isset($err_msg)) {
                            $err_msg = '';
                        }
                        $err_msg .= sprintf($lang['upgrp0001'], $value['group'], $lang['wigrptime']).'<br>';
                        $err_lvl = 3;
                        $errcnf++;
                    }
                }
            }
        }

        if ($_POST['rankup_definition'] == '') {
            $err_msg = 'Saving of empty defintion prevented.<br><br>Your changes were <b>not</b> be saved!<br><br>You need at least one entry to be able to save the configuration!';
            $err_lvl = 3;
        } else {
            if ($errcnf == 0) {
                if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_definition','{$_POST['rankup_definition']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
                    $err_msg = print_r($mysqlcon->errorInfo(), true);
                    $err_lvl = 3;
                } else {
                    $err_msg = $lang['wisvsuc'].' '.sprintf($lang['wisvres'], '<span class="item-margin"><form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i><span class="item-margin">'.$lang['wibot7'].'</span></button></form></span>');
                    $err_lvl = null;
                }
            } else {
                $err_msg .= '<br>'.$lang['errgrpid'];
            }
        }
    } elseif (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
        $rankup_definition = '';
        if (isset($_POST['rankuptime']) && isset($_POST['rankupgroup'])) {
            $rankupgroups = [];
            foreach ($_POST['rankuptime'] as $key => $entry) {
                $servergroupId = isset($_POST['rankupgroup'][$key]) ? $_POST['rankupgroup'][$key] : 0;
                if (isset($_POST['rankupkeep']) && in_array($key, $_POST['rankupkeep'])) {
                    $keepflag = 1;
                } else {
                    $keepflag = 0;
                }
                if (empty($entry)) {
                    $entry = 0;
                }
                $rankupgroups[] = "$entry=>$servergroupId=>$keepflag";
            }
            $rankup_definition = implode(',', $rankupgroups);
            $grouparr = [];
            foreach (explode(',', $rankup_definition) as $entry) {
                list($time, $groupid, $keepflag) = explode('=>', $entry);
                $grouparr[$groupid] = $time;
            }

            $err_msg = '';
            $errcnf = 0;
            if (isset($groupslist) && $groupslist != null) {
                foreach ($grouparr as $groupid => $time) {
                    if ((! isset($groupslist[$groupid]) && $groupid != null) || $groupid == 0) {
                        $err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wigrptime']).'<br>';
                        $err_lvl = 3;
                        $errcnf++;
                    }
                }
            }

            $cfg['rankup_definition'] = $rankup_definition;

            if ($errcnf == 0) {
                if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_definition','{$cfg['rankup_definition']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
                    $err_msg = print_r($mysqlcon->errorInfo(), true);
                    $err_lvl = 3;
                } else {
                    $err_msg = $lang['wisvsuc'].' '.sprintf($lang['wisvres'], '<span class="item-margin"><form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i><span class="item-margin">'.$lang['wibot7'].'</span></button></form></span>');
                    $err_lvl = null;
                }
            } else {
                $err_msg .= '<br>'.$lang['errgrpid'];
            }

            if (empty($rankup_definition)) {
                $cfg['rankup_definition'] = null;
            } else {
                $grouptimearr = explode(',', $rankup_definition);
                foreach ($grouptimearr as $entry) {
                    list($time, $groupid, $keepflag) = explode('=>', $entry);
                    $addnewvalue1[$time] = ['time'=>$time, 'group'=>$groupid, 'keep'=>$keepflag];
                    $cfg['rankup_definition'] = $addnewvalue1;
                }
            }
        } else {
            $err_msg = $lang['errukwn'];
            $err_lvl = 3;
        }
    } elseif (isset($_POST['update']) || isset($_POST['update_old'])) {
        echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
        rem_session_ts3();
        exit;
    }
    ?>
			<div id="page-wrapper" class="webinterface_rank">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					
					<form class="form-horizontal" data-toggle="validator" name="update" method="POST" id="new">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header">
									<span><?php echo $lang['stmy0002'],' ',$lang['wihlset']; ?></span>
									<div class="btn pull-right expertelement">
										<input id="switchexpert1" class="switch-animate" type="checkbox" data-size="mini" value="switchexpert1" data-label-width="100" data-label-text="<?php echo $lang['wigrpimp'] ?>" data-off-text="OFF">
									</div>
								</h1>
							</div>
						</div>
						<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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
											<div class="col-sm-2">
												<b><?php echo $lang['wigrpt3'] ?></b>
											</div>
											<div class="col-sm-1"></div>
										</div>
									<?php
                                    $rowid = 0;
    foreach ($cfg['rankup_definition'] as $rank) {
        ?>
										<div class="form-group" name="rankupgroup">
											<div class="col-sm-4">
												<input type="text" class="form-control rankuptime" name="rankuptime[]" value="<?php echo $rank['time']; ?>">
											</div>
											<div class="col-sm-5">
												<select class="selectpicker show-tick form-control" data-live-search="true" name="rankupgroup[]">
												<?php
                    foreach ($groupslist as $groupID => $groupParam) {
                        if ($groupID == $rank['group']) {
                            $selected = ' selected';
                        } else {
                            $selected = '';
                        }
                        if (isset($groupParam['iconid']) && $groupParam['iconid'] != 0) {
                            $iconid = $groupParam['iconid'].'.';
                        } else {
                            $iconid = 'placeholder.png';
                        }
                        if ($groupParam['type'] == 0 || $groupParam['type'] == 2) {
                            $disabled = ' disabled';
                        } else {
                            $disabled = '';
                        }
                        if ($groupParam['type'] == 0) {
                            $grouptype = ' [TEMPLATE GROUP]';
                        } else {
                            $grouptype = '';
                        }
                        if ($groupParam['type'] == 2) {
                            $grouptype = ' [QUERY GROUP]';
                        }
                        if ($groupID != 0) {
                            echo '<option data-content="<img src=\'../tsicons/',$iconid,$groupParam['ext'],'\' width=\'16\' height=\'16\'><span class=\'item-margin\'>',$groupParam['sgidname'],'</span><span class=\'text-muted small item-margin\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$selected,$disabled,'></option>';
                        }
                    }
        ?>
												</select>
											</div>
											<div class="col-sm-2">
												<?php if ($rank['keep'] == 1) {
												    echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="rankupkeep[]" value="',$rowid,'">';
												} else {
												    echo '<input class="switch-animate" type="checkbox" data-size="mini" name="rankupkeep[]" value="',$rowid,'">';
												} ?>
											</div>
											<div class="col-sm-1 text-center delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;" title="delete line"></i></div>
										</div>
									<?php
                                        $rowid++;
    }
    ?>
										<div class="form-group" id="addrankupgroup">
											<div class="col-sm-9"></div>
											<div class="col-sm-1 text-center">
												<span class="d-inline-block" ata-toggle="tooltip" title="Add new line">
													<button class="btn btn-primary" onclick="addrankupgroup()" style="margin-top: 5px;" type="button"><i class="fas fa-plus"></i></button>
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
								<button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i><span class="item-margin"><?php echo $lang['wisvconf']; ?></span></button>
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
										<input id="switchexpert2" class="switch-animate" type="checkbox" checked data-size="mini" value="switchexpert2" data-label-width="100" data-label-text="<?php echo $lang['wigrpimp'] ?>" data-on-text="ON">
									</div>
								</h1>
							</div>
						</div>

						<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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
										<textarea class="form-control required" data-pattern="^([0-9]{1,9}=>[0-9]{1,9}=>[0-1]{1},)*[0-9]{1,9}=>[0-9]{1,9}=>[0-1]{1}$" data-error="Wrong definition, please look at description for more details. No comma at ending!" rows="15" name="rankup_definition" maxlength="21588" required><?php $implode_definition = '';
    foreach ($cfg['rankup_definition'] as $rank) {
        $implode_definition .= $rank['time'].'=>'.$rank['group'].'=>'.$rank['keep'].',';
    } $implode_definition = substr($implode_definition, 0, -1);
    echo $implode_definition; ?></textarea>
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
						<div class="row">
							<div class="text-center">
								<button type="submit" class="btn btn-primary" name="update_old"><i class="fas fa-save"></i><span class="item-margin"><?php echo $lang['wisvconf']; ?></span></button>
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
			<?php echo $lang['wigrptime2desc'],$lang['wigrptime3desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
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
			<?php echo $lang['wigrptimedesc'],$lang['wigrptime3desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
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
	function addrankupgroup() {
		$("[name='rankupkeep[]']").last().bootstrapSwitch('destroy', true);
		var $clone = $("div[name='rankupgroup']").last().clone();
		$("[name='rankupkeep[]']").last().bootstrapSwitch();
		$clone.insertBefore("#addrankupgroup");
		$("[name='rankupkeep[]']").last().bootstrapSwitch();
		$clone.find('.bootstrap-select').replaceWith(function() { return $('select', this); });
		$clone.find('select').selectpicker('val', '');
		$clone.find('.bootstrap-touchspin').replaceWith(function() { return $('input', this); });;
		$("input[name='rankuptime[]']").last().TouchSpin({min: 0,max: 999999999,verticalbuttons: true,prefix: 'Sec.:'});
		$("input[name='rankuptime[]']").last().trigger("touchspin.uponce");
		$('.delete').removeClass("hidden");
	};
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
	$("[name='rankupkeep[]']").bootstrapSwitch();
	$("[id='switchexpert1']").bootstrapSwitch();
	</script>
	</body>
	</html>
	<?php
} catch(Throwable $ex) {
}
?>