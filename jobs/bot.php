#!/usr/bin/php
<?PHP
set_time_limit(0);
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');

error_reporting(0);

echo "Initialize Bot...";
require_once(substr(__DIR__,0,-4).'other/config.php');
require_once(substr(__DIR__,0,-4).'ts3_lib/TeamSpeak3.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_user.php');
require_once(substr(__DIR__,0,-4).'jobs/get_avatars.php');
require_once(substr(__DIR__,0,-4).'jobs/update_groups.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_serverstats.php');
require_once(substr(__DIR__,0,-4).'jobs/calc_userstats.php');
require_once(substr(__DIR__,0,-4).'jobs/clean.php');
echo " finished\n";

function log_mysql($jobname, $mysqlcon) {
	$timestamp = time();
	if($mysqlcon->exec("INSERT INTO $dbname.job_log (timestamp,job_name,status) VALUES ('$timestamp','$jobname','9')") === false) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),print_r($mysqlcon->errorInfo()),"\n";
	} else {
		return $jobid = $mysqlcon->lastInsertId();
	}
}

echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"Connect to TS3 Server (Address: \"",$ts['host'],"\" Voice-Port: \"",$ts['voice'],"\" Query-Port: \"",$ts['query'],"\") ...";
try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice'] . "&blocking=0");
	echo " finished\n";
	
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
            echo "\n",DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),$lang['error'], $e->getCode(), ': ', $e->getMessage(),"\n";
        }
    }
	echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"Join to specified Channel...";
	usleep($slowmode);
	$whoami = $ts3->whoami();
	if($defchid != 0) {
		try {
			usleep($slowmode);
			$ts3->clientMove($whoami['client_id'],$defchid);
			echo " finished\n";
		} catch (Exception $e) {
			if($e->getCode() != 770) {
				echo "\n",DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),$lang['error'], $e->getCode(), ': ', $e->getMessage(),"\n";
			} else {
				echo " finished\n";
			}
		}
	} else {
		echo " no Channel defined\n";
	}

	echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"Bot starts now his work!\n";
	while(1) {
		usleep($slowmode);
		$ts3->clientListReset();
		$allclients = $ts3->clientList();
		usleep($slowmode);
		$ts3->serverInfoReset();
		$serverinfo = $ts3->serverInfo();
		if($defchid != 0) {
			try { usleep($slowmode); $ts3->clientMove($whoami['client_id'],$defchid); } catch (Exception $e) {}
		}
		$jobid = log_mysql('calc_user',$mysqlcon);
		calc_user($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$showgen,$update,$grouptime,$boostarr,$resetbydbchange,$msgtouser,$uniqueid,$updateinfotime,$currvers,$substridle,$exceptuuid,$exceptgroup,$allclients);
		usleep($slowmode);
		$jobid = log_mysql('get_avatars',$mysqlcon);
		get_avatars($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone);
		usleep($slowmode);
		$jobid = log_mysql('update_groups',$mysqlcon);
		update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$serverinfo);
		usleep($slowmode);
		$jobid = log_mysql('calc_serverstats',$mysqlcon);
		calc_serverstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$serverinfo,$substridle,$grouptime);
		usleep($slowmode);
		$jobid = log_mysql('calc_userstats',$mysqlcon);
		calc_userstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone);
		usleep($slowmode);
		$jobid = log_mysql('clean',$mysqlcon);
		clean($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$cleanclients,$cleanperiod);
		usleep($slowmode);
		//check auf fehler in job_log
		if(!is_file(substr(__DIR__,0,-4).'logs/pid')) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),"Received signal to stop. Shutting down...\n";
			exit;
		}
	}
}
catch (Exception $e) {
    echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),$lang['error'] . $e->getCode() . ': ' . $e->getMessage(),"\n";
	$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
	if(in_array($e->getCode(), $offline_status)) {
		if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($timezone))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
	}
	$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
	$sqlerr++;
}
?>