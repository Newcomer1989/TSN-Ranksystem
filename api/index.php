<?PHP
header("Content-Type: application/json; charset=UTF-8");

require_once('../other/config.php');

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

if (isset($_GET['apikey'])) {
	$matchkey = 0;
	foreach($cfg['stats_api_keys'] as $apikey => $desc) {
		if ($apikey == $_GET['apikey']) $matchkey = 1;
	}
	if ($matchkey == 0) {
		$json = array(
			"Error" => array(
				"invalid" => array(
					"apikey" => "API Key is invalid"
				)
			)
		);
		echo json_encode($json);
		exit;
	}
} else {
	$json = array(
		"Error" => array(
			"required" => array(
				"apikey" => array(
					"desc" => "API Key for authentification. API keys can be created inside the Ranksystem Webinterface",
					"usage" => "Use \$_GET parameter 'apikey' and add as value a valid API key",
					"example" => "/api/?apikey=XXXXX"
				)
			)
		)
	);
	echo json_encode($json);
	exit;
}

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 && $_GET['limit'] <= 1000) ? $_GET['limit'] : 100;
$sort = (isset($_GET['sort'])) ? htmlspecialchars_decode($_GET['sort']) : '1';
$order = (isset($_GET['order']) && strtolower($_GET['order']) == 'desc') ? 'DESC' : 'ASC';
$part = (isset($_GET['part']) && is_numeric($_GET['part']) && $_GET['part'] > 0) ? (($_GET['part'] - 1) * $limit) : 0;

if (isset($_GET['groups'])) {
	$sgidname = $all = '----------_none_selected_----------';
	$sgid = -1;
	if(isset($_GET['all'])) $all = 1;
	if(isset($_GET['sgid'])) $sgid = htmlspecialchars_decode($_GET['sgid']);
	if(isset($_GET['sgidname'])) $sgidname = htmlspecialchars_decode($_GET['sgidname']);

	if($sgid == -1 && $sgidname == '----------_none_selected_----------' && $all == '----------_none_selected_----------') {
		$json = array(
			"usage" => array(
				"all" => array(
					"desc" => "Get details about all TeamSpeak servergroups",
					"usage" => "Use \$_GET parameter 'all' without any value",
					"example" => "/api/?groups&all"
				),
				"limit" => array(
					"desc" => "Define a number that limits the number of results. Maximum value is 1000. Default is 100.",
					"usage" => "Use \$_GET parameter 'limit' and add as value a number above 1",
					"example" => "/api/?groups&limit=10"
				),
				"order" => array(
					"desc" => "Define a sorting order. Value of 'sort' param is necessary.",
					"usage" => "Use \$_GET parameter 'order' and add as value 'asc' for ascending or 'desc' for descending",
					"example" => "/api/?groups&all&sort=sgid&order=asc"
				),
				"sgid" => array(
					"desc" => "Get details about TeamSpeak servergroups by the servergroup TS-database-ID",
					"usage" => "Use \$_GET parameter 'sgid' and add as value the servergroup TS-database-ID",
					"example" => "/api/?groups&sgid=123"
				),
				"sgidname" => array(
					"desc" => "Get details about TeamSpeak servergroups by servergroup name or a part of it",
					"usage" => "Use \$_GET parameter 'sgidname' and add as value a name or a part of it",
					"example" => array(
						"1" => array(
							"desc" => "Filter by servergroup name",
							"url" => "/api/?groups&sgidname=Level01"
						),
						"2" => array(
							"desc" => "Filter by servergroup name with a percent sign as placeholder",
							"url" => "/api/?groups&sgidname=Level%"
						)
					)
				),
				"sort" => array(
					"desc" => "Define a sorting. Available is each column name, which is given back as a result.",
					"usage" => "Use \$_GET parameter 'sort' and add as value a column name",
					"example" => array(
						"1" => array(
							"desc" => "Sort by servergroup name",
							"url" => "/api/?groups&all&sort=sgidname"
						),
						"2" => array(
							"desc" => "Sort by TeamSpeak sort-ID",
							"url" => "/api/?groups&all&sort=sortid"
						)
					)
				)
			)
		);
	} else {
		if ($all == 1) {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`groups` ORDER BY {$sort} {$order} LIMIT :start, :limit");
		} else {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`groups` WHERE (`sgidname` LIKE :sgidname OR `sgid` LIKE :sgid) ORDER BY {$sort} {$order} LIMIT :start, :limit");
			$dbdata->bindValue(':sgidname', '%'.$sgidname.'%', PDO::PARAM_STR);
			$dbdata->bindValue(':sgid', (int) $sgid, PDO::PARAM_INT);
		}
		$dbdata->bindValue(':start', (int) $part, PDO::PARAM_INT);
		$dbdata->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
		$dbdata->execute();
		$json = $dbdata->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
		foreach ($json as $sgid => $sqlpart) {
			if ($sqlpart['icondate'] != 0 && $sqlpart['sgidname'] == 'ServerIcon') {
				$json[$sgid]['iconpath'] = './tsicons/servericon.'.$sqlpart['ext'];
			} elseif ($sqlpart['icondate'] == 0 && $sqlpart['iconid'] > 0 && $sqlpart['iconid'] < 601) {
				$json[$sgid]['iconpath'] = './tsicons/'.$sqlpart['iconid'].'.'.$sqlpart['ext'];
			} elseif ($sqlpart['icondate'] != 0) {
				$json[$sgid]['iconpath'] = './tsicons/'.$sgid.'.'.$sqlpart['ext'];
			} else {
				$json[$sgid]['iconpath'] = '';
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
	$part = $cldbid = $all = 0;
	if(!isset($_GET['sort'])) $sort = '`rank`';
	if(isset($_GET['all'])) $all = 1;
	if(isset($_GET['uuid'])) $uuid = htmlspecialchars_decode($_GET['uuid']);
	if(isset($_GET['cldbid'])) $cldbid = htmlspecialchars_decode($_GET['cldbid']);
	if(isset($_GET['name'])) $name = htmlspecialchars_decode($_GET['name']);
	if(isset($_GET['part'])) $part = (htmlspecialchars_decode($_GET['part']) - 1) * 100;
	if(isset($_GET['online']) && $uuid == '----------_none_selected_----------' && $name == '----------_none_selected_----------' && $cldbid == 0) {
		$filter = '`online`=1';
	} elseif(isset($_GET['online'])) {
		$filter = '(`uuid` LIKE :uuid OR `cldbid` LIKE :cldbid OR `name` LIKE :name) AND `online`=1';
	} elseif($uuid != '----------_none_selected_----------' || $name != '----------_none_selected_----------' || $cldbid != 0) {
		$filter = '(`uuid` LIKE :uuid OR `cldbid` LIKE :cldbid OR `name` LIKE :name)';
	}
	
	if($uuid == '----------_none_selected_----------' && $name == '----------_none_selected_----------' && $filter == '' && $cldbid == 0 && $all == 0) {
		$json = array(
			"usage" => array(
				"all" => array(
					"desc" => "Get details about all TeamSpeak user. Result is limited by 100 entries.",
					"usage" => "Use \$_GET parameter 'all' without any value",
					"example" => "/api/?user&all"
				),
				"cldbid" => array(
					"desc" => "Get details about TeamSpeak user by client TS-database ID",
					"usage" => "Use \$_GET parameter 'cldbid' and add as value a single client TS-database ID",
					"example" => "/api/?user&cldbid=7775"
				),
				"limit" => array(
					"desc" => "Define a number that limits the number of results. Maximum value is 1000. Default is 100.",
					"usage" => "Use \$_GET parameter 'limit' and add as value a number above 1",
					"example" => "/api/?user&all&limit=10"
				),
				"name" => array(
					"desc" => "Get details about TeamSpeak user by client nickname",
					"usage" => "Use \$_GET parameter 'name' and add as value a name or a part of it",
					"example" => array(
						"1" => array(
							"desc" => "Filter by client nickname",
							"url" => "/api/?user&name=Newcomer1989"
						),
						"2" => array(
							"desc" => "Filter by client nickname with a percent sign as placeholder",
							"url" => "/api/?user&name=%user%"
						)
					)
				),
				"online" => array(
					"desc" => "Get the online TeamSpeak user",
					"usage" => "Use \$_GET parameter 'online' without any value",
					"example" => "/api/?user&online"
				),
				"order" => array(
					"desc" => "Define a sorting order.",
					"usage" => "Use \$_GET parameter 'order' and add as value 'asc' for ascending or 'desc' for descending",
					"example" => "/api/?user&all&order=asc"
				),
				"part" => array(
					"desc" => "Define, which part of the result you want to get. This is needed, when more then 100 clients are inside the result. At default you will get the first 100 clients. To get the next 100 clients, you will need to ask for part 2.",
					"usage" => "Use \$_GET parameter 'part' and add as value a number above 1",
					"example" => "/api/?user&name=TeamSpeakUser&part=2"
				),
				"sort" => array(
					"desc" => "Define a sorting. Available is each column name, which is given back as a result.",
					"usage" => "Use \$_GET parameter 'sort' and add as value a column name",
					"example" => array(
						"1" => array(
							"desc" => "Sort by online time",
							"url" => "/api/?user&all&sort=count"
						),
						"2" => array(
							"desc" => "Sort by active time",
							"url" => "/api/?user&all&sort=(count-idle)"
						),
						"3" => array(
							"desc" => "Sort by rank",
							"url" => "/api/?user&all&sort=rank"
						)
					)
				),
				"uuid" => array(
					"desc" => "Get details about TeamSpeak user by unique client ID",
					"usage" => "Use \$_GET parameter 'uuid' and add as value one unique client ID or a part of it",
					"example" => "/api/?user&uuid=xrTKhT/HDl4ea0WoFDQH2zOpmKg="
				)
			)
		);
	} else {
		if ($all == 1) {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`user` ORDER BY {$sort} {$order} LIMIT :start, :limit");
		} else {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`user` WHERE {$filter} ORDER BY {$sort} {$order} LIMIT :start, :limit");
		}
		if($filter != '`online`=1' && $all == 0) {
			$dbdata->bindValue(':uuid', '%'.$uuid.'%', PDO::PARAM_STR);
			$dbdata->bindValue(':cldbid', (int) $cldbid, PDO::PARAM_INT);
			$dbdata->bindValue(':name', '%'.$name.'%', PDO::PARAM_STR);
		}
		$dbdata->bindValue(':start', (int) $part, PDO::PARAM_INT);
		$dbdata->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
		$dbdata->execute();
		$json = $dbdata->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
	}
} elseif (isset($_GET['userstats'])) {
	$uuid = '----------_none_selected_----------';
	$filter = '';
	$part = $all = 0;
	if(isset($_GET['all'])) $all = 1;
	if(!isset($_GET['sort'])) $sort = '`count_week`';
	if(isset($_GET['uuid'])) {
		$uuid = htmlspecialchars_decode($_GET['uuid']);
		$filter = '`stats_user`.`uuid` LIKE :uuid';
	}

	if($uuid == '----------_none_selected_----------' && $all == 0 && $filter == '') {
		$json = array(
			"usage" => array(
				"all" => array(
					"desc" => "Get additional statistics about all TeamSpeak user. Result is limited by 100 entries.",
					"usage" => "Use \$_GET parameter 'all' without any value",
					"example" => "/api/?userstats&all"
				),
				"limit" => array(
					"desc" => "Define a number that limits the number of results. Maximum value is 1000. Default is 100.",
					"usage" => "Use \$_GET parameter 'limit' and add as value a number above 1",
					"example" => "/api/?userstats&limit=10"
				),
				"order" => array(
					"desc" => "Define a sorting order.",
					"usage" => "Use \$_GET parameter 'order' and add as value 'asc' for ascending or 'desc' for descending",
					"example" => "/api/?userstats&all&order=asc"
				),
				"part" => array(
					"desc" => "Define, which part of the result you want to get. This is needed, when more then 100 clients are inside the result. At default you will get the first 100 clients. To get the next 100 clients, you will need to ask for part 2.",
					"usage" => "Use \$_GET parameter 'part' and add as value a number above 1",
					"example" => "/api/?userstats&all&part=2"
				),
				"sort" => array(
					"desc" => "Define a sorting. Available is each column name, which is given back as a result.",
					"usage" => "Use \$_GET parameter 'sort' and add as value a column name",
					"example" => array(
						"1" => array(
							"desc" => "Sort by online time of the week",
							"url" => "/api/?userstats&all&sort=count_week"
						),
						"2" => array(
							"desc" => "Sort by active time of the week",
							"url" => "/api/?userstats&all&sort=(count_week-idle_week)"
						),
						"3" => array(
							"desc" => "Sort by online time of the month",
							"url" => "/api/?userstats&all&sort=count_month"
						)
					)
				),
				"uuid" => array(
					"desc" => "Get additional statistics about TeamSpeak user by unique client ID",
					"usage" => "Use \$_GET parameter 'uuid' and add as value one unique client ID or a part of it",
					"example" => "/api/?userstats&uuid=xrTKhT/HDl4ea0WoFDQH2zOpmKg="
				)
			)
		);
	} else {
		if ($all == 1) {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`stats_user` INNER JOIN `user` ON `user`.`uuid` = `stats_user`.`uuid` ORDER BY {$sort} {$order} LIMIT :start, :limit");
		} else {
			$dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`stats_user` INNER JOIN `user` ON `user`.`uuid` = `stats_user`.`uuid` WHERE {$filter} ORDER BY {$sort} {$order} LIMIT :start, :limit");
			$dbdata->bindValue(':uuid', '%'.$uuid.'%', PDO::PARAM_STR);
		}
		$dbdata->bindValue(':start', (int) $part, PDO::PARAM_INT);
		$dbdata->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
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
			),
			"userstats" => array(
				"desc" => "Get additional statistics about the TeamSpeak user",
				"usage" => "Use \$_GET parameter 'userstats'",
				"example" => "/api/?userstats"
			)
		)
	);
}

echo json_encode($json);
?>