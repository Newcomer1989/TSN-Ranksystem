<?PHP
header("Content-Type: application/json; charset=UTF-8");

require_once('../other/dbconfig.php');

$dbname = $db['dbname'];
$dbtype = $db['type'];
if($db['type'] != "type") {
	$dbserver  = $db['type'].':host='.$db['host'].';dbname='.$dbname.';charset=utf8mb4';
	$dboptions = array();
	try {
		$mysqlcon = new PDO($dbserver, $db['user'], $db['pass'], $dboptions);
	} catch (PDOException $e) {
		echo 'Database Connection failed: <b>'.$e->getMessage().'</b>';
		exit;
	}
}

if (isset($_GET['groups'])) {
	$sgidname = $all = '----------_none_selected_----------';
	$sgid = -1;
	if(isset($_GET['all'])) {
		$all = 1;
	}
	if(isset($_GET['sgid'])) {
		$sgid = htmlspecialchars_decode($_GET['sgid']);
	}
	if(isset($_GET['sgidname'])) {
		$sgidname = htmlspecialchars_decode($_GET['sgidname']);
	}

	if($sgid == -1 && $sgidname == '----------_none_selected_----------' && $all == '----------_none_selected_----------') {
		$json = array(
			"usage" => array(
				"all" => array(
					"desc" => "Get details about all TeamSpeak servergroups",
					"usage" => "Use \$_GET parameter 'all' without any value",
					"example" => "/api/?groups&all"
				),
				"sgid" => array(
					"desc" => "Get details about TeamSpeak servergroups by the servergroup TS-database-ID",
					"usage" => "Use \$_GET parameter 'sgid' and add as value the servergroup TS-database-ID",
					"example" => "/api/?groups&sgid=123"
				),
				"sgidname" => array(
					"desc" => "Get details about TeamSpeak servergroups by servergroup name or a part of it",
					"usage" => "Use \$_GET parameter 'sgidname' and add as value a name or a part of it",
					"example" => "/api/?groups&sgidname=Level01"
				)
			)
		);
	} else {
		if ($all == 1) {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`groups`");
		} else {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`groups` WHERE (`sgidname` LIKE :sgidname OR `sgid` LIKE :sgid)");
		}
		$dbdata->bindValue(':sgidname', '%'.$sgidname.'%', PDO::PARAM_STR);
		$dbdata->bindValue(':sgid', (int) $sgid, PDO::PARAM_INT);
		$dbdata->execute();
		$json = $dbdata->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
		foreach ($json as $sgid => $sqlpart) {
			if ($sqlpart['icondate'] != 0 && $sqlpart['sgidname'] == 'ServerIcon') {
				$json[$sgid]['iconpath'] = './tsicons/servericon.png';
			} elseif ($sqlpart['iconid'] != 0) {
				$json[$sgid]['iconpath'] = './tsicons/'.$sqlpart['iconid'].'.png';
			}
		}
	}
} elseif (isset($_GET['rankconfig'])) {
	$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`cfg_params` WHERE `param` in ('rankup_definition', 'rankup_time_assess_mode')");
	$dbdata->execute();
	$sql = $dbdata->fetchAll(PDO::FETCH_KEY_PAIR);
	$json = array();
	if ($sql['rankup_time_assess_mode'] == 1) {
		$modedesc = "active time";
	} else {
		$modedesc = "online time";
	}
	$json['rankup_time_assess_mode'] = array (
		"mode" => $sql['rankup_time_assess_mode'],
		"mode_desc" => $modedesc
	);
	$count = 0;
	krsort($sql['rankup_definition']);
	foreach (explode(',', $sql['rankup_definition']) as $entry) {
		list($key, $value) = explode('=>', $entry);
		$addnewvalue1[$count] = array(
			"grpid" => $value,
			"seconds" => $key
		);
		$count++;
		$json['rankup_definition'] = $addnewvalue1;
	}
} elseif (isset($_GET['server'])) {
	$dbdata = $mysqlcon->prepare("SELECT 0 as `row`, `$dbname`.`stats_server`.* FROM `$dbname`.`stats_server`");
	$dbdata->execute();
	$json = $dbdata->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
} elseif (isset($_GET['user'])) {
	$uuid = $name = '----------_none_selected_----------';
	$filter = '';
	$part = $cldbid = 0;
	if(isset($_GET['uuid'])) {
		$uuid = htmlspecialchars_decode($_GET['uuid']);
	}
	if(isset($_GET['cldbid'])) {
		$cldbid = htmlspecialchars_decode($_GET['cldbid']);
	}
	if(isset($_GET['name'])) {
		$name = htmlspecialchars_decode($_GET['name']);
	}
	if(isset($_GET['part'])) {
		$part = (htmlspecialchars_decode($_GET['part']) - 1) * 100;
	}
	if(isset($_GET['online']) && $uuid == '----------_none_selected_----------' && $name == '----------_none_selected_----------' && $cldbid == 0) {
		$filter = '`online`=1';
	} elseif(isset($_GET['online'])) {
		$filter = '(`uuid` LIKE :uuid OR `cldbid` LIKE :cldbid OR `name` LIKE :name) AND `online`=1';
	} else {
		$filter = '(`uuid` LIKE :uuid OR `cldbid` LIKE :cldbid OR `name` LIKE :name)';
	}
	
	if($uuid == '----------_none_selected_----------' && $name == '----------_none_selected_----------' && $filter == '' && $cldbid == 0) {
		$json = array(
			"usage" => array(
				"uuid" => array(
					"desc" => "Get details about TeamSpeak user by unique client ID",
					"usage" => "Use \$_GET parameter 'uuid' and add as value one unique client ID or a part of it",
					"example" => "/api/?user&uuid=xrTKhT/HDl4ea0WoFDQH2zOpmKg="
				),
				"cldbid" => array(
					"desc" => "Get details about TeamSpeak user by client TS-database ID",
					"usage" => "Use \$_GET parameter 'cldbid' and add as value a single client TS-database ID",
					"example" => "/api/?user&cldbid=7775"
				),
				"name" => array(
					"desc" => "Get details about TeamSpeak user by client nickname",
					"usage" => "Use \$_GET parameter 'name' and add as value a name or a part of it",
					"example" => "/api/?user&name=Newcomer1989"
				),
				"online" => array(
					"desc" => "Get the online TeamSpeak user",
					"usage" => "Use \$_GET parameter 'online' without any value",
					"example" => "/api/?user&online"
				),
				"part" => array(
					"desc" => "Define, which part of the result you want to get. This is needed, when more then 10 clients are inside the result. At default you will get the first 100 clients. To get the next 100 clients, you will need to answer for part 2.",
					"usage" => "Use \$_GET parameter 'part' and add as value a number above 1",
					"example" => "/api/?user&name=TeamSpeakUser&part=2"
				)
			)
		);
	} else {
		$dbdata = $mysqlcon->prepare("SELECT `uuid`,`cldbid`,`rank`,`count`,`name`,`idle`,`cldgroup`,`online`,`nextup`,`lastseen`,`grpid`,`except`,`grpsince` FROM `$dbname`.`user` WHERE {$filter} LIMIT :start, :limit");
		if($filter != '`online`=1') {
			$dbdata->bindValue(':uuid', '%'.$uuid.'%', PDO::PARAM_STR);
			$dbdata->bindValue(':cldbid', (int) $cldbid, PDO::PARAM_INT);
			$dbdata->bindValue(':name', '%'.$name.'%', PDO::PARAM_STR);
		}
		$dbdata->bindValue(':start', (int) $part, PDO::PARAM_INT);
		$dbdata->bindValue(':limit', (int) 100, PDO::PARAM_INT);
		$dbdata->execute();
		$json = $dbdata->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
	}	
} else {
	$json = array(
		"usage" => array(
			"groups" => array(
				"desc" => "Get details about the TeamSpeak servergroups",
				"usage" => "Use \$_GET parameter 'groups'",
				"example" => "/api/?groups"
			),
			"rankconfig" => array(
				"desc" => "Get the rankup definition, which contains the assignment of (needed) time to servergroup",
				"usage" => "Use \$_GET parameter 'rankconfig'",
				"example" => "/api/?rankconfig"
			),
			"server" => array(
				"desc" => "Get details about the TeamSpeak server",
				"usage" => "Use \$_GET parameter 'server'",
				"example" => "/api/?server"
			),
			"user" => array(
				"desc" => "Get details about the TeamSpeak user",
				"usage" => "Use \$_GET parameter 'user'",
				"example" => "/api/?user"
			)
		)
	);
}

echo json_encode($json);
?>