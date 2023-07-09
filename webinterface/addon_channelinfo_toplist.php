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

    if (($channellist = $mysqlcon->query("SELECT * FROM `$dbname`.`channel` ORDER BY `pid`,`channel_order`,`channel_name` ASC")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC)) === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
        $err_lvl = 3;
    }

    $channelinfo_toplist_active = 0;

    if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
        if (isset($_POST['channelinfo_toplist_active'])) {
            $channelinfo_toplist_active = 1;
        }
        if (is_array($_POST['channelid'])) {
            $_POST['channelid'] = $_POST['channelid'][0];
        }

        if (! isset($err_lvl) || $err_lvl < 3) {
            $sqlexec = $mysqlcon->prepare("INSERT INTO `$dbname`.`addons_config` (`param`,`value`) VALUES ('channelinfo_toplist_active', :channelinfo_toplist_active), ('channelinfo_toplist_desc', :channelinfo_toplist_desc), ('channelinfo_toplist_delay', :channelinfo_toplist_delay), ('channelinfo_toplist_channelid', :channelinfo_toplist_channelid), ('channelinfo_toplist_modus', :channelinfo_toplist_modus) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`= :csrf_token");
            $sqlexec->bindParam(':channelinfo_toplist_active', $channelinfo_toplist_active, PDO::PARAM_STR);
            $sqlexec->bindParam(':channelinfo_toplist_desc', $_POST['channelinfo_toplist_desc'], PDO::PARAM_STR);
            $sqlexec->bindParam(':channelinfo_toplist_delay', $_POST['channelinfo_toplist_delay'], PDO::PARAM_STR);
            $sqlexec->bindParam(':channelinfo_toplist_channelid', $_POST['channelid'], PDO::PARAM_STR);
            $sqlexec->bindParam(':channelinfo_toplist_modus', $_POST['channelinfo_toplist_modus'], PDO::PARAM_STR);
            $sqlexec->bindParam(':csrf_token', $_POST['csrf_token']);
            $sqlexec->execute();

            if ($sqlexec->errorCode() != 0) {
                $err_msg = print_r($sqlexec->errorInfo(), true);
                $err_lvl = 3;
            } else {
                $err_msg = $lang['wisvsuc'].' '.sprintf($lang['wisvres'], '<span class="item-margin"><form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i><span class="item-margin">'.$lang['wibot7'].'</span></button></form></span>');
                $err_lvl = null;
            }
        }

        $addons_config['channelinfo_toplist_active']['value'] = $channelinfo_toplist_active;
        $addons_config['channelinfo_toplist_channelid']['value'] = $_POST['channelid'];
        $addons_config['channelinfo_toplist_modus']['value'] = $_POST['channelinfo_toplist_modus'];
        $addons_config['channelinfo_toplist_delay']['value'] = $_POST['channelinfo_toplist_delay'];
        $addons_config['channelinfo_toplist_desc']['value'] = $_POST['channelinfo_toplist_desc'];
    } elseif (isset($_POST['update'])) {
        echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
        rem_session_ts3();
        exit;
    }
    ?>
			<div id="page-wrapper" class="webinterface_addon_channelinfo_toplist">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['addonchtopl']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
					<div class="form-horizontal">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-sm-12 pointer" data-toggle="modal" data-target="#addonchtopldesc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
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
									<?php if ($addons_config['channelinfo_toplist_active']['value'] == '1') {
									    echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="channelinfo_toplist_active" value="',$channelinfo_toplist_active,'">';
									} else {
									    echo '<input class="switch-animate" type="checkbox" data-size="mini" name="channelinfo_toplist_active" value="',$channelinfo_toplist_active,'">';
									} ?>
									</div>
								</div>
								<div class="row">&nbsp;</div>
								<div class="row">&nbsp;</div>
							</div>
							<div class="col-md-3">
							</div>

							<div class="col-md-12">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-2 control-label" data-toggle="modal" data-target="#addonchchdesc"><?php echo $lang['addonchch']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-10">
											<?php
									        echo select_channel($channellist, $addons_config['channelinfo_toplist_channelid']['value']);
    ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" data-toggle="modal" data-target="#addonchmodesc"><?php echo $lang['addonchmo']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-10">
											<select class="selectpicker show-tick form-control" id="basic" name="channelinfo_toplist_modus">
											<?php
    echo '<option value="1"';
    if ($addons_config['channelinfo_toplist_modus']['value'] == '1') {
        echo ' selected="selected"';
    } echo '>',$lang['addonchmo1'],'</option>';
    echo '<option value="2"';
    if ($addons_config['channelinfo_toplist_modus']['value'] == '2') {
        echo ' selected="selected"';
    } echo '>',$lang['addonchmo2'],'</option>';
    echo '<option value="3"';
    if ($addons_config['channelinfo_toplist_modus']['value'] == '3') {
        echo ' selected="selected"';
    } echo '>',$lang['addonchmo3'],'</option>';
    echo '<option value="4"';
    if ($addons_config['channelinfo_toplist_modus']['value'] == '4') {
        echo ' selected="selected"';
    } echo '>',$lang['addonchmo4'],'</option>';
    echo '<option value="5"';
    if ($addons_config['channelinfo_toplist_modus']['value'] == '5') {
        echo ' selected="selected"';
    } echo '>',$lang['addonchmo5'],'</option>';
    echo '<option value="6"';
    if ($addons_config['channelinfo_toplist_modus']['value'] == '6') {
        echo ' selected="selected"';
    } echo '>',$lang['addonchmo6'],'</option>';
    ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" data-toggle="modal" data-target="#addonchdelaydesc"><?php echo $lang['addonchdelay']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-10">
											<input type="text" class="form-control" name="channelinfo_toplist_delay" title="<?php echo $lang['addonchdescdesc31'].': '.date('Y-m-d H:i:s', $addons_config['channelinfo_toplist_lastupdate']['value']); ?>" value="<?php echo $addons_config['channelinfo_toplist_delay']['value']; ?>">
											<script>
											$("input[name='channelinfo_toplist_delay']").TouchSpin({
												min: 0,
												max: 65535,
												verticalbuttons: true,
												prefix: 'Sec.:'
											});
											</script>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label" data-toggle="modal" data-target="#addonchdescdesc"><?php echo $lang['addonchdesc']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-10">
											<textarea class="form-control" rows="25" name="channelinfo_toplist_desc" maxlength="16000"><?php echo $addons_config['channelinfo_toplist_desc']['value']; ?></textarea>
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
					</div>
					</form>
				</div>
			</div>
		</div>
	<div class="modal fade" id="addonchtopldesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['addonchtopl']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['addonchtopldesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="addonchchdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['addonchch']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['addonchchdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="addonchmodesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['addonchmo']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['addonchmodesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="addonchdelaydesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['addonchdelay']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['addonchdelaydesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade bd-example-modal-lg" id="addonchdescdesc" tabindex="-1">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['addonchdesc']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo sprintf($lang['addonchdescdesc'].'<br><br>%1$s%63$s%2$s%64$s%3$s%1$s%4$s%2$s%4$s%3$s%1$s%5$s%2$s%6$s%3$s%1$s%7$s%2$s%8$s%3$s%1$s%9$s%2$s%10$s%3$s%1$s%11$s%2$s%12$s%3$s%1$s%13$s%2$s%14$s%3$s%1$s%15$s%2$s%16$s%3$s%1$s%17$s%2$s%18$s%3$s%1$s%19$s%2$s%20$s%3$s%1$s%21$s%2$s%22$s%3$s%1$s%4$s%2$s%4$s%3$s%1$s%23$s%2$s%24$s%3$s%1$s%25$s%2$s%26$s%3$s%1$s%27$s%2$s%28$s%3$s%1$s%29$s%2$s%30$s%3$s%1$s%31$s%2$s%32$s%3$s%1$s%33$s%2$s%34$s%3$s%1$s%35$s%2$s%36$s%3$s%1$s%37$s%2$s%38$s%3$s%1$s%4$s%2$s%4$s%3$s%1$s%39$s%2$s%40$s%3$s%1$s%41$s%2$s%42$s%3$s%1$s%43$s%2$s%44$s%3$s%1$s%45$s%2$s%46$s%3$s%1$s%47$s%2$s%48$s%3$s%1$s%49$s%2$s%50$s%3$s%1$s%51$s%2$s%52$s%3$s%1$s%53$s%2$s%54$s%3$s%1$s%55$s%2$s%56$s%3$s%1$s%57$s%2$s%58$s%3$s%1$s%59$s%2$s%60$s%3$s%1$s%4$s%2$s%4$s%3$s%1$s%61$s%2$s%62$s%3$s<br>', '<div class="row"><div class="col-md-5">', '</div><div class="col-md-7">', '</div></div>', '&nbsp;', '{$CLIENT_ACTIVE_TIME_ALL_XXX}', $lang['addonchdescdesc01'], '{$CLIENT_ACTIVE_TIME_LAST_MONTH_XXX}', $lang['addonchdescdesc02'], '{$CLIENT_ACTIVE_TIME_LAST_WEEK_XXX}', $lang['addonchdescdesc03'], '{$CLIENT_ONLINE_TIME_ALL_XXX}', $lang['addonchdescdesc04'], '{$CLIENT_ONLINE_TIME_LAST_MONTH_XXX}', $lang['addonchdescdesc05'], '{$CLIENT_ONLINE_TIME_LAST_WEEK_XXX}', $lang['addonchdescdesc06'], '{$CLIENT_IDLE_TIME_ALL_XXX}', $lang['addonchdescdesc07'], '{$CLIENT_IDLE_TIME_LAST_MONTH_XXX}', $lang['addonchdescdesc08'], '{$CLIENT_IDLE_TIME_LAST_WEEK_XXX}', $lang['addonchdescdesc09'], '{$CLIENT_CURRENT_CHANNEL_ID_XXX}', $lang['addonchdescdesc10'], '{$CLIENT_CURRENT_CHANNEL_NAME_XXX}', $lang['addonchdescdesc11'], '{$CLIENT_CURRENT_RANK_GROUP_ICON_URL_XXX}', $lang['addonchdescdesc12'], '{$CLIENT_CURRENT_RANK_GROUP_ID_XXX}', $lang['addonchdescdesc13'], '{$CLIENT_CURRENT_RANK_GROUP_NAME_XXX}', $lang['addonchdescdesc14'], '{$CLIENT_LAST_RANKUP_TIMEXXX}', $lang['addonchdescdesc15'], '{$CLIENT_NEXT_RANKUP_TIME_XXX}', $lang['addonchdescdesc16'], '{$CLIENT_RANK_POSITION_XXX}', $lang['addonchdescdesc17'], '{$CLIENT_COUNTRY_XXX}', $lang['addonchdescdesc18'], '{$CLIENT_CREATED_XXX}', $lang['addonchdescdesc20'], '{$CLIENT_DATABASE_ID_XXX}', $lang['addonchdescdesc22'], '{$CLIENT_DESCRIPTION_XXX}', $lang['addonchdescdesc23'], '{$CLIENT_LAST_SEEN_XXX}', $lang['addonchdescdesc24'], '{$CLIENT_NICKNAME_XXX}', $lang['addonchdescdesc25'], '{$CLIENT_ONLINE_STATUS_XXX}', $lang['addonchdescdesc26'], '{$CLIENT_PLATFORM_XXX}', $lang['addonchdescdesc27'], '{$CLIENT_TOTAL_CONNECTIONS_XXX}', $lang['addonchdescdesc28'], '{$CLIENT_UNIQUE_IDENTIFIER_XXX}', $lang['addonchdescdesc29'], '{$CLIENT_VERSION_XXX}', $lang['addonchdescdesc30'], '{$LAST_UPDATE_TIME}', $lang['addonchdescdesc31'], '<b>'.$lang['addonchdescdesc00'].'</b>', '<b>'.$lang['descr'].'</b>').'<br><br>'.sprintf($lang['addonchdesc2desc'], '<a href="https://www.smarty.net/docs/en/language.modifiers.tpl" target="_blank">https://www.smarty.net/docs/en/language.modifiers.tpl</a>', '<a href="https://www.smarty.net/docs/en/language.builtin.functions.tpl" target="_blank">https://www.smarty.net/docs/en/language.builtin.functions.tpl</a>'); ?>
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
	$("[name='channelinfo_toplist_active']").bootstrapSwitch();
	</script>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>