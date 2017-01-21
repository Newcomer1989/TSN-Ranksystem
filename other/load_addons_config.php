<?PHP
function load_addons_config($mysqlcon,$lang,$dbname,$timezone,$logpath) {
	if(($addons_config = $mysqlcon->query("SELECT * FROM $dbname.addons_config")) === false) {
		enter_logfile($logpath,$timezone,2,"load_addons_config 0:".print_r($mysqlcon->errorInfo(), true));
	}
	$addons_config = $addons_config->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
	return $addons_config;
	//$addons_config['assign_groups_groupids']['value'];
}
?>