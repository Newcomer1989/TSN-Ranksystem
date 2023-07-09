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

    if (isset($_POST['update']) && isset($db_csrf[$_POST['csrf_token']])) {
        $stats_api_keys = $err_msg = '';

        if (isset($_POST['apikey']) && isset($_POST['desc'])) {
            $apidefinition = [];
            foreach ($_POST['apikey'] as $rowid => $apikey) {
                $desc = isset($_POST['desc'][$rowid]) ? $_POST['desc'][$rowid] : null;
                if (isset($_POST['perm_bot']) && in_array($rowid, $_POST['perm_bot'])) {
                    $perm_bot = 1;
                } else {
                    $perm_bot = 0;
                }
                $apidefinition[] = "$apikey=>$desc=>$perm_bot";
            }

            $stats_api_keys = implode(',', $apidefinition);

            $cfg['stats_api_keys'] = $stats_api_keys;
        } else {
            $cfg['stats_api_keys'] = null;
        }

        if ($mysqlcon->exec("INSERT INTO `$dbname`.`cfg_params` (`param`,`value`) VALUES ('stats_api_keys',".$mysqlcon->quote($cfg['stats_api_keys']).") ON DUPLICATE KEY UPDATE `value`=VALUES(`value`); DELETE FROM `$dbname`.`csrf_token` WHERE `token`='{$_POST['csrf_token']}'") === false) {
            $err_msg = print_r($mysqlcon->errorInfo(), true);
            $err_lvl = 3;
        } else {
            $err_msg = $lang['wisvsuc'];
            $err_lvl = null;
        }

        if (empty($stats_api_keys)) {
            $cfg['stats_api_keys'] = null;
        } else {
            $keyarr = explode(',', $stats_api_keys);
            foreach ($keyarr as $entry) {
                list($key, $desc, $perm_bot) = explode('=>', $entry);
                $addnewvalue[$key] = ['key'=>$key, 'desc'=>$desc, 'perm_bot'=>$perm_bot];
                $cfg['stats_api_keys'] = $addnewvalue;
            }
        }
    } elseif (isset($_POST['update'])) {
        echo '<div class="alert alert-danger alert-dismissible">',$lang['errcsrf'],'</div>';
        rem_session_ts3();
        exit;
    }
    ?>
			<div id="page-wrapper" class="webinterface_api">
	<?php if (isset($err_msg)) {
	    error_handling($err_msg, $err_lvl);
	} ?>
				<div class="container-fluid">
					
					<form class="form-horizontal" data-toggle="validator" name="update" method="POST" id="new">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header">
									<span><?php echo $lang['api'],' ',$lang['wihlset']; ?></span>
								</h1>
							</div>
						</div>
						<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-sm-12 pointer" data-toggle="modal" data-target="#wiapidesc"><?php echo $lang['wihladm0']; ?><i class="help-hover fas fa-question-circle"></i></label>
									<div class="panel-body">
										<div class="row">&nbsp;</div>
										<div class="row">&nbsp;</div>
										<div class="form-group">
											<div class="col-sm-4">
												<b><?php echo $lang['apikey'] ?></b>
											</div>
											<div class="col-sm-1">
												<b><?php echo $lang['permission'] ?></b>
											</div>
											<div class="col-sm-1">
												<b><?php echo '' ?></b>
											</div>
											<div class="col-sm-5">
												<b><?php echo $lang['descr']; ?></b>
											</div>
											<div class="col-sm-1"></div>
										</div>
										<div class="form-group hidden" name="template">
											<div class="col-sm-4">
												<input type="text" data-pattern="^[a-zA-Z0-9]{1,64}$" data-error="No special characters allowed and maximum 64 characters!" maxlength="64" class="form-control" name="tempapikey[]" value="<?php $apikey = bin2hex(openssl_random_pseudo_bytes(32));
    echo $apikey; ?>">
												<div class="help-block with-errors"></div>
											</div>
											<div class="col-sm-1">
												<span class="d-inline-block" data-toggle="tooltip" title="Permission: Allow to start/stop Ranksystem Bot via API (ON = Allow ; OFF = Deny)"><input class="switch-animate" type="checkbox" data-size="mini" name="temp_perm_bot[]" value=""></span>
											</div>
											<div class="col-sm-1 text-left"></div>
											<div class="col-sm-5">
												<input type="text" data-pattern="^[^,=>]{1,128}$" data-error="No comma, equal sign or greater-than sign allowed and maximum 128 characters!" maxlength="128" class="form-control" name="tempdesc[]" value="" placeholder="set a description..">
												<div class="help-block with-errors"></div>
											</div>
											<div class="col-sm-1 text-center delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;" title="delete line"></i></div>
											<div class="col-sm-2"></div>
										</div>
									<?php
                                    $rowid = 0;
    if (isset($cfg['stats_api_keys']) && $cfg['stats_api_keys'] != '') {
        foreach ($cfg['stats_api_keys'] as $apikey) {
            ?>
										<div class="form-group" name="apidef">
											<div class="col-sm-4">
												<input type="text" data-pattern="^[a-zA-Z0-9]{1,64}$" data-error="No special characters allowed and maximum 64 characters!" maxlength="64" class="form-control" name="apikey[]" value="<?php echo $apikey['key']; ?>">
												<div class="help-block with-errors"></div>
											</div>
											<div class="col-sm-1">
												<?php if ($apikey['perm_bot'] == 1) {
												    echo '<span class="d-inline-block" data-toggle="tooltip" title="'.$lang['apiperm001'].' - '.$lang['apipermdesc'].'"><input class="switch-animate" type="checkbox" checked data-size="mini" name="perm_bot[]" value="',$rowid,'"></span>';
												} else {
												    echo '<span class="d-inline-block" data-toggle="tooltip" title="'.$lang['apiperm001'].' - '.$lang['apipermdesc'].'"><input class="switch-animate" type="checkbox" data-size="mini" name="perm_bot[]" value="',$rowid,'"></span>';
												} ?>
											</div>
											<div class="col-sm-1 text-left">
												<span class="item-margin"><i class="fas fa-link" onclick="openurl('../api/?apikey=<?php echo $apikey['key']; ?>')" style="margin-top:10px;cursor:pointer;" title="open URL"></i></span>
												<span class="item-margin"><i class="fas fa-copy" onclick="copyurl('<?php echo $_SERVER['SERVER_NAME'],substr(dirname($_SERVER['SCRIPT_NAME']), 0, -12),'api/?apikey=',$apikey['key']; ?>')" style="margin-top:10px;cursor:pointer;" title="copy URL to clipboard"></i></span>
											</div>
											<div class="col-sm-5">
												<input type="text" data-pattern="^[^,=>]{1,128}$" data-error="No comma, equal sign or greater-than sign allowed and maximum 128 characters!" maxlength="128" class="form-control" name="desc[]" value="<?php echo $apikey['desc']; ?>" placeholder="set a description..">
												<div class="help-block with-errors"></div>
											</div>
											<div class="col-sm-1 text-center delete" name="delete"><i class="fas fa-trash" style="margin-top:10px;cursor:pointer;" title="delete line"></i></div>
											<div class="col-sm-2"></div>
										</div>
										<?php
                                        $rowid++;
        }
    }
    ?>
										<div class="form-group" id="addapikey">
											<?php
            if (! isset($cfg['stats_api_keys'])) {
                echo '<div class="col-sm-11"><div id="noentry"><i>',$lang['wiboostempty'],'</i></div></div>';
            } else {
                echo '<div class="col-sm-11"></div>';
            }?>
											<div class="col-sm-1 text-center">
												<span class="d-inline-block" data-toggle="tooltip" title="Add new line">
													<button class="btn btn-primary" onclick="addapikey()" style="margin-top: 5px;" type="button"><i class="fas fa-plus"></i></button>
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
				</div>
			</div>
		</div>

	<div class="modal fade" id="wiapidesc" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title"><?php echo $lang['api']; ?></h4>
		  </div>
		  <div class="modal-body">
			<?php
            $host = '<a href="//'.$_SERVER['HTTP_HOST'].substr(rtrim(dirname($_SERVER['PHP_SELF']), '/\\'), 0, -12).'api" target="_blank">'.$_SERVER['HTTP_HOST'].substr(rtrim(dirname($_SERVER['PHP_SELF']), '/\\'), 0, -12).'api</a>';
    echo sprintf($lang['wiapidesc'], $host); ?>
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
	function addapikey() {
		var $clone = $("div[name='template']").last().clone();
		var $lastvalue = $("div[name='apidef'] input[name='perm_bot[]']").last().val();
		$lastvalue++;
		$clone.removeClass("hidden");
		$clone.attr('name','apidef');
		$clone.insertBefore("#addapikey");
		$("input[name='tempapikey[]']").last().attr('name', 'apikey[]');
		$("input[name='tempdesc[]']").last().attr('name', 'desc[]');
		$("input[name='temp_perm_bot[]']").last().attr('name', 'perm_bot[]');
		$("div[name='apidef'] input[name='perm_bot[]']").last().attr('value',$lastvalue);
		$("div[name='apidef'] input[name='perm_bot[]']").last().bootstrapSwitch();
		$('.delete').removeClass("hidden");
		if (document.contains(document.getElementById("noentry"))) {
			document.getElementById("noentry").remove();
		}

		var newapikey = {
			_pattern : /[a-z0-9]/,
			_getRandomByte : function() {
				if(window.crypto && window.crypto.getRandomValues) {
					var result = new Uint8Array(1);
					window.crypto.getRandomValues(result);
					return result[0];
				} else if(window.msCrypto && window.msCrypto.getRandomValues) {
					var result = new Uint8Array(1);
					window.msCrypto.getRandomValues(result);
					return result[0];
				} else {
					return Math.floor(Math.random() * 256);
				}
			},

			generate : function(length) {
				return Array.apply(null, {'length': length})
				.map(function() {
					var result;
					while(true) {
						result = String.fromCharCode(this._getRandomByte());
						if(this._pattern.test(result)) {
							return result;
						}
					}
				}, this)
				.join('');
			}
		};
		$("input[name='apikey[]']").last().attr('value', newapikey.generate(64));
	};
	$(document).on("click", ".delete", function(){
		$(this).parent().remove();
	});
	function openurl(url) {
		window.open(url,'_blank');
	}
	function copyurl(url) {
	  navigator.clipboard.writeText(url).then(function() { });
	}
	$("[name='perm_bot[]']").bootstrapSwitch();
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	</script>
	</body>
	</html>
<?php
} catch(Throwable $ex) {
}
?>