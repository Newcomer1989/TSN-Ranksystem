<?PHP
$target = $_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/stats/';
if(isset($_SERVER['HTTPS'])) {
	header('Location: https://'.$target);
} else {
	header('Location: http://'.$target);
}
?>