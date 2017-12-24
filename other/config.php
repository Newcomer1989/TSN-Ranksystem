<?PHP
require_once('dbconfig.php');
if(isset($db['type']) === false) {
	$db['type']="mysql";
}
$dbname = $db['dbname'];
$dbserver  = $db['type'].':host='.$db['host'].';dbname='.$db['dbname'];
if ($db['type'] == 'mysql') {
	$dboptions = array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		PDO::ATTR_PERSISTENT => true
	);
} else {
	$dboptions = array();
}
try {
	$mysqlcon = new PDO($dbserver, $db['user'], $db['pass'], $dboptions);
} catch (PDOException $e) {
	$err_msg = "Database Connection failed: ".$e->getMessage()."\n"; $err_lvl = 3;
	$language = "en";
}

if (isset($mysqlcon) && ($config = $mysqlcon->query("SELECT * FROM config"))) {
	if($config->rowCount() != 0) {
		$config          = $config->fetchAll();
		$ts['host']      = $config[0]['tshost'];
		$ts['query']     = $config[0]['tsquery'];
		$ts['voice']     = $config[0]['tsvoice'];
		$ts['user']      = $config[0]['tsuser'];
		$ts['pass']      = $config[0]['tspass'];
		$webuser         = $config[0]['webuser'];
		$webpass         = $config[0]['webpass'];
		if(!isset($_GET["lang"])) {
			if(isset($_SESSION['language'])) {
				$language = $_SESSION['language'];
			} else {
				$language = $config[0]['language'];
			}
		} elseif($_GET["lang"] == "ar") {
			$language = "ar";
			$_SESSION['language'] = "ar";
		} elseif($_GET["lang"] == "de") {
			$language = "de";
			$_SESSION['language'] = "de";
		} elseif($_GET["lang"] == "fr") {
			$language = "fr";
			$_SESSION['language'] = "fr";
		} elseif($_GET["lang"] == "it") {
			$language = "it";
			$_SESSION['language'] = "it";
		} elseif($_GET["lang"] == "nl") {
			$language = "nl";
			$_SESSION['language'] = "nl";
		} elseif($_GET["lang"] == "ro") {
			$language = "ro";
			$_SESSION['language'] = "ro";
		} elseif($_GET["lang"] == "ru") {
			$language = "ru";
			$_SESSION['language'] = "ru";
		} elseif($_GET["lang"] == "pt") {
			$language = "pt";
			$_SESSION['language'] = "pt";
		} else {
			$language = "en";
			$_SESSION['language'] = "en";
		}
		$queryname       = $config[0]['queryname'];
		$queryname2      = $config[0]['queryname2'];
		$slowmode        = $config[0]['slowmode'];
		if(empty($config[0]['grouptime'])) {
			$grouptime = null;
		} else {
			$grouptimearr = explode(',', $config[0]['grouptime']);
			foreach ($grouptimearr as $entry) {
				list($key, $value) = explode('=>', $entry);
				$grouptime[$key] = $value;
			}
		}
		if(empty($config[0]['boost'])) {
			$boostarr = null;
		} else {
			$boostexp = explode(',', $config[0]['boost']);
			foreach ($boostexp as $entry) {
				list($key, $value1, $value2) = explode('=>', $entry);
				$boostarr[$key] = array("group"=>$key,"factor"=>$value1,"time"=>$value2);
			}
		}
		$resetbydbchange = $config[0]['resetbydbchange'];
		$msgtouser       = $config[0]['msgtouser'];
		$update          = $config[0]['upcheck'];
		$uniqueid        = explode(',', $config[0]['uniqueid']);
		$updateinfotime  = $config[0]['updateinfotime'];
		$currvers        = $config[0]['currvers'];
		$substridle      = $config[0]['substridle'];
		$exceptuuid      = explode(',', $config[0]['exceptuuid']);
		$exceptgroup     = explode(',', $config[0]['exceptgroup']);
		$exceptcid		 = explode(',', $config[0]['exceptcid']);
		$timeformat      = $config[0]['dateformat'];
		$showexcld       = $config[0]['showexcld'];
		$showhighest     = $config[0]['showhighest'];
		$showcolrg       = $config[0]['showcolrg'];
		$showcolcld      = $config[0]['showcolcld'];
		$showcoluuid     = $config[0]['showcoluuid'];
		$showcoldbid     = $config[0]['showcoldbid'];
		$showcolls       = $config[0]['showcolls'];
		$showcolot       = $config[0]['showcolot'];
		$showcolit       = $config[0]['showcolit'];
		$showcolat       = $config[0]['showcolat'];
		$showcolas       = $config[0]['showcolas'];
		$showcolnx       = $config[0]['showcolnx'];
		$showcolsg       = $config[0]['showcolsg'];
		$cleanclients    = $config[0]['cleanclients'];
		$cleanperiod     = $config[0]['cleanperiod'];
		$defchid         = $config[0]['defchid'];
		$logpath         = $config[0]['logpath'];
		if ($config[0]['timezone'] == NULL) {
			$timezone    = "Europe/Berlin";
		} else {
			$timezone    = $config[0]['timezone'];
		}
		date_default_timezone_set($timezone);
		$advancemode	 = $config[0]['advancemode'];
		$count_access	 = $config[0]['count_access'];
		$last_access	 = $config[0]['last_access'];
		$ignoreidle		 = $config[0]['ignoreidle'];
		$rankupmsg		 = $config[0]['rankupmsg'];
		$newversion		 = $config[0]['newversion'];
		$servernews		 = $config[0]['servernews'];
		$adminuuid		 = $config[0]['adminuuid'];
		$nextupinfo		 = $config[0]['nextupinfo'];
		$nextupinfomsg1	 = $config[0]['nextupinfomsg1'];
		$nextupinfomsg2	 = $config[0]['nextupinfomsg2'];
		$nextupinfomsg3	 = $config[0]['nextupinfomsg3'];
		$shownav		 = $config[0]['shownav'];
		$showgrpsince	 = $config[0]['showgrpsince'];
		$resetexcept	 = $config[0]['resetexcept'];
		$upchannel		 = $config[0]['upchannel'];
		$avatar_delay	 = $config[0]['avatar_delay'];
	}
}
if(!isset($language) || $language == "en") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_en.php');
} elseif($language == "ar") {
	require_once(substr(dirname(__FILE__),0,-5).'languages/core_ar.php');
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