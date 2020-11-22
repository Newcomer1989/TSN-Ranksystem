<?PHP
require_once('_preload.php');

try {
	require_once('_nav.php');

	if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
		if (isset($_POST['stats_column_rank_switch'])) $cfg['stats_column_rank_switch'] = 1; else $cfg['stats_column_rank_switch'] = 0;
		if (isset($_POST['stats_column_client_name_switch'])) $cfg['stats_column_client_name_switch'] = 1; else $cfg['stats_column_client_name_switch'] = 0;
		if (isset($_POST['stats_column_unique_id_switch'])) $cfg['stats_column_unique_id_switch'] = 1; else $cfg['stats_column_unique_id_switch'] = 0;
		if (isset($_POST['stats_column_client_db_id_switch'])) $cfg['stats_column_client_db_id_switch'] = 1; else $cfg['stats_column_client_db_id_switch'] = 0;
		if (isset($_POST['stats_column_last_seen_switch'])) $cfg['stats_column_last_seen_switch'] = 1; else $cfg['stats_column_last_seen_switch'] = 0;
		if (isset($_POST['stats_column_nation_switch'])) $cfg['stats_column_nation_switch'] = 1; else $cfg['stats_column_nation_switch'] = 0;
		if (isset($_POST['stats_column_version_switch'])) $cfg['stats_column_version_switch'] = 1; else $cfg['stats_column_version_switch'] = 0;
		if (isset($_POST['stats_column_platform_switch'])) $cfg['stats_column_platform_switch'] = 1; else $cfg['stats_column_platform_switch'] = 0;
		if (isset($_POST['stats_column_online_time_switch'])) $cfg['stats_column_online_time_switch'] = 1; else $cfg['stats_column_online_time_switch'] = 0;
		if (isset($_POST['stats_column_idle_time_switch'])) $cfg['stats_column_idle_time_switch'] = 1; else $cfg['stats_column_idle_time_switch'] = 0;
		if (isset($_POST['stats_column_active_time_switch'])) $cfg['stats_column_active_time_switch'] = 1; else $cfg['stats_column_active_time_switch'] = 0;
		if (isset($_POST['stats_column_current_server_group_switch'])) $cfg['stats_column_current_server_group_switch'] = 1; else $cfg['stats_column_current_server_group_switch'] = 0;
		if (isset($_POST['stats_column_next_rankup_switch'])) $cfg['stats_column_next_rankup_switch'] = 1; else $cfg['stats_column_next_rankup_switch'] = 0;
		if (isset($_POST['stats_column_next_server_group_switch'])) $cfg['stats_column_next_server_group_switch'] = 1; else $cfg['stats_column_next_server_group_switch'] = 0;
		if (isset($_POST['stats_column_current_group_since_switch'])) $cfg['stats_column_current_group_since_switch'] = 1; else $cfg['stats_column_current_group_since_switch'] = 0;
		if (isset($_POST['stats_show_excepted_clients_switch'])) $cfg['stats_show_excepted_clients_switch'] = 1; else $cfg['stats_show_excepted_clients_switch'] = 0;
		if (isset($_POST['stats_show_clients_in_highest_rank_switch'])) $cfg['stats_show_clients_in_highest_rank_switch'] = 1; else $cfg['stats_show_clients_in_highest_rank_switch'] = 0;
		$cfg['stats_column_default_order'] = $_POST['stats_column_default_order'];
		$cfg['stats_column_default_sort'] = $_POST['stats_column_default_sort'];

		if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_column_rank_switch','{$cfg['stats_column_rank_switch']}'),('stats_column_client_name_switch','{$cfg['stats_column_client_name_switch']}'),('stats_column_unique_id_switch','{$cfg['stats_column_unique_id_switch']}'),('stats_column_client_db_id_switch','{$cfg['stats_column_client_db_id_switch']}'),('stats_column_last_seen_switch','{$cfg['stats_column_last_seen_switch']}'),('stats_column_nation_switch','{$cfg['stats_column_nation_switch']}'),('stats_column_version_switch','{$cfg['stats_column_version_switch']}'),('stats_column_platform_switch','{$cfg['stats_column_platform_switch']}'),('stats_column_online_time_switch','{$cfg['stats_column_online_time_switch']}'),('stats_column_idle_time_switch','{$cfg['stats_column_idle_time_switch']}'),('stats_column_active_time_switch','{$cfg['stats_column_active_time_switch']}'),('stats_column_current_server_group_switch','{$cfg['stats_column_current_server_group_switch']}'),('stats_column_current_group_since_switch','{$cfg['stats_column_current_group_since_switch']}'),('stats_column_next_rankup_switch','{$cfg['stats_column_next_rankup_switch']}'),('stats_column_next_server_group_switch','{$cfg['stats_column_next_server_group_switch']}'),('stats_column_default_order','{$cfg['stats_column_default_order']}'),('stats_column_default_sort','{$cfg['stats_column_default_sort']}'),('stats_show_excepted_clients_switch','{$cfg['stats_show_excepted_clients_switch']}'),('stats_show_clients_in_highest_rank_switch','{$cfg['stats_show_clients_in_highest_rank_switch']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
			$err_msg = print_r($mysqlcon->errorInfo(), true);
			$err_lvl = 3;
		} else {
			$err_msg = $lang['wisvsuc'];
			$err_lvl = NULL;
		}
	} elseif(isset($_POST['update'])) {
		echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
		rem_session_ts3();
		exit;
	}
	?>
			<div id="page-wrapper">
	<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php echo $lang['stnv0029'],' ',$lang['wihlset']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
						<div class="row">
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listrank']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_rank_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_rank_switch" value="',$cfg['stats_column_rank_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_rank_switch" value="',$cfg['stats_column_rank_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listnick']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_client_name_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_client_name_switch" value="',$cfg['stats_column_client_name_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_client_name_switch" value="',$cfg['stats_column_client_name_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listuid']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_unique_id_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_unique_id_switch" value="',$cfg['stats_column_unique_id_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_unique_id_switch" value="',$cfg['stats_column_unique_id_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listcldbid']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_client_db_id_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_client_db_id_switch" value="',$cfg['stats_column_client_db_id_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_client_db_id_switch" value="',$cfg['stats_column_client_db_id_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listseen']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_last_seen_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_last_seen_switch" value="',$cfg['stats_column_last_seen_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_last_seen_switch" value="',$cfg['stats_column_last_seen_switch'],'">';
												} ?>
											</div>
										</div>
										
										
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listnat']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_nation_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_nation_switch" value="',$cfg['stats_column_nation_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_nation_switch" value="',$cfg['stats_column_nation_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listver']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_version_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_version_switch" value="',$cfg['stats_column_version_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_version_switch" value="',$cfg['stats_column_version_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listpla']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_platform_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_platform_switch" value="',$cfg['stats_column_platform_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_platform_switch" value="',$cfg['stats_column_platform_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumo']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_online_time_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_online_time_switch" value="',$cfg['stats_column_online_time_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_online_time_switch" value="',$cfg['stats_column_online_time_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumi']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_idle_time_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_idle_time_switch" value="',$cfg['stats_column_idle_time_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_idle_time_switch" value="',$cfg['stats_column_idle_time_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsuma']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_active_time_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_active_time_switch" value="',$cfg['stats_column_active_time_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_active_time_switch" value="',$cfg['stats_column_active_time_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listacsg']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_current_server_group_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_current_server_group_switch" value="',$cfg['stats_column_current_server_group_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_current_server_group_switch" value="',$cfg['stats_column_current_server_group_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listgrps']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_current_group_since_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_current_group_since_switch" value="',$cfg['stats_column_current_group_since_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_current_group_since_switch" value="',$cfg['stats_column_current_group_since_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listnxup']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_next_rankup_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_next_rankup_switch" value="',$cfg['stats_column_next_rankup_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_next_rankup_switch" value="',$cfg['stats_column_next_rankup_switch'],'">';
												} ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listnxsg']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_next_server_group_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_next_server_group_switch" value="',$cfg['stats_column_next_server_group_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_next_server_group_switch" value="',$cfg['stats_column_next_server_group_switch'],'">';
												} ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishdefdesc"><?php echo $lang['wishdef']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control basic" name="stats_column_default_sort">
												<?PHP
												echo '<option data-icon="fas fa-hashtag" data-subtext="[default]" value="rank"'.($cfg['stats_column_default_sort'] === '1' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listrank'].'</option>';
												echo '<option data-icon="fas fa-user" value="name"'.($cfg['stats_column_default_sort'] === 'name' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listnick'].'</option>';
												echo '<option data-icon="fas fa-id-card" value="uuid"'.($cfg['stats_column_default_sort'] === 'uuid' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listuid'].'</option>';
												echo '<option data-icon="fas fa-database" value="cldbid"'.($cfg['stats_column_default_sort'] === 'cldbid' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listcldbid'].'</option>';
												echo '<option data-icon="fas fa-user-clock" value="lastseen"'.($cfg['stats_column_default_sort'] === 'lastseen' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listseen'].'</option>';
												echo '<option data-icon="fas fa-globe-europe" value="nation"'.($cfg['stats_column_default_sort'] === 'nation' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listnat'].'</option>';
												echo '<option data-icon="fas fa-tag" value="version"'.($cfg['stats_column_default_sort'] === 'version' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listver'].'</option>';
												echo '<option data-icon="fas fa-server" value="platform"'.($cfg['stats_column_default_sort'] === 'platform' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listpla'].'</option>';
												echo '<option data-icon="fas fa-hourglass-start" value="count"'.($cfg['stats_column_default_sort'] === 'count' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listsumo'].'</option>';
												echo '<option data-icon="fas fa-hourglass-end" value="idle"'.($cfg['stats_column_default_sort'] === 'idle' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listsumi'].'</option>';
												echo '<option data-icon="fas fa-hourglass-half" value="active"'.($cfg['stats_column_default_sort'] === 'active' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listsuma'].'</option>';
												echo '<option data-icon="fas fa-clipboard-check" value="grpid"'.($cfg['stats_column_default_sort'] === 'grpid' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listacsg'].'</option>';
												echo '<option data-icon="fas fa-history" value="grpidsince"'.($cfg['stats_column_default_sort'] === 'grpidsince' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listgrps'].'</option>';
												echo '<option data-icon="fas fa-clock" value="nextup"'.($cfg['stats_column_default_sort'] === 'nextup' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listnxup'].'</option>';
												echo '<option data-icon="fas fa-clipboard-list" value="active"'.($cfg['stats_column_default_sort'] === 'active' ? ' selected="selected"' : '').'>&nbsp;'.$lang['listnxsg'].'</option>';
												?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishsortdesc"><?php echo $lang['wishsort']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control basic" name="stats_column_default_order">
												<?PHP
												echo '<option data-subtext="[ASC]" data-icon="fas fa-sort-asc" value="asc"'.($cfg['stats_column_default_order'] === 'asc' ? ' selected="selected"' : '').'>&nbsp;'.$lang['asc'].'</option>';
												echo '<option data-subtext="[DESC]" data-icon="fas fa-sort-desc" value="desc"'.($cfg['stats_column_default_order'] === 'desc' ? ' selected="selected"' : '').'>&nbsp;'.$lang['desc'].'</option>';
												?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="panel-body expertelement">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishexclddesc"><?php echo $lang['wishexcld']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_show_excepted_clients_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_show_excepted_clients_switch" value="',$cfg['stats_show_excepted_clients_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_show_excepted_clients_switch" value="',$cfg['stats_show_excepted_clients_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishhiclddesc"><?php echo $lang['wishhicld']; ?><i class="help-hover fas fa-question-circle"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_show_clients_in_highest_rank_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_show_clients_in_highest_rank_switch" value="',$cfg['stats_show_clients_in_highest_rank_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_show_clients_in_highest_rank_switch" value="',$cfg['stats_show_clients_in_highest_rank_switch'],'">';
											} ?>
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
				</div>
			</div>
		</div>
		
	<div class="modal fade" id="wishcoldesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wishcol']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wishcoldesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wishdefdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wishdef']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wishdefdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wishsortdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wishsort']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wishsortdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
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
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>