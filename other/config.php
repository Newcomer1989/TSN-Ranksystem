<?PHP
require_once('dbconfig.php');
if(isset($db['type']) === false) {
	$db['type']="mysql";
}
$dbname = $db['dbname'];
$dbtype = $db['type'];
if($db['type'] != "type") {
	$dbserver  = $db['type'].':host='.$db['host'].';dbname='.$db['dbname'].';charset=utf8mb4';
	if ($db['type'] == 'mysql') {
		$dboptions = array(
			PDO::ATTR_PERSISTENT => true
		);
	} else {
		$dboptions = array();
	}
	try {
		$mysqlcon = new PDO($dbserver, $db['user'], $db['pass'], $dboptions);
	} catch (PDOException $e) {
		echo "Database Connection failed: ".$e->getMessage()."\n"; $err_lvl = 3;
		exit;
	}
}

$rspathhex = 'rs_'.dechex(crc32(__DIR__)).'_';
function rem_session_ts3($rspathhex) {
	unset($_SESSION[$rspathhex.'admin']);
	unset($_SESSION[$rspathhex.'clientip']);
	unset($_SESSION[$rspathhex.'connected']);
	unset($_SESSION[$rspathhex.'inactivefilter']);
	unset($_SESSION[$rspathhex.'language']);
	unset($_SESSION[$rspathhex.'logfilter']);
	unset($_SESSION[$rspathhex.'logfilter2']);
	unset($_SESSION[$rspathhex.'multiple']);
	unset($_SESSION[$rspathhex.'newversion']);
	unset($_SESSION[$rspathhex.'number_lines']);
	unset($_SESSION[$rspathhex.'password']);
	unset($_SESSION[$rspathhex.'serverport']);
	unset($_SESSION[$rspathhex.'tsavatar']);
	unset($_SESSION[$rspathhex.'tscldbid']);
	unset($_SESSION[$rspathhex.'tsconnections']);
	unset($_SESSION[$rspathhex.'tscreated']);
	unset($_SESSION[$rspathhex.'tsname']);
	unset($_SESSION[$rspathhex.'tsuid']);
	unset($_SESSION[$rspathhex.'upinfomsg']);
	unset($_SESSION[$rspathhex.'username']);
	unset($_SESSION[$rspathhex.'uuid_verified']);
}

