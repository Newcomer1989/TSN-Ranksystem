<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
<?PHP
echo'</head><body>';
$starttime=microtime(true);
require_once('other/config.php');
require_once('lang.php');
require_once('ts3_lib/TeamSpeak3.php');

if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL-Database: ".mysqli_connect_error();
}

try
{
	$ts3_VirtualServer=TeamSpeak3::factory("serverquery://".$ts['user'].":".$ts['pass']."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']);

	$nowtime=time();

	try
	{
		$ts3_VirtualServer->selfUpdate(array('client_nickname'=>$queryname));
	}
	catch(Exception $e)
	{
		try
		{
			$ts3_VirtualServer->selfUpdate(array('client_nickname'=>$queryname2));
			echo $lang['queryname'].'<br><br>';
		}
		catch(Exception $e)
		{
			echo $lang['error'].$e->getCode().': '.$e->getMessage();
		}
	}

	if($update==1)
	{
		$updatetime=$nowtime-$updateinfotime;
		$lastupdate=$mysqlcon->query("SELECT * FROM upcheck");
		$lastupdate=$lastupdate->fetch_row();
		if($lastupdate[0]<$updatetime)
		{
			$newversion=file_get_contents('http://ts-n.net/ranksystem/version');
			if(substr($newversion,0,4)!=substr($currvers,0,4))
			{
				echo'<b>'.$lang['upinf'].'</b><br>';
				foreach($uniqueid as $clientid)
				{
					try
					{
						$ts3_VirtualServer->clientGetByUid($clientid)->message(sprintf($lang['upmsg'],$currvers,$newversion));
						echo'<sccolor>'.sprintf($lang['upusrinf'],$clientid).'</sccolor><br>';
					}
					catch(Exception $e)
					{
						echo'<wncolor>'.sprintf($lang['upusrerr'],$clientid).'</wncolor><br>';
					}
				}
				echo'<br><br>';
			}
			if(!$mysqlcon->query("UPDATE upcheck SET timestamp=$nowtime"))
			{
				echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
			}
		}
	}

	echo'<hdcolor><b>'.$lang['crawl'].'</b></hdcolor><br>';
	$dbdata=$mysqlcon->query("SELECT * FROM user");
	if($dbdata->num_rows==0)
	{
		echo $lang['firstuse'].'<br><br>';
		$uidarr[]="firstrun";
		$count=1;
		if(!$mysqlcon->query("INSERT INTO user (uuid, lastseen) VALUES ('lastscantime','$nowtime')"))
		{
			echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
		}
	}
	else
	{
		if(!$mysqlcon->query("UPDATE user SET lastseen='$nowtime' WHERE uuid='lastscantime'"))
		{
			echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
		}
		while($uuid=$dbdata->fetch_assoc())
		{
			$sqlhis[$uuid['uuid']]=array("cldbid"=>$uuid['cldbid'],"count"=>$uuid['count'],"lastseen"=>$uuid['lastseen'],"grpid"=>$uuid['grpid'],"nextup"=>$uuid['nextup'],"idle"=>$uuid['idle'],"cldgroup"=>$uuid['cldgroup']);
			$uidarr[]=$uuid['uuid'];
		}
	}

	$allclients=$ts3_VirtualServer->clientList();
	$ts3groups=$ts3_VirtualServer->serverGroupList();
	$yetonline[]='';
	$insertdata='';
	krsort($grouptime);
	$nextupforinsert = key($grouptime) - 1;
	foreach($allclients as $client)
	{
		$sumentries++;
		$cldbid=$client['client_database_id'];
		$ip=ip2long($client['connection_client_ip']);
		$name=htmlspecialchars($client['client_nickname'],ENT_QUOTES);
		$uid=htmlspecialchars($client['client_unique_identifier'],ENT_QUOTES);
		$cldgroup=$client['client_servergroups'];
		$sgroups=explode(",",$cldgroup);
		if(!in_array($uid,$yetonline) && $client['client_version']!="ServerQuery")
		{
			$clientidle=floor($client['client_idle_time'] / 1000);
			$yetonline[]=$uid;
			if(in_array($uid,$uidarr))
			{
				$idle=$sqlhis[$uid]["idle"]+$clientidle;
				$grpid=$sqlhis[$uid]["grpid"];
				$nextup=$sqlhis[$uid]["nextup"];
				if($sqlhis[$uid]["cldbid"]!=$cldbid && $resetbydbchange==1)
				{
					echo'<wncolor>'.sprintf($lang['changedbid'],$name,$uid,$cldbid,$sqlhis[$uid]["cldbid"]).'</wncolor><br>';
					$count=1;
					$idle=0;
				}
				else
				{
					$count=$nowtime-$sqlhis["lastscantime"]["lastseen"]+$sqlhis[$uid]["count"];
					if($clientidle>($nowtime - $sqlhis["lastscantime"]["lastseen"]))
					{
						$idle=$nowtime - $sqlhis["lastscantime"]["lastseen"]+$sqlhis[$uid]["idle"];
					}
				}
				$dtF=new DateTime("@0");
				if($substridle==1)
				{
					$activetime=$count - $idle;
				}
				else
				{
					$activetime=$count;
				}
				$dtT=new DateTime("@$activetime");
				foreach($grouptime as $time => $groupid)
				{
					if(in_array($groupid,$sgroups))
					{
						$grpid=$groupid;
						break;
					}
				}
				foreach($grouptime as $time => $groupid)
				{
					
					if($activetime>$time && !in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup))
					{
						if($sqlhis[$uid]["grpid"]!=$groupid)
						{
							if($sqlhis[$uid]["grpid"]!=0 && in_array($sqlhis[$uid]["grpid"],$sgroups))
							{
								try
								{
									$ts3_VirtualServer->serverGroupClientDel($sqlhis[$uid]["grpid"],$cldbid);
									echo'<sifcolor>'.sprintf($lang['sgrprm'],$sqlhis[$uid]["grpid"],$name,$uid,$cldbid).'</ifcolor><br>';
								}
								catch(Exception $e)
								{
									echo'<wncolor>'.sprintf($lang['sgrprerr'],$name,$uid,$cldbid).'</wncolor><br>';
								}
							}
							if(!in_array($groupid,$sgroups))
							{
								try
								{
									$ts3_VirtualServer->serverGroupClientAdd($groupid,$cldbid);
									echo'<ifcolor>'.sprintf($lang['sgrpadd'],$groupid,$name,$uid,$cldbid).'</ifcolor><br>';
								}
								catch(Exception $e)
								{
									echo'<wncolor>'.sprintf($lang['sgrprerr'],$name,$uid,$cldbid).'</wncolor><br>';
								}
							}
							$grpid=$groupid;
							if($msgtouser==1)
							{
								$days=$dtF->diff($dtT)->format('%a');
								$hours=$dtF->diff($dtT)->format('%h');
								$mins=$dtF->diff($dtT)->format('%i');
								$secs=$dtF->diff($dtT)->format('%s');
								if($substridle==1)
								{
									$ts3_VirtualServer->clientGetByUid($uid)->message(sprintf($lang['usermsgactive'],$days,$hours,$mins,$secs));
								}
								else
								{
									$ts3_VirtualServer->clientGetByUid($uid)->message(sprintf($lang['usermsgonline'],$days,$hours,$mins,$secs));
								}
							}
						}
						break;
					}
					else
					{
						$nextup=$time - $activetime;
					}
				}
				$updatedata[]=array("uuid"=>$uid,"cldbid"=>$cldbid,"count"=>$count,"ip"=>$ip,"name"=>$name,"lastseen"=>$nowtime,"grpid"=>$grpid,"nextup"=>$nextup,"idle"=>$idle,"cldgroup"=>$cldgroup);
				echo sprintf($lang['upuser'],$name,$uid,$cldbid,$count,$activetime).'<br>';
			}
			else
			{
				$grpid='0';
				foreach($grouptime as $time => $groupid)
				{
					if(in_array($groupid,$sgroups))
					{
						$grpid=$groupid;
						break;
					}
				}
				$insertdata[]=array("uuid"=>$uid,"cldbid"=>$cldbid,"count"=>"1","ip"=>$ip,"name"=>$name,"lastseen"=>$nowtime,"grpid"=>$grpid,"nextup"=>$nextupforinsert,"cldgroup"=>$cldgroup);
				echo'<sccolor>'.sprintf($lang['adduser'],$name,$uid,$cldbid).'</sccolor><br>';
			}
		}
		else
		{
			echo'<wncolor>'.sprintf($lang['nocount'],$name,$uid,$cldbid).'</wncolor><br>';
		}
	}

	if(!$mysqlcon->query("UPDATE user SET online=''"))
	{
		echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
	}

	if($insertdata!='')
	{
		$allinsertdata='';
		foreach($insertdata as $insertarr)
		{
			$allinsertdata=$allinsertdata."('".$insertarr["uuid"]."', '".$insertarr["cldbid"]."', '".$insertarr["count"]."', '".$insertarr["ip"]."', '".$insertarr["name"]."', '".$insertarr["lastseen"]."', '".$insertarr["grpid"]."', '".$insertarr["nextup"]."', '".$insertarr["cldgroup"]."','1'),";
		}
		$allinsertdata=substr($allinsertdata,0,-1);

		if($allinsertdata!='')
		{
			if(!$mysqlcon->query("INSERT INTO user (uuid, cldbid, count, ip, name, lastseen, grpid, nextup, cldgroup, online) VALUES $allinsertdata"))
			{
				echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
			}
		}
	}

	if($updatedata!=0)
	{
		$allupdateuuid='';
		$allupdatecldbid='';
		$allupdatecount='';
		$allupdateip='';
		$allupdatename='';
		$allupdatelastseen='';
		$allupdategrpid='';
		$allupdatenextup='';
		$allupdateidle='';
		$allupdatecldgroup='';
		foreach($updatedata as $updatearr)
		{
			$allupdateuuid=$allupdateuuid."'".$updatearr["uuid"]."',";
			$allupdatecldbid=$allupdatecldbid."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["cldbid"]."' ";
			$allupdatecount=$allupdatecount."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["count"]."' ";
			$allupdateip=$allupdateip."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["ip"]."' ";
			$allupdatename=$allupdatename."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["name"]."' ";
			$allupdatelastseen=$allupdatelastseen."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["lastseen"]."' ";
			$allupdategrpid=$allupdategrpid."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["grpid"]."' ";
			$allupdatenextup=$allupdatenextup."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["nextup"]."' ";
			$allupdateidle=$allupdateidle."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["idle"]."' ";
			$allupdatecldgroup=$allupdatecldgroup."WHEN '".$updatearr["uuid"]."' THEN '".$updatearr["cldgroup"]."' ";
		}
		$allupdateuuid=substr($allupdateuuid,0,-1);
		
		if(!$mysqlcon->query("UPDATE user set cldbid = CASE uuid $allupdatecldbid END, count = CASE uuid $allupdatecount END, ip = CASE uuid $allupdateip END, name = CASE uuid $allupdatename END, lastseen = CASE uuid $allupdatelastseen END, grpid = CASE uuid $allupdategrpid END, nextup = CASE uuid $allupdatenextup END, idle = CASE uuid $allupdateidle END, cldgroup = CASE uuid $allupdatecldgroup END, online = 1 WHERE uuid IN ($allupdateuuid)"))
		{
			echo $lang['error'].'<wncolor>this'.$mysqlcon->error.'.</wncolor><br>';
		}
	}

	$dbdata=$mysqlcon->query("SELECT * FROM user WHERE online<>1");
	if($dbdata->num_rows!=0)
	{
		while($uuid=$dbdata->fetch_assoc())
		{
			$idle=$uuid["idle"];
			$count=$uuid["count"];
			$grpid=$uuid["grpid"];
			$cldgroup=$uuid['cldgroup'];
			$sgroups=explode(",",$cldgroup);
			if($substridle==1)
			{
				$activetime=$count - $idle;
				$dtF=new DateTime("@0");
				$dtT=new DateTime("@$activetime");
			}
			else
			{
				$activetime=$count;
				$dtF=new DateTime("@0");
				$dtT=new DateTime("@$count");
			}
			foreach($grouptime as $time => $groupid)
			{
				if($activetime>$time)
				{
					break;
				}
				else
				{
					$nextup=$time - $activetime;
				}
			}
			if($uuid['uuid']!="lastscantime")
			{
				$updatenextup[]=array("uuid"=>$uuid['uuid'],"nextup"=>$nextup);
			}
		}
	}

	if($updatenextup!=0)
	{
		$allupdateuuid='';
		$allupdatenextup='';
		foreach($updatenextup as $updatedata)
		{
			$allupdateuuid=$allupdateuuid."'".$updatedata["uuid"]."',";
			$allupdatenextup=$allupdatenextup."WHEN '".$updatedata["uuid"]."' THEN '".$updatedata["nextup"]."' ";
		}
		$allupdateuuid=substr($allupdateuuid,0,-1);
		
		if(!$mysqlcon->query("UPDATE user set nextup = CASE uuid $allupdatenextup END WHERE uuid IN ($allupdateuuid)"))
		{
			echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
		}
	}

	$dbgroups=$mysqlcon->query("SELECT * FROM groups");
	if($dbgroups->num_rows==0)
	{
		$sqlhisgroup="empty";
	}
	else
	{
		while($servergroup=$dbgroups->fetch_assoc())
		{
			$sqlhisgroup[$servergroup['sgid']]=$servergroup['sgidname'];
		}
	}

	foreach($ts3groups as $servergroup)
	{
		if($sqlhisgroup!="empty")
		{
			foreach($sqlhisgroup as $sgid => $sname)
			{
				if($sgid==$servergroup['sgid'])
				{
					$gefunden=1;
					$updategroups[]=array("sgid"=>$servergroup['sgid'],"sgidname"=>$servergroup['name']);
					break;
				}
			}
			if($gefunden!=1)
			{
				$insertgroups[]=array("sgid"=>$servergroup['sgid'],"sgidname"=>$servergroup['name']);
			}
		}
		else
		{
			$insertgroups[]=array("sgid"=>$servergroup['sgid'],"sgidname"=>$servergroup['name']);
		}
	}
	if($insertgroups!='')
	{
		$allinsertdata='';
		foreach($insertgroups as $insertarr)
		{
			$allinsertdata=$allinsertdata."('".$insertarr["sgid"]."', '".$insertarr["sgidname"]."'),";
		}
		$allinsertdata=substr($allinsertdata,0,-1);

		if($allinsertdata!='')
		{
			if(!$mysqlcon->query("INSERT INTO groups (sgid, sgidname) VALUES $allinsertdata"))
			{
				echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
			}
		}
	}
	if($updategroups!=0)
	{
		$allsgids='';
		$allupdatesgid='';
		foreach($updategroups as $updatedata)
		{
			$allsgids=$allsgids."'".$updatedata["sgid"]."',";
			$allupdatesgid=$allupdatesgid."WHEN '".$updatedata["sgid"]."' THEN '".$updatedata["sgidname"]."' ";
		}
		$allsgids=substr($allsgids,0,-1);
		
		if(!$mysqlcon->query("UPDATE groups set sgidname = CASE sgid $allupdatesgid END WHERE sgid IN ($allsgids)"))
		{
			echo $lang['error'].'<wncolor>'.$mysqlcon->error.'.</wncolor><br>';
		}
	}
}
catch(Exception $e)
{
	echo $lang['error'].$e->getCode().': '.$e->getMessage();
}

if($showgen==1)
{
	$buildtime=microtime(true)-$starttime;
	echo'<br>'.sprintf($lang['sitegen'],$buildtime,$sumentries).'<br>';
}
?>
</body>
</html>