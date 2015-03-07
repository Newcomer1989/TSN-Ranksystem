<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
<?php
echo'</head><body>';
$starttime=microtime(true);
require_once('other/config.php');
require_once('lang.php');

if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL-Database: ".mysqli_connect_error();
}

$keysort='';
$keyorder='';

if(isset($_GET['sort']))
{
	$keysort=$_GET['sort'];
}
if($keysort!='uuid' && $keysort!='cldbid' && $keysort!='count' && $keysort!='name' && $keysort!='idle' && $keysort!='active')
{
	$keysort='nextup';
}

if(isset($_GET['order']))
{
	$keyorder=$_GET['order'];
}

if($keyorder=='desc')
{
	$keyorder='DESC';
}
else
{
	$keyorder='ASC';
}

$countentries=0;
if($keysort=='active' && $keyorder=='ASC')
{
	$dbdata=$mysqlcon->query("SELECT * FROM user ORDER BY (count - idle)");
}
elseif($keysort=='active' && $keyorder=='DESC')
{
	$dbdata=$mysqlcon->query("SELECT * FROM user ORDER BY (idle - count)");
}
else
{
	$dbdata=$mysqlcon->query("SELECT * FROM user ORDER BY $keysort $keyorder");
}
while($uuid=$dbdata->fetch_assoc())
{
	if($uuid['uuid']!="lastscantime")
	{
		$sqlhis[$uuid['uuid']]=array("cldbid"=>$uuid['cldbid'],"count"=>$uuid['count'],"name"=>$uuid['name'],"idle"=>$uuid['idle'],"cldgroup"=>$uuid['cldgroup'],"online"=>$uuid['online']);
		$uidarr[]=$uuid['uuid'];
		$countentries=$countentries+1;
	}
	else
	{
		$scantime=$uuid['lastseen'];
	}
}
$sumentries=$dbdata->num_rows;
$lifetime=time() - $scantime;

$dbgroups=$mysqlcon->query("SELECT * FROM groups");
while($servergroup=$dbgroups->fetch_assoc())
{
	$sqlhisgroup[$servergroup['sgid']]=$servergroup['sgidname'];
}

switch($keyorder)
{
	case "ASC": $keyorder2="desc"; break;
	case "DESC": $keyorder2="asc";
}

echo'<table class="tabledefault"><tr>';
if($showcolcld==1) echo ($keysort=='name') ? '<th><a href="?sort=name&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listnick'].'</hdcolor></a></th>' : '<th><a href="?sort=name&amp;order='.$keyorder.'"><hdcolor>'.$lang['listnick'].'</hdcolor></a></th>';
if($showcoluuid==1) echo '<th><a href="?sort=uuid&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listuid'].'</hdcolor></a></th>';
if($showcoldbid==1) echo'<th><a href="?sort=cldbid&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listcldbid'].'</hdcolor></a></th>';
if($showcolot==1) echo'<th><a href="?sort=count&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listsumo'].'</hdcolor></a></th>';
if($showcolit==1) echo'<th><a href="?sort=idle&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listsumi'].'</hdcolor></a></th>';
if($showcolat==1) echo'<th><a href="?sort=active&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listsuma'].'</hdcolor></a></th>';
if($showcolnx==1) echo ($keysort=='nextup') ? '<th><a href="?sort=nextup&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listnxup'].'</hdcolor></a></th>' : '<th><a href="?sort=nextup&amp;order='.$keyorder.'"><hdcolor>'.$lang['listnxup'].'</hdcolor></a></th>';
if($showcolsg==1) echo'<th><a href="?sort=nextsgrp&amp;order='.$keyorder2.'"><hdcolor>'.$lang['listnxsg'].'</hdcolor></a></th>';
echo'</tr>';

ksort($grouptime);

if($countentries>0)
{
	foreach($uidarr as $uid)
	{
		$cldgroup=$sqlhis[$uid]['cldgroup'];
		$count=$sqlhis[$uid]['count'];
		$idle=$sqlhis[$uid]['idle'];
		$status=$sqlhis[$uid]['online'];
		$sgroups=explode(",",$cldgroup);
		$active=$count - $idle;
		if($substridle==1)
		{
			$activetime=$count - $idle;
		}
		else
		{
			$activetime=$count;
		}
		foreach($grouptime as $time => $groupid)
		{
			$showrow=1;
			if(array_intersect($sgroups, $exceptgroup) && $showexgrp!=1)
			{
				$showrow=0;
			}
			if(in_array($uid, $exceptuuid) && $showexcld!=1)
			{
				$showrow=0;
			}
			
			if($activetime<$time && $showrow==1)
			{
				if($status==1)
				{
					$neededtime=$time - $activetime - $lifetime;
				} else
				{
					$neededtime=$time - $activetime;
				}
				echo'<tr>';
				if($showcolcld==1)
				{
					echo'<td class="center">'.$sqlhis[$uid]['name'].'</td>';
				}
				if($showcoluuid==1)
				{
					echo'<td class="center">'.$uid.'</td>';
				}
				if($showcoldbid==1)
				{
					echo'<td class="center">'.$sqlhis[$uid]['cldbid'].'</td>';
				}
				if($showcolot==1)
				{
					echo'<td class="center">';
					$dtF=new DateTime("@0");
					$dtT=new DateTime("@$count");
					$timecount=$dtF->diff($dtT)->format($timeformat);
					echo $timecount;
				}
				if($showcolit==1)
				{
					echo'<td class="center">';
					$dtF=new DateTime("@0");
					$dtT=new DateTime("@$idle");
					$timecount=$dtF->diff($dtT)->format($timeformat);
					echo $timecount;
				}
				if($showcolat==1)
				{
					echo'<td class="center">';
					$dtF=new DateTime("@0");
					$dtT=new DateTime("@$active");
					$timecount=$dtF->diff($dtT)->format($timeformat);
					echo $timecount;
				}
				if($showcolnx==1)
				{
					echo'<td class="center">';
					$dtF=new DateTime("@0");
					$dtT=new DateTime("@$neededtime");
					$timecount=$dtF->diff($dtT)->format($timeformat);
					
					if(!in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup) && $neededtime>0)
					{
						echo $timecount.'</td>';
					}
					elseif(!in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup))
					{
						$timecount=0;
						echo $timecount.'</td>';
					}
					elseif(in_array($uid, $exceptuuid))
					{
						echo $lang['listexuid'].'</td>';
					}
					elseif(array_intersect($sgroups, $exceptgroup))
					{
						echo $lang['listexgrp'].'</td>';
					}
					else
					{
						echo $lang['errukwn'];
					}
				}
				if($showcolsg==1)
				{
					echo'<td class="center">'.$sqlhisgroup[$groupid].'</td>';
				}
				echo'</tr>';
				break;
			}
		}
	}
}
else
{
echo'<tr><td colspan="6">'.$lang['noentry'].'</td></tr>';
}
echo'</table>';

if($showgen==1)
{
	$buildtime=microtime(true)-$starttime;
	echo'<span class="tabledefault">'.sprintf($lang['sitegen'],$buildtime,$sumentries).'</span>';
}
?>
</body>
</html>
