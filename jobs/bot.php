#!/usr/bin/php
<?PHP
set_time_limit(0);
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');

error_reporting(0);

function enter_logfile($logpath,$timezone,$loglevel,$logtext) {
	$file = $logpath.'ranksystem.log';
	if ($loglevel == 1) {
		$loglevel = "  CRITICAL  ";
	} elseif ($loglevel == 2) {
		$loglevel = "  ERROR     ";
	} elseif ($loglevel == 3) {
		$loglevel = "  WARNING   ";
	} elseif ($loglevel == 4) {
		$loglevel = "  NOTICE    ";
	} elseif ($loglevel == 5) {
		$loglevel = "  INFO      ";
	}
	$input = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ").$loglevel.$logtext."\n";
	$loghandle = fopen($file, 'a');
	fwrite($loghandle, $input);
	if (filesize($file) > 5242880) {
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Restart Bot to continue with new log file...\n");
		fclose($loghandle);
		$file2 = "$file.old";
		if (file_exists($file2)) unlink($file2);
		rename($file, $file2);
		if (substr(php_uname(), 0, 7) == "Windows") {
			exec("del /F ".substr(__DIR__,0,-4).'logs/pid');
			$WshShell = new COM("WScript.Shell");
			$oExec = $WshShell->Run("cmd /C php ".substr(__DIR__,0,-4)."worker.php start", 0, false);
			exit;
		} else {
			exec("rm -f ".substr(__DIR__,0,-4).'logs/pid');
			exec("php ".substr(__DIR__,0,-4)."worker.php start");
			exit;
		}
	}
}

require_once(substr(__DIR__,0,-4).'other/config.php');

if(version_compare(phpversion(), '5.5.0', '<')) {
	enter_logfile($logpath,$timezone,1,"Your PHP version (".phpversion().") is below 5.5.0. Update of PHP needed! Shuttin down!\n\n");
	exit;
}

enter_logfile($logpath,$timezone,5,"Initialize Bot...");
require_once(substr(__DIR__,0,-4).'ts3_lib/TeamSpeak3.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_user.php');
require_once(substr(__DIR__,0,-4).'jobs/get_avatars.php');
require_once(substr(__DIR__,0,-4).'jobs/update_groups.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_serverstats.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_userstats.php');
require_once(substr(__DIR__,0,-4).'jobs/clean.php');
require_once(substr(__DIR__,0,-4).'jobs/check_db.php');

function log_mysql($jobname,$mysqlcon,$timezone,$dbname) {
	$timestamp = time();
	if($mysqlcon->exec("INSERT INTO $dbname.job_log (timestamp,job_name,status) VALUES ('$timestamp','$jobname','9')") === false) {
		enter_logfile($logpath,$timezone,2,print_r($mysqlcon->errorInfo()));
	} else {
		return $jobid = $mysqlcon->lastInsertId();
	}
}

function check_shutdown($timezone,$logpath) {
	if(!is_file(substr(__DIR__,0,-4).'logs/pid')) {
		enter_logfile($logpath,$timezone,5,"Received signal to stop. Shutting down!\n\n");
		exit;
	}
}

$currvers = check_db($mysqlcon,$lang,$dbname,$timezone,$currvers,$logpath);
enter_logfile($logpath,$timezone,5,"  Ranksystem Version: ".$currvers);

enter_logfile($logpath,$timezone,5,"Connect to TS3 Server (Address: \"".$ts['host']."\" Voice-Port: \"".$ts['voice']."\" Query-Port: \"".$ts['query']."\").");
try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice'] . "&blocking=0");
	enter_logfile($logpath,$timezone,5,"  Conenction to TS3 Server established.");
	
    try {
		usleep($slowmode);
        $ts3->selfUpdate(array('client_nickname' => $queryname));
    }
    catch (Exception $e) {
        try {
			usleep($slowmode);
            $ts3->selfUpdate(array('client_nickname' => $queryname2));
        }
        catch (Exception $e) {
            enter_logfile($logpath,$timezone,2,$lang['error'].$e->getCode().': '.$e->getMessage());
        }
    }
	
	usleep($slowmode);
	$whoami = $ts3->whoami();
	if($defchid != 0) {
		try {
			usleep($slowmode);
			$ts3->clientMove($whoami['client_id'],$defchid);
			enter_logfile($logpath,$timezone,5,"  Joined to specified Channel.");
		} catch (Exception $e) {
			if($e->getCode() != 770) {
				echo "\n",DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),$lang['error'], $e->getCode(), ': ', $e->getMessage(),"\n";
			} else {
				enter_logfile($logpath,$timezone,5,"  Joined to specified channel.");
			}
		}
	} else {
		enter_logfile($logpath,$timezone,4,"  No channel defined where the Ranksystem should be entered.");
	}

	enter_logfile($logpath,$timezone,5,"Bot starts now his work!");
	$looptime = 1;
	while(1) {
		if($looptime<1) {
			$loopsleep = 1 - $looptime;
			check_shutdown($timezone,$logpath); usleep($loopsleep);
		}
		$starttime = microtime(true);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$ts3->clientListReset();
		$allclients = $ts3->clientList();
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$ts3->serverInfoReset();
		$serverinfo = $ts3->serverInfo();
		if($defchid != 0) {
			try { usleep($slowmode); $ts3->clientMove($whoami['client_id'],$defchid); } catch (Exception $e) {}
		}
		$jobid = log_mysql('calc_user',$mysqlcon,$timezone,$dbname);
		calc_user($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$update,$grouptime,$boostarr,$resetbydbchange,$msgtouser,$uniqueid,$updateinfotime,$currvers,$substridle,$exceptuuid,$exceptgroup,$allclients,$logpath,$rankupmsg,$ignoreidle,$exceptcid);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$jobid = log_mysql('get_avatars',$mysqlcon,$timezone,$dbname);
		get_avatars($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$jobid = log_mysql('update_groups',$mysqlcon,$timezone,$dbname);
		update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$serverinfo,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$jobid = log_mysql('calc_serverstats',$mysqlcon,$timezone,$dbname);
		calc_serverstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$serverinfo,$substridle,$grouptime,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$jobid = log_mysql('calc_userstats',$mysqlcon,$timezone,$dbname);
		calc_userstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$jobid = log_mysql('clean',$mysqlcon,$timezone,$dbname);
		clean($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$cleanclients,$cleanperiod,$logpath);
		$looptime = microtime(true) - $starttime;
	}
}
catch (Exception $e) {
    enter_logfile($logpath,$timezone,2,$lang['error'].$e->getCode().': '.$e->getMessage());
	$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
	if(in_array($e->getCode(), $offline_status)) {
		if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
			enter_logfile($logpath,$timezone,2,$lang['error'].print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
	}
	$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
	$sqlerr++;
}
?>