<?PHP
session_start();
?>
<!doctype html>
<html>
<head>
	<title>TS-N.NET Ranksystem - Webinterface</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="other/style.css.php" />
	<link rel="stylesheet" type="text/css" href="jquerylib/jquery.autocomplete.css" />
	<script type="text/javascript" src="jquerylib/jquery.js"></script>
	<script type='text/javascript' src='jquerylib/jquery.autocomplete.js'></script>
	<script type="text/javascript">
	function disablediv(div)
	{
		var objDiv = document.getElementById(div);
		objDiv.innerHTML="&nbsp;";
	}
	window.setTimeout("disablediv('alert')",10000);
	
	function showpwd(fieldid)
	{
	document.getElementById(fieldid).type = 'text';
	}
	
	function hidepwd(fieldid)
	{
	document.getElementById(fieldid).type = 'password';
	}
	
	var toggle = function (number) {
	  var layers = document.getElementsByClassName('layers');
	  for(var i = 0; i < layers.length; ++i)
	  {
		if(i == number)
		{
		  layers[i].style.display = 'block';
		}
		else
		{
		  layers[i].style.display = 'none';
		}
	  }
	}

	$().ready(function() {

		function log(event, data, formatted) {
			$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
		}
		
		function formatItem(row) {
			return row[0] + " (<i>uuid: " + row[1] + "</i>)";
		}
		function formatResult(row) {
			return row[0].replace(/(<.+?>)/gi, '');
		}

		$("#clients").autocomplete('other/search.php', {
			width: 420,
			scrollHeight: 300,
			max: 999,
			multiple: true,
			matchContains: true,
			formatItem: formatItem,
			formatResult: formatResult
		});
		
		$(":text, textarea").result(log).next().click(function() {
			$(this).prev().search();
		});
		$("#clients").result(function(event, data, formatted) {
			var hidden = $(this).parent().next().find(">:input");
			hidden.val( (hidden.val() ? hidden.val() + "," : hidden.val()) + data[1]);
		});
		
	});
	</script>
<?PHP
echo '</head><body>';
$starttime = microtime(true);
require_once('other/config.php');
if ($mysqlprob === false) {
	echo '<span class="wncolor">',$sqlconerr,'</span><br>';
	exit;
}
require_once('lang.php');
$alert = "&nbsp;";
if (isset($_POST['changeclients'])) {
    $selectedclients = $_POST['selectedclients'];
    echo $selectedclients;
    echo '<br>';
    $selecteduuids = $_POST['selecteduuids'];
    echo $selecteduuids;
}
if (isset($_POST['updatets'])) {
    $tshost     = $_POST['tshost'];
    $tsquery    = $_POST['tsquery'];
    $tsvoice    = $_POST['tsvoice'];
    $tsuser     = $_POST['tsuser'];
    $tspass     = $_POST['tspass'];
    $queryname  = $_POST['queryname'];
    $queryname2 = $_POST['queryname2'];
    $slowmode   = $_POST['slowmode'];
    if ($slowmode == "on") {
        $slowmode = 1;
    } else {
        $slowmode = 0;
    }
    if ($mysqlcon->exec("UPDATE config set tshost='$tshost',tsquery='$tsquery',tsvoice='$tsvoice',tsuser='$tsuser',tspass='$tspass',queryname='$queryname',queryname2='$queryname2',slowmode='$slowmode'") === false) {
        $alert = '<span class="wncolor">' . $mysqlcon->errorCode() . '</span><br>';
    } else {
        $alert = '<span class="sccolor">' . $lang['wisvsuc'] . '</span>';
    }
    require_once('other/webinterface_list.php');
}
if (isset($_POST['updatecore'])) {
    $grouptime       = $_POST['grouptime'];
    $resetbydbchange = $_POST['resetbydbchange'];
    if ($resetbydbchange == "on") {
        $resetbydbchange = 1;
    } else {
        $resetbydbchange = 0;
    }
    $msgtouser = $_POST['msgtouser'];
    if ($msgtouser == "on") {
        $msgtouser = 1;
    } else {
        $msgtouser = 0;
    }
    $upcheck = $_POST['upcheck'];
    if ($upcheck == "on") {
        $upcheck = 1;
    } else {
        $upcheck = 0;
    }
    $uniqueid       = $_POST['uniqueid'];
    $updateinfotime = $_POST['updateinfotime'];
    $substridle     = $_POST['substridle'];
    if ($substridle == "on") {
        $substridle = 1;
    } else {
        $substridle = 0;
    }
    $exceptuuid  = $_POST['exceptuuid'];
    $exceptgroup = $_POST['exceptgroup'];
    if ($mysqlcon->exec("UPDATE config set grouptime='$grouptime',resetbydbchange='$resetbydbchange',msgtouser='$msgtouser',upcheck='$upcheck',uniqueid='$uniqueid',updateinfotime='$updateinfotime',substridle='$substridle',exceptuuid='$exceptuuid',exceptgroup='$exceptgroup'") === false) {
        $alert = '<span class="wncolor">' . $mysqlcon->errorCode() . '</span><br>';
    } else {
        $alert = '<span class="sccolor">' . $lang['wisvsuc'] . '</span>';
    }
    require_once('other/webinterface_list.php');
}
if (isset($_POST['updatestyle'])) {
    $language   = $_POST['languagedb'];
    $dateformat = $_POST['dateformat'];
    $showexgrp  = $_POST['showexgrp'];
    if ($showexgrp == "on") {
        $showexgrp = 1;
    } else {
        $showexgrp = 0;
    }
    $showexcld = $_POST['showexcld'];
    if ($showexcld == "on") {
        $showexcld = 1;
    } else {
        $showexcld = 0;
    }
    $showcolrg = $_POST['showcolrg'];
    if ($showcolrg == "on") {
        $showcolrg = 1;
    } else {
        $showcolrg = 0;
    }
    $showcolcld = $_POST['showcolcld'];
    if ($showcolcld == "on") {
        $showcolcld = 1;
    } else {
        $showcolcld = 0;
    }
    $showcoluuid = $_POST['showcoluuid'];
    if ($showcoluuid == "on") {
        $showcoluuid = 1;
    } else {
        $showcoluuid = 0;
    }
    $showcoldbid = $_POST['showcoldbid'];
    if ($showcoldbid == "on") {
        $showcoldbid = 1;
    } else {
        $showcoldbid = 0;
    }
    $showcolls = $_POST['showcolls'];
    if ($showcolls == "on") {
        $showcolls = 1;
    } else {
        $showcolls = 0;
    }
    $showcolot = $_POST['showcolot'];
    if ($showcolot == "on") {
        $showcolot = 1;
    } else {
        $showcolot = 0;
    }
    $showcolit = $_POST['showcolit'];
    if ($showcolit == "on") {
        $showcolit = 1;
    } else {
        $showcolit = 0;
    }
    $showcolat = $_POST['showcolat'];
    if ($showcolat == "on") {
        $showcolat = 1;
    } else {
        $showcolat = 0;
    }
    $showcolnx = $_POST['showcolnx'];
    if ($showcolnx == "on") {
        $showcolnx = 1;
    } else {
        $showcolnx = 0;
    }
    $showcolsg = $_POST['showcolsg'];
    if ($showcolsg == "on") {
        $showcolsg = 1;
    } else {
        $showcolsg = 0;
    }
    $bgcolor = $_POST['bgcolor'];
    $hdcolor = $_POST['hdcolor'];
    $txcolor = $_POST['txcolor'];
    $hvcolor = $_POST['hvcolor'];
    $ifcolor = $_POST['ifcolor'];
    $wncolor = $_POST['wncolor'];
    $sccolor = $_POST['sccolor'];
    $showgen = $_POST['showgen'];
    if ($showgen == "on") {
        $showgen = 1;
    } else {
        $showgen = 0;
    }
    include('lang.php');
    if ($mysqlcon->exec("UPDATE config set language='$language',dateformat='$dateformat',showexgrp='$showexgrp',showexcld='$showexcld',showcolrg='$showcolrg',showcolcld='$showcolcld',showcoluuid='$showcoluuid',showcoldbid='$showcoldbid',showcolls='$showcolls',showcolot='$showcolot',showcolit='$showcolit',showcolat='$showcolat',showcolnx='$showcolnx',showcolsg='$showcolsg',bgcolor='$bgcolor',hdcolor='$hdcolor',txcolor='$txcolor',hvcolor='$hvcolor',ifcolor='$ifcolor',wncolor='$wncolor',sccolor='$sccolor',showgen='$showgen'") === false) {
        $alert = '<span class="wncolor">' . $mysqlcon->errorCode() . '</span><br>';
    } else {
        $alert = '<span class="sccolor">' . $lang['wisvsuc'] . '</span>';
    }
    require_once('other/webinterface_list.php');
}
if (isset($_POST['selectivclients'])) {
    $seluuid   = $_POST['selecteduuids'];
    $uuidarr   = explode(',', $seluuid);
    $counttime = $_POST['counttime'];
    if ($_POST['delclients'] == "on" && $seluuid != '' && $counttime == 0) {
        require_once('ts3_lib/TeamSpeak3.php');
        $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
        try {
            $ts3_VirtualServer->selfUpdate(array(
                'client_nickname' => $queryname
            ));
        }
        catch (Exception $e) {
            try {
                $ts3_VirtualServer->selfUpdate(array(
                    'client_nickname' => $queryname2
                ));
            }
            catch (Exception $e) {
                echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
            }
        }
        foreach ($uuidarr as $uuid) {
            if ($_POST['delsrvgrp'] == "on") {
                $dbremsgrp = $mysqlcon->query("SELECT cldbid,grpid from user where uuid='$uuid'");
                while ($remsgrp = $dbremsgrp->fetch(PDO::FETCH_ASSOC)) {
                    if ($remsgrp['grpid'] != 0) {
                        try {
                            $ts3_VirtualServer->serverGroupClientDel($remsgrp['grpid'], $remsgrp['cldbid']);
                        }
                        catch (Exception $e) {
                            $alert = $alert . '<span class="wncolor">' . sprintf($lang['errremgrp'], $uuid, $remsgrp['grpid']) . $e->getCode() . ': ' . $e->getMessage() . '</span><br>';
                        }
                    }
                }
            }
            if ($mysqlcon->exec("DELETE FROM user WHERE uuid='$uuid'") === false) {
                $alert = $alert . '<span class="wncolor">' . sprintf($lang['errremdb'], $uuid) . $mysqlcon->errorCode() . '</span><br>';
            } else {
                $alert = $alert . '<span class="sccolor">' . sprintf($lang['sccrmcld'], $uuid) . '</span><br>';
            }
        }
    } elseif ($_POST['delclients'] == "" && $seluuid != '' && $counttime != 0) {
        $dtF       = new DateTime("@0");
        $dtT       = new DateTime("@$counttime");
        $timecount = $dtF->diff($dtT)->format($timeformat);
        foreach ($uuidarr as $uuid) {
            if ($mysqlcon->exec("UPDATE user SET count='$counttime' WHERE uuid='$uuid'") === false) {
                $alert = $alert . '<span class="wncolor">' . sprintf($lang['errupcount'], $timecount, $uuid) . $mysqlcon->errorCode() . '</span><br>';
            } else {
                $alert = $alert . '<span class="sccolor">' . sprintf($lang['sccupcount'], $uuid, $timecount) . '</span><br>';
            }
        }
    } else {
        echo $_POST['delclients'];
        $alert = '<span class="wncolor">' . sprintf($lang['errsel'], $seluuid, $_POST['delclients'], $counttime) . '</span>';
    }
    require_once('other/webinterface_list.php');
}
if (isset($_POST['globalclients'])) {
	if($_POST['delcldgrps'] == "on") {
		$selectbefore = $mysqlcon->query("SELECT * FROM user WHERE grpid!='0'");
		$before       = $selectbefore->rowCount();
		if($mysqlcon->exec("UPDATE user SET grpid='0'") && $selectbefore->rowCount() != 0) {
			$alert = '<span class="sccolor">' . sprintf($lang['delcldgrpsc'], $before) . '</span>';
		} elseif($selectbefore->rowCount() == 0) {
			$alert = '<span class="ifcolor">' . sprintf($lang['delcldgrpsc'], $before) . '</span>';
		} else {
			$alert = '<span class="wncolor">' . sprintf($lang['delcldgrpif'], $selectbefore->errorCode()) . '</span>';
		}
	} else {
		$selectbefore = $mysqlcon->query("SELECT * FROM user");
		$before       = $selectbefore->rowCount();
		$cleantime    = time() - $_POST['cleantime'];
		if ($_POST['delsrvgrp'] == "on") {
			require_once('ts3_lib/TeamSpeak3.php');
			$ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
			try {
				$ts3_VirtualServer->selfUpdate(array(
					'client_nickname' => $queryname
				));
			}
			catch (Exception $e) {
				try {
					$ts3_VirtualServer->selfUpdate(array(
						'client_nickname' => $queryname2
					));
				}
				catch (Exception $e) {
					echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
				}
			}
			$dbremsgrp = $mysqlcon->query("SELECT cldbid,grpid from user where lastseen<'$cleantime'");
			$dbremsgrp = $dbremsgrp->fetchAll();
			foreach ($dbremsgrp as $remsgrp) {
				if ($remsgrp['grpid'] != 0) {
					$ts3_VirtualServer->serverGroupClientDel($remsgrp['grpid'], $remsgrp['cldbid']);
				}
			}
		}
		if ($_POST['cleantime'] < 1) {
			$dbcount = $mysqlcon->exec("DELETE from user");
		} else {
			$dbcount = $mysqlcon->exec("DELETE from user where lastseen<'$cleantime'");
		}
		$selectafter = $mysqlcon->query("SELECT * from user");
		$after       = $selectafter->rowCount();
		$countdel    = $before - $after;
		if ($countdel == 0) {
			$alert = '<span class="ifcolor">' . sprintf($lang['delclientsif'], $countdel) . '</span>';
		} else {
			$alert = '<span class="sccolor">' . sprintf($lang['delclientssc'], $countdel) . '</span>';
		}
	}
    require_once('other/webinterface_list.php');
}
if (isset($_POST['updatetdbsettings'])) {
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
		$handle=fopen('./other/dbconfig.php','w');
		if(!fwrite($handle,$newconfig))
		{
			$alert = '<span class="wncolor">' . sprintf($lang['widbcfgerr']) . '</span>';
		} else {
			$alert = '<span class="sccolor">' . sprintf($lang['widbcfgsuc']) . '</span>';
		}
		fclose($handle);
	} catch (PDOException $e) {
		$alert = '<span class="wncolor">' . sprintf($lang['widbcfgerr']) . '</span>';
	}
	require_once('other/webinterface_list.php');
}
if (is_file('install.php') || is_file('update_0-02.php') || is_file('update_0-10.php')) {
    echo sprintf($lang['isntwidel'], "<a href=\"webinterface.php\">webinterface.php</a>");
} else {
    if (isset($_GET['logout']) == "true") {
        session_destroy();
        header("location:webinterface.php");
    } elseif (isset($_POST['abschicken']) || isset($_SESSION['username'])) {
        if (isset($_SESSION['username']) || ($_POST['username'] == $webuser && $_POST['password'] == $webpass)) {
            $_SESSION['username'] = $webuser;
			set_error_handler(function() { });
            $newversion = file_get_contents('http://ts-n.net/ranksystem/version');
			restore_error_handler();
            if (substr($newversion, 0, 4) != substr($currvers, 0, 4) && $newversion != '') {
				$alert = '<a href="http://ts-n.net/ranksystem.php" target="_blank"><span class="ifcolor">Update available!</span></a>';
			}
            require_once('other/webinterface_list.php');
        } else {
            $showerrlogin = 1;
            require_once('other/webinterface_login.php');
        }
    } else {
        session_destroy();
        require_once('other/webinterface_login.php');
    }
}
?>