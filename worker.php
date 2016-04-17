<?php
require_once(__DIR__.'/other/config.php');
$GLOBALS['exec'] = FALSE;
$GLOBALS['logfile'] = $logpath.'ranksystem.log';
$GLOBALS['pidfile'] = __DIR__.'/logs/pid';

function checkProcess($pid = null) {
	if (substr(php_uname(), 0, 7) == "Windows") {
		if(!empty($pid)) {
			exec("wmic process where 'Name='php.exe' and processid='".$pid."'' get processid 2>nul", $result);
			if(isset($result[1]) && is_numeric($result[1])) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			if (file_exists($GLOBALS['pidfile'])) {
				preg_match_all('!\d+!', file_get_contents($GLOBALS['pidfile']), $pid);
				exec("wmic process where 'Name='php.exe' and processid='".$pid[0][0]."'' get processid", $result);
				if(isset($result[1]) && is_numeric($result[1])) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}
	} else {
		if(!empty($pid)) {
			$check_pid = "ps ".$pid;
			$result = shell_exec($check_pid);
			if (count(preg_split("/\n/", $result)) > 2) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			if (file_exists($GLOBALS['pidfile'])) {
				$check_pid = "ps ".file_get_contents($GLOBALS['pidfile']);
				$result = shell_exec($check_pid);
				if (count(preg_split("/\n/", $result)) > 2) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}
	}
}

function start() {
	if (substr(php_uname(), 0, 7) == "Windows") {
		if (checkProcess() == FALSE) {
			echo "Starting the Ranksystem Bot.";
			$cmd = "php ".dirname(__FILE__)."\jobs\bot.php >> ".$GLOBALS['logfile'];
			pclose(popen("start /B ". $cmd, "r")); 
			exec("wmic process where 'Name='php.exe' and commandline LIKE '%jobs\\\\bot.php%'' get processid", $pid);
			if(isset($pid[1]) && is_numeric($pid[1])) {
				exec("echo ".$pid[1]." > ".$GLOBALS['pidfile']);
				echo " [OK]\n";
			} else {
				echo " [Failed]\n";
			}
		} else {
			echo "The Ranksystem is already running.\n";
		}
		$GLOBALS['exec'] = TRUE;
	} else {
		if (checkProcess() == FALSE) {
			echo "Starting the Ranksystem Bot.";
			exec("php ".dirname(__FILE__)."/jobs/bot.php >> ".$GLOBALS['logfile']." 2>&1 & echo $! > ".$GLOBALS['pidfile']);
			if (checkProcess() == FALSE) {
				echo " [Failed]\n";
			} else {
				echo " [OK]\n";
			}
		} else {
			echo "The Ranksystem is already running.\n";
		}
		$GLOBALS['exec'] = TRUE;
	}
}

function stop() {
	if (substr(php_uname(), 0, 7) == "Windows") {
		if (checkProcess() == TRUE) {
			echo "Stopping the Ranksystem Bot.\n";
			preg_match_all('!\d+!', file_get_contents($GLOBALS['pidfile']), $pid);
			exec("del /F ".$GLOBALS['pidfile']);
			echo "Wait until Bot is down";
			$count_check=0;
			while (checkProcess($pid[0][0]) == TRUE) {
				sleep(1);
				echo ".";
				$count_check ++;
				if($count_check > 5) {
					break;
				}
			}
			if (checkProcess($pid[0][0]) == TRUE) {
				echo " [Failed]\n";
			} else {
				echo " [OK]\n";
			}
		} else {
			echo "The Ranksystem seems not running.\n";
		}
		$GLOBALS['exec'] = TRUE;
	} else {
				if (checkProcess() == TRUE) {
			echo "Stopping the Ranksystem Bot.\n";
			$pid = file_get_contents($GLOBALS['pidfile']);
			exec("rm -f ".$GLOBALS['pidfile']);
			echo "Wait until Bot is down";
			$count_check=0;
			while (checkProcess($pid) == TRUE) {
				sleep(1);
				echo ".";
				$count_check ++;
				if($count_check > 5) {
					break;
				}
			}
			if (checkProcess($pid) == TRUE) {
				echo " [Failed]\n";
			} else {
				echo " [OK]\n";
			}
		} else {
			echo "The Ranksystem seems not running.\n";
		}
		$GLOBALS['exec'] = TRUE;
	}
}
	
function check() {
	if (substr(php_uname(), 0, 7) == "Windows") {
		if (checkProcess() == FALSE) {
			if (file_exists($GLOBALS['pidfile'])) {
				exec("del /F ".$GLOBALS['pidfile']);
			}
			start();
		}
		$GLOBALS['exec'] = TRUE;
	} else {
				if (checkProcess() == FALSE) {
			if (file_exists($GLOBALS['pidfile'])) {
				exec("rm -f ".$GLOBALS['pidfile']);
			}
			start();
		}
		$GLOBALS['exec'] = TRUE;
	}
}

function restart() {
	stop();
	start();
	$GLOBALS['exec'] = TRUE;
}

function status() {
	if (checkProcess() == FALSE) {
		echo "The Ranksystem does not seem to run.\n";
	} else {
		echo "The Ranksystem seems to be running.\n";
	}
	$GLOBALS['exec'] = TRUE;
}

function help() {
	echo " Usage: php worker.php {start|stop|restart|check|status}\n\n",
		  "\t* start   \t\t [start Ranksystem Bot]\n",
		  "\t* stop    \t\t [stop Ranksystem Bot]\n",
		  "\t* restart \t\t [restart Ranksystem Bot]\n",
		  "\t* check   \t\t [check Ranksystem Bot is running; if not, start it; no output if all is ok]\n",
		  "\t* status  \t\t [output status Ranksystem Bot]\n";
	$GLOBALS['exec'] = TRUE;
}

if (isset($_SERVER['argv'][1]) == 0) {
	help();
} else {
	$cmd = $_SERVER['argv'][1];
	if ($cmd == 'start')	start();
	if ($cmd == 'stop')		stop();
	if ($cmd ==	'restart')	restart();
	if ($cmd ==	'check')	check();
	if ($cmd ==	'status')	status();
	if ($cmd == 'help')		help();

	if ($GLOBALS['exec'] == FALSE) echo " Error parameter '$cmd' not valid. Type \"php worker.php help\" to get a list of valid parameter.\n";
}
?>