<?PHP
function load_addons_config($mysqlcon,$lang,$cfg,$dbname) {
	if(!isset($mysqlcon) || $mysqlcon == NULL || ($addons_config = $mysqlcon->query("SELECT * FROM `$dbname`.`addons_config`")) === false) {
		if(function_exists('enter_logfile')) { 
			enter_logfile($cfg,2,"Error on loading addons config.. Database down, not reachable, corrupt or empty?");
		} else {
			echo 'Error on loading addons config..<br><br>Check:<br>- You have already installed the Ranksystem? Run <a href="../install.php">install.php</a> first!<br>- Is the database reachable?<br>- You have installed all needed PHP extenstions? Have a look here for <a href="//ts-ranksystem.com/#windows">Windows</a> or <a href="//ts-ranksystem.com/#linux">Linux</a>?';
		}
	} else {
		return $addons_config->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	}
	//$addons_config['assign_groups_groupids']['value'];
}
?>