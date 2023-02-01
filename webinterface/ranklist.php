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
		if (isset($_POST['stats_column_online_day_switch'])) $cfg['stats_column_online_day_switch'] = 1; else $cfg['stats_column_online_day_switch'] = 0;
		if (isset($_POST['stats_column_idle_day_switch'])) $cfg['stats_column_idle_day_switch'] = 1; else $cfg['stats_column_idle_day_switch'] = 0;
		if (isset($_POST['stats_column_active_day_switch'])) $cfg['stats_column_active_day_switch'] = 1; else $cfg['stats_column_active_day_switch'] = 0;
		if (isset($_POST['stats_column_online_week_switch'])) $cfg['stats_column_online_week_switch'] = 1; else $cfg['stats_column_online_week_switch'] = 0;
		if (isset($_POST['stats_column_idle_week_switch'])) $cfg['stats_column_idle_week_switch'] = 1; else $cfg['stats_column_idle_week_switch'] = 0;
		if (isset($_POST['stats_column_active_week_switch'])) $cfg['stats_column_active_week_switch'] = 1; else $cfg['stats_column_active_week_switch'] = 0;
		if (isset($_POST['stats_column_online_month_switch'])) $cfg['stats_column_online_month_switch'] = 1; else $cfg['stats_column_online_month_switch'] = 0;
		if (isset($_POST['stats_column_idle_month_switch'])) $cfg['stats_column_idle_month_switch'] = 1; else $cfg['stats_column_idle_month_switch'] = 0;
		if (isset($_POST['stats_column_active_month_switch'])) $cfg['stats_column_active_month_switch'] = 1; else $cfg['stats_column_active_month_switch'] = 0;
		if (isset($_POST['stats_show_excepted_clients_switch'])) $cfg['stats_show_excepted_clients_switch'] = 1; else $cfg['stats_show_excepted_clients_switch'] = 0;
		if (isset($_POST['stats_show_clients_in_highest_rank_switch'])) $cfg['stats_show_clients_in_highest_rank_switch'] = 1; else $cfg['stats_show_clients_in_highest_rank_switch'] = 0;

		$cfg['stats_column_default_order'] = $_POST['stats_column_default_order'];
		$cfg['stats_column_default_sort'] = $_POST['stats_column_default_sort'];
		$cfg['stats_column_default_order_2'] = $_POST['stats_column_default_order_2'];
		$cfg['stats_column_default_sort_2'] = $_POST['stats_column_default_sort_2'];

		if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_column_rank_switch','{$cfg['stats_column_rank_switch']}'),('stats_column_client_name_switch','{$cfg['stats_column_client_name_switch']}'),('stats_column_unique_id_switch','{$cfg['stats_column_unique_id_switch']}'),('stats_column_client_db_id_switch','{$cfg['stats_column_client_db_id_switch']}'),('stats_column_last_seen_switch','{$cfg['stats_column_last_seen_switch']}'),('stats_column_nation_switch','{$cfg['stats_column_nation_switch']}'),('stats_column_version_switch','{$cfg['stats_column_version_switch']}'),('stats_column_platform_switch','{$cfg['stats_column_platform_switch']}'),('stats_column_online_time_switch','{$cfg['stats_column_online_time_switch']}'),('stats_column_idle_time_switch','{$cfg['stats_column_idle_time_switch']}'),('stats_column_active_time_switch','{$cfg['stats_column_active_time_switch']}'),('stats_column_current_server_group_switch','{$cfg['stats_column_current_server_group_switch']}'),('stats_column_current_group_since_switch','{$cfg['stats_column_current_group_since_switch']}'),('stats_column_online_day_switch','{$cfg['stats_column_online_day_switch']}'),('stats_column_idle_day_switch','{$cfg['stats_column_idle_day_switch']}'),('stats_column_active_day_switch','{$cfg['stats_column_active_day_switch']}'),('stats_column_online_week_switch','{$cfg['stats_column_online_week_switch']}'),('stats_column_idle_week_switch','{$cfg['stats_column_idle_week_switch']}'),('stats_column_active_week_switch','{$cfg['stats_column_active_week_switch']}'),('stats_column_online_month_switch','{$cfg['stats_column_online_month_switch']}'),('stats_column_idle_month_switch','{$cfg['stats_column_idle_month_switch']}'),('stats_column_active_month_switch','{$cfg['stats_column_active_month_switch']}'),('stats_column_next_rankup_switch','{$cfg['stats_column_next_rankup_switch']}'),('stats_column_next_server_group_switch','{$cfg['stats_column_next_server_group_switch']}'),('stats_column_default_order','{$cfg['stats_column_default_order']}'),('stats_column_default_sort','{$cfg['stats_column_default_sort']}'),('stats_column_default_order_2','{$cfg['stats_column_default_order_2']}'),('stats_column_default_sort_2','{$cfg['stats_column_default_sort_2']}'),('stats_show_excepted_clients_switch','{$cfg['stats_show_excepted_clients_switch']}'),('stats_show_clients_in_highest_rank_switch','{$cfg['stats_show_clients_in_highest_rank_switch']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
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
			<div id="page-wrapper" class="webinterface_ranklist">
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
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumo'] ,' ', $lang['stix0013']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_online_day_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_online_day_switch" value="',$cfg['stats_column_online_day_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_online_day_switch" value="',$cfg['stats_column_online_day_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumi'] ,' ', $lang['stix0013']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_idle_day_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_idle_day_switch" value="',$cfg['stats_column_idle_day_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_idle_day_switch" value="',$cfg['stats_column_idle_day_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsuma'] ,' ', $lang['stix0013']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_active_day_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_active_day_switch" value="',$cfg['stats_column_active_day_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_active_day_switch" value="',$cfg['stats_column_active_day_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumo'] ,' ', $lang['stix0014']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_online_week_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_online_week_switch" value="',$cfg['stats_column_online_week_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_online_week_switch" value="',$cfg['stats_column_online_week_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumi'] ,' ', $lang['stix0014']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_idle_week_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_idle_week_switch" value="',$cfg['stats_column_idle_week_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_idle_week_switch" value="',$cfg['stats_column_idle_week_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsuma'] ,' ', $lang['stix0014']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_active_week_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_active_week_switch" value="',$cfg['stats_column_active_week_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_active_week_switch" value="',$cfg['stats_column_active_week_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumo'] ,' ', $lang['stix0015']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_online_month_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_online_month_switch" value="',$cfg['stats_column_online_month_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_online_month_switch" value="',$cfg['stats_column_online_month_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsumi'] ,' ', $lang['stix0015']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_idle_month_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_idle_month_switch" value="',$cfg['stats_column_idle_month_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_idle_month_switch" value="',$cfg['stats_column_idle_month_switch'],'">';
												} ?>
											</div>
										</div><div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldesc"><?php echo $lang['listsuma'] ,' ', $lang['stix0015']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<?PHP if ($cfg['stats_column_active_month_switch'] == 1) {
													echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_active_month_switch" value="',$cfg['stats_column_active_month_switch'],'">';
												} else {
													echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_active_month_switch" value="',$cfg['stats_column_active_month_switch'],'">';
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
												$arr_sort_options = sort_options($lang);
												foreach ($arr_sort_options as $opt => $val) {
													echo '<option data-icon="'.$val['icon'].'" value="'.$val['option'].'"'.($cfg['stats_column_default_sort'] === $val['option'] ? ' selected="selected"' : '').'><span class="item-margin">'.$val['title'].'</span></option>';
												}
												?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishsortdesc"><?php echo $lang['wishsort']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control basic" name="stats_column_default_order">
												<?PHP
												echo '<option data-subtext="[ASC]" data-icon="fas fa-sort-asc" value="asc"'.($cfg['stats_column_default_order'] === 'asc' ? ' selected="selected"' : '').'><span class="item-margin">'.$lang['asc'].'</span></option>';
												echo '<option data-subtext="[DESC]" data-icon="fas fa-sort-desc" value="desc"'.($cfg['stats_column_default_order'] === 'desc' ? ' selected="selected"' : '').'><span class="item-margin">'.$lang['desc'].'</span></option>';
												?>
												</select>
											</div>
										</div>
										<div class="row">&nbsp;</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishdef2desc"><?php echo $lang['wishdef2']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control basic" name="stats_column_default_sort_2">
												<?PHP
												foreach ($arr_sort_options as $opt => $val) {
													echo '<option data-icon="'.$val['icon'].'" value="'.$val['option'].'"'.($cfg['stats_column_default_sort_2'] === $val['option'] ? ' selected="selected"' : '').'><span class="item-margin">'.$val['title'].'</span></option>';
												}
												?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishsort2desc"><?php echo $lang['wishsort2']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control basic" name="stats_column_default_order_2">
												<?PHP
												echo '<option data-subtext="[ASC]" data-icon="fas fa-sort-asc" value="asc"'.($cfg['stats_column_default_order_2'] === 'asc' ? ' selected="selected"' : '').'><span class="item-margin">'.$lang['asc'].'</span></option>';
												echo '<option data-subtext="[DESC]" data-icon="fas fa-sort-desc" value="desc"'.($cfg['stats_column_default_order_2'] === 'desc' ? ' selected="selected"' : '').'><span class="item-margin">'.$lang['desc'].'</span></option>';
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
								<button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i><span class="item-margin"><?php echo $lang['wisvconf']; ?></span></button>
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
	<div class="modal fade" id="wishdef2desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wishdef2']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wishdef2desc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="wishsort2desc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['wishsort2']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['wishsort2desc']; ?>
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
	<script>
	$("[name='stats_column_rank_switch']").bootstrapSwitch();
	$("[name='stats_column_client_name_switch']").bootstrapSwitch();
	$("[name='stats_column_unique_id_switch']").bootstrapSwitch();
	$("[name='stats_column_client_db_id_switch']").bootstrapSwitch();
	$("[name='stats_column_last_seen_switch']").bootstrapSwitch();
	$("[name='stats_column_nation_switch']").bootstrapSwitch();
	$("[name='stats_column_version_switch']").bootstrapSwitch();
	$("[name='stats_column_platform_switch']").bootstrapSwitch();
	$("[name='stats_column_online_time_switch']").bootstrapSwitch();
	$("[name='stats_column_idle_time_switch']").bootstrapSwitch();
	$("[name='stats_column_active_time_switch']").bootstrapSwitch();
	$("[name='stats_column_current_server_group_switch']").bootstrapSwitch();
	$("[name='stats_column_next_rankup_switch']").bootstrapSwitch();
	$("[name='stats_column_next_server_group_switch']").bootstrapSwitch();
	$("[name='stats_column_current_group_since_switch']").bootstrapSwitch();
	$("[name='stats_show_excepted_clients_switch']").bootstrapSwitch();
	$("[name='stats_show_clients_in_highest_rank_switch']").bootstrapSwitch();
	$("[name='stats_column_online_day_switch']").bootstrapSwitch();
	$("[name='stats_column_idle_day_switch']").bootstrapSwitch();
	$("[name='stats_column_active_day_switch']").bootstrapSwitch();
	$("[name='stats_column_online_week_switch']").bootstrapSwitch();
	$("[name='stats_column_idle_week_switch']").bootstrapSwitch();
	$("[name='stats_column_active_week_switch']").bootstrapSwitch();
	$("[name='stats_column_online_month_switch']").bootstrapSwitch();
	$("[name='stats_column_idle_month_switch']").bootstrapSwitch();
	$("[name='stats_column_active_month_switch']").bootstrapSwitch();
	</script>
	</body>
	</html>
<?PHP
} catch(Throwable $ex) { }
?>