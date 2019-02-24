<?PHP
function get_avatars($ts3,$cfg) {
	try {
		usleep($cfg['teamspeak_query_command_delay']);
		$tsfilelist = $ts3->channelFileList($cid="0", $cpw="", $path="/");
	} catch (Exception $e) {
		if ($e->getCode() != 1281) {
			enter_logfile($cfg,2,"get_avatars 1:".$e->getCode().': '."Error while getting avatarlist: ".$e->getMessage());
		}
	}
	$fsfilelist = opendir(substr(__DIR__,0,-4).'avatars/');
	while (false !== ($fsfile = readdir($fsfilelist))) {
		if ($fsfile != '.' && $fsfile != '..') {
			$fsfilelistarray[$fsfile] = filemtime(substr(__DIR__,0,-4).'avatars/'.$fsfile);
		}
    }
	unset($fsfilelist);

	if (isset($tsfilelist)) {
		foreach($tsfilelist as $tsfile) {
			$fullfilename = '/'.$tsfile['name'];
			$uuidasbase16 = substr($tsfile['name'],7);
			if (!isset($fsfilelistarray[$uuidasbase16.'.png']) || ($tsfile['datetime'] - $cfg['teamspeak_avatar_download_delay']) > $fsfilelistarray[$uuidasbase16.'.png']) {
				if (substr($tsfile['name'],0,7) == 'avatar_') {
					try {
						check_shutdown($cfg); usleep($cfg['teamspeak_query_command_delay']);
						$avatar = $ts3->transferInitDownload($clientftfid="5",$cid="0",$name=$fullfilename,$cpw="", $seekpos=0);
						$transfer = TeamSpeak3::factory("filetransfer://" . $avatar["host"] . ":" . $avatar["port"]);
						$tsfile = $transfer->download($avatar["ftkey"], $avatar["size"]);
						$avatarfilepath	= substr(__DIR__,0,-4).'avatars/'.$uuidasbase16.'.png';
						enter_logfile($cfg,5,"Download avatar: ".$fullfilename);
						if(file_put_contents($avatarfilepath, $tsfile) === false) {
							enter_logfile($cfg,2,"Error while writing out the avatar. Please check the permission for the folder 'avatars'");
						}
					}
					catch (Exception $e) {
						enter_logfile($cfg,2,"get_avatars 2:".$e->getCode().': '."Error while downloading avatar: ".$e->getMessage());
					}
				}
			}
		}
		unset($fsfilelistarray);
	}
}
?>