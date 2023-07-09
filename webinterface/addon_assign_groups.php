<?php
require_once '_preload.php';

try {
    require_once '_nav.php';
    require_once '../other/load_addons_config.php';
    $addons_config = load_addons_config($mysqlcon, $lang, $cfg, $dbname);

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

    $assign_groups_active = 0;
    if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']]) && isset($_POST['assign_groups_active']) && ! isset($_POST['assign_groups_groupids']) && ! isset($_POST['assign_groups_excepted_groupids'])) {
        $err_msg = $lang['stag0010'];
        $err_lvl = 3;
    } elseif (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
        $limit = $alwgr = $excgr = $name = '';
        if (isset($_POST['assign_groups_active'])) {
            $assign_groups_active = 1;
        }
        foreach ($_POST['assign_groups_limit'] as $rowid => $value) {
            $name .= isset($_POST['assign_groups_name'][$rowid]) ? $_POST['assign_groups_name'][$rowid].';' : ';';
            $limit .= isset($_POST['assign_groups_limit'][$rowid]) ? intval($_POST['assign_groups_limit'][$rowid]).';' : '1;';
            if (isset($_POST['assign_groups_groupids'][$rowid])) {
                foreach ($_POST['assign_groups_groupids'][$rowid] as $group) {
                    $alwgr .= $group.',';
                }
                $alwgr = substr($alwgr, 0, -1);
            } else {
                $err_msg = $lang['stag0010'];
                $err_lvl = 3;
            }
            $alwgr .= ';';
            if (isset($_POST['assign_groups_excepted_groupids'][$rowid])) {
                foreach ($_POST['assign_groups_excepted_groupids'][$rowid] as $group) {
                    $excgr .= $group.',';
                }
                $excgr = substr($excgr, 0, -1);
            } else {
            }
            $excgr .= ';';
        }
        $name = substr($name, 0, -1);
        $limit = substr($limit, 0, -1);
        $alwgr = substr($alwgr, 0, -1);
        $excgr = substr($excgr, 0, -1);

        if (! isset($err_lvl) || $err_lvl < 3) {
            $sqlexec = $mysqlcon->prepare("INSERT INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('assign_groups_name', :assign_groups_name), ('assign_groups_active', :assign_groups_active), ('assign_groups_limit', :assign_groups_limit), ('assign_groups_groupids', :assign_groups_groupids), ('assign_groups_excepted_groupids', :assign_groups_excepted_groupids) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`= :csrf_token;");
            $sqlexec->bindParam(':assign_groups_name', $name, PDO::PARAM_STR);
            $sqlexec->bindParam(':assign_groups_active', $assign_groups_active, PDO::PARAM_STR);
            $sqlexec->bindParam(':assign_groups_limit', $limit, PDO::PARAM_STR);
            $sqlexec->bindParam(':assign_groups_groupids', $alwgr, PDO::PARAM_STR);
            $sqlexec->bindParam(':assign_groups_excepted_groupids', $excgr, PDO::PARAM_STR);
            $sqlexec->bindParam(':csrf_token', $_POST['csrf_token']);
            $sqlexec->execute();

            if ($sqlexec->errorCode() != 0) {
                $err_msg = print_r($sqlexec->errorInfo(), true);
                $err_lvl = 3;
            } elseif ($addons_config['assign_groups_active']['value'] != $assign_groups_active && $assign_groups_active == 1) {
                $err_msg = $lang['wisvsuc'].' '.sprintf($lang['wisvres'], '<span class="item-margin"><form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i><span class="item-margin">'.$lang['wibot7'].'</span></button></form></span>');
                $err_lvl = null;
            } else {
                $err_msg = $lang['wisvsuc'];
                $err_lvl = null;
            }
        }

        $addons_config['assign_groups_groupids']['value'] = $alwgr;
        $addons_config['assign_groups_excepted_groupids']['value'] = $excgr;
        $addons_config['assign_groups_name']['value'] = $name;
        $addons_config['assign_groups_limit']['value'] = $limit;
        $addons_config['assign_groups_active']['value'] = $assign_groups_active;
    } elseif (isset($_POST['update'])) {
        echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
        rem_session_ts3();
        exit;
    }
    ?>
			<div id="page-wrapper" class="webinterface_addon_assign_groups">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['stag0001']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
					<div class="form-horizontal">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-sm-12 pointer" data-toggle="modal" data-target="#stag0001desc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="panel-body">
									</div>
								</div>
							</div>
							<div class="col-md-3">
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0014"><?php echo $lang['stag0013']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="col-sm-8">
									<?php if ($addons_config['assign_groups_active']['value'] == '1') {
									    echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="assign_groups_active" value="',$assign_groups_active,'">';
									} else {
									    echo '<input class="switch-animate" type="checkbox" data-size="mini" name="assign_groups_active" value="',$assign_groups_active,'">';
									} ?>
									</div>
								</div>
								<div class="row">&nbsp;</div>
								<div class="row">&nbsp;</div>
							</div>
							<div class="col-md-3">
							</div>
							
							<div class="col-md-6 hidden onlyforcount" id="template" name="onlyforcount">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<div class="col-sm-1 delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;" title="delete this block"></i></div>
											<label class="col-sm-3 control-label" data-toggle="modal" data-target="#stag0021"><?php echo $lang['stag0020']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" data-pattern="^[a-zA-Z0-9]{1,64}$" data-error="No special characters allowed!" name="temp_assign_groups_name[]" value="" minlength="2" maxlength="65535">
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0003"><?php echo $lang['stag0002']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker form-control" data-live-search="true" data-actions-box="true" multiple name="temp_assign_groups_groupids[]">
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
									                    echo '<option data-content="<span class=\'item-margin\'><img src=\'../tsicons/',$iconid,$groupParam['ext'],'\' width=\'16\' height=\'16\'></span><span class=\'item-margin\'>',$groupParam['sgidname'],'</span><span class=\'text-muted small item-margin\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$disabled,'></option>';
									                }
									            }
    ?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0005"><?php echo $lang['stag0004']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="text" class="form-control assign_groups_limit" name="temp_assign_groups_limit[]" value="1">
												<script>
												$("input[name='assign_groups_limit[]']").TouchSpin({
													min: 1,
													max: 65534,
													verticalbuttons: true,
													prefix: 'No.'
												});
												</script>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0018"><?php echo $lang['wiexgrp']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker form-control" data-live-search="true" data-actions-box="true" multiple name="temp_assign_groups_excepted_groupids[]">
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
            echo '<option data-content="<span class=\'item-margin\'><img src=\'../tsicons/',$iconid,$groupParam['ext'],'\' width=\'16\' height=\'16\'></span><span class=\'item-margin\'>',$groupParam['sgidname'],'</span><span class=\'text-muted small item-margin\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$disabled,'></option>';
        }
    }
    ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php

                            $alwgr = explode(';', $addons_config['assign_groups_groupids']['value']);
    $limit = explode(';', $addons_config['assign_groups_limit']['value']);
    $excgr = explode(';', $addons_config['assign_groups_excepted_groupids']['value']);
    if (isset($addons_config['assign_groups_name']['value'])) {
        $name = explode(';', $addons_config['assign_groups_name']['value']);
    } else {
        $name = '';
    }
    foreach ($alwgr as $rowid => $value) {
        ?>
							<div class="col-md-6" name="onlyforcount">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<div class="col-sm-1 delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;" title="delete this block"></i></div>
											<label class="col-sm-3 control-label" data-toggle="modal" data-target="#stag0021"><?php echo $lang['stag0020']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" data-pattern="^[a-zA-Z0-9]{1,64}$" data-error="No special characters allowed!" name="assign_groups_name[]" value="<?php echo $name[$rowid]; ?>" minlength="2" maxlength="65535">
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0003"><?php echo $lang['stag0002']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker form-control<?php if (! isset($alwgr[$rowid]) || $alwgr[$rowid] == null) {
												    echo ' form-control-danger';
												} ?>" data-live-search="true" data-actions-box="true" multiple name="assign_groups_groupids[<?php echo $rowid; ?>][]">
												<?php
                                                $assign_groups_groupids = explode(',', $alwgr[$rowid]);
        foreach ($groupslist as $groupID => $groupParam) {
            if (in_array($groupID, $assign_groups_groupids)) {
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
                echo '<option data-content="<span class=\'item-margin\'><img src=\'../tsicons/',$iconid,$groupParam['ext'],'\' width=\'16\' height=\'16\'></span><span class=\'item-margin\'>',$groupParam['sgidname'],'</span><span class=\'text-muted small item-margin\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$selected,$disabled,'></option>';
            }
        }
        ?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0005"><?php echo $lang['stag0004']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<input type="text" class="form-control" name="assign_groups_limit[]" value="<?php echo $limit[$rowid]; ?>">
												<script>
												$("input[name='assign_groups_limit[]']").TouchSpin({
													min: 1,
													max: 65534,
													verticalbuttons: true,
													prefix: 'No.'
												});
												</script>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#stag0018"><?php echo $lang['wiexgrp']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker form-control" data-live-search="true" data-actions-box="true" multiple name="assign_groups_excepted_groupids[<?php echo $rowid; ?>][]">
												<?php
        $assign_groups_excepted_groupids = explode(',', $excgr[$rowid]);
        foreach ($groupslist as $groupID => $groupParam) {
            if (in_array($groupID, $assign_groups_excepted_groupids)) {
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
                echo '<option data-content="<span class=\'item-margin\'><img src=\'../tsicons/',$iconid,$groupParam['ext'],'\' width=\'16\' height=\'16\'></span><span class=\'item-margin\'>',$groupParam['sgidname'],'</span><span class=\'text-muted small item-margin\'>SGID:&nbsp;',$groupID,$grouptype,'</span>" value="',$groupID,'"',$selected,$disabled,'></option>';
            }
        }
        ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
    }
    ?>
							
							<div class="col-md-6" id="addboostgroup">
								<div class="panel panel-default">
								<div class="panel-body">
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
									<div class="row text-center">
										<span class="d-inline-block" ata-toggle="tooltip" title="Add new block 'assign group'">
											<button class="btn btn-primary" onclick="addboostgroup()" style="margin-top: 5px;" type="button"><i class="fas fa-plus"></i></button>
										</span>
									</div>
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
									<div class="row">&nbsp;</div>
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
					</div>
					</form>
				</div>
			</div>
		</div>
	<div class="modal fade" id="stag0001desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['stag0001']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['stag0001desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="stag0003" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['stag0002']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['stag0003']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="stag0018" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wiexgrp']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['stag0018']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="stag0005" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['stag0004']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['stag0005']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="stag0014" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['stag0013']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['stag0014']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="stag0021" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['stag0020']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['stag0021']; ?>
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
	$("[name='assign_groups_active']").bootstrapSwitch();
	function addboostgroup() {
		var $clone = $("div[id='template']").last().clone();
		$clone.removeClass("hidden");
		$clone.insertBefore("#addboostgroup");
		var $cnt = $("div[name='onlyforcount']").length;
		$cnt = $cnt - 2;		
		$clone.find('.bootstrap-select').replaceWith(function() { return $('select', this); });
		$clone.find('select').selectpicker('val', '');
		$("select[name='temp_assign_groups_groupids[]']").last().attr('name', 'assign_groups_groupids[' + $cnt + '][]');	
		$("input[name='temp_assign_groups_limit[]']").last().attr('name', 'assign_groups_limit[]');
		$("input[name='temp_assign_groups_name[]']").last().attr('name', 'assign_groups_name[]');
		$("select[name='temp_assign_groups_excepted_groupids[]']").last().attr('name', 'assign_groups_excepted_groupids[' + $cnt + '][]');
		$clone.find('.assign_groups_limit').TouchSpin({min: 1,max: 65534,verticalbuttons: true,prefix: 'No.'});
		$clone.find('.assign_groups_limit').removeClass("assign_groups_limit");
	};
	$(document).on("click", ".delete", function(){
		$(this).parent().parent().parent().parent().remove();
	});
	</script>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>