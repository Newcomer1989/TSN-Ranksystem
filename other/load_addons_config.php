<?PHP
function load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath) {
	if(!isset($mysqlcon) || $mysqlcon == NULL || ($addons_config = $mysqlcon->query("SELECT * FROM `$dbname`.`addons_config`")) === false) {
		if(function_exists('enter_logfile')) { 
			enter_logfile($logpath,$timezone,2,"Error on loading addons config.. Database down, not reachable, corrupt or empty?");
		} else {
			echo "Error on loading addons config.. Database down, not reachable, corrupt or empty?";
		}
	} else {
		return $addons_config->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	}
	//$addons_config['assign_groups_groupids']['value'];
}
?>