<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'other/_functions.php';
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'other/config.php';

$prot = start_session($cfg);
$lang = set_language(get_language());

error_reporting(E_ALL);
ini_set('log_errors', 1);
set_error_handler('php_error_handling');
ini_set('error_log', $GLOBALS['logfile']);

try {
    if (isset($_POST['logout'])) {
        echo 'logout';
        rem_session_ts3();
        header("Location: $prot://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
        exit;
    }
    if (strtolower(basename($_SERVER['SCRIPT_NAME'])) != 'index.php' && basename($_SERVER['SCRIPT_NAME']) != 'resetpassword.php' && (! isset($_SESSION[$rspathhex.'username']) || ! isset($_SESSION[$rspathhex.'password']) || ! isset($_SESSION[$rspathhex.'clientip']) || ! isset($cfg['webinterface_user']) || ! isset($cfg['webinterface_pass']) || ! hash_equals($_SESSION[$rspathhex.'username'], $cfg['webinterface_user']) || ! hash_equals($_SESSION[$rspathhex.'password'], $cfg['webinterface_pass']) || ! hash_equals($_SESSION[$rspathhex.'clientip'], getclientip()))) {
        rem_session_ts3();
        header("Location: $prot://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
        exit;
    }

    $csrf_token = bin2hex(openssl_random_pseudo_bytes(32));
} catch(Throwable $ex) {
}
