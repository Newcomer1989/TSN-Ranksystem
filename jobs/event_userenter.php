<?PHP
function event_userenter(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {

	global $cfg, $mysqlcon, $dbname;

	$sqlexec3 = '';

	if($event['client_type'] == 0) {
		enter_logfile($cfg,6,"User ".$event['client_nickname']." (uuid: ".$event['client_unique_identifier'].") connected to the server.");
		try {
			$host->serverGetSelected()->clientListReset();
			usleep($cfg['teamspeak_query_command_delay']);
			$clientlist = $host->serverGetSelected()->clientListtsn("-ip");
			foreach ($clientlist as $client) {
				if($client['client_database_id'] == $event['client_database_id']) {
					if(strstr($client['connection_client_ip'], '[')) {
						$ip = str_replace(array('[',']'),'',$client['connection_client_ip']);
					} else {
						$ip = $client['connection_client_ip'];
					}
					break;
				}
			}
			unset($clientlist,$host);
			if(!isset($ip)) {
				enter_logfile($cfg,6,"New user ({$event['client_nickname']} [{$event['client_database_id']}]) joined the server, but can't found a valid IP address.");
			} else {
				if($cfg['rankup_hash_ip_addresses_mode'] == 1) {
					$hash = password_hash($ip, PASSWORD_DEFAULT);
					$ip = '';
				} elseif($cfg['rankup_hash_ip_addresses_mode'] == 2) {
					$salt = md5(dechex(crc32(substr(__DIR__,0,-4))));
					$hash = password_hash($ip, PASSWORD_DEFAULT, array("cost" => 10, "salt" => $salt));
					$ip = '';
				} else {
					$hash = '';
				}
				enter_logfile($cfg,6,"Event Userenter: Users IP-hash: ".$hash);
				
				if(($sqlhashs = $mysqlcon->query("SELECT * FROM `$dbname`.`user_iphash`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
					enter_logfile($cfg,2,"event_userenter 2:".print_r($mysqlcon->errorInfo(), true));
				}
				
				$uuid = htmlspecialchars($event['client_unique_identifier'], ENT_QUOTES);
				if(isset($sqlhashs[$uuid])) {
					$sqlexec3 .= "UPDATE `$dbname`.`user_iphash` SET `iphash`='$hash',`ip`='$ip' WHERE `uuid`='{$event['client_unique_identifier']}'; ";
					enter_logfile($cfg,6,"Userenter: UPDATE `$dbname`.`user_iphash` SET `iphash`='$hash',`ip`='$ip' WHERE `uuid`='{$event['client_unique_identifier']}'; ");
				} else {
					$sqlexec3 .= "INSERT INTO `$dbname`.`user_iphash` (`uuid`,`iphash`,`ip`) VALUES ('{$event['client_unique_identifier']}','$hash','$ip'); ";
					enter_logfile($cfg,6,"Userenter: INSERT INTO `$dbname`.`user_iphash` (`uuid`,`iphash`,`ip`) VALUES ('{$event['client_unique_identifier']}','$hash','$ip'); ");
				}
				if($mysqlcon->exec($sqlexec3) === false) {
					enter_logfile($cfg,2,"event_userenter 3:".print_r($mysqlcon->errorInfo(), true));
				}
			}
		} catch (Exception $e) {
			enter_logfile($cfg,2,"event_userenter 4:".$e->getCode().': '.$e->getMessage());
		}
	}
}
?>