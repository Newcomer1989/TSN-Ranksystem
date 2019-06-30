<?PHP
function addon_assign_groups($addons_config,$ts3,$cfg,$dbname,$allclients,$select_arr) {
	$sqlexec = '';
	
	if(isset($select_arr['addon_assign_groups']) && count($select_arr['addon_assign_groups']) != 0) {
		foreach($select_arr['addon_assign_groups'] as $uuid => $value) {
			$cld_groups = explode(',', $value['grpids']);
			foreach($cld_groups as $group) {
				foreach ($allclients as $client) {
					if($client['client_unique_identifier'] == $uuid) {
						$cldbid = $client['client_database_id'];
						$nickname = htmlspecialchars($client['client_nickname'], ENT_QUOTES);
						$uid = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
						break;
					}
				}
				if(isset($cldbid)) {
					if(strstr($group, '-')) {
						$group = str_replace('-','',$group);
						try {
							usleep($cfg['teamspeak_query_command_delay']);
							$ts3->serverGroupClientDel($group, $cldbid);
							enter_logfile($cfg,6,"Removed servergroup $group from user $nickname (UID: $uid), requested by Add-on 'Assign Servergroups'");
						}
						catch (Exception $e) {
							enter_logfile($cfg,2,"addon_assign_groups:".$e->getCode().': '."Error while removing group: ".$e->getMessage());
						}
					} else {
						try {
							usleep($cfg['teamspeak_query_command_delay']);
							$ts3->serverGroupClientAdd($group, $cldbid);
							enter_logfile($cfg,6,"Added servergroup $group from user $nickname (UID: $uid), requested by Add-on 'Assign Servergroups'");
						}
						catch (Exception $e) {
							enter_logfile($cfg,2,"addon_assign_groups:".$e->getCode().': '."Error while adding group: ".$e->getMessage());
						}
					}
				}
			}
		}
		$sqlexec .= "DELETE FROM `$dbname`.`addon_assign_groups`; ";
	}
	return $sqlexec;
}
?>