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
        if (empty($_POST['rankup_boost_definition'])) {
            $grouparr_old = null;
        } else {
            foreach (explode(',', $_POST['rankup_boost_definition']) as $entry) {
                list($key, $value1, $value2) = explode('=>', $entry);
                $grouparr_old[$key] = ['group'=>$key, 'factor'=>$value1, 'time'=>$value2];
                $cfg['rankup_boost_definition'] = $grouparr_old;
            }
        }

        if (isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != null) {
            foreach ($cfg['rankup_boost_definition'] as $groupid => $value) {
                if (! isset($groupslist[$groupid]) && $groupid != null) {
                    $err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wiboost']).'<br>';
                    $err_lvl = 3;
                    $errcnf++;
                }
            }
        }

        $cfg['rankup_boost_definition'] = $_POST['rankup_boost_definition'];

        if ($errcnf == 0) {
            if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_boost_definition','{$cfg['rankup_boost_definition']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
                $err_msg = print_r($mysqlcon->errorInfo(), true);
                $err_lvl = 3;
            } else {
                $err_msg = $lang['wisvsuc'].' '.sprintf($lang['wisvres'], '<span class="item-margin"><form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i><span class="item-margin">'.$lang['wibot7'].'</span></button></form></span>');
                $err_lvl = null;
            }
        } else {
            $err_msg .= '<br>'.$lang['errgrpid'];
        }

        if (empty($_POST['rankup_boost_definition'])) {
            $cfg['rankup_boost_definition'] = null;
        } else {
            foreach (explode(',', $_POST['rankup_boost_definition']) as $entry) {
                list($key, $value1, $value2) = explode('=>', $entry);
                $addnewvalue2[$key] = ['group'=>$key, 'factor'=>$value1, 'time'=>$value2];
                $cfg['rankup_boost_definition'] = $addnewvalue2;
            }
        }
    } elseif (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
        $rankup_boost_definition = $err_msg = '';
        $errcnf = 0;

        if (isset($_POST['boostduration']) && ! isset($_POST['boostgroup']) && isset($_POST['boostfactor'])) {
            $errcnf++;
            $err_msg = '<b>Missing servergroup in your defintion!</b><br>';
            $err_lvl = 3;
            $cfg['rankup_boost_definition'] = null;
        } elseif (isset($_POST['boostduration']) && isset($_POST['boostgroup']) && isset($_POST['boostfactor'])) {
            $boostdefinition = [];
            foreach ($_POST['boostgroup'] as $rowid => $groupid) {
                $factor = isset($_POST['boostfactor'][$rowid]) ? floatval($_POST['boostfactor'][$rowid]) : 1;
                $duration = isset($_POST['boostduration'][$rowid]) ? intval($_POST['boostduration'][$rowid]) : 1;
                $boostdefinition[] = "$groupid=>$factor=>$duration";
            }

            $rankup_boost_definition = implode(',', $boostdefinition);

            $grouparr = [];
            foreach (explode(',', $rankup_boost_definition) as $entry) {
                list($groupid, $factor, $duration) = explode('=>', $entry);
                $grouparr[$groupid] = $factor;
            }

            if (isset($groupslist) && $groupslist != null) {
                foreach ($grouparr as $groupid => $time) {
                    if ((! isset($groupslist[$groupid]) && $groupid != null) || $groupid == 0) {
                        $err_msg .= sprintf($lang['upgrp0001'], $groupid, $lang['wigrptime']).'<br>';
                        $err_lvl = 3;
                        $errcnf++;
                    }
                }
            }

            $cfg['rankup_boost_definition'] = $rankup_boost_definition;
        } else {
            $cfg['rankup_boost_definition'] = null;
            if ($mysqlcon->exec("UPDATE `$dbname`.`user` SET `boosttime`=0;") === false) {
                $err_msg = print_r($mysqlcon->errorInfo(), true);
                $err_lvl = 3;
            }
        }

        if ($errcnf == 0) {
            if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('rankup_boost_definition','{$cfg['rankup_boost_definition']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
                $err_msg = print_r($mysqlcon->errorInfo(), true);
                $err_lvl = 3;
            } else {
                $err_msg = $lang['wisvsuc'].' '.sprintf($lang['wisvres'], '<span class="item-margin"><form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i><span class="item-margin">'.$lang['wibot7'].'</span></button></form></span>');
                $err_lvl = null;
            }
        } else {
            $err_msg .= '<br>'.$lang['errgrpid'];
        }

        if (empty($rankup_boost_definition)) {
            $cfg['rankup_boost_definition'] = null;
        } else {
            $boostexp = explode(',', $rankup_boost_definition);
            foreach ($boostexp as $entry) {
                list($key, $value1, $value2) = explode('=>', $entry);
                $addnewvalue2[$key] = ['group'=>$key, 'factor'=>$value1, 'time'=>$value2];
                $cfg['rankup_boost_definition'] = $addnewvalue2;
            }
        }
    } elseif (isset($_POST['update']) || isset($_POST['update_old'])) {
        echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
        rem_session_ts3();
        exit;
    }
    ?>
			<div id="page-wrapper" class="webinterface_boost">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					
					<form class="form-horizontal" data-toggle="validator" name="update" method="POST" id="new">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header">
									<span><?php echo $lang['wiboost'],' ',$lang['wihlset']; ?></span>
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
											<div class="col-sm-5">
												<b><?php echo $lang['wigrpt2'] ?></b>
											</div>
											<div class="col-sm-3">
												<b><?php echo $lang['wiboost'],' ', $lang['factor']; ?></b>
											</div>
											<div class="col-sm-3">
												<b><?php echo $lang['duration'],' ',$lang['insec'] ?></b>
											</div>
											<div class="col-sm-1"></div>
										</div>
										<div class="form-group hidden" name="template">
											<div class="col-sm-5">
												<select class="selectpicker show-tick form-control" data-live-search="true" name="tempboostgroup[]">
												<?php
                                                foreach ($groupslist as $groupID => $groupParam) {
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
                                                        echo '<option data-content="<img src=\'../tsicons/',$iconid,$groupParam['ext'],'\' width=\'16\' height=\'16\'><span class=\'item-margin\'>',$groupParam['sgidname'],'</span><span class=\'text-muted small item-margin\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$disabled,'></option>';
                                                    }
                                                }
    ?>
												</select>
											</div>
											<div class="col-sm-3">
												<input type="text" data-pattern="^[0-9]{0,9}\.?[0-9]+$" data-error="Only decimal numbers are allowed. As seperator use a dot!" class="form-control boostfactor" name="tempboostfactor[]" value="1.25">
												<div class="help-block with-errors"></div>
											</div>
											<div class="col-sm-3">
												<input type="text" class="form-control boostduration" name="tempboostduration[]" value="600">
											</div>
											<div class="col-sm-1 text-center delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;" title="delete line"></i></div>
											<div class="col-sm-2"></div>
										</div>
									<?php
                                    if (isset($cfg['rankup_boost_definition'])) {
                                        foreach ($cfg['rankup_boost_definition'] as $boost) {
                                            ?>
											<div class="form-group" name="boostgroup">
												<div class="col-sm-5">
													<select class="selectpicker show-tick form-control" data-live-search="true" name="boostgroup[]">
													<?php
                                                        foreach ($groupslist as $groupID => $groupParam) {
                                                            if ($groupID == $boost['group']) {
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
												<div class="col-sm-3">
													<input type="text" data-pattern="^[0-9]{0,9}\.?[0-9]+$" data-error="Only decimal numbers are allowed. As seperator use a dot!" class="form-control boostfactor" name="boostfactor[]" value="<?php echo $boost['factor']; ?>">
													<div class="help-block with-errors"></div>
												</div>
												<div class="col-sm-3">
													<input type="text" class="form-control boostduration" name="boostduration[]" value="<?php echo $boost['time']; ?>">
												</div>
												<div class="col-sm-1 text-center delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;" title="delete line"></i></div>
												<div class="col-sm-2"></div>
											</div>
										<?php
                                        }
                                    }
    ?>
										<div class="form-group" id="addboostgroup">
											<?php
        if (! isset($cfg['rankup_boost_definition'])) {
            echo '<div class="col-sm-11"><div id="noentry"><i>',$lang['wiboostempty'],'</i></div></div>';
        } else {
            echo '<div class="col-sm-11"></div>';
        }?>
											<div class="col-sm-1 text-center">
												<span class="d-inline-block" ata-toggle="tooltip" title="Add new line">
													<button class="btn btn-primary" onclick="addboostgroup()" style="margin-top: 5px;" type="button"><i class="fas fa-plus"></i></button>
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
									<span><?php echo $lang['wiboost'],' ',$lang['wihlset']; ?></span>
									<div class="btn pull-right">
										<input id="switchexpert2" class="switch-animate" type="checkbox" checked data-size="mini" alue="switchexpert2" data-label-width="100" data-label-text="<?php echo $lang['wigrpimp'] ?>" data-on-text="ON">
									</div>
								</h1>
							</div>
						</div>

						<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-sm-12" data-toggle="modal" data-target="#wiboostdesc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="panel-body">
									<div class="row">&nbsp;</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php echo $lang['wiboost']; ?></label>
									<div class="col-sm-10">
										<textarea class="form-control" data-pattern="^([1-9][0-9]{0,9}=>[0-9]{0,9}\.?[0-9]+=>[1-9][0-9]{0,9},)*[1-9][0-9]{0,9}=>[0-9]{0,9}\.?[0-9]+=>[1-9][0-9]{0,9}$" data-error="Wrong definition, please look at description for more details. No comma at ending!" rows="15" name="rankup_boost_definition" maxlength="21588"><?php
                                        $implode_boost = '';
    if (isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != null) {
        foreach ($cfg['rankup_boost_definition'] as $r) {
            $implode_boost .= $r['group'].'=>'.$r['factor'].'=>'.$r['time'].',';
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
			<h4 class="modal-title"><?php echo $lang['wiboost']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wiboost2desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
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

	$(".boostduration").TouchSpin({
		min: 1,
		max: 999999999,
		verticalbuttons: true,
		prefix: 'Sec.:'
	});
	$(".boostfactor").TouchSpin({
		min: 0,
		max: 999999999,
		decimals: 9,
		step: 0.000000001,
		verticalbuttons: true,
		prefix: '<i class="fas fa-times"></i>:'
	});
	function addboostgroup() {
		var $clone = $("div[name='template']").last().clone();
		$clone.removeClass("hidden");
		$clone.attr('name','boostgroup');
		$clone.insertBefore("#addboostgroup");
		$clone.find('.bootstrap-select').replaceWith(function() { return $('select', this); });
		$clone.find('select').selectpicker('val', '');
		$("select[name='tempboostgroup[]']").last().attr('name', 'boostgroup[]');	
		$("input[name='tempboostfactor[]']").last().attr('name', 'boostfactor[]');
		$("input[name='tempboostduration[]']").last().attr('name', 'boostduration[]');
		$('.delete').removeClass("hidden");
		if (document.contains(document.getElementById("noentry"))) {
			document.getElementById("noentry").remove();
		}
		$clone.find('.bootstrap-touchspin').replaceWith(function() { return $('input', this); });
		$clone.find('.boostfactor').TouchSpin({min: 0,max: 999999999,decimals: 9,step: 0.000000001,verticalbuttons: true,prefix: '<i class="fas fa-times"></i>:'});
		$clone.find('.boostduration').TouchSpin({min: 1,max: 999999999,verticalbuttons: true,prefix: 'Sec.:'});
	};
	$(document).on("click", ".delete", function(){
		$(this).parent().remove();
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
	$("[id='switchexpert1']").bootstrapSwitch();
	</script>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>