<?PHP
session_start();

require_once('../other/config.php');
require_once('../other/csrf_handler.php');

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

if (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $webuser || $_SESSION[$rspathhex.'password'] != $webpass || $_SESSION[$rspathhex.'clientip'] != getclientip()) {
	header("Location: //".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
	exit;
}

require_once('nav.php');

if (isset($_POST['update']) && $_SESSION[$rspathhex.'username'] == $webuser && $_SESSION[$rspathhex.'password'] == $webpass && $_SESSION[$rspathhex.'clientip'] == getclientip()) {
	$timezone 		= $_POST['timezone'];
	$timeformat 	= $_POST['dateformat'];
	$logpath		= addslashes($_POST['logpath']);
	$language  		= $_POST['languagedb'];
	$adminuuid     	= $_POST['adminuuid'];
	if ($mysqlcon->exec("UPDATE $dbname.config set timezone='$timezone',dateformat='$timeformat',logpath='$logpath',language='$language',adminuuid='$adminuuid'") === false) {
        $err_msg = print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
    } else {
        $err_msg = $lang['wisvsuc']." ".sprintf($lang['wisvres'], '&nbsp;&nbsp;<form class="btn-group" name="restart" action="bot.php" method="POST"><button
		type="submit" class="btn btn-primary" name="restart"><i class="fa fa-fw fa-refresh"></i>&nbsp;'.$lang['wibot7'].'</button></form>');
		$err_lvl = NULL;
    }
	$logpath				= $_POST['logpath'];
	$config['adminuuid']	= $_POST['adminuuid'];
}
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, $err_lvl); ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo $lang['wihlvs']; ?>
						</h1>
					</div>
				</div>
				<form class="form-horizontal" data-toggle="validator" name="update" method="POST">
					<?php echo $CSRF; ?>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#witimedesc"><?php echo $lang['witime']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control" data-live-search="true" name="timezone">
									<?PHP
									$timezonearr = DateTimeZone::listIdentifiers();
									foreach ($timezonearr as $timez) {
										if ($timez == $timezone) {
											echo '<option value="'.$timezone,'" selected=selected>',$timezone,'</option>';
										} else {
											echo '<option value="',$timez,'">',$timez,'</option>';
										}
									}
									?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#widaformdesc"><?php echo $lang['widaform']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="dateformat" value="<?php echo $timeformat; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wilogdesc"><?php echo $lang['wilog']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8 required-field-block">
									<input type="text" class="form-control" data-pattern=".*(\/|\\)$" data-error="The Logpath must end with / or \" name="logpath" value="<?php echo $logpath; ?>" required>
									<div class="help-block with-errors"></div>
									<div class="required-icon"><div class="text">*</div></div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wivlangdesc"><?php echo $lang['wivlang']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8">
									<select class="selectpicker show-tick form-control" name="languagedb">
									<?PHP
									echo '<option data-subtext="العربية" value="ar"'.($language === 'ar' ? ' selected="selected"' : '').'>AR</option>';
									echo '<option data-subtext="čeština" value="cz"'.($language === 'cz' ? ' selected="selected"' : '').'>CZ</option>';
									echo '<option data-subtext="Deutsch" value="de"'.($language === 'de' ? ' selected="selected"' : '').'>DE</option>';
									echo '<option data-subtext="English" value="en"'.($language === 'en' ? ' selected="selected"' : '').'>EN</option>';
									echo '<option data-subtext="français" value="fr"'.($language === 'fr' ? ' selected="selected"' : '').'>FR</option>';
									echo '<option data-subtext="Italiano" value="it"'.($language === 'it' ? ' selected="selected"' : '').'>IT</option>';
									echo '<option data-subtext="Nederlands" value="nl"'.($language === 'nl' ? ' selected="selected"' : '').'>NL</option>';
									echo '<option data-subtext="Română" value="ro"'.($language === 'ro' ? ' selected="selected"' : '').'>RO</option>';
									echo '<option data-subtext="Pусский" value="ru"'.($language === 'ru' ? ' selected="selected"' : '').'>RU</option>';
									echo '<option data-subtext="Português" value="pt"'.($language === 'pt' ? ' selected="selected"' : '').'>PT</option>';
									?>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-label" data-toggle="modal" data-target="#wiadmuuiddesc"><?php echo $lang['wiadmuuid']; ?><i class="help-hover glyphicon glyphicon-question-sign"></i></label>
								<div class="col-sm-8 required-field-block">
									<textarea class="form-control" data-pattern="^([A-Za-z0-9\\\/\+]{27}=,)*([A-Za-z0-9\\\/\+]{27}=)$" data-error="Check all unique IDs are correct and your list do not ends with a comma!" rows="1" name="adminuuid" maxlength="500"><?php echo $config['adminuuid']; ?></textarea>
											<div class="help-block with-errors"></div>
									<div class="required-icon"><div class="text">*</div></div>
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

<div class="modal fade" id="witimedesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['witime']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['witimedesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="widaformdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['widaform']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['widaformdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wilogdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wilog']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wilogdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wivlangdesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wivlang']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wivlangdesc']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?PHP echo $lang['stnv0002']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="wiadmuuiddesc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $lang['wiadmuuid']; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $lang['wiadmuuiddesc']; ?>
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