if (isset($mysqlcon) && ($config = $mysqlcon->query("SELECT * FROM config")->fetch())) {
	if(count($config) != 0) {
		$ts['host']      = $config['tshost'];
		$ts['query']     = $config['tsquery'];
		$ts['voice']     = $config['tsvoice'];
		$ts['user']      = $config['tsuser'];
		$ts['pass']      = $config['tspass'];
		$webuser         = $config['webuser'];
		$webpass         = $config['webpass'];
		if(!isset($_GET["lang"])) {
			if(isset($_SESSION[$rspathhex.'language'])) {
				$language = $_SESSION[$rspathhex.'language'];
			} else {
				$language = $config['language'];
			}
		} elseif($_GET["lang"] == "ar") {
			$language = "ar";
			$_SESSION[$rspathhex.'language'] = "ar";
		} elseif($_GET["lang"] == "cz") {
			$language = "cz";
			$_SESSION[$rspathhex.'language'] = "cz";
		} elseif($_GET["lang"] == "de") {
			$language = "de";
			$_SESSION[$rspathhex.'language'] = "de";
		} elseif($_GET["lang"] == "fr") {
			$language = "fr";
			$_SESSION[$rspathhex.'language'] = "fr";
		} elseif($_GET["lang"] == "it") {
			$language = "it";
			$_SESSION[$rspathhex.'language'] = "it";
		} elseif($_GET["lang"] == "nl") {
			$language = "nl";
			$_SESSION[$rspathhex.'language'] = "nl";
		} elseif($_GET["lang"] == "ro") {
			$language = "ro";
			$_SESSION[$rspathhex.'language'] = "ro";
		} elseif($_GET["lang"] == "ru") {
			$language = "ru";
			$_SESSION[$rspathhex.'language'] = "ru";
		} elseif($_GET["lang"] == "pt") {
			$language = "pt";
			$_SESSION[$rspathhex.'language'] = "pt";
		} else {
			$language = "en";
			$_SESSION[$rspathhex.'language'] = "en";
		}
		$queryname       = $config['queryname'];
		$queryname2      = $config['queryname2'];
		$slowmode        = $config['slowmode'];
		if(empty($config['grouptime'])) {
			$grouptime = null;
		} else {
			$grouptimearr = explode(',', $config['grouptime']);
			foreach ($grouptimearr as $entry) {
				list($key, $value) = explode('=>', $entry);
				$grouptime[$key] = $value;
			}
		}
		if(empty($config['boost'])) {
			$boostarr = null;
		} else {
			$boostexp = explode(',', $config['boost']);
			foreach ($boostexp as $entry) {
				list($key, $value1, $value2) = explode('=>', $entry);
				$boostarr[$key] = array("group"=>$key,"factor"=>$value1,"time"=>$value2);
			}
		}
		$resetbydbchange = $config['resetbydbchange'];
		$msgtouser       = $config['msgtouser'];
		$currvers        = $config['currvers'];
		$substridle      = $config['substridle'];
		$exceptuuid      = array_flip(explode(',', $config['exceptuuid']));
		$exceptgroup     = array_flip(explode(',', $config['exceptgroup']));
		$exceptcid		 = array_flip(explode(',', $config['exceptcid']));
		$timeformat      = $config['dateformat'];
		$showexcld       = $config['showexcld'];
		$showhighest     = $config['showhighest'];
		$showcolrg       = $config['showcolrg'];
		$showcolcld      = $config['showcolcld'];
		$showcoluuid     = $config['showcoluuid'];
		$showcoldbid     = $config['showcoldbid'];
		$showcolls       = $config['showcolls'];
		$showcolot       = $config['showcolot'];
		$showcolit       = $config['showcolit'];
		$showcolat       = $config['showcolat'];
		$showcolas       = $config['showcolas'];
		$showcolnx       = $config['showcolnx'];
		$showcolsg       = $config['showcolsg'];
		$cleanclients    = $config['cleanclients'];
		$cleanperiod     = $config['cleanperiod'];
		$defchid         = $config['defchid'];
		$logpath         = $config['logpath'];
		if ($config['timezone'] == NULL) {
			$timezone    = "Europe/Berlin";
		} else {
			$timezone    = $config['timezone'];
		}
		date_default_timezone_set($timezone);
		$advancemode	 = $config['advancemode'];
		$count_access	 = $config['count_access'];
		$last_access	 = $config['last_access'];
		$ignoreidle		 = $config['ignoreidle'];
		$rankupmsg		 = $config['rankupmsg'];
		$newversion		 = $config['newversion'];
		$servernews		 = $config['servernews'];
		if(empty($config['adminuuid'])) {
			$adminuuid = NULL;
		} else {
			$adminuuid = explode(',', $config['adminuuid']);
		}
		$nextupinfo		 = $config['nextupinfo'];
		$nextupinfomsg1	 = $config['nextupinfomsg1'];
		$nextupinfomsg2	 = $config['nextupinfomsg2'];
		$nextupinfomsg3	 = $config['nextupinfomsg3'];
		$shownav		 = $config['shownav'];
		$showgrpsince	 = $config['showgrpsince'];
		$resetexcept	 = $config['resetexcept'];
		$upchannel		 = $config['upchannel'];
		$avatar_delay	 = $config['avatar_delay'];
		$registercid	 = $config['registercid'];
	}
}
if(!isset($language) || $language == "en") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_en.php');
} elseif($language == "ar") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_ar.php');
} elseif($language == "cz") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_cz.php');
} elseif($language == "de") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_de.php');
} elseif($language == "fr") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_fr.php');
} elseif($language == "it") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_it.php');
} elseif($language == "nl") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_nl.php');
} elseif($language == "ro") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_ro.php');
} elseif($language == "ru") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_ru.php');
} elseif($language == "pt") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_pt.php');
}
?>