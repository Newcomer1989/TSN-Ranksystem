<?PHP
if(isset($_POST['refresh'])) {
	$_SESSION = array();
	session_destroy();
}

function set_session_ts3($hpclientip, $voiceport, $mysqlcon, $dbname) {
	$allclients = $mysqlcon->query("SELECT u.uuid,u.cldbid,u.name,u.ip,u.firstcon,s.total_connections FROM $dbname.user as u LEFT JOIN $dbname.stats_user as s ON u.uuid=s.uuid WHERE online='1';")->fetchAll();
	$_SESSION['connected']						= 0;
	$_SESSION['serverport']						= $voiceport;
	foreach ($allclients as $client) {
		if ($hpclientip == $client['ip']) {
			$_SESSION['tsuid']					= $client['uuid'];
			$_SESSION['tscldbid']				= $client['cldbid'];
			$_SESSION['tsname']					= $client['name'];
			if ($client['firstcon'] == 0) {
				$_SESSION['tscreated']			= "unkown";
			} else {
				$_SESSION['tscreated']				= date('d-m-Y',$client['firstcon']);
			}
			if ($client['total_connections'] != NULL) {
				$_SESSION['tsconnections']			= $client['total_connections'];
			} else {
				$_SESSION['tsconnections']			= 0;
			}
			$convert = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p');
			$uuidasbase16 = '';
			for ($i = 0; $i < 20; $i++) {
				$char = ord(substr(base64_decode($_SESSION['tsuid']), $i, 1));
				$uuidasbase16 .= $convert[($char & 0xF0) >> 4];
				$uuidasbase16 .= $convert[$char & 0x0F];
			}
			if(is_file('../avatars/'.$uuidasbase16.'.png')) {
				$_SESSION['tsavatar']			= $uuidasbase16.'.png';
			} else {
				$_SESSION['tsavatar']			= "none";
			}
			$_SESSION['connected']				= 1;
			break;
		}
	}
}
?>