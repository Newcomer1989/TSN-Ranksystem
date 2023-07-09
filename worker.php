<?php

require_once __DIR__.'/other/_functions.php';
require_once __DIR__.'/other/config.php';
require_once __DIR__.'/other/phpcommand.php';

$GLOBALS['exec'] = false;
if ($cfg['logs_path'] == null) {
    $cfg['logs_path'] = '.'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR;
}
$GLOBALS['logpath'] = $cfg['logs_path'];
$GLOBALS['logfile'] = $cfg['logs_path'].'ranksystem.log';
$GLOBALS['pidfile'] = $cfg['logs_path'].'pid';
$GLOBALS['autostart'] = $cfg['logs_path'].'autostart_deactivated';

function checkProcess($pid = null)
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

function start($delay = 0)
{
    usleep($delay);
    global $phpcommand;
    if (isset($_SERVER['USER']) && $_SERVER['USER'] == 'root' || isset($_SERVER['USERNAME']) && $_SERVER['USERNAME'] == 'administrator') {
        echo "\n !!!! Do not start the Ranksystem with root privileges !!!!\n\n";
        echo " Start Ranksystem Bot in 10 seconds...\n\n";
        sleep(10);
    }

    if (! is_writable($GLOBALS['logpath'])) {
        echo "\n !!!! Logs folder is not writable !!!!\n\n";
        echo " Cancel start request...\n\n";
        exit;
    }

    if (file_exists($GLOBALS['logfile']) && ! is_writable($GLOBALS['logfile'])) {
        echo "\n !!!! Log file is not writable !!!!\n\n";
        echo " Cancel start request...\n\n";
        exit;
    }

    if (substr(php_uname(), 0, 7) == 'Windows') {
        if (checkProcess() == false) {
            echo 'Starting the Ranksystem Bot.';
            try {
                $WshShell = new COM('WScript.Shell');
            } catch (Exception $e) {
                echo "\n Error due loading the PHP COM module (wrong server configuration!): ",$e->getMessage(),"\n";
            }
            try {
                $wcmd = 'cmd /C '.$phpcommand.' '.__DIR__."\jobs\bot.php";
                $oExec = $WshShell->Run($wcmd, 0, false);
            } catch (Exception $e) {
                echo "\n Error due starting Bot (exec command enabled?): ",$e->getMessage(),"\n";
            }
            try {
                exec('wmic process where "Name LIKE "%php%" AND CommandLine LIKE "%bot.php%"" get ProcessId', $pid);
            } catch (Exception $e) {
                echo "\n Error due getting process list (wmic command enabled?): ",$e->getMessage(),"\n";
            }
            if (isset($pid[1]) && is_numeric($pid[1])) {
                exec('echo '.$pid[1].' > '.$GLOBALS['pidfile']);
                echo ' [OK]';
                if (file_exists($GLOBALS['autostart'])) {
                    unlink($GLOBALS['autostart']);
                }
            } else {
                echo " [Failed]\n";
            }
        } else {
            echo "The Ranksystem is already running.\n";
        }
        $GLOBALS['exec'] = true;
    } else {
        if (checkProcess() == false) {
            echo 'Starting the Ranksystem Bot.';
            exec($phpcommand.' '.__DIR__.'/jobs/bot.php >/dev/null 2>&1 & echo $! > '.$GLOBALS['pidfile']);
            if (checkProcess() == false) {
                echo " [Failed]\n";
            } else {
                echo " [OK]\n";
                if (file_exists($GLOBALS['autostart'])) {
                    unlink($GLOBALS['autostart']);
                }
            }
        } else {
            echo "The Ranksystem is already running.\n";
        }
        $GLOBALS['exec'] = true;
    }
}

function stop()
{
    if (checkProcess() == true) {
        echo "Stopping the Ranksystem Bot.\n";
        $pid = str_replace(["\r", "\n"], '', file_get_contents($GLOBALS['pidfile']));
        unlink($GLOBALS['pidfile']);
        echo 'Wait until Bot is down';
        $count_check = 0;
        while (checkProcess($pid) == true) {
            sleep(1);
            echo '.';
            $count_check++;
            if ($count_check > 10) {
                if (substr(php_uname(), 0, 7) == 'Windows') {
                    exec('taskkill /F /PID '.$pid);
                } else {
                    exec('kill -9 '.$pid);
                }
                echo 'Stop command received! Bot does not react, process killed!';
                break;
            }
        }
        if (checkProcess($pid) == true) {
            echo " [Failed]\n";
        } else {
            file_put_contents($GLOBALS['autostart'], '');
            echo " [OK]\n";
        }
    } else {
        if (is_file($GLOBALS['pidfile'])) {
            unlink($GLOBALS['pidfile']);
        }
        echo "The Ranksystem seems not running.\n";
    }
    $GLOBALS['exec'] = true;
}

function check()
{
    if (checkProcess() == false) {
        if (! file_exists($GLOBALS['autostart'])) {
            if (file_exists($GLOBALS['pidfile'])) {
                unlink($GLOBALS['pidfile']);
            }
            start();
        } else {
            echo "Starting the Ranksystem Bot. [Failed]\nAutostart is deactivated. Use start command instead.\n";
        }
    }
    $GLOBALS['exec'] = true;
}

function restart()
{
    stop();
    start();
    $GLOBALS['exec'] = true;
}

function status()
{
    if (checkProcess() == false) {
        echo "The Ranksystem Bot is stopped.\n";
    } else {
        echo "The Ranksystem process is running.\n";
    }
    $GLOBALS['exec'] = true;
}

function help()
{
    echo " Usage: php worker.php {start|stop|restart|check|status}\n\n",
    "\t* start   \t\t [start Ranksystem Bot]\n",
    "\t* stop    \t\t [stop Ranksystem Bot]\n",
    "\t* restart \t\t [restart Ranksystem Bot]\n",
    "\t* check   \t\t [check Ranksystem Bot is running; if not, start it; no output if all is ok]\n",
    "\t* status  \t\t [output status Ranksystem Bot]\n";
    $GLOBALS['exec'] = true;
}

if (isset($_SERVER['argv'][1]) == 0) {
    help();
} else {
    $cmd = $_SERVER['argv'][1];
    if (isset($_SERVER['argv'][2]) && is_numeric($_SERVER['argv'][2]) && $cmd == 'start') {
        start($_SERVER['argv'][2]);
    } elseif ($cmd == 'start') {
        start();
    }
    if ($cmd == 'stop') {
        stop();
    }
    if ($cmd == 'restart') {
        restart();
    }
    if ($cmd == 'check') {
        check();
    }
    if ($cmd == 'status') {
        status();
    }
    if ($cmd == 'help') {
        help();
    }

    if ($GLOBALS['exec'] == false) {
        echo " Error parameter '$cmd' not valid. Type \"php worker.php help\" to get a list of valid parameter.\n";
    }
}
