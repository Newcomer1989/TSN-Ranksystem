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
		$newconfig='<?php
	$db[\'type\']=\''.$_POST['dbtype'].'\';
	$db[\'host\']=\''.$_POST['dbhost'].'\';
	$db[\'user\']=\''.$_POST['dbuser'].'\';
	$db[\'pass\']=\''.$_POST['dbpass'].'\';
	$db[\'dbname\']=\''.$_POST['dbname'].'\';
	?>';
		$dbserver = $_POST['dbtype'].':host='.$_POST['dbhost'].';dbname='.$_POST['dbname'].';charset=utf8mb4';
		try {
			$mysqlcon = new PDO($dbserver, $_POST['dbuser'], $_POST['dbpass']);
			$handle=fopen('../other/dbconfig.php','w');
			if(!fwrite($handle,$newconfig))
			{
				$err_msg = sprintf($lang['widbcfgerr']);
				$err_lvl = 3;
			} else {
				$err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><input type="hidden" name="csrf_token" value="'.$csrf_token.'"><button
			type="submit" class="btn btn-primary" name="restart"><i class="fas fa-sync"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
				$err_lvl = 0;
				$db['type']	= $_POST['dbtype'];
				$db['host']	= $_POST['dbhost'];
				$dbname		= $_POST['dbname'];
				$db['user']	= $_POST['dbuser'];
				$db['pass']	= $_POST['dbpass'];
			}
			fclose($handle);
		} catch (PDOException $e) {
			$err_msg = sprintf($lang['widbcfgerr']);
			$err_lvl = 3;
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
								<?php echo $lang['winav2'],' ',$lang['wihlset']; ?>
							</h1>
						</div>
					</div>
					<form class="form-horizontal" data-toggle="validator" name="update" method="POST">
					<input type="hidden" name="csrf_token" value="<?PHP echo $csrf_token; ?>">
						<div class="row">
							<div class="col-md-3">
							</div>
							<div class="col-md-6">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbtypedesc"><?php echo $lang['isntwidbtype']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8">
												<select class="selectpicker show-tick form-control required" id="basic" name="dbtype" required>
												<option disabled value=""> -- select database -- </option>
												<option data-divider="true">&nbsp;</option>
												<?PHP
												echo '<option data-subtext="Cubrid" value="cubrid"'; if($db['type']=="cubrid") echo " selected=selected"; echo '>cubrid</option>';
												echo '<option data-subtext="FreeTDS / Microsoft SQL Server / Sybase" value="dblib"'; if($db['type']=="dblib") echo " selected=selected"; echo '>dblib</option>';
												echo '<option data-subtext="Firebird/Interbase 6" value="firebird"'; if($db['type']=="firebird") echo " selected=selected"; echo '>firebird</option>';
												echo '<option data-subtext="IBM DB2" value="ibm"'; if($db['type']=="ibm") echo " selected=selected"; echo '>ibm</option>';
												echo '<option data-subtext="IBM Informix Dynamic Server" value="informix"'; if($db['type']=="informix") echo " selected=selected"; echo '>informix</option>';
												echo '<option data-subtext="MySQL 3.x/4.x/5.x [recommended]" value="mysql"'; if($db['type']=="mysql") echo " selected=selected"; echo '>mysql</option>';
												echo '<option data-subtext="Oracle Call Interface" value="oci"'; if($db['type']=="oci") echo " selected=selected"; echo '>oci</option>';
												echo '<option data-subtext="ODBC v3 (IBM DB2, unixODBC und win32 ODBC)" value="odbc"'; if($db['type']=="odbc") echo " selected=selected"; echo '>odbc</option>';
												echo '<option data-subtext="PostgreSQL" value="pgsql"'; if($db['type']=="pgsql") echo " selected=selected"; echo '>pgsql</option>';
												echo '<option data-subtext="SQLite 3 und SQLite 2" value="sqlite"'; if($db['type']=="sqlite") echo " selected=selected"; echo '>sqlite</option>';
												echo '<option data-subtext="Microsoft SQL Server / SQL Azure" value="sqlsrv"'; if($db['type']=="sqlsrv") echo " selected=selected"; echo '>sqlsrv</option>';
												echo '<option data-subtext="4D" value="4d"'; if($db['type']=="4d") echo " selected=selected"; echo '>4d</option>';
												?>
												</select>
											</div>
											<div class="help-block with-errors"></div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbhostdesc"><?php echo $lang['isntwidbhost']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8 required-field-block">
												<input type="text" class="form-control required" name="dbhost" value="<?php echo $db['host']; ?>" required>
												<div class="help-block with-errors"></div>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbnamedesc"><?php echo $lang['isntwidbname']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8 required-field-block">
												<input type="text" data-pattern="^([A-Za-z0-9$_]){1,64}$" data-error="Please do not use special characters or more then 64 characters inside the database name!" class="form-control required" name="dbname" value="<?php echo $dbname; ?>" required>
												<div class="help-block with-errors"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">&nbsp;</div>
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbusrdesc"><?php echo $lang['isntwidbusr']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8 required-field-block">
												<input type="text" data-pattern="^[^&quot;'\\-\s]+$" data-error="Please do not use one of the following special characters: ' \ &quot; - also no whitespace and do not user more then 64 characters inside the database user!" class="form-control required" name="dbuser" value="<?php echo $db['user']; ?>" required>
												<div class="help-block with-errors"></div>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbpassdesc"><?php echo $lang['isntwidbpass']; ?><i class="help-hover fas fa-question-circle"></i></label>
											<div class="col-sm-8 required-field-block">
												<div class="input-group">
													<span id="toggle-password2" class="input-group-addon" onclick="togglepwd()" style="cursor: pointer; pointer-events: all;"><svg class="svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z"></path></svg></span>
													<span id="toggle-password1" class="input-group-addon" onclick="togglepwd()" style="cursor: pointer; pointer-events: all; display: none;"><svg class="svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z"></path></svg></span>
													<input id="password" type="password" class="form-control required" name="dbpass" value="<?php echo $db['pass']; ?>" required>
												</div>
												<div class="help-block with-errors"></div>
											</div>
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
		
	<div class="modal fade" id="isntwidbtypedesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbtype']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo sprintf($lang['isntwidbtypedesc'], '<a href="https://ts-ranksystem.com/#linux" target="_blank">https://ts-ranksystem.com/#linux</a>'); ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbhostdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbhost']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbhostdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbusrdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbusr']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbusrdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbpassdesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbpass']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbpassdesc']; ?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<div class="modal fade" id="isntwidbnamedesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['isntwidbname']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php echo $lang['isntwidbnamedesc']; ?>
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
<?PHP
} catch(Throwable $ex) { }
?>