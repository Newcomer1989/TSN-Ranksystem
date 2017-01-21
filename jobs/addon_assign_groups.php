<?PHP
function addon_assign_groups($addons_config,$ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$logpath,$allclients) {
	
	if(($lastupdate = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='check_update'")) === false) {
		enter_logfile($logpath,$timezone,2,"calc_user 0:".print_r($mysqlcon->errorInfo(), true));
	}
		
	if(($dbdata = $mysqlcon->query("SELECT * FROM $dbname.addon_assign_groups")) === false) {
		enter_logfile($logpath,$timezone,2,"addon_assign_groups: Error while getting data out of db: ".print_r($mysqlcon->errorInfo(), true));
	}
	$dbdata_fetched = $dbdata->fetchAll();
	
	foreach($dbdata_fetched as $entry) {
		$cld_groups = explode(',', $entry['grpids']);
		foreach($cld_groups as $group) {
			foreach ($allclients as $client) {
				if($client['client_unique_identifier'] == $entry['uuid']) {
					$cldbid = $client['client_database_id'];
					$nickname = htmlspecialchars($client['client_nickname'], ENT_QUOTES);
					break;
				}
			}
			if(isset($cldbid)) {
				if(strstr($group, '-')) {
					$group = str_replace('-','',$group);
					try {
						$ts3->serverGroupClientDel($group, $cldbid);
					}
					catch (Exception $e) {
						enter_logfile($logpath,$timezone,2,"addon_assign_groups:".$e->getCode().': '."Error while removing group: ".$e->getMessage());
					}
				} else {
					try {
						$ts3->serverGroupClientAdd($group, $cldbid);
					}
					catch (Exception $e) {
						enter_logfile($logpath,$timezone,2,"addon_assign_groups:".$e->getCode().': '."Error while adding group: ".$e->getMessage());
					}
				}
			}
		}
	}
	
	if($mysqlcon->exec("DELETE FROM $dbname.addon_assign_groups") === false) {
		enter_logfile($logpath,$timezone,2,"addon_assign_groups: Error while deleting data out of db: ".print_r($mysqlcon->errorInfo(), true));
	}
}
?>