<?PHP
require_once('_preload.php');

try {
	if ($mysqlcon->exec("INSERT INTO `$dbname`.`csrf_token` (`token`,`timestamp`,`sessionid`) VALUES ('$csrf_token','".time()."','".session_id()."')") === false) {
		$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (($db_csrf = $mysqlcon->query("SELECT * FROM `$dbname`.`csrf_token` WHERE `sessionid`='".session_id()."'")->fetchALL(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) === false) {
		$err_msg = $lang['isntwidbmsg'].print_r($mysqlcon->errorInfo(), true);
		$err_lvl = 3;
	}

	if (isset($db_csrf[$_GET['csrf_token']]) && isset($_GET['file']) && substr($_GET['file'],0,10) == "db_export_" && file_exists($cfg['logs_path'].$_GET['file'])) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($cfg['logs_path'].$_GET['file']).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($cfg['logs_path'].$_GET['file']));
		readfile($cfg['logs_path'].$_GET['file']);
	} else {
		rem_session_ts3();
		echo "Error on downloading file. File do not exists (anymore)? If yes, try it again. There could happened a problem with your session.";
	}
	?>
<?PHP
} catch(Throwable $ex) { }
?>