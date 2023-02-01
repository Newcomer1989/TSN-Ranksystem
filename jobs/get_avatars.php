<?PHP
function get_avatars($ts3,$cfg,$dbname,&$db_cache) {
	$starttime = microtime(true);
	$nowtime = time();
	$sqlexec = '';

	if(intval($db_cache['job_check']['get_avatars']['timestamp']) < ($nowtime - 32)) {
		$db_cache['job_check']['get_avatars']['timestamp'] = $nowtime;
		$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`={$nowtime} WHERE `job_name`='get_avatars';\n";
		try {
			usleep($cfg['teamspeak_query_command_delay']);
			$tsfilelist = $ts3->channelFileList($cid="0", $cpw="", $path="/");
		} catch (Exception $e) {
			if ($e->getCode() != 1281) {
				enter_logfile(2,"get_avatars 1:".$e->getCode().': '."Error while getting avatarlist: ".$e->getMessage());
			}
		}
		$fsfilelist = opendir($GLOBALS['avatarpath']);
		while (false !== ($fsfile = readdir($fsfilelist))) {
			if ($fsfile != '.' && $fsfile != '..') {
				$fsfilelistarray[$fsfile] = filemtime($GLOBALS['avatarpath'].$fsfile);
			}
		}
		unset($fsfilelist);

		if (isset($tsfilelist)) {
			$downloadedavatars = 0;
			foreach($tsfilelist as $tsfile) {
				if($downloadedavatars > 9) break;
				$fullfilename = '/'.$tsfile['name'];
				$uuidasbase16 = substr($tsfile['name'],7);
				if (!isset($fsfilelistarray[$uuidasbase16.'.png']) || ($tsfile['datetime'] - $cfg['teamspeak_avatar_download_delay']) > $fsfilelistarray[$uuidasbase16.'.png']) {
					if (substr($tsfile['name'],0,7) == 'avatar_') {
						try {
							check_shutdown(); usleep($cfg['teamspeak_query_command_delay']);
							$avatar = $ts3->transferInitDownload($clientftfid="5",$cid="0",$name=$fullfilename,$cpw="", $seekpos=0);
							$transfer = TeamSpeak3::factory("filetransfer://" . $avatar["host"] . ":" . $avatar["port"]);
							$tsfile = $transfer->download($avatar["ftkey"], $avatar["size"]);
							$avatarfilepath	= $GLOBALS['avatarpath'].$uuidasbase16.'.png';
							$downloadedavatars++;
							enter_logfile(5,"Download avatar: ".$fullfilename);
							if(file_put_contents($avatarfilepath, $tsfile) === false) {
								enter_logfile(2,"Error while writing out the avatar. Please check the permission for the folder '".$GLOBALS['avatarpath']."'");
							}
						}
						catch (Exception $e) {
							enter_logfile(2,"get_avatars 2:".$e->getCode().': '."Error while downloading avatar: ".$e->getMessage());
						}
					}
				}
				if((microtime(true) - $starttime) > 5) {
					enter_logfile(6,"get_avatars needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
					return;
				}
			}
			unset($fsfilelistarray);
		}
	}

	enter_logfile(6,"get_avatars needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
	return($sqlexec);
}
?>