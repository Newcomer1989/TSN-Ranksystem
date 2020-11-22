<?PHP
require_once('../other/_functions.php');
require_once('../other/config.php');

start_session($cfg);
$lang = set_language(get_language($cfg));

error_reporting(E_ALL);
ini_set("log_errors", 1);
set_error_handler("php_error_handling");
ini_set("error_log", $cfg['logs_path'].'ranksystem.log');

if(isset($_POST['refresh'])) {
	rem_session_ts3();
}

try {
	require_once('../other/phpcommand.php');
	require_once('../other/load_addons_config.php');

	$addons_config = load_addons_config($mysqlcon,$lang,$cfg,$dbname);

	if(!isset($_SESSION[$rspathhex.'tsuid'])) {
		set_session_ts3($mysqlcon,$cfg,$lang,$dbname);
	}
	require_once('_nav.php');
} catch(Throwable $ex) { }
?>