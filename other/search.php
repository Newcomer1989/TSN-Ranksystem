<?PHP
$q = strtolower($_GET["q"]);
if (!$q)
    return;
require_once('config.php');
$dbuserlist = $mysqlcon->query("SELECT * FROM user ORDER BY online DESC");
$items      = array();
$dbuserlist = $dbuserlist->fetchAll();
foreach ($dbuserlist as $userlist) {
    $items[$userlist['name']] = $userlist['uuid'];
}
foreach ($items as $key => $value) {
    if (strpos(strtolower($key), $q) !== false) {
		$key=str_replace('|','&#124;',$key);
        echo "$key|$value\n";
    }
}
?>