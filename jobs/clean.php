<?PHP
function clean($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid,$timezone,$cleanclients,$cleanperiod,$logpath) {
	$starttime = microtime(true);
	$sqlmsg = '';
	$sqlerr = 0;
	$count_tsuser['count'] = 0;
	$nowtime = time();

	// clean old logs
	if($mysqlcon->query("DELETE a FROM $dbname.job_log AS a CROSS JOIN(SELECT id FROM $dbname.job_log ORDER BY id DESC LIMIT 1 OFFSET 1000) AS b WHERE b.id>a.id") === false) {
		enter_logfile($logpath,$timezone,2,"clean 1:".print_r($mysqlcon->errorInfo()));
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
	
	// clean usersnaps older then 1 month
	if($mysqlcon->query("DELETE a FROM $dbname.user_snapshot AS a CROSS JOIN(SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 1000 OFFSET 121) AS b WHERE a.timestamp=b.timestamp") === false) {
		enter_logfile($logpath,$timezone,2,"clean 2:".print_r($mysqlcon->errorInfo()));
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
	
	// clean old clients out of the database
	if ($cleanclients == 1) {
		$cleantime = $nowtime - $cleanperiod;
		if(($lastclean = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='check_clean'")) === false) {
			enter_logfile($logpath,$timezone,2,"clean 3:".print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$lastclean = $lastclean->fetchAll();
		if(($dbuserdata = $mysqlcon->query("SELECT uuid FROM $dbname.user")) === false) {
			enter_logfile($logpath,$timezone,2,"clean 4:".print_r($mysqlcon->errorInfo()));
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$countrs = $dbuserdata->rowCount();
		$uuids = $dbuserdata->fetchAll();
		if ($lastclean[0]['timestamp'] < $cleantime) {
			enter_logfile($logpath,$timezone,4,$lang['clean']);
			$start=0;
			$break=200;
			$clientdblist=array();
			$countdel=0;
			$countts=0;
			while($getclientdblist=$ts3->clientListDb($start, $break)) {
				$clientdblist=array_merge($clientdblist, $getclientdblist);
				$start=$start+$break;
				$count_tsuser=array_shift($getclientdblist);
				if ($start == 100000 || $count_tsuser['count'] <= $start) {
					break;
				}
				check_shutdown($timezone,$logpath); usleep($slowmode);
			}
			foreach($clientdblist as $uuidts) {
				$single_uuid = $uuidts['client_unique_identifier']->toString();
				$uidarrts[$single_uuid]= 1;
			}
			unset($clientdblist);
			
			foreach($uuids as $uuid) {
				if(isset($uidarrts[$uuid[0]])) {
					$countts++;
				} else {
					$deleteuuids[] = $uuid[0];
					$countdel++;
				}
			}

			unset($uidarrts);
			enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['cleants'], $countts, $count_tsuser['count']));
			enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['cleanrs'], $countrs));

			if(isset($deleteuuids)) {
				$alldeldata = '';
				foreach ($deleteuuids as $dellarr) {
					$alldeldata = $alldeldata . "'" . $dellarr . "',";
				}
				$alldeldata = substr($alldeldata, 0, -1);
				$alldeldata = "(".$alldeldata.")";
				if ($alldeldata != '') {
					if($mysqlcon->exec("DELETE FROM $dbname.user WHERE uuid IN $alldeldata") === false) {
						enter_logfile($logpath,$timezone,2,"clean 5:".print_r($mysqlcon->errorInfo()));
						$sqlmsg .= print_r($mysqlcon->errorInfo());
						$sqlerr++;
					} else {
						enter_logfile($logpath,$timezone,4,"  ".sprintf($lang['cleandel'], $countdel));
						if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='check_clean'") === false) {
							enter_logfile($logpath,$timezone,2,"clean 6:".print_r($mysqlcon->errorInfo()));
							$sqlmsg .= print_r($mysqlcon->errorInfo());
							$sqlerr++;
						}
					}
				}
			} else {
				enter_logfile($logpath,$timezone,4,"  ".$lang['cleanno']);
				if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='check_clean'") === false) {
					enter_logfile($logpath,$timezone,2,"clean 7:".print_r($mysqlcon->errorInfo()));
					$sqlmsg .= print_r($mysqlcon->errorInfo());
					$sqlerr++;
				}
			}
		}
	}
	
	$buildtime = microtime(true) - $starttime;
	if ($buildtime < 0) { $buildtime = 0; }

	if ($sqlerr == 0) {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
			enter_logfile($logpath,$timezone,2,"clean 8:".print_r($mysqlcon->errorInfo()));
		}
	} else {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
			enter_logfile($logpath,$timezone,2,"clean 9:".print_r($mysqlcon->errorInfo()));
		}
	}
}
?>