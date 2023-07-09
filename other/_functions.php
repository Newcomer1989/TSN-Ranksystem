<?php

function bot_check()
{
    $output = [];
    if (check_bot_process() == false) {
        if (! file_exists($GLOBALS['autostart'])) {
            if (file_exists($GLOBALS['pidfile'])) {
                unlink($GLOBALS['pidfile']);
            }
            $output = bot_start();
        } else {
            $output['rc'] = 1;
            $output['msg'] = 'Starting the Ranksystem Bo failed! Autostart is deactivated. Use start command instead.';
            $output['loglvl'] = 3;
        }
    } else {
        $output['rc'] = 0;
        $output['msg'] = 'The Ranksystem Bot seems to be running.';
        $output['loglvl'] = 4;
    }

    return $output;
}

function bot_restart()
{
    $output = [];
    $result = bot_stop();
    if ($result['rc'] != 0) {
        $output['rc'] = $result['rc'];
        $output['msg'] = $result['msg'];
        $output['loglvl'] = $result['loglvl'];
        $output['ranksystemlog'] = $result['ranksystemlog'];

        return $output;
    }
    $result = bot_start();
    if ($result['rc'] != 0) {
        $output['rc'] = $result['rc'];
        $output['msg'] = $result['msg'];
        $output['loglvl'] = $result['loglvl'];
        $output['ranksystemlog'] = $result['ranksystemlog'];

        return $output;
    }
    $output['rc'] = 0;
    $output['msg'] = 'Ranksystem Bot successfully restarted!';
    $output['loglvl'] = 4;
    usleep(80000);
    $output['log'] = getlog('40', explode(',', 'CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG,NONE'), null, null);

    return $output;
}

function bot_start()
{
    $output = [];
    if (check_log_permissions() === true) {
        if (check_bot_process() == false) {
            if (substr(php_uname(), 0, 7) == 'Windows') {
                try {
                    $WshShell = new COM('WScript.Shell');
                } catch (Exception $e) {
                    $output['rc'] = 1;
                    $output['msg'] = 'Error due loading the PHP COM module (wrong server configuration!): '.$e->getMessage();
                    $output['loglvl'] = 3;
                }
                try {
                    $wcmd = 'cmd /C '.$GLOBALS['phpcommand'].' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs'.DIRECTORY_SEPARATOR.'bot.php';
                    $oExec = $WshShell->Run($wcmd, 0, false);
                } catch (Exception $e) {
                    $output['rc'] = 1;
                    $output['msg'] = 'Error due starting the Ranksystem Bot (exec command enabled?): '.$e->getMessage();
                    $output['loglvl'] = 3;
                }
                try {
                    exec('wmic process where "Name LIKE "%php%" AND CommandLine LIKE "%bot.php%"" get ProcessId', $pid);
                } catch (Exception $e) {
                    $output['rc'] = 1;
                    $output['msg'] = 'Error due getting process list (wmic command enabled?): '.$e->getMessage();
                    $output['loglvl'] = 3;
                }
                if (isset($pid[1]) && is_numeric($pid[1])) {
                    exec('echo '.$pid[1].' > '.$GLOBALS['pidfile']);
                    if (file_exists($GLOBALS['autostart'])) {
                        unlink($GLOBALS['autostart']);
                    }
                    $output['rc'] = 0;
                    $output['msg'] = 'Ranksystem Bot successfully started!';
                    $output['loglvl'] = 4;
                    usleep(80000);
                    $output['log'] = getlog('40', explode(',', 'CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG,NONE'), null, null);
                } else {
                    $output['rc'] = 1;
                    $output['msg'] = 'Starting the Ranksystem Bot failed!';
                    $output['loglvl'] = 3;
                }
            } else {
                exec($GLOBALS['phpcommand'].' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'jobs'.DIRECTORY_SEPARATOR.'bot.php >/dev/null 2>&1 & echo $! > '.$GLOBALS['pidfile']);
                if (check_bot_process() == false) {
                    $output['rc'] = 1;
                    $output['msg'] = 'Starting the Ranksystem Bot failed!';
                    $output['loglvl'] = 3;
                } else {
                    if (file_exists($GLOBALS['autostart'])) {
                        unlink($GLOBALS['autostart']);
                    }
                    $output['rc'] = 0;
                    $output['msg'] = 'Ranksystem Bot successfully started!';
                    $output['loglvl'] = 4;
                    usleep(80000);
                    $output['log'] = getlog('40', explode(',', 'CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG,NONE'), null, null);
                }
            }
        } else {
            $output['rc'] = 0;
            $output['msg'] = 'The Ranksystem is already running.';
            $output['loglvl'] = 4;
        }
    } else {
        $output['rc'] = 1;
        $output['msg'] = check_log_permissions().' Canceled start request!';
        $output['loglvl'] = 3;
    }

    return $output;
}

function bot_stop()
{
    $output = [];
    if (check_log_permissions() === true) {
        if (check_bot_process() == false) {
            if (is_file($GLOBALS['pidfile'])) {
                unlink($GLOBALS['pidfile']);
            }
            $output['rc'] = 0;
            $output['msg'] = 'The Ranksystem seems not to be running.';
            $output['loglvl'] = 4;
        } else {
            $pid = str_replace(["\r", "\n"], '', file_get_contents($GLOBALS['pidfile']));
            unlink($GLOBALS['pidfile']);
            $count_check = 0;
            while (check_bot_process($pid) == true) {
                sleep(1);
                $count_check++;
                if ($count_check > 10) {
                    if (substr(php_uname(), 0, 7) == 'Windows') {
                        exec('taskkill /F /PID '.$pid);
                    } else {
                        exec('kill -9 '.$pid);
                    }
                    $output['rc'] = 1;
                    $output['msg'] = 'Stop command received! Bot does not react, process killed!';
                    $output['loglvl'] = 3;
                    break;
                }
            }
            if (check_bot_process($pid) == true) {
                $output['rc'] = 1;
                $output['msg'] = 'Stopping the Ranksystem Bot failed!';
                $output['loglvl'] = 3;
            } else {
                file_put_contents($GLOBALS['autostart'], '');
                $output['rc'] = 0;
                $output['msg'] = 'Ranksystem Bot successfully stopped!';
                $output['loglvl'] = 4;
                usleep(80000);
                $output['log'] = getlog('40', explode(',', 'CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG,NONE'), null, null);
            }
        }
    } else {
        $output['rc'] = 1;
        $output['msg'] = check_log_permissions().' Canceled stop request!';
        $output['loglvl'] = 3;
    }

    return $output;
}

function check_bot_process($pid = null)
{
    if (substr(php_uname(), 0, 7) == 'Windows') {
        if (! empty($pid)) {
            exec('wmic process where "processid='.$pid.'" get processid 2>nul', $result);
            if (isset($result[1]) && is_numeric($result[1])) {
                return true;
            } else {
                return false;
            }
        } else {
            if (file_exists($GLOBALS['pidfile'])) {
                $pid = str_replace(["\r", "\n"], '', file_get_contents($GLOBALS['pidfile']));
                exec('wmic process where "processid='.$pid.'" get processid 2>nul', $result);
                if (isset($result[1]) && is_numeric($result[1])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    } else {
        if (! empty($pid)) {
            $result = str_replace(["\r", "\n"], '', shell_exec('ps '.$pid));
            if (strstr($result, $pid)) {
                return true;
            } else {
                return false;
            }
        } else {
            if (file_exists($GLOBALS['pidfile'])) {
                $check_pid = str_replace(["\r", "\n"], '', file_get_contents($GLOBALS['pidfile']));
                $result = str_replace(["\r", "\n"], '', shell_exec('ps '.$check_pid));
                if (strstr($result, $check_pid)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
}

function check_log_permissions()
{
    if (! is_writable($GLOBALS['logpath'])) {
        return '!!!! Logs folder is not writable !!!!';
    } elseif (file_exists($GLOBALS['logfile']) && ! is_writable($GLOBALS['logfile'])) {
        return '!!!! Log file is not writable !!!!';
    } else {
        return true;
    }
}

function check_shutdown()
{
    if (! file_exists($GLOBALS['pidfile'])) {
        shutdown(null, 4, 'Received signal to stop!');
    }
}

function date_format_to($format, $syntax)
{
    $strf_syntax = [
        '%O', '%d', '%a', '%e', '%A', '%u', '%w', '%j',
        '%V',
        '%B', '%m', '%b', '%-m',
        '%G', '%Y', '%y',
        '%P', '%p', '%l', '%I', '%H', '%M', '%S',
        '%z', '%Z',
        '%s',
    ];
    $date_syntax = [
        'S', 'd', 'D', 'j', 'l', 'N', 'w', 'z',
        'W',
        'F', 'm', 'M', 'n',
        'o', 'Y', 'y',
        'a', 'A', 'g', 'h', 'H', 'i', 's',
        'O', 'T',
        'U',
    ];
    switch ($syntax) {
        case 'date':
            $from = $strf_syntax;
            $to = $date_syntax;
            break;

        case 'strf':
            $from = $date_syntax;
            $to = $strf_syntax;
            break;

        default:
            return false;
    }
    $pattern = array_map(
        function ($s) {
            return '/(?<!\\\\|\%)'.$s.'/';
        },
        $from
    );

    return preg_replace($pattern, $to, $format);
}

function db_connect($dbtype, $dbhost, $dbname, $dbuser, $dbpass, $exit = null, $persistent = null)
{
    if ($dbtype != 'type') {
        $dbserver = $dbtype.':host='.$dbhost.';dbname='.$dbname.';charset=utf8mb4';
        if ($dbtype == 'mysql' && $persistent != null) {
            $dboptions = [
                PDO::ATTR_PERSISTENT => true,
            ];
        } else {
            $dboptions = [];
        }
        try {
            $mysqlcon = new PDO($dbserver, $dbuser, $dbpass, $dboptions);

            return $mysqlcon;
        } catch (PDOException $e) {
            echo 'Delivered Parameter: '.$dbserver.'<br><br>';
            echo 'Database Connection failed: <b>'.$e->getMessage().'</b><br><br>Check:<br>- You have already installed the Ranksystem? Run <a href="../install.php">install.php</a> first!<br>- Is the database reachable?<br>- You have installed all needed PHP extenstions? Have a look here for <a href="//ts-ranksystem.com/#windows">Windows</a> or <a href="//ts-ranksystem.com/#linux">Linux</a>?';
            $err_lvl = 3;
            if ($exit != null) {
                exit;
            }
        }
    }
}

function enter_logfile($loglevel, $logtext, $norotate = false)
{
    if ($loglevel != 9 && $loglevel > $GLOBALS['logs_debug_level']) {
        return;
    }
    $file = $GLOBALS['logfile'];
    switch ($loglevel) {
        case 1: $loglevel = '  CRITICAL  ';
            break;
        case 2: $loglevel = '  ERROR     ';
            break;
        case 3: $loglevel = '  WARNING   ';
            break;
        case 4: $loglevel = '  NOTICE    ';
            break;
        case 5: $loglevel = '  INFO      ';
            break;
        case 6: $loglevel = '  DEBUG     ';
            break;
        default:$loglevel = '  NONE      ';
    }
    $loghandle = fopen($file, 'a');
    fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($GLOBALS['logs_timezone']))->format('Y-m-d H:i:s.u ').$loglevel.$logtext."\n");
    fclose($loghandle);
    if ($norotate == false && filesize($file) > ($GLOBALS['logs_rotation_size'] * 1048576)) {
        $loghandle = fopen($file, 'a');
        fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($GLOBALS['logs_timezone']))->format('Y-m-d H:i:s.u ')."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
        fclose($loghandle);
        $file2 = "$file.old";
        if (file_exists($file2)) {
            unlink($file2);
        }
        rename($file, $file2);
        $loghandle = fopen($file, 'a');
        fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($GLOBALS['logs_timezone']))->format('Y-m-d H:i:s.u ')."  NOTICE    Rotated logfile...\n");
        fclose($loghandle);
    }
}

function error_handling($msg, $type = null)
{
    if (is_string($msg) && is_string($type) && strstr($type, '#') && strstr($msg, '#####')) {
        $type_arr = explode('#', $type);
        $msg_arr = explode('#####', $msg);
        $cnt = 0;

        foreach ($msg_arr as $msg) {
            switch ($type_arr[$cnt]) {
                case null: echo '<div class="alert alert-success alert-dismissible">';
                    break;
                case 0: echo '<div class="alert alert-success alert-dismissible">';
                    break;
                case 1: echo '<div class="alert alert-info alert-dismissible">';
                    break;
                case 2: echo '<div class="alert alert-warning alert-dismissible">';
                    break;
                case 3: echo '<div class="alert alert-danger alert-dismissible">';
                    break;
            }
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',$msg_arr[$cnt],'</div>';
            $cnt++;
        }
    } else {
        switch ($type) {
            case null: echo '<div class="alert alert-success alert-dismissible">';
                break;
            case 0: echo '<div class="alert alert-success alert-dismissible">';
                break;
            case 1: echo '<div class="alert alert-info alert-dismissible">';
                break;
            case 2: echo '<div class="alert alert-warning alert-dismissible">';
                break;
            case 3: echo '<div class="alert alert-danger alert-dismissible">';
                break;
        }
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',$msg,'</div>';
    }
}

function exception_client_code($code)
{
    switch ($code) {
        case 1:
            return '1 - Channel Exception';
        case 2:
            return '2 - ServerGroup Exception';
        case 3:
            return '3 - Client Exception';
    }
}

function getclientip()
{
    if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (! empty($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    } elseif (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (! empty($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    } elseif (! empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    } else {
        return false;
    }
}

function getlog($number_lines, $filters, $filter2, $inactivefilter = null)
{
    $lines = [];
    if (file_exists($GLOBALS['logfile'])) {
        $fp = fopen($GLOBALS['logfile'], 'r');
        $buffer = [];
        while ($line = fgets($fp, 4096)) {
            array_push($buffer, htmlspecialchars($line));
        }
        fclose($fp);
        $buffer = array_reverse($buffer);
        $lastfilter = 'init';
        foreach ($buffer as $line) {
            if (substr($line, 0, 2) != '20' && in_array($lastfilter, $filters)) {
                array_push($lines, $line);
                if (count($lines) > $number_lines) {
                    break;
                }
                continue;
            }
            foreach ($filters as $filter) {
                if (($filter != null && strstr($line, $filter) && $filter2 == null) || ($filter2 != null && strstr($line, $filter2) && $filter != null && strstr($line, $filter))) {
                    if ($filter == 'CRITICAL' || $filter == 'ERROR') {
                        array_push($lines, '<span class="text-danger">'.$line.'</span>');
                    } elseif ($filter == 'WARNING') {
                        array_push($lines, '<span class="text-warning">'.$line.'</span>');
                    } else {
                        array_push($lines, $line);
                    }
                    $lastfilter = $filter;
                    if (count($lines) > $number_lines) {
                        break 2;
                    }
                    break;
                } elseif ($inactivefilter != null) {
                    foreach ($inactivefilter as $defilter) {
                        if ($defilter != null && strstr($line, $defilter)) {
                            $lastfilter = $defilter;
                        }
                    }
                }
            }
        }
    } else {
        $lines[] = "No log entry found...\n";
        $lines[] = "The logfile will be created with next startup.\n";
    }

    return $lines;
}

function get_language()
{
    $rspathhex = get_rspath();
    if (isset($_GET['lang'])) {
        if (is_dir($GLOBALS['langpath'])) {
            foreach (scandir($GLOBALS['langpath']) as $file) {
                if ('.' === $file || '..' === $file || is_dir($file)) {
                    continue;
                }
                $sep_lang = preg_split('/[._]/', $file);
                if (isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
                    if (strtolower($_GET['lang']) == strtolower($sep_lang[1])) {
                        $_SESSION[$rspathhex.'language'] = $sep_lang[1];

                        return $sep_lang[1];
                    }
                }
            }
        }
    }
    if (isset($_SESSION[$rspathhex.'language'])) {
        return $_SESSION[$rspathhex.'language'];
    }
    if (isset($GLOBALS['default_language'])) {
        return $GLOBALS['default_language'];
    }

    return 'en';
}

function get_percentage($max_value, $value)
{
    if ($max_value > 0) {
        return round(($value / $max_value) * 100);
    } else {
        return 0;
    }
}

function get_rspath()
{
    return 'rs_'.dechex(crc32(__DIR__)).'_';
}

function get_style($default_style)
{
    $rspathhex = get_rspath();
    if (isset($_GET['style'])) {
        if (is_dir($GLOBALS['stylepath'])) {
            foreach (scandir($GLOBALS['stylepath']) as $folder) {
                if ('.' === $folder || '..' === $folder) {
                    continue;
                }
                if (is_dir($GLOBALS['stylepath'].DIRECTORY_SEPARATOR.$folder)) {
                    foreach (scandir($GLOBALS['stylepath'].DIRECTORY_SEPARATOR.$folder) as $file) {
                        if ('.' === $file || '..' === $file || is_dir($file)) {
                            continue;
                        }
                        $sep_style = preg_split('/[._]/', $file);
                        if ($file == 'ST.css') {
                            $_SESSION[$rspathhex.'style'] = $folder;

                            return $folder;
                        }
                    }
                }
            }
        }
    }
    if (isset($_SESSION[$rspathhex.'style'])) {
        return $_SESSION[$rspathhex.'style'];
    }
    if (isset($default_style)) {
        return $default_style;
    }

    return null;
}

function human_readable_size($bytes, $lang)
{
    $size = [$lang['size_byte'], $lang['size_kib'], $lang['size_mib'], $lang['size_gib'], $lang['size_tib'], $lang['size_pib'], $lang['size_eib'], $lang['size_zib'], $lang['size_yib']];
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf('%.2f', $bytes / pow(1024, $factor)).' '.@$size[$factor];
}

function list_rankup($cfg, $lang, $sqlhisgroup, $value, $adminlogin, $nation, $grpcount, $rank = null)
{
    $return = '<tr>';
    if ($cfg['stats_column_rank_switch'] == 1 || $adminlogin == 1) {
        if ($value['except'] == 2 || $value['except'] == 3) {
            $return .= '<td></td>';
        } else {
            $return .= '<td>'.$value['rank'].'</td>';
        }
    }
    if ($adminlogin == 1) {
        $return .= '<td><a href="//tsviewer.com/index.php?page=search&action=ausgabe_user&nickname='.htmlspecialchars($value['name']).'" target="_blank">'.htmlspecialchars($value['name']).'</a></td>';
    } elseif ($cfg['stats_column_client_name_switch'] == 1) {
        $return .= '<td>'.htmlspecialchars($value['name']).'</td>';
    }
    if ($adminlogin == 1) {
        $return .= '<td><a href="//ts3index.com/?page=searchclient&uid='.$value['uuid'].'" target="_blank">'.$value['uuid'].'</a></td>';
    } elseif ($cfg['stats_column_unique_id_switch'] == 1) {
        $return .= '<td>'.$value['uuid'].'</td>';
    }
    if ($cfg['stats_column_client_db_id_switch'] == 1 || $adminlogin == 1) {
        $return .= '<td>'.$value['cldbid'].'</td>';
    }
    if ($cfg['stats_column_last_seen_switch'] == 1 || $adminlogin == 1) {
        if ($value['online'] == 1) {
            $return .= '<td class="text-success">online</td>';
        } else {
            $return .= '<td>'.date('Y-m-d H:i:s', $value['lastseen']).'</td>';
        }
    }
    if ($cfg['stats_column_nation_switch'] == 1 || $adminlogin == 1) {
        if (strtoupper($value['nation']) == 'XX' || $value['nation'] == null) {
            $return .= '<td><i class="fas fa-question-circle" title="'.$lang['unknown'].'"></i></td>';
        } else {
            $return .= '<td><span class="flag-icon flag-icon-'.strtolower(htmlspecialchars($value['nation'])).'" title="'.$value['nation'].' - '.$nation[$value['nation']].'"></span></td>';
        }
    }
    if ($cfg['stats_column_version_switch'] == 1 || $adminlogin == 1) {
        $return .= '<td>'.htmlspecialchars($value['version']).'</td>';
    }
    if ($cfg['stats_column_platform_switch'] == 1 || $adminlogin == 1) {
        $return .= '<td>'.htmlspecialchars($value['platform']).'</td>';
    }
    if ($cfg['stats_column_online_time_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['count'])) {
            $return .= '<td title="'.number_format($value['count'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['count']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_idle_time_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['idle'])) {
            $return .= '<td title="'.number_format($value['idle'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['idle']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_active_time_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['count']) && is_numeric($value['idle'])) {
            $return .= '<td title="'.number_format(($value['count']) - $value['idle'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.(round($value['count']) - round($value['idle'])));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_online_day_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['count_day'])) {
            $return .= '<td title="'.number_format($value['count_day'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['count_day']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_idle_day_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['idle_day'])) {
            $return .= '<td title="'.number_format($value['idle_day'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['idle_day']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_active_day_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['active_day'])) {
            $return .= '<td title="'.number_format($value['active_day'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['active_day']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_online_week_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['count_week'])) {
            $return .= '<td title="'.number_format($value['count_week'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['count_week']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_idle_week_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['idle_week'])) {
            $return .= '<td title="'.number_format($value['idle_week'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['idle_week']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_active_week_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['active_week'])) {
            $return .= '<td title="'.number_format($value['active_week'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['active_week']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_online_month_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['count_month'])) {
            $return .= '<td title="'.number_format($value['count_month'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['count_month']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_idle_month_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['idle_month'])) {
            $return .= '<td title="'.number_format($value['idle_month'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['idle_month']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_active_month_switch'] == 1 || $adminlogin == 1) {
        if (is_numeric($value['active_month'])) {
            $return .= '<td title="'.number_format($value['active_month'], 0, ',', '.').' '.$lang['time_sec'].'">';
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.round($value['active_month']));
            $return .= $dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } else {
            $return .= '<td>'.$lang['unknown'].'</td>';
        }
    }
    if ($cfg['stats_column_current_server_group_switch'] == 1 || $adminlogin == 1) {
        if ($value['grpid'] == 0) {
            $return .= '<td></td>';
        } elseif (isset($sqlhisgroup[$value['grpid']]) && $sqlhisgroup[$value['grpid']]['iconid'] != 0) {
            $return .= '<td title="'.$lang['wigrpt2'].' ID: '.$value['grpid'].'"><img src="../tsicons/'.$sqlhisgroup[$value['grpid']]['iconid'].'.'.$sqlhisgroup[$value['grpid']]['ext'].'" width="16" height="16" alt="groupicon"><span class="item-margin">'.$sqlhisgroup[$value['grpid']]['sgidname'].'</span></td>';
        } elseif (isset($sqlhisgroup[$value['grpid']])) {
            $return .= '<td title="'.$lang['wigrpt2'].' ID: '.$value['grpid'].'">'.$sqlhisgroup[$value['grpid']]['sgidname'].'</td>';
        } else {
            $return .= '<td><i>'.$lang['unknown'].'</i></td>';
        }
    }
    if ($cfg['stats_column_current_group_since_switch'] == 1 || $adminlogin == 1) {
        if ($value['grpsince'] == 0) {
            $return .= '<td></td>';
        } else {
            $return .= '<td>'.date('Y-m-d H:i:s', $value['grpsince']).'</td>';
        }
    }
    if ($cfg['stats_column_next_rankup_switch'] == 1 || $adminlogin == 1) {
        $return .= '<td title="';
        if (($value['except'] == 0 || $value['except'] == 1) && $value['nextup'] > 0) {
            $dtF = new DateTime('@0');
            $dtT = new DateTime('@'.$value['nextup']);
            $return .= number_format($value['nextup'], 0, ',', '.').' '.$lang['time_sec'].'">'.$dtF->diff($dtT)->format($cfg['default_date_format']).'</td>';
        } elseif ($value['except'] == 0 || $value['except'] == 1) {
            $return .= '0 '.$lang['time_sec'].'">0</td>';
        } elseif ($value['except'] == 2 || $value['except'] == 3) {
            $return .= '0 '.$lang['time_sec'].'">0</td>';
        } else {
            $return .= $lang['errukwn'].'</td>';
        }
    }
    if ($cfg['stats_column_next_server_group_switch'] == 1 || $adminlogin == 1) {
        if ($grpcount == count($cfg['rankup_definition']) && $value['nextup'] == 0 && $cfg['stats_show_clients_in_highest_rank_switch'] == 1 || $grpcount == count($cfg['rankup_definition']) && $value['nextup'] == 0 && $adminlogin == 1) {
            $return .= '<td><em>'.$lang['highest'].'</em></td>';
        } elseif ($value['except'] == 2 || $value['except'] == 3) {
            $return .= '<td><em>'.$lang['listexcept'].'</em></td>';
        } elseif (isset($sqlhisgroup[$rank['group']]) && $sqlhisgroup[$rank['group']]['iconid'] != 0) {
            $return .= '<td title="'.$lang['wigrpt2'].' ID: '.$rank['group'].'"><img src="../tsicons/'.$sqlhisgroup[$rank['group']]['iconid'].'.'.$sqlhisgroup[$rank['group']]['ext'].'" width="16" height="16" alt="missed_icon"><span class="item-margin">'.$sqlhisgroup[$rank['group']]['sgidname'].'</span></td>';
        } elseif (isset($sqlhisgroup[$rank['group']])) {
            $return .= '<td title="'.$lang['wigrpt2'].' ID: '.$rank['group'].'">'.$sqlhisgroup[$rank['group']]['sgidname'].'</td>';
        } else {
            $return .= '<td></td>';
        }
    }

    return $return;
}

function mime2extension($mimetype)
{
    $mimearr = [
        'image/bmp' => 'bmp',
        'image/x-bmp' => 'bmp',
        'image/x-bitmap' => 'bmp',
        'image/x-xbitmap' => 'bmp',
        'image/x-win-bitmap' => 'bmp',
        'image/x-windows-bmp' => 'bmp',
        'image/ms-bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/x-portable-bitmap' => 'pbm',
        'image/x-portable-graymap' => 'pgm',
        'image/png' => 'png',
        'image/x-png' => 'png',
        'image/x-portable-pixmap' => 'ppm',
        'image/svg+xml' => 'svg',
        'image/x-xbitmap' => 'xbm',
        'image/x-xpixmap' => 'xpm',
    ];

    return isset($mimearr[$mimetype]) ? $mimearr[$mimetype] : false;
}

function pagination($keysort, $keyorder, $user_pro_seite, $seiten_anzahl_gerundet, $seite, $getstring)
{
    $pagination = '<nav><div class="text-center"><ul class="pagination"><li><a href="?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite=1&amp;user='.$user_pro_seite.'&amp;search='.$getstring.'" aria-label="backward"><span aria-hidden="true"><span class="fas fa-caret-square-left fa-fw" aria-hidden="true"></span></span></a></li>';
    for ($a = 0; $a < $seiten_anzahl_gerundet; $a++) {
        $b = $a + 1;
        if ($seite == $b) {
            $pagination .= '<li class="active"><a href="">'.$b.'</a></li>';
        } elseif ($b > $seite - 5 && $b < $seite + 5) {
            $pagination .= '<li><a href="?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite='.$b.'&amp;user='.$user_pro_seite.'&amp;search='.$getstring.'">'.$b.'</a></li>';
        }
    }
    $pagination .= '<li><a href="?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite='.$seiten_anzahl_gerundet.'&amp;user='.$user_pro_seite.'&amp;search='.$getstring.'" aria-label="forward"><span aria-hidden="true"><span class="fas fa-caret-square-right fa-fw" aria-hidden="true"></span></span></a></li></ul></div></nav>';

    return $pagination;
}

function php_error_handling($err_code, $err_msg, $err_file, $err_line)
{
    global $cfg;
    switch ($err_code) {
        case E_USER_ERROR: $loglevel = 2;
            break;
        case E_USER_WARNING: $loglevel = 3;
            break;
        case E_USER_NOTICE: $loglevel = 4;
            break;
        default: $loglevel = 4;
    }
    if (substr($err_msg, 0, 15) != 'password_hash()' && substr($err_msg, 0, 11) != 'fsockopen()') {
        enter_logfile($loglevel, $err_code.': '.$err_msg.' on line '.$err_line.' in '.$err_file);
    }

    return true;
}

function rem_session_ts3()
{
    $rspathhex = get_rspath();
    unset($_SESSION[$rspathhex.'admin']);
    unset($_SESSION[$rspathhex.'clientip']);
    unset($_SESSION[$rspathhex.'connected']);
    unset($_SESSION[$rspathhex.'inactivefilter']);
    unset($_SESSION[$rspathhex.'language']);
    unset($_SESSION[$rspathhex.'logfilter']);
    unset($_SESSION[$rspathhex.'logfilter2']);
    unset($_SESSION[$rspathhex.'multiple']);
    unset($_SESSION[$rspathhex.'newversion']);
    unset($_SESSION[$rspathhex.'number_lines']);
    unset($_SESSION[$rspathhex.'password']);
    unset($_SESSION[$rspathhex.'serverport']);
    unset($_SESSION[$rspathhex.'temp_cldbid']);
    unset($_SESSION[$rspathhex.'temp_name']);
    unset($_SESSION[$rspathhex.'temp_uuid']);
    unset($_SESSION[$rspathhex.'token']);
    unset($_SESSION[$rspathhex.'tsavatar']);
    unset($_SESSION[$rspathhex.'tscldbid']);
    unset($_SESSION[$rspathhex.'tsconnections']);
    unset($_SESSION[$rspathhex.'tscreated']);
    unset($_SESSION[$rspathhex.'tsname']);
    unset($_SESSION[$rspathhex.'tsuid']);
    unset($_SESSION[$rspathhex.'upinfomsg']);
    unset($_SESSION[$rspathhex.'username']);
    unset($_SESSION[$rspathhex.'uuid_verified']);
}

function select_channel($channellist, $cfg_cid, $multiple = null)
{
    if (isset($channellist) && count($channellist) > 0) {
        $options = '';
        if ($multiple == 1) {
            $options = ' multiple=""';
        }
        $selectbox = '<select class="selectpicker form-control" data-live-search="true" data-actions-box="true"'.$options.' name="channelid[]">';
        $channelarr = sort_channel_tree($channellist);

        foreach ($channelarr as $cid => $channel) {
            if (isset($channel['sub_level'])) {
                $prefix = '';
                for ($y = 0; $y < $channel['sub_level']; $y++) {
                    if (($y + 1) == $channel['sub_level'] && isset($channel['has_childs'])) {
                        $prefix .= '<img src=\'../tsicons/arrow_down.png\' width=\'16\' height=\'16\'>';
                        $prefix2 = '<img class=\'arrowtree\' src=\'../tsicons/arrow_down.png\' width=\'16\' height=\'16\'>';
                    } else {
                        $prefix .= '<img src=\'../tsicons/placeholder.png\' width=\'16\' height=\'16\'>';
                        $prefix2 = '<img src=\'../tsicons/placeholder.png\' width=\'16\' height=\'16\'>';
                    }
                }
            }
            $chname = htmlspecialchars($channel['channel_name']);
            if (isset($channel['iconid']) && $channel['iconid'] != 0) {
                $iconid = $channel['iconid'].'.';
            } else {
                $iconid = 'placeholder.png';
            }
            if ($cid != 0) {
                if ($multiple !== 1 && $cid == $cfg_cid || $multiple === 1 && is_array($cfg_cid) && isset($cfg_cid[$cid])) {
                    $selectbox .= '<option selected="selected" data-content="';
                } else {
                    $selectbox .= '<option data-content="';
                }
                if (preg_match("/\[[^\]]*spacer[^\]]*\]/", $channel['channel_name']) && $channel['pid'] == 0) {
                    $exploded_chname = explode(']', $channel['channel_name']);
                    $isspacer = false;

                    switch($exploded_chname[1]) {
                        case '___':
                            $chname = "<span class='tsspacer5 tsspacercolor tsspacerimg'></span>";
                            $isspacer = true;
                            break;
                        case '---':
                            $chname = "<span class='tsspacer4 tsspacercolor tsspacerimg'></span>";
                            $isspacer = true;
                            break;
                        case '...':
                            $chname = "<span class='tsspacer3 tsspacercolor tsspacerimg'></span>";
                            $isspacer = true;
                            break;
                        case '-.-':
                            $chname = "<span class='tsspacer2 tsspacercolor tsspacerimg'></span>";
                            $isspacer = true;
                            break;
                        case '-..':
                            $chname = "<span class='tsspacer1 tsspacercolor tsspacerimg'></span>";
                            $isspacer = true;
                            break;
                        default:
                            $chname = htmlspecialchars($exploded_chname[1]);
                    }

                    if ($isspacer === false && preg_match("/\[(.*)spacer.*\]/", $channel['channel_name'], $matches)) {
                        switch($matches[1]) {
                            case '*':
                                $postfix = $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer margincid"';
                                break;
                            case 'c':
                                $postfix = $prefix2.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer text-center margincid"';
                                break;
                            case 'r':
                                $postfix = $prefix2.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer text-right margincid"';
                                break;
                            default:
                                $postfix = $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer margincid"';
                        }
                    } else {
                        $postfix = $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer margincid"';
                    }
                    $selectbox .= $postfix;
                } else {
                    $selectbox .= $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="margincid"';
                }
                $selectbox .= ' value="'.$cid.'"></option>';
            }
        }
        $selectbox .= '</select>';
    } elseif ($multiple === 1) {
        $selectbox = '<textarea class="form-control" data-pattern="^([0-9]{1,9},)*[0-9]{1,9}$" data-error="Only use digits separated with a comma! Also must the first and last value be digit!" rows="1" name="channelid" maxlength="21588">';
        if (! empty($cfg_cid)) {
            $selectbox .= implode(',', array_flip($cfg_cid));
        }
        $selectbox .= '</textarea>';
        $selectbox .= '<div class="help-block with-errors"></div>';
    } else {
        $selectbox = '<input type="text" class="form-control" name="channelid" value="'.$cfg_cid.'">';
        $selectbox .= '<script>$("input[name=\'channelid\']").TouchSpin({
			min: 0,
			max: 2147483647,
			verticalbuttons: true,
			prefix: \'ID:\'
		});</script>';
    }

    return $selectbox;
}

function set_language($language)
{
    if (is_dir($GLOBALS['langpath'])) {
        foreach (scandir($GLOBALS['langpath']) as $file) {
            if ('.' === $file || '..' === $file || is_dir($file)) {
                continue;
            }
            $sep_lang = preg_split('/[._]/', $file);
            if (isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
                if (strtolower($language) == strtolower($sep_lang[1])) {
                    include $GLOBALS['langpath'].DIRECTORY_SEPARATOR.'/core_'.$sep_lang[1].'_'.$sep_lang[2].'_'.$sep_lang[3].'.'.$sep_lang[4];
                    $_SESSION[get_rspath().'language'] = $sep_lang[1];
                    $required_lang = 1;
                    break;
                }
            }
        }
    }
    if (! isset($required_lang)) {
        include $GLOBALS['langpath'].DIRECTORY_SEPARATOR.'core_en_english_gb.php';
    }

    return $lang;
}

function set_session_ts3($mysqlcon, $cfg, $lang, $dbname)
{
    $hpclientip = getclientip();
    $rspathhex = get_rspath();

    $allclients = $mysqlcon->query("SELECT `u`.`uuid`,`u`.`cldbid`,`u`.`name`,`u`.`firstcon`,`s`.`total_connections` FROM `$dbname`.`user` AS `u` LEFT JOIN `$dbname`.`stats_user` AS `s` ON `u`.`uuid`=`s`.`uuid` WHERE `online`='1'")->fetchAll();
    $iptable = $mysqlcon->query("SELECT `uuid`,`iphash`,`ip` FROM `$dbname`.`user_iphash`")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
    if (! isset($_SESSION[$rspathhex.'connected']) && isset($cfg['stats_news_html'])) {
        $_SESSION[$rspathhex.'stats_news_html'] = $cfg['stats_news_html'];
    }
    $_SESSION[$rspathhex.'connected'] = 0;
    $_SESSION[$rspathhex.'tsname'] = $lang['stag0016'];
    $_SESSION[$rspathhex.'serverport'] = $cfg['teamspeak_voice_port'];
    $_SESSION[$rspathhex.'multiple'] = [];

    if ($cfg['rankup_hash_ip_addresses_mode'] == 2) {
        $salt = md5(dechex(crc32(dirname(__DIR__))));
        $hashedip = crypt($hpclientip, '$2y$10$'.$salt.'$');
    }

    foreach ($allclients as $client) {
        if (isset($_SESSION[$rspathhex.'uuid_verified']) && $_SESSION[$rspathhex.'uuid_verified'] != $client['uuid']) {
            continue;
        }
        $verify = false;
        if ($cfg['rankup_hash_ip_addresses_mode'] == 1) {
            if (isset($iptable[$client['uuid']]['iphash']) && $iptable[$client['uuid']]['iphash'] != null && password_verify($hpclientip, $iptable[$client['uuid']]['iphash'])) {
                $verify = true;
            }
        } elseif ($cfg['rankup_hash_ip_addresses_mode'] == 2) {
            if (isset($iptable[$client['uuid']]['iphash']) && $hashedip == $iptable[$client['uuid']]['iphash'] && $iptable[$client['uuid']]['iphash'] != null) {
                $verify = true;
            }
        } else {
            if (isset($iptable[$client['uuid']]['ip']) && $hpclientip == $iptable[$client['uuid']]['ip'] && $iptable[$client['uuid']]['ip'] != null) {
                $verify = true;
            }
        }
        if ($verify == true) {
            $_SESSION[$rspathhex.'tsname'] = htmlspecialchars($client['name']);
            if (isset($_SESSION[$rspathhex.'tsuid']) && $_SESSION[$rspathhex.'tsuid'] != $client['uuid']) {
                $_SESSION[$rspathhex.'multiple'][$client['uuid']] = htmlspecialchars($client['name']);
                $_SESSION[$rspathhex.'tsname'] = 'verification needed (multiple)!';
                unset($_SESSION[$rspathhex.'admin']);
            } elseif (! isset($_SESSION[$rspathhex.'tsuid'])) {
                $_SESSION[$rspathhex.'multiple'][$client['uuid']] = htmlspecialchars($client['name']);
            }
            $_SESSION[$rspathhex.'tsuid'] = $client['uuid'];
            if (isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != null) {
                foreach (array_flip($cfg['webinterface_admin_client_unique_id_list']) as $auuid) {
                    if ($_SESSION[$rspathhex.'tsuid'] == $auuid) {
                        $_SESSION[$rspathhex.'admin'] = true;
                    }
                }
            }
            $_SESSION[$rspathhex.'tscldbid'] = $client['cldbid'];
            if ($client['firstcon'] == 0) {
                $_SESSION[$rspathhex.'tscreated'] = $lang['unknown'];
            } else {
                $_SESSION[$rspathhex.'tscreated'] = date('d-m-Y', $client['firstcon']);
            }
            if ($client['total_connections'] != null) {
                $_SESSION[$rspathhex.'tsconnections'] = $client['total_connections'];
            } else {
                $_SESSION[$rspathhex.'tsconnections'] = 0;
            }
            $convert = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p'];
            $uuidasbase16 = '';
            for ($i = 0; $i < 20; $i++) {
                $char = ord(substr(base64_decode($_SESSION[$rspathhex.'tsuid']), $i, 1));
                $uuidasbase16 .= $convert[($char & 0xF0) >> 4];
                $uuidasbase16 .= $convert[$char & 0x0F];
            }
            if (is_file('../avatars/'.$uuidasbase16.'.png')) {
                $_SESSION[$rspathhex.'tsavatar'] = $uuidasbase16.'.png';
            } else {
                $_SESSION[$rspathhex.'tsavatar'] = 'none';
            }
            $_SESSION[$rspathhex.'connected'] = 1;
            $_SESSION[$rspathhex.'language'] = $cfg['default_language'];
            $_SESSION[$rspathhex.'style'] = $cfg['default_style'];
        }
    }
}

function sendmessage($ts3, $cfg, $uuid, $msg, $targetmode, $targetid = null, $erromsg = null, $loglevel = null, $successmsg = null, $nolog = null)
{
    try {
        if (strlen($msg) > 1024) {
            $fragarr = explode('##*##', wordwrap($msg, 1022, '##*##', true), 1022);
            foreach ($fragarr as $frag) {
                usleep($cfg['teamspeak_query_command_delay']);
                if ($targetmode == 2 && $targetid != null) {
                    $ts3->serverGetSelected()->channelGetById($targetid)->message("\n".$frag);
                    if ($nolog == null) {
                        enter_logfile(6, "sendmessage fragment to channel (ID: $targetid): ".$frag);
                    }
                } elseif ($targetmode == 3) {
                    $ts3->serverGetSelected()->message("\n".$frag);
                    if ($nolog == null) {
                        enter_logfile(6, 'sendmessage fragment to server: '.$frag);
                    }
                } elseif ($targetmode == 1 && $targetid != null) {
                    $ts3->serverGetSelected()->clientGetById($targetid)->message("\n".$frag);
                    if ($nolog == null) {
                        enter_logfile(6, "sendmessage fragment to connectionID $targetid (uuid $uuid): ".$frag);
                    }
                } else {
                    $ts3->serverGetSelected()->clientGetByUid($uuid)->message("\n".$frag);
                    if ($nolog == null) {
                        enter_logfile(6, "sendmessage fragment to uuid $uuid (connectionID $targetid): ".$frag);
                    }
                }
            }
        } else {
            usleep($cfg['teamspeak_query_command_delay']);
            if ($targetmode == 2 && $targetid != null) {
                $ts3->serverGetSelected()->channelGetById($targetid)->message($msg);
                if ($nolog == null) {
                    enter_logfile(6, "sendmessage to channel (ID: $targetid): ".$msg);
                }
            } elseif ($targetmode == 3) {
                $ts3->serverGetSelected()->message($msg);
                if ($nolog == null) {
                    enter_logfile(6, 'sendmessage to server: '.$msg);
                }
            } elseif ($targetmode == 1 && $targetid != null) {
                $ts3->serverGetSelected()->clientGetById($targetid)->message($msg);
                if ($nolog == null) {
                    enter_logfile(6, "sendmessage to connectionID $targetid (uuid $uuid): ".$msg);
                }
            } else {
                $ts3->serverGetSelected()->clientGetByUid($uuid)->message($msg);
                if ($nolog == null) {
                    enter_logfile(6, "sendmessage to uuid $uuid (connectionID $targetid): ".$msg);
                }
            }
        }
        if ($successmsg != null) {
            enter_logfile(5, $successmsg);
        }
    } catch (Exception $e) {
        if ($loglevel != null) {
            enter_logfile($loglevel, $erromsg.' TS3: '.$e->getCode().': '.$e->getMessage());
        } else {
            enter_logfile(3, 'sendmessage: '.$e->getCode().': '.$e->getMessage().", targetmode: $targetmode, targetid: $targetid");
        }
    }
}

function shutdown($mysqlcon, $loglevel, $reason, $nodestroypid = true)
{
    if ($nodestroypid === true) {
        if (file_exists($GLOBALS['pidfile'])) {
            unlink($GLOBALS['pidfile']);
        }
    }
    if ($nodestroypid === true) {
        enter_logfile($loglevel, $reason.' Shutting down!');
        enter_logfile(9, '###################################################################');
    } else {
        enter_logfile($loglevel, $reason.' Ignore request!');
    }
    if (isset($mysqlcon)) {
        $mysqlcon = null;
    }
    exit;
}

function sort_channel_tree($channellist)
{
    foreach ($channellist as $cid => $results) {
        $channel['channel_order'][$results['pid']][$results['channel_order']] = $cid;
        $channel['pid'][$results['pid']][] = $cid;
    }

    foreach ($channel['pid'] as $pid => $pid_value) {
        $channel_order = 0;
        $count_pid = count($pid_value);
        for ($y = 0; $y < $count_pid; $y++) {
            foreach ($channellist as $cid => $value) {
                if (isset($channel['channel_order'][$pid][$channel_order]) && $channel['channel_order'][$pid][$channel_order] == $cid) {
                    $channel['sorted'][$pid][$cid] = $channellist[$cid];
                    $channel_order = $cid;
                }
            }
        }
    }

    function channel_list($channel, $channel_list, $pid, $sub)
    {
        if ($channel['sorted'][$pid]) {
            foreach ($channel['sorted'][$pid] as $cid => $value) {
                $channel_list[$cid] = $value;
                $channel_list[$cid]['sub_level'] = $sub;
                if (isset($channel['pid'][$cid])) {
                    $sub++;
                    $channel_list[$cid]['has_childs'] = 1;
                    $channel_list = channel_list($channel, $channel_list, $cid, $sub);
                    $sub--;
                }
            }
        }

        return $channel_list;
    }

    $sorted_channel = channel_list($channel, [], 0, 1);

    return $sorted_channel;
}

function sort_options($lang)
{
    $arr_sort_options = [
        ['option' => 'rank', 'title' => $lang['listrank'], 'icon' => 'fas fa-hashtag', 'config' => 'stats_column_rank_switch'],
        ['option' => 'name', 'title' => $lang['listnick'], 'icon' => 'fas fa-user', 'config' => 'stats_column_client_name_switch'],
        ['option' => 'uuid', 'title' => $lang['listuid'], 'icon' => 'fas fa-id-card', 'config' => 'stats_column_unique_id_switch'],
        ['option' => 'cldbid', 'title' => $lang['listcldbid'], 'icon' => 'fas fa-database', 'config' => 'stats_column_client_db_id_switch'],
        ['option' => 'lastseen', 'title' => $lang['listseen'], 'icon' => 'fas fa-user-clock', 'config' => 'stats_column_last_seen_switch'],
        ['option' => 'nation', 'title' => $lang['listnat'], 'icon' => 'fas fa-globe-europe', 'config' => 'stats_column_nation_switch'],
        ['option' => 'version', 'title' => $lang['listver'], 'icon' => 'fas fa-tag', 'config' => 'stats_column_version_switch'],
        ['option' => 'platform', 'title' => $lang['listpla'], 'icon' => 'fas fa-server', 'config' => 'stats_column_platform_switch'],
        ['option' => 'count', 'title' => $lang['listsumo'], 'icon' => 'fas fa-hourglass-start', 'config' => 'stats_column_online_time_switch'],
        ['option' => 'idle', 'title' => $lang['listsumi'], 'icon' => 'fas fa-hourglass-end', 'config' => 'stats_column_idle_time_switch'],
        ['option' => 'active', 'title' => $lang['listsuma'], 'icon' => 'fas fa-hourglass-half', 'config' => 'stats_column_active_time_switch'],
        ['option' => 'count_day', 'title' => $lang['listsumo'].' '.$lang['stix0013'], 'icon' => 'fas fa-hourglass-start', 'config' => 'stats_column_online_day_switch'],
        ['option' => 'idle_day', 'title' => $lang['listsumi'].' '.$lang['stix0013'], 'icon' => 'fas fa-hourglass-half', 'config' => 'stats_column_idle_day_switch'],
        ['option' => 'active_day', 'title' => $lang['listsuma'].' '.$lang['stix0013'], 'icon' => 'fas fa-hourglass-end', 'config' => 'stats_column_active_day_switch'],
        ['option' => 'count_week', 'title' => $lang['listsumo'].' '.$lang['stix0014'], 'icon' => 'fas fa-hourglass-start', 'config' => 'stats_column_online_week_switch'],
        ['option' => 'idle_week', 'title' => $lang['listsumi'].' '.$lang['stix0014'], 'icon' => 'fas fa-hourglass-half', 'config' => 'stats_column_idle_week_switch'],
        ['option' => 'active_week', 'title' => $lang['listsuma'].' '.$lang['stix0014'], 'icon' => 'fas fa-hourglass-end', 'config' => 'stats_column_active_week_switch'],
        ['option' => 'count_month', 'title' => $lang['listsumo'].' '.$lang['stix0015'], 'icon' => 'fas fa-hourglass-start', 'config' => 'stats_column_online_month_switch'],
        ['option' => 'idle_month', 'title' => $lang['listsumi'].' '.$lang['stix0015'], 'icon' => 'fas fa-hourglass-half', 'config' => 'stats_column_idle_month_switch'],
        ['option' => 'active_month', 'title' => $lang['listsuma'].' '.$lang['stix0015'], 'icon' => 'fas fa-hourglass-end', 'config' => 'stats_column_active_month_switch'],
        ['option' => 'grpid', 'title' => $lang['listacsg'], 'icon' => 'fas fa-clipboard-check', 'config' => 'stats_column_current_server_group_switch'],
        ['option' => 'grpidsince', 'title' => $lang['listgrps'], 'icon' => 'fas fa-history', 'config' => 'stats_column_current_group_since_switch'],
        ['option' => 'nextup', 'title' => $lang['listnxup'], 'icon' => 'fas fa-clock', 'config' => 'stats_column_next_rankup_switch'],
        ['option' => 'active', 'title' => $lang['listnxsg'], 'icon' => 'fas fa-clipboard-list', 'config' => 'stats_column_next_server_group_switch'],
    ];

    return $arr_sort_options;
}

function start_session($cfg)
{
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.sid_length', 128);
    if (isset($cfg['default_header_xss'])) {
        header('X-XSS-Protection: '.$cfg['default_header_xss']);
    } else {
        header('X-XSS-Protection: 1; mode=block');
    }
    if (! isset($cfg['default_header_contenttyp']) || $cfg['default_header_contenttyp'] == 1) {
        header('X-Content-Type-Options: nosniff');
    }
    if (isset($cfg['default_header_frame']) && $cfg['default_header_frame'] != null) {
        header('X-Frame-Options: '.$cfg['default_header_frame']);
    }

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $prot = 'https';
        ini_set('session.cookie_secure', 1);
        if (! headers_sent()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload;');
        }
    } else {
        $prot = 'http';
    }

    if (isset($cfg['default_header_origin']) && $cfg['default_header_origin'] != null && $cfg['default_header_origin'] != 'null') {
        if (strstr($cfg['default_header_origin'], ',')) {
            $origin_arr = explode(',', $cfg['default_header_origin']);
            if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $origin_arr)) {
                header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
            }
        } else {
            header('Access-Control-Allow-Origin: '.$cfg['default_header_origin']);
        }
    }

    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        if (isset($cfg['default_session_sametime'])) {
            ini_set('session.cookie_samesite', $cfg['default_session_sametime']);
        } else {
            ini_set('session.cookie_samesite', 'Strict');
        }
    }

    session_start();

    return $prot;
}
