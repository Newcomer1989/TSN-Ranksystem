<?PHP
function update_rs($mysqlcon,$lang,$dbname,$logpath,$timezone,$newversion,$phpcommand,$norotate=NULL) {
	$norotate = true;
	enter_logfile($logpath,$timezone,4,"  Start updating the Ranksystem...\n",$norotate);
	enter_logfile($logpath,$timezone,4,"    Backup the database due cloning tables...\n",$norotate);
	$countbackuperr = 0;
	
	$tables = array('addons_config','addon_assign_groups','config','groups','job_check','server_usage','stats_nations','stats_platforms','stats_server','stats_user','stats_versions','user','user_snapshot');
	
	foreach ($tables as $table) {
		if($mysqlcon->query("SELECT 1 FROM bak_$table LIMIT 1") !== false) {
			if($mysqlcon->exec("DROP TABLE $dbname.bak_$table") === false) {
				enter_logfile($logpath,$timezone,1,"      Error due deleting old backup table ".$table.".",$norotate);
				$countbackuperr++;
			} else {
				enter_logfile($logpath,$timezone,4,"      Old backup table ".$table." successfully removed.",$norotate);
			}
		}
	}
	
	foreach ($tables as $table) {
		if($mysqlcon->exec("CREATE TABLE $dbname.bak_$table LIKE $dbname.$table") === false) {
			enter_logfile($logpath,$timezone,1,"      Error due creating table bak_".$table.".",$norotate);
			$countbackuperr++;
		} else {
			if($mysqlcon->exec("INSERT $dbname.bak_$table SELECT * FROM $dbname.$table") === false) { 
				enter_logfile($logpath,$timezone,1,"      Error due inserting data from table ".$table.".",$norotate);
				$countbackuperr++;
			} else {
				enter_logfile($logpath,$timezone,4,"      Table ".$table." successfully cloned.",$norotate);
			}
		}
	}

	if($countbackuperr != 0) {
		enter_logfile($logpath,$timezone,4,"    Backup failed. Please check your database permissions.\n",$norotate);
		enter_logfile($logpath,$timezone,4,"  Update failed. Go on with normal work on old version.\n",$norotate);
		return;
	} else {
		enter_logfile($logpath,$timezone,4,"    Database-tables successfully backuped.\n",$norotate);
	}
	
	if(!is_file(substr(__DIR__,0,-4).'update/'.$newversion.'.zip')) {
		enter_logfile($logpath,$timezone,4,"    Downloading new update...\n",$norotate);
		$newUpdate = file_get_contents('https://ts-n.net/downloads/ranksystem_'.$newversion.'.zip');
		if(!is_dir(substr(__DIR__,0,-4).'update/')) {
			mkdir (substr(__DIR__,0,-4).'update/');
		}
		$dlHandler = fopen(substr(__DIR__,0,-4).'update/ranksystem_'.$newversion.'.zip', 'w');
		if(!fwrite($dlHandler,$newUpdate)) {
			enter_logfile($logpath,$timezone,1,"    Could not save new update. Please check the permissions for folder 'update'.\n",$norotate);
			enter_logfile($logpath,$timezone,4,"  Update failed. Go on with normal work on old version.\n",$norotate);
			return;
		}
		if(!is_file(substr(__DIR__,0,-4).'update/ranksystem_'.$newversion.'.zip')) {
			enter_logfile($logpath,$timezone,4,"    Something gone wrong with downloading/saving the new update file.\n",$norotate);
			enter_logfile($logpath,$timezone,4,"  Update failed. Go on with normal work on old version.\n",$norotate);
			return;
		}
		fclose($dlHandler);
		enter_logfile($logpath,$timezone,4,"    New update successfully saved.\n",$norotate);
	} else {
		enter_logfile($logpath,$timezone,5,"    New update file already here...\n",$norotate);
	}
	
	$zipHandle = zip_open(substr(__DIR__,0,-4).'update/ranksystem_'.$newversion.'.zip');
	
	$countwrongfiles = 0;
	$countchangedfiles = 0;

	while ($aF = zip_read($zipHandle)) {
		$thisFileName = zip_entry_name($aF);
		$thisFileDir = dirname($thisFileName);

		if(substr($thisFileName,-1,1) == '/') {
			continue;
		}

		if(!is_dir(substr(__DIR__,0,-4).'/'.$thisFileDir)) {
			if(mkdir(substr(__DIR__,0,-4).$thisFileDir, 0777, true)) {
				enter_logfile($logpath,$timezone,4,"      Create new folder ".$thisFileDir."\n",$norotate);
			} else {
				enter_logfile($logpath,$timezone,1,"      Error by creating folder ".$thisFileDir.". Please check the permissions on your folder ".substr(__DIR__,0,-4).".\n",$norotate);
			}
		}

		if(!is_dir(substr(__DIR__,0,-4).'/'.$thisFileName)) {
			$contents = zip_entry_read($aF, zip_entry_filesize($aF));
			$updateThis = '';
			if($thisFileName == 'other/dbconfig.php' || $thisFileName == 'install.php' || $thisFileName == 'other/phpcommand.php') {
				enter_logfile($logpath,$timezone,5,"      Did not touch ".$thisFileName."\n",$norotate);
			} else {
				if(($updateThis = fopen(substr(__DIR__,0,-4).'/'.$thisFileName, 'w')) === false) {
					enter_logfile($logpath,$timezone,1,"      Failed to open file ".$thisFileName."\n",$norotate);
					$countwrongfiles++;
				} elseif(!fwrite($updateThis, $contents)) {
					enter_logfile($logpath,$timezone,1,"      Failed to write file ".$thisFileName."\n",$norotate);
					$countwrongfiles++;
				} else {
					enter_logfile($logpath,$timezone,4,"      Replaced file ".$thisFileName."\n",$norotate);
					$countchangedfiles++;
				}
				fclose($updateThis);
				unset($contents);
			}
		}
	}
	if($countwrongfiles == 0 && $countchangedfiles != 0) {
		if(!unlink(substr(__DIR__,0,-4).'update/ranksystem_'.$newversion.'.zip')) {
			enter_logfile($logpath,$timezone,3,"    Could not clean update folder. Please remove the unneeded file ".substr(__DIR__,0,-4)."update/ranksystem_".$newversion.".zip",$norotate);
		} else {
			enter_logfile($logpath,$timezone,5,"    Cleaned update folder.",$norotate);
		}
		enter_logfile($logpath,$timezone,4,"  Files updated successfully. Wait for restart via cron/task. Shutting down!\n\n",$norotate);
		
		$path = substr(__DIR__, 0, -4);

		if (substr(php_uname(), 0, 7) == "Windows") {
			exec("start ".$phpcommand." ".$path."worker.php restart");
			exit;
		} else {
			exec($phpcommand." ".$path."worker.php restart > /dev/null 2>/dev/null &");
			exit;
		}
	} else {
		enter_logfile($logpath,$timezone,1,"  Files updated with at least one error. Please check the log!\n",$norotate);
		// how to handle this.. Perhaps try again automatically in 30 minutes
	}
}