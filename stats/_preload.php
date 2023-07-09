<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'other/_functions.php';
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'other/config.php';

start_session($cfg);
$lang = set_language(get_language());

error_reporting(E_ALL);
ini_set('log_errors', 1);
set_error_handler('php_error_handling');
ini_set('error_log', $GLOBALS['logfile']);

if (isset($_POST['refresh'])) {
    rem_session_ts3();
}

try {
    require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'other/load_addons_config.php';

    $addons_config = load_addons_config($mysqlcon, $lang, $cfg, $dbname);

    if (! isset($_SESSION[$rspathhex.'tsuid'])) {
        set_session_ts3($mysqlcon, $cfg, $lang, $dbname);
    }
    require_once __DIR__.DIRECTORY_SEPARATOR.'_nav.php';
} catch(Throwable $ex) {
}
