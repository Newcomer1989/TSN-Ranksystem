<?PHP
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'other/_functions.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'other/config.php');

$prot = start_session($cfg);

if(isset($_POST['stats_news_html'])) {
	unset($_SESSION[$rspathhex.$_POST['stats_news_html']]);
}
?>