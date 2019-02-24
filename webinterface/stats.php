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
	if (isset($_POST['stats_column_rank_switch'])) $cfg['stats_column_rank_switch'] = 1; else $cfg['stats_column_rank_switch'] = 0;
	if (isset($_POST['stats_column_client_name_switch'])) $cfg['stats_column_client_name_switch'] = 1; else $cfg['stats_column_client_name_switch'] = 0;
	if (isset($_POST['stats_column_unique_id_switch'])) $cfg['stats_column_unique_id_switch'] = 1; else $cfg['stats_column_unique_id_switch'] = 0;
	if (isset($_POST['stats_column_client_db_id_switch'])) $cfg['stats_column_client_db_id_switch'] = 1; else $cfg['stats_column_client_db_id_switch'] = 0;
	if (isset($_POST['stats_column_last_seen_switch'])) $cfg['stats_column_last_seen_switch'] = 1; else $cfg['stats_column_last_seen_switch'] = 0;
	if (isset($_POST['stats_column_online_time_switch'])) $cfg['stats_column_online_time_switch'] = 1; else $cfg['stats_column_online_time_switch'] = 0;
	if (isset($_POST['stats_column_idle_time_switch'])) $cfg['stats_column_idle_time_switch'] = 1; else $cfg['stats_column_idle_time_switch'] = 0;
	if (isset($_POST['stats_column_active_time_switch'])) $cfg['stats_column_active_time_switch'] = 1; else $cfg['stats_column_active_time_switch'] = 0;
	if (isset($_POST['stats_column_current_server_group_switch'])) $cfg['stats_column_current_server_group_switch'] = 1; else $cfg['stats_column_current_server_group_switch'] = 0;
	if (isset($_POST['stats_column_next_rankup_switch'])) $cfg['stats_column_next_rankup_switch'] = 1; else $cfg['stats_column_next_rankup_switch'] = 0;
	if (isset($_POST['stats_column_next_server_group_switch'])) $cfg['stats_column_next_server_group_switch'] = 1; else $cfg['stats_column_next_server_group_switch'] = 0;
	if (isset($_POST['stats_column_current_group_since_switch'])) $cfg['stats_column_current_group_since_switch'] = 1; else $cfg['stats_column_current_group_since_switch'] = 0;
	if (isset($_POST['stats_show_excepted_clients_switch'])) $cfg['stats_show_excepted_clients_switch'] = 1; else $cfg['stats_show_excepted_clients_switch'] = 0;
	if (isset($_POST['stats_show_clients_in_highest_rank_switch'])) $cfg['stats_show_clients_in_highest_rank_switch'] = 1; else $cfg['stats_show_clients_in_highest_rank_switch'] = 0;
	if (isset($_POST['stats_show_site_navigation_switch'])) $cfg['stats_show_site_navigation_switch'] = 1; else $cfg['stats_show_site_navigation_switch'] = 0;
	
	if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_column_rank_switch','{$cfg['stats_column_rank_switch']}'),('','{$cfg['stats_column_client_name_switch']}'),('stats_column_unique_id_switch','{$cfg['stats_column_unique_id_switch']}'),('stats_column_client_db_id_switch','{$cfg['stats_column_client_db_id_switch']}'),('stats_column_last_seen_switch','{$cfg['stats_column_last_seen_switch']}'),('stats_column_online_time_switch','{$cfg['stats_column_online_time_switch']}'),('stats_column_idle_time_switch','{$cfg['stats_column_idle_time_switch']}'),('stats_column_active_time_switch','{$cfg['stats_column_active_time_switch']}'),('stats_column_current_server_group_switch','{$cfg['stats_column_current_server_group_switch']}'),('stats_column_current_group_since_switch','{$cfg['stats_column_current_group_since_switch']}'),('stats_column_next_rankup_switch','{$cfg['stats_column_next_rankup_switch']}'),('stats_column_next_server_group_switch','{$cfg['stats_column_next_server_group_switch']}'),('stats_show_excepted_clients_switch','{$cfg['stats_show_excepted_clients_switch']}'),('stats_show_clients_in_highest_rank_switch','{$cfg['stats_show_clients_in_highest_rank_switch']}'),('stats_show_site_navigation_switch','{$cfg['stats_show_site_navigation_switch']}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc'];
		$err_lvl = NULL;
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
						<h1 class="page-header">
							<?php echo $lang['winav6'],' ',$lang['wihlset']; ?>
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
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolrgdesc"><?php echo $lang['wishcolrg']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_rank_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_rank_switch" value="',$cfg['stats_column_rank_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_rank_switch" value="',$cfg['stats_column_rank_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolclddesc"><?php echo $lang['wishcolcld']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_client_name_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_client_name_switch" value="',$cfg['stats_column_client_name_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_client_name_switch" value="',$cfg['stats_column_client_name_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoluuiddesc"><?php echo $lang['wishcoluuid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_unique_id_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_unique_id_switch" value="',$cfg['stats_column_unique_id_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_unique_id_switch" value="',$cfg['stats_column_unique_id_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcoldbiddesc"><?php echo $lang['wishcoldbid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_client_db_id_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_client_db_id_switch" value="',$cfg['stats_column_client_db_id_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_client_db_id_switch" value="',$cfg['stats_column_client_db_id_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcollsdesc"><?php echo $lang['wishcolls']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_last_seen_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_last_seen_switch" value="',$cfg['stats_column_last_seen_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_last_seen_switch" value="',$cfg['stats_column_last_seen_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolotdesc"><?php echo $lang['wishcolot']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_online_time_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_online_time_switch" value="',$cfg['stats_column_online_time_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_online_time_switch" value="',$cfg['stats_column_online_time_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolitdesc"><?php echo $lang['wishcolit']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_idle_time_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_idle_time_switch" value="',$cfg['stats_column_idle_time_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_idle_time_switch" value="',$cfg['stats_column_idle_time_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolatdesc"><?php echo $lang['wishcolat']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_active_time_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_active_time_switch" value="',$cfg['stats_column_active_time_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_active_time_switch" value="',$cfg['stats_column_active_time_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolasdesc"><?php echo $lang['wishcolas']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_current_server_group_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_current_server_group_switch" value="',$cfg['stats_column_current_server_group_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_current_server_group_switch" value="',$cfg['stats_column_current_server_group_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolgsdesc"><?php echo $lang['wishcolgs']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_current_group_since_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_current_group_since_switch" value="',$cfg['stats_column_current_group_since_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_current_group_since_switch" value="',$cfg['stats_column_current_group_since_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolnxdesc"><?php echo $lang['wishcolnx']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
										<div class="col-sm-8">
											<?PHP if ($cfg['stats_column_next_rankup_switch'] == 1) {
												echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_column_next_rankup_switch" value="',$cfg['stats_column_next_rankup_switch'],'">';
											} else {
												echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_column_next_rankup_switch" value="',$cfg['stats_column_next_rankup_switch'],'">';
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishcolsgdesc"><?php echo $lang['wishcolsg']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
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
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishexclddesc"><?php echo $lang['wishexcld']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<?PHP if ($cfg['stats_show_excepted_clients_switch'] == 1) {
										echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_show_excepted_clients_switch" value="',$cfg['stats_show_excepted_clients_switch'],'">';
									} else {
										echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_show_excepted_clients_switch" value="',$cfg['stats_show_excepted_clients_switch'],'">';
									} ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishhiclddesc"><?php echo $lang['wishhicld']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<?PHP if ($cfg['stats_show_clients_in_highest_rank_switch'] == 1) {
										echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_show_clients_in_highest_rank_switch" value="',$cfg['stats_show_clients_in_highest_rank_switch'],'">';
									} else {
										echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_show_clients_in_highest_rank_switch" value="',$cfg['stats_show_clients_in_highest_rank_switch'],'">';
									} ?>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wishnavdesc"><?php echo $lang['wishnav']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<?PHP if ($cfg['stats_show_site_navigation_switch'] == 1) {
										echo '<input class="switch-animate" type="checkbox" checked data-size="mini" name="stats_show_site_navigation_switch" value="',$cfg['stats_show_site_navigation_switch'],'">';
									} else {
										echo '<input class="switch-animate" type="checkbox" data-size="mini" name="stats_show_site_navigation_switch" value="',$cfg['stats_show_site_navigation_switch'],'">';
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