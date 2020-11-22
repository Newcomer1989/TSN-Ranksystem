<?PHP
require_once('../other/_functions.php');
require_once('../other/config.php');

$prot = start_session($cfg);
$lang = set_language(get_language($cfg));

error_reporting(E_ALL);
ini_set("log_errors", 1);
set_error_handler("php_error_handling");
ini_set("error_log", $cfg['logs_path'].'ranksystem.log');

try {
	require_once('../other/phpcommand.php');

	if (isset($_POST['logout'])) {
		echo "logout";
		rem_session_ts3();
		header("Location: $prot://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
		exit;
	}

	if (basename($_SERVER['SCRIPT_NAME']) != "index.php" && basename($_SERVER['SCRIPT_NAME']) != "resetpassword.php" && (!isset($_SESSION[$rspathhex.'username']) || $_SESSION[$rspathhex.'username'] != $cfg['webinterface_user'] || $_SESSION[$rspathhex.'password'] != $cfg['webinterface_pass'] || $_SESSION[$rspathhex.'clientip'] != getclientip())) {
		rem_session_ts3();
		header("Location: $prot://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
		exit;
	}

	$csrf_token = bin2hex(openssl_random_pseudo_bytes(32));
} catch(Throwable $ex) { }
?>