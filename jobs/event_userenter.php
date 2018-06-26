<?PHP
function event_userenter(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {

	global $logpath, $timezone, $mysqlcon, $dbname, $slowmode, $iphash, $sqlexec2;

	if($event['client_type'] == 0) {
		#enter_logfile($logpath,$timezone,6,"User ".$event['client_nickname']." (uuid: ".$event['client_unique_identifier'].") connected to the server.");
		try {
			usleep($slowmode);
			$clientinfo = $host->serverGetSelected()->clientInfoDb($event["client_database_id"]);
			if($clientinfo['client_lastip'] == NULL) {   //TeamSpeak sucks sometimes and gives a empty result
				enter_logfile($logpath,$timezone,4,"event_userenter: TeamSpeak gives an empty value for the IP address of ".$event['client_nickname']." (uuid: ".$event['client_unique_identifier'].").. retry in 0,1 seconds..");
				usleep(100000);
				try {
					unset($clientinfo);
					usleep($slowmode);
					$clientinfo = $host->serverGetSelected()->clientInfoDb($event["client_database_id"]);
					if($clientinfo['client_lastip'] == NULL) {
						enter_logfile($logpath,$timezone,4,"event_userenter: TeamSpeak gives an empty value for the IP address of ".$event['client_nickname']." (uuid: ".$event['client_unique_identifier'].").. retry in 0,5 seconds..");
						usleep(500000);
						try {
							unset($clientinfo);
							usleep($slowmode);
							$clientinfo = $host->serverGetSelected()->clientInfoDb($event["client_database_id"]);
							if($clientinfo['client_lastip'] == NULL) {
								enter_logfile($logpath,$timezone,2,"event_userenter: Fuck it.. TeamSpeak gives an empty value for the IP address of of ".$event['client_nickname']." (uuid: ".$event['client_unique_identifier'].").");
								return 0;
							}
						} catch (Exception $e) {
							enter_logfile($logpath,$timezone,2,"event_userenter 4:".$e->getCode().': '.$e->getMessage());
						}
					}
				} catch (Exception $e) {
					enter_logfile($logpath,$timezone,2,"event_userenter 4:".$e->getCode().': '.$e->getMessage());
				}
			}
			if($iphash == 1) {
				$hash = password_hash($clientinfo['client_lastip'], PASSWORD_DEFAULT);
				$ip = '';
			} else {
				$hash = '';
				$ip = $clientinfo['client_lastip'];
			}
			#enter_logfile($logpath,$timezone,6,"Event Userenter: Users IP-hash: ".$hash."  IP: ".$ip);
			if(($sqlhashs = $mysqlcon->query("SELECT * FROM `$dbname`.`user_iphash`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
				enter_logfile($logpath,$timezone,2,"event_userenter 1:".print_r($mysqlcon->errorInfo(), true));
			}
			#enter_logfile($logpath,$timezone,6,"User-hash-Table: ".print_r($sqlhashs, true));
			$uuid = htmlspecialchars($event['client_unique_identifier'], ENT_QUOTES);
			if(isset($sqlhashs[$uuid])) {
				#enter_logfile($logpath,$timezone,6,"UPDATE $dbname.user_iphash SET iphash='".$hash."', ip='".$ip."' WHERE uuid='".$event['client_unique_identifier']."'");
				$sqlexec2 .= "UPDATE `$dbname`.`user_iphash` SET `iphash`='".$hash."',`ip`='".$ip."' WHERE `uuid`='".$event['client_unique_identifier']."'; ";
			} else {
				#enter_logfile($logpath,$timezone,6,"INSERT INTO $dbname.user_iphash (uuid,iphash,ip) VALUES ('".$event['client_unique_identifier']."','".$hash."','".$ip."')");
				$sqlexec2 .= "INSERT INTO `$dbname`.`user_iphash` (`uuid`,`iphash`,`ip`) VALUES ('".$event['client_unique_identifier']."','".$hash."','".$ip."'); ";
			}
		
		} catch (Exception $e) {
			enter_logfile($logpath,$timezone,2,"event_userenter 4:".$e->getCode().': '.$e->getMessage());
		}
	}
}
?>