<?php
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
echo'</head><body>';
$starttime=microtime(true);
require_once('other/config.php');
require_once('lang.php');

$alert="&nbsp;";

if(isset($_POST['changeclients']))
{
	$selectedclients=$_POST["selectedclients"];
	echo $selectedclients;
	echo '<br>';
	$selecteduuids=$_POST["selecteduuids"];
	echo $selecteduuids;
}
if(isset($_POST['updatets']))
{
	$tshost=$_POST["tshost"];
	$tsquery=$_POST["tsquery"];
	$tsvoice=$_POST["tsvoice"];
	$tsuser=$_POST["tsuser"];
	$tspass=$_POST["tspass"];
	$queryname=$_POST["queryname"];
	$queryname2=$_POST["queryname2"];
	if(!$mysqlcon->query("UPDATE config set tshost='$tshost',tsquery='$tsquery',tsvoice='$tsvoice',tsuser='$tsuser',tspass='$tspass',queryname='$queryname',queryname2='$queryname2'"))
	{
		$alert='<wncolor>'.$lang['error'].$mysqlcon->error.'</wncolor>';
	}
	else
	{
		$alert='<sccolor>'.$lang['wisvsuc'].'</sccolor>';
	}
	require_once('other/webinterface_list.php');
}
if(isset($_POST['updatecore']))
{
	$grouptime=$_POST["grouptime"];
	$resetbydbchange=$_POST["resetbydbchange"];
	if($resetbydbchange=="on"){$resetbydbchange=1;}else{$resetbydbchange=0;}
	$msgtouser=$_POST["msgtouser"];
	if($msgtouser=="on"){$msgtouser=1;}else{$msgtouser=0;}
	$upcheck=$_POST["upcheck"];
	if($upcheck=="on"){$upcheck=1;}else{$upcheck=0;}
	$uniqueid=$_POST["uniqueid"];
	$updateinfotime=$_POST["updateinfotime"];
	$substridle=$_POST["substridle"];
	if($substridle=="on"){$substridle=1;}else{$substridle=0;}
	$exceptuuid=$_POST["exceptuuid"];
	$exceptgroup=$_POST["exceptgroup"];
	if(!$mysqlcon->query("UPDATE config set grouptime='$grouptime',resetbydbchange='$resetbydbchange',msgtouser='$msgtouser',upcheck='$upcheck',uniqueid='$uniqueid',updateinfotime='$updateinfotime',substridle='$substridle',exceptuuid='$exceptuuid',exceptgroup='$exceptgroup'"))
	{
		$alert='<wncolor>'.$lang['error'].$mysqlcon->error.'</wncolor>';
	}
	else
	{
		$alert='<sccolor>'.$lang['wisvsuc'].'</sccolor>';
	}
	require_once('other/webinterface_list.php');
}
if(isset($_POST['updatestyle']))
{
	$language=$_POST["languagedb"];
	$dateformat=$_POST["dateformat"];
	$showexgrp=$_POST["showexgrp"];
	if($showexgrp=="on"){$showexgrp=1;}else{$showexgrp=0;}
	$showexcld=$_POST["showexcld"];
	if($showexcld=="on"){$showexcld=1;}else{$showexcld=0;}
	$showcolcld=$_POST["showcolcld"];
	if($showcolcld=="on"){$showcolcld=1;}else{$showcolcld=0;}
	$showcoluuid=$_POST["showcoluuid"];
	if($showcoluuid=="on"){$showcoluuid=1;}else{$showcoluuid=0;}
	$showcoldbid=$_POST["showcoldbid"];
	if($showcoldbid=="on"){$showcoldbid=1;}else{$showcoldbid=0;}
	$showcolot=$_POST["showcolot"];
	if($showcolot=="on"){$showcolot=1;}else{$showcolot=0;}
	$showcolit=$_POST["showcolit"];
	if($showcolit=="on"){$showcolit=1;}else{$showcolit=0;}
	$showcolat=$_POST["showcolat"];
	if($showcolat=="on"){$showcolat=1;}else{$showcolat=0;}
	$showcolnx=$_POST["showcolnx"];
	if($showcolnx=="on"){$showcolnx=1;}else{$showcolnx=0;}
	$showcolsg=$_POST["showcolsg"];
	if($showcolsg=="on"){$showcolsg=1;}else{$showcolsg=0;}
	$bgcolor=$_POST["bgcolor"];
	$hdcolor=$_POST["hdcolor"];
	$txcolor=$_POST["txcolor"];
	$hvcolor=$_POST["hvcolor"];
	$ifcolor=$_POST["ifcolor"];
	$wncolor=$_POST["wncolor"];
	$sccolor=$_POST["sccolor"];
	$showgen=$_POST["showgen"];
	if($showgen=="on"){$showgen=1;}else{$showgen=0;}
	include('lang.php');
	
	if(!$mysqlcon->query("UPDATE config set language='$language',dateformat='$dateformat',showexgrp='$showexgrp',showexcld='$showexcld',showcolcld='$showcolcld',showcoluuid='$showcoluuid',showcoldbid='$showcoldbid',showcolot='$showcolot',showcolit='$showcolit',showcolat='$showcolat',showcolnx='$showcolnx',showcolsg='$showcolsg',bgcolor='$bgcolor',hdcolor='$hdcolor',txcolor='$txcolor',hvcolor='$hvcolor',ifcolor='$ifcolor',wncolor='$wncolor',sccolor='$sccolor',showgen='$showgen'"))
	{
		$alert='<wncolor>'.$lang['error'].$mysqlcon->error.'</wncolor>';
	}
	else
	{
		$alert='<sccolor>'.$lang['wisvsuc'].'</sccolor>';
	}
	require_once('other/webinterface_list.php');
}
if(isset($_POST['selectivclients']))
{
	$seluuid=$_POST["selecteduuids"];
	$uuidarr=explode(',',$seluuid);
	$counttime=$_POST["counttime"];
	if($_POST["delclients"]=="on" && $seluuid!='' && $counttime==0)
	{
		require_once('ts3_lib/TeamSpeak3.php');
		$ts3_VirtualServer=TeamSpeak3::factory("serverquery://".$ts['user'].":".$ts['pass']."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']);
		try
		{
			$ts3_VirtualServer->selfUpdate(array('client_nickname'=>$queryname));
		}
		catch(Exception $e)
		{
			try
			{
				$ts3_VirtualServer->selfUpdate(array('client_nickname'=>$queryname2));
			}
			catch(Exception $e)
			{
				echo $lang['error'].$e->getCode().': '.$e->getMessage();
			}
		}
		foreach($uuidarr as $uuid)
		{
			if($_POST['delsrvgrp']=="on")
			{
				$dbremsgrp=$mysqlcon->query("SELECT cldbid,grpid from user where uuid='$uuid'");
				while($remsgrp=$dbremsgrp->fetch_assoc())
				{
					if($remsgrp["grpid"]!=0)
					{
						try
						{
							$ts3_VirtualServer->serverGroupClientDel($remsgrp["grpid"],$remsgrp["cldbid"]);
						}
						catch(Exception $e)
						{
							$alert=$alert.'<wncolor>'.sprintf($lang['errremgrp'],$uuid,$remsgrp["grpid"]).$e->getCode().': '.$e->getMessage().'</wncolor><br>';
						}
					}
				}
			}
			if(!$mysqlcon->query("DELETE FROM user WHERE uuid='$uuid'") || $mysqlcon->affected_rows==0)
			{
				$alert=$alert.'<wncolor>'.sprintf($lang['errremdb'],$uuid).$mysqlcon->error.'</wncolor><br>';
			}
			else
			{
				$alert=$alert.'<sccolor>'.sprintf($lang['sccrmcld'],$uuid).'</sccolor><br>';
			}
		}
	}
	elseif($_POST["delclients"]=="" && $seluuid!='' && $counttime!=0)
	{
		$dtF=new DateTime("@0");
		$dtT=new DateTime("@$counttime");
		$timecount=$dtF->diff($dtT)->format($timeformat);
		foreach($uuidarr as $uuid)
		{
			if(!$mysqlcon->query("UPDATE user SET count='$counttime' WHERE uuid='$uuid'") || $mysqlcon->affected_rows==0)
			{
				$alert=$alert.'<wncolor>'.sprintf($lang['errupcount'],$timecount,$uuid).$mysqlcon->error.'</wncolor><br>';
			}
			else
			{
				$alert=$alert.'<sccolor>'.sprintf($lang['sccupcount'],$uuid,$timecount).'</sccolor><br>';
			}
		}
	}
	else
	{
		echo $_POST["delclients"];
		$alert='<wncolor>error by choosing selections</wncolor>';
	}
	require_once('other/webinterface_list.php');
}
if(isset($_POST['globalclients']))
{
	$selectbefore=$mysqlcon->query("SELECT count(*) from user");
	$before=$selectbefore->fetch_row();
	$cleantime=time() - $_POST["cleantime"];
	if($_POST['delsrvgrp']=="on")
	{
		require_once('ts3_lib/TeamSpeak3.php');
		$ts3_VirtualServer=TeamSpeak3::factory("serverquery://".$ts['user'].":".$ts['pass']."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']);
		try
		{
			$ts3_VirtualServer->selfUpdate(array('client_nickname'=>$queryname));
		}
		catch(Exception $e)
		{
			try
			{
				$ts3_VirtualServer->selfUpdate(array('client_nickname'=>$queryname2));
			}
			catch(Exception $e)
			{
				echo $lang['error'].$e->getCode().': '.$e->getMessage();
			}
		}
		$dbremsgrp=$mysqlcon->query("SELECT cldbid,grpid from user where lastseen<'$cleantime'");
		while($remsgrp=$dbremsgrp->fetch_assoc())
		{
			if($remsgrp["grpid"]!=0)
			{
				$ts3_VirtualServer->serverGroupClientDel($remsgrp["grpid"],$remsgrp["cldbid"]);
			}
		}
	}

	if($_POST["cleantime"]<1)
	{
		$dbcount=$mysqlcon->query("DELETE from user");
	}
	else
	{	
		$dbcount=$mysqlcon->query("DELETE from user where lastseen<'$cleantime'");
	}
	$selectafter=$mysqlcon->query("SELECT count(*) from user");
	$after=$selectafter->fetch_row();
	$countdel=$before[0] - $after[0];
	if($countdel==0)
	{
		$alert='<ifcolor>'.sprintf($lang['delclientsif'],$countdel).'</ifcolor>';
	}
	else
	{
		$alert='<sccolor>'.sprintf($lang['delclientssc'],$countdel).'</sccolor>';
	}
	require_once('other/webinterface_list.php');
}
if(is_file('install.php') || is_file('update_0-02.php') || is_file('update_0-10.php'))
{
	echo sprintf($lang['isntwidel'],"<a href=\"webinterface.php\">webinterface.php</a>");
}
else
{
	if(isset($_GET['logout'])=="true")
	{
		session_destroy();
		header("location:webinterface.php");
	}
	elseif(isset($_POST['abschicken']) || isset($_SESSION['username']))
	{
		$access=$mysqlcon->query("SELECT * FROM config");
		$access=$access->fetch_row();
		if(isset($_SESSION['username']) || ($_POST["username"]==$access[0] && $_POST["password"]==$access[1]))
		{
			$_SESSION['username']=$access[0];
			require_once('other/webinterface_list.php');
		}
		else
		{
			$showerrlogin=1;
			require_once('other/webinterface_login.php');
		}
	}
	else
	{
		session_destroy();
		require_once('other/webinterface_login.php');
	}
}
?>
