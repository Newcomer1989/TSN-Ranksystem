<?PHP
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
    $_SESSION = array();
    session_destroy();
	if($_SERVER['HTTPS'] == "on") {
		header("Location: https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	} else {
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	}
	exit;
}

if (!isset($_SESSION['username']) || $_SESSION['username'] != $webuser || $_SESSION['password'] != $webpass || $_SESSION['clientip'] != getclientip()) {
	if($_SERVER['HTTPS'] == "on") {
		header("Location: https://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	} else {
		header("Location: http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	}
	exit;
}

require_once('nav.php');

if (isset($_POST['update']) && $_SESSION['username'] == $webuser && $_SESSION['password'] == $webpass && $_SESSION['clientip'] == getclientip()) {
	$newconfig='<?php
$db[\'type\']="'.$_POST['dbtype'].'";
$db[\'host\']="'.$_POST['dbhost'].'";
$db[\'user\']="'.$_POST['dbuser'].'";
$db[\'pass\']="'.$_POST['dbpass'].'";
$db[\'dbname\']="'.$_POST['dbname'].'";
?>';
	$dbserver = $_POST['dbtype'].':host='.$_POST['dbhost'].';dbname='.$_POST['dbname'];
	try {
		$mysqlcon = new PDO($dbserver, $_POST['dbuser'], $_POST['dbpass']);
		$handle=fopen('../other/dbconfig.php','w');
		if(!fwrite($handle,$newconfig))
		{
			$err_msg = sprintf($lang['widbcfgerr']);
			$err_lvl = 3;
		} else {
			$err_msg = $lang['wisvsuc']." ".$lang['wisvres'];
			$err_lvl = 0;
		}
		fclose($handle);
	} catch (PDOException $e) {
		$err_msg = sprintf($lang['widbcfgerr']);
		$err_lvl = 3;
	}
	$db['type']		= $_POST['dbtype'];
	$db['host']		= $_POST['dbhost'];
	$dbname			= $_POST['dbname'];
	$db['user']		= $_POST['dbuser'];
	$db['pass']		= $_POST['dbpass'];
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['wihldb']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" name="update" method="POST">
					<div class="row">
						<div class="col-md-3">
						</div>
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbtypedesc"><?php echo $lang['isntwidbtype']; ?></label>
										<div class="col-sm-8">
											<select class="selectpicker show-tick form-control" id="basic" name="dbtype" required>
											<option disabled value=""> -- select database -- </option>
											<?PHP
											echo '<option data-subtext="Cubrid" value="cubrid"'; if($db['type']=="cubrid") echo " selected=selected"; echo '>cubrid</option>';
											echo '<option data-subtext="FreeTDS / Microsoft SQL Server / Sybase" value="dblib"'; if($db['type']=="dblib") echo " selected=selected"; echo '>dblib</option>';
											echo '<option data-subtext="Firebird/Interbase 6" value="firebird"'; if($db['type']=="firebird") echo " selected=selected"; echo '>firebird</option>';
											echo '<option data-subtext="IBM DB2" value="ibm"'; if($db['type']=="ibm") echo " selected=selected"; echo '>ibm</option>';
											echo '<option data-subtext="IBM Informix Dynamic Server" value="informix"'; if($db['type']=="informix") echo " selected=selected"; echo '>informix</option>';
											echo '<option data-subtext="MySQL 3.x/4.x/5.x [recommend]" value="mysql"'; if($db['type']=="mysql") echo " selected=selected"; echo '>mysql</option>';
											echo '<option data-subtext="Oracle Call Interface" value="oci"'; if($db['type']=="oci") echo " selected=selected"; echo '>oci</option>';
											echo '<option data-subtext="ODBC v3 (IBM DB2, unixODBC und win32 ODBC)" value="odbc"'; if($db['type']=="odbc") echo " selected=selected"; echo '>odbc</option>';
											echo '<option data-subtext="PostgreSQL" value="pgsql"'; if($db['type']=="pgsql") echo " selected=selected"; echo '>pgsql</option>';
											echo '<option data-subtext="SQLite 3 und SQLite 2" value="sqlite"'; if($db['type']=="sqlite") echo " selected=selected"; echo '>sqlite</option>';
											echo '<option data-subtext="Microsoft SQL Server / SQL Azure" value="sqlsrv"'; if($db['type']=="sqlsrv") echo " selected=selected"; echo '>sqlsrv</option>';
											echo '<option data-subtext="4D" value="4d"'; if($db['type']=="4d") echo " selected=selected"; echo '>4d</option>';
											?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbhostdesc"><?php echo $lang['isntwidbhost']; ?></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control" name="dbhost" value="<?php echo $db['host']; ?>" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbnamedesc"><?php echo $lang['isntwidbname']; ?></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control" name="dbname" value="<?php echo $dbname; ?>" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">&nbsp;</div>
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbusrdesc"><?php echo $lang['isntwidbusr']; ?></label>
										<div class="col-sm-8 required-field-block">
											<input type="text" class="form-control" name="dbuser" value="<?php echo $db['user']; ?>" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label" data-toggle="modal" data-target="#isntwidbpassdesc"><?php echo $lang['isntwidbpass']; ?></label>
										<div class="col-sm-8 required-field-block">
											<input type="password" class="form-control" name="dbpass" value="<?php echo $db['pass']; ?>" data-toggle="password" data-placement="before" required>
											<div class="required-icon"><div class="text">*</div></div>
										</div>
									</div>
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
	
<div class="modal fade" id="isntwidbtypedesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['isntwidbtype']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['isntwidbtypedesc']; ?>
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
</body>
</html>