<?PHP
function addon_assign_groups($addons_config,$ts3,$dbname,$slowmode,$timezone,$logpath,$allclients,$select_arr) {
	$sqlexec = '';
	
	if(isset($select_arr['addon_assign_groups']) && count($select_arr['addon_assign_groups']) != 0) {
		foreach($select_arr['addon_assign_groups'] as $uuid => $value) {
			$cld_groups = explode(',', $value['grpids']);
			foreach($cld_groups as $group) {
				foreach ($allclients as $client) {
					if($client['client_unique_identifier'] == $uuid) {
						$cldbid = $client['client_database_id'];
						$nickname = htmlspecialchars($client['client_nickname'], ENT_QUOTES);
						break;
					}
				}
				if(isset($cldbid)) {
					if(strstr($group, '-')) {
						$group = str_replace('-','',$group);
						usleep($slowmode);
						try {
							$ts3->serverGroupClientDel($group, $cldbid);
						}
						catch (Exception $e) {
							enter_logfile($logpath,$timezone,2,"addon_assign_groups:".$e->getCode().': '."Error while removing group: ".$e->getMessage());
						}
					} else {
						usleep($slowmode);
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
		
		$sqlexec .= "DELETE FROM `$dbname`.`addon_assign_groups`; ";
	}
	return($sqlexec);
}
?>