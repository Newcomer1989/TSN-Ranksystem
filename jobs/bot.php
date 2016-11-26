#!/usr/bin/php
<?PHP
set_time_limit(0);
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');
error_reporting(0);

function enter_logfile($logpath,$timezone,$loglevel,$logtext,$norotate = false) {
	global $phpcommand;
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
	fclose($loghandle);
	if($norotate == false && filesize($file) > 5242880) {
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u ")."  NOTICE    Restart Bot to continue with new log file...\n");
		fclose($loghandle);
		$file2 = "$file.old";
		if (file_exists($file2)) unlink($file2);
		rename($file, $file2);
		if (substr(php_uname(), 0, 7) == "Windows") {
			exec("del /F ".substr(__DIR__,0,-4).'logs/pid');
			$WshShell = new COM("WScript.Shell");
			$oExec = $WshShell->Run("cmd /C ".$phpcommand." ".substr(__DIR__,0,-4)."worker.php start", 0, false);
			exit;
		} else {
			exec("rm -f ".substr(__DIR__,0,-4).'logs/pid');
			exec($phpcommand." ".substr(__DIR__,0,-4)."worker.php start");
			exit;
		}
	}
}

require_once(substr(__DIR__,0,-4).'other/config.php');
require_once(substr(__DIR__,0,-4).'other/phpcommand.php');

if(isset($_SERVER['HTTP_HOST']) || isset($_SERVER['REMOTE_ADDR'])) {
	enter_logfile($logpath,$timezone,1,"Request to start the Ranksystem from ".$_SERVER['REMOTE_ADDR'].". It seems the request came not from the command line! Shuttin down!\n\n");
	exit;
}
if(version_compare(phpversion(), '5.5.0', '<')) {
	enter_logfile($logpath,$timezone,1,"Your PHP version (".phpversion().") is below 5.5.0. Update of PHP needed! Shuttin down!\n\n");
	exit;
}
if(!function_exists('simplexml_load_file')) {
	enter_logfile($logpath,$timezone,1,"SimpleXML is missed. Installation of SimpleXML is needed! Shuttin down!\n\n");
	exit;
}
if(!in_array('curl', get_loaded_extensions())) {
	enter_logfile($logpath,$timezone,1,"PHP cURL is missed. Installation of PHP cURL is needed! Shuttin down!\n\n");
	exit;
}
if(!in_array('zip', get_loaded_extensions())) {
	enter_logfile($logpath,$timezone,1,"PHP Zip is missed. Installation of PHP Zip is needed! Shuttin down!\n\n");
	exit;
}

enter_logfile($logpath,$timezone,5,"Initialize Bot...");
require_once(substr(__DIR__,0,-4).'libs/ts3_lib/TeamSpeak3.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_user.php');
require_once(substr(__DIR__,0,-4).'jobs/get_avatars.php');
require_once(substr(__DIR__,0,-4).'jobs/update_groups.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_serverstats.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_userstats.php');
require_once(substr(__DIR__,0,-4).'jobs/clean.php');
require_once(substr(__DIR__,0,-4).'jobs/check_db.php');
require_once(substr(__DIR__,0,-4).'jobs/handle_messages.php');
require_once(substr(__DIR__,0,-4).'jobs/update_rs.php');

function check_shutdown($timezone,$logpath) {
	if(!is_file(substr(__DIR__,0,-4).'logs/pid')) {
		enter_logfile($logpath,$timezone,5,"Received signal to stop. Shutting down!\n\n");
		exit;
	}
}

function get_data($url,$currvers,$ts) {
	$ch = curl_init();curl_setopt($ch, CURLOPT_URL, $url);curl_setopt($ch, CURLOPT_REFERER, php_uname("s")." ".$ts['host'].":".$ts['voice']);curl_setopt($ch, CURLOPT_USERAGENT, "TSN Ranksystem ".$currvers);curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);curl_setopt($ch, CURLOPT_MAXREDIRS, 10);curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);$data = curl_exec($ch);curl_close($ch);return $data;
}

$currvers = check_db($mysqlcon,$lang,$dbname,$timezone,$currvers,$logpath);
enter_logfile($logpath,$timezone,5,"  Ranksystem Version: ".$currvers);

enter_logfile($logpath,$timezone,5,"Connect to TS3 Server (Address: \"".$ts['host']."\" Voice-Port: \"".$ts['voice']."\" Query-Port: \"".$ts['query']."\").");
try {
    $ts3 = TeamSpeak3::factory("serverquery://".$ts['user'].":".$ts['pass']."@".$ts['host'].":".$ts['query']."/?server_port=".$ts['voice']."&blocking=0");
	enter_logfile($logpath,$timezone,5,"  Connection to TS3 Server established.");
	try {
		usleep($slowmode);
		$ts3->notifyRegister("textprivate");
		$ts3->notifyRegister("textchannel");
		$ts3->notifyRegister("textserver");
		TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "handle_messages");
	} catch (Exception $e) {
		enter_logfile($logpath,$timezone,2,"  Error due register notifyTextmessage ".$e->getCode().': '.$e->getMessage());
	}

	
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
				enter_logfile($logpath,$timezone,5,"  Could not join specified channel.");
			} else {
				enter_logfile($logpath,$timezone,5,"  Joined to specified channel.");
			}
		}
	} else {
		enter_logfile($logpath,$timezone,4,"  No channel defined where the Ranksystem Bot should be entered.");
	}

	enter_logfile($logpath,$timezone,5,"Bot starts now his work!");
	$looptime = 1;
	usleep(5000000);
	while(1) {
		if($looptime<1) {
			$loopsleep = (1 - $looptime) * 1000000;
			//enter_logfile($logpath,$timezone,6,"  Sleep for ".(1 - $looptime)." seconds till next loop starts.");
			check_shutdown($timezone,$logpath); usleep($loopsleep);
		}
		$starttime = microtime(true);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$ts3->clientListReset();
		$allclients = $ts3->clientList();
		check_shutdown($timezone,$logpath); usleep($slowmode);
		$ts3->serverInfoReset();
		$serverinfo = $ts3->serverInfo();
		calc_user($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$update,$grouptime,$boostarr,$resetbydbchange,$msgtouser,$uniqueid,$updateinfotime,$currvers,$substridle,$exceptuuid,$exceptgroup,$allclients,$logpath,$rankupmsg,$ignoreidle,$exceptcid,$ts,$resetexcept,$upchannel);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		get_avatars($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$serverinfo,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		calc_serverstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$serverinfo,$substridle,$grouptime,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		calc_userstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$logpath);
		check_shutdown($timezone,$logpath); usleep($slowmode);
		clean($ts3,$mysqlcon,$lang,$dbname,$slowmode,$timezone,$cleanclients,$cleanperiod,$logpath);
		$looptime = microtime(true) - $starttime;
		try { $ts3->getAdapter(); } catch (Exception $e) {}
	}
}
catch (Exception $e) {
    enter_logfile($logpath,$timezone,2,$lang['error'].$e->getCode().': '.$e->getMessage());
	$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
	if(in_array($e->getCode(), $offline_status)) {
		if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
			enter_logfile($logpath,$timezone,2,$lang['error'].print_r($mysqlcon->errorInfo(), true));
		}
	}
}
?>
