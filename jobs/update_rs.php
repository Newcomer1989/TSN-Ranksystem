<?PHP
function update_rs($mysqlcon,$lang,$cfg,$dbname,$norotate=NULL) {
	$nowtime = time();
	$sqlexec = '';
	$norotate = true;
	enter_logfile(4,"  Start updating the Ranksystem...",$norotate);
	enter_logfile(4,"    Backup the database due cloning tables...",$norotate);
	$countbackuperr = 0;
	
	$tables = array('addon_assign_groups','addons_config','admin_addtime','cfg_params','channel','csrf_token','groups','job_check','server_usage','stats_nations','stats_platforms','stats_server','stats_user','stats_versions','user','user_iphash','user_snapshot');
	
	foreach ($tables as $table) {
		try {
			if($mysqlcon->query("SELECT 1 FROM `$dbname`.`bak_$table` LIMIT 1") !== false) {
				if($mysqlcon->exec("DROP TABLE `$dbname`.`bak_$table`") === false) {
					enter_logfile(1,"      Error due deleting old backup table bak_".$table.".",$norotate);
					$countbackuperr++;
				} else {
					enter_logfile(4,"      Old backup table bak_".$table." successfully removed.",$norotate);
				}
			}
		} catch (Exception $e) { }
	}
	
	foreach ($tables as $table) {
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`bak_$table` LIKE `$dbname`.`$table`") === false) {
			enter_logfile(1,"      Error due creating table bak_".$table.".",$norotate);
			$countbackuperr++;
		} else {
			if($mysqlcon->exec("INSERT `$dbname`.`bak_$table` SELECT * FROM `$dbname`.`$table`") === false) { 
				enter_logfile(1,"      Error due inserting data from table ".$table.".",$norotate);
				$countbackuperr++;
			} else {
				enter_logfile(4,"      Table ".$table." successfully cloned.",$norotate);
			}
		}
	}

	if($countbackuperr != 0) {
		enter_logfile(4,"    Backup failed. Please check your database permissions.",$norotate);
		enter_logfile(4,"  Update failed. Go on with normal work on old version.",$norotate);
		return;
	} else {
		enter_logfile(4,"    Database-tables successfully backuped.",$norotate);
	}
	
	if(!is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'update/ranksystem_'.$cfg['version_latest_available'].'.zip')) {
		enter_logfile(4,"    Downloading new update...",$norotate);
		$newUpdate = file_get_contents('https://ts-n.net/downloads/ranksystem_'.$cfg['version_latest_available'].'.zip');
		if(!is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.'update/')) {
			mkdir (dirname(__DIR__).DIRECTORY_SEPARATOR.'update/');
		}
		$dlHandler = fopen(dirname(__DIR__).DIRECTORY_SEPARATOR.'update/ranksystem_'.$cfg['version_latest_available'].'.zip', 'w');
		if(!fwrite($dlHandler,$newUpdate)) {
			enter_logfile(1,"    Could not save new update. Please check the permissions for folder 'update'.",$norotate);
			enter_logfile(4,"  Update failed. Go on with normal work on old version.",$norotate);
			return;
		}
		if(!is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'update/ranksystem_'.$cfg['version_latest_available'].'.zip')) {
			enter_logfile(4,"    Something gone wrong with downloading/saving the new update file.",$norotate);
			enter_logfile(4,"  Update failed. Go on with normal work on old version.",$norotate);
			return;
		}
		fclose($dlHandler);
		enter_logfile(4,"    New update successfully saved.",$norotate);
	} else {
		enter_logfile(5,"    New update file (update/ranksystem_".$cfg['version_latest_available'].".zip) already here...",$norotate);
	}

	$countwrongfiles = 0;
	$countchangedfiles = 0;
	
	$zip = new ZipArchive;

	if($zip->open(dirname(__DIR__).DIRECTORY_SEPARATOR.'update/ranksystem_'.$cfg['version_latest_available'].'.zip')) {
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$thisFileName = $zip->getNameIndex($i);
			$thisFileDir = dirname($thisFileName);
			enter_logfile(6,"      Parent directory: ".$thisFileDir,$norotate);
			enter_logfile(6,"      File/Dir: ".$thisFileName,$norotate);
			
			if(substr($thisFileName,-1,1) == '/' || substr($thisFileName,-1,1) == '\\') {
				enter_logfile(6,"      Check folder is existing: ".$thisFileName,$norotate);
				if(!is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.substr($thisFileName,0,-1))) {
					enter_logfile(5,"        Create folder: ".dirname(__DIR__).DIRECTORY_SEPARATOR.substr($thisFileName,0,-1),$norotate);
					if(mkdir((dirname(__DIR__).DIRECTORY_SEPARATOR.substr($thisFileName,0,-1)), 0750, true)) {
						enter_logfile(4,"      Created new folder ".dirname(__DIR__).DIRECTORY_SEPARATOR.substr($thisFileName,0,-1),$norotate);
					} else {
						enter_logfile(2,"      Error by creating folder ".dirname(__DIR__).DIRECTORY_SEPARATOR.substr($thisFileName,0,-1).". Please check the permissions on the folder one level above.",$norotate);
						$countwrongfiles++;
					}				
				} else {
					enter_logfile(6,"        Folder still existing.",$norotate);
				}
				continue;
			}

			if(!is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.$thisFileDir)) {
				enter_logfile(6,"      Check parent folder is existing: ".$thisFileDir,$norotate);
				if(mkdir(dirname(__DIR__).DIRECTORY_SEPARATOR.$thisFileDir, 0750, true)) {
					enter_logfile(4,"      Created new folder ".$thisFileDir,$norotate);
				} else {
					enter_logfile(2,"      Error by creating folder ".$thisFileDir.". Please check the permissions on your folder ".dirname(__DIR__),$norotate);
					$countwrongfiles++;
				}
			} else {
				enter_logfile(6,"        Parent folder still existing.",$norotate);
			}

			enter_logfile(6,"      Check file: ".dirname(__DIR__).DIRECTORY_SEPARATOR.$thisFileName,$norotate);
			if(!is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.$thisFileName)) {
				$contents = $zip->getFromName($thisFileName);
				$updateThis = '';
				if($thisFileName == 'other/dbconfig.php' || $thisFileName == 'install.php' || $thisFileName == 'other/phpcommand.php' || $thisFileName == 'logs/autostart_deactivated') {
					enter_logfile(5,"      Did not touch ".$thisFileName,$norotate);
				} else {
					if(($updateThis = fopen(dirname(__DIR__).DIRECTORY_SEPARATOR.$thisFileName, 'w')) === false) {
						enter_logfile(2,"      Failed to open file ".$thisFileName,$norotate);
						$countwrongfiles++;
					} elseif(!fwrite($updateThis, $contents)) {
						enter_logfile(2,"      Failed to write file ".$thisFileName,$norotate);
						$countwrongfiles++;
					} else {
						enter_logfile(4,"      Replaced file ".$thisFileName,$norotate);
						$countchangedfiles++;
					}
					fclose($updateThis);
					unset($contents);
				}
			} else {
				enter_logfile(2,"      Unknown thing happened.. Is the parent directory existing? ".$thisFileDir." # ".$thisFileName,$norotate);
				$countwrongfiles++;
			}
		}

		$zip->close();
		unset($zip);
		sleep(1);
	} else {
		enter_logfile(2,"      Error with downloaded Zip file happened. Is the file inside the folder 'update' valid and readable?",$norotate);
		$countwrongfiles++;
	}

	if(!unlink(dirname(__DIR__).DIRECTORY_SEPARATOR.'update'.DIRECTORY_SEPARATOR.'ranksystem_'.$cfg['version_latest_available'].'.zip')) {
		enter_logfile(3,"    Could not clean update folder. Please remove the unneeded file ".dirname(__DIR__).DIRECTORY_SEPARATOR."update".DIRECTORY_SEPARATOR."ranksystem_".$cfg['version_latest_available'].".zip",$norotate);
	} else {
		enter_logfile(5,"    Cleaned update folder.",$norotate);
	}

	if($countwrongfiles == 0 && $countchangedfiles != 0) {
		$sqlexec .= "UPDATE `$dbname`.`cfg_params` SET `value`='{$cfg['version_latest_available']}' WHERE `param`='version_latest_available';\n";

		if (file_exists($GLOBALS['pidfile'])) {
			unlink($GLOBALS['pidfile']);
		}

		enter_logfile(4,"  Files updated successfully.",$norotate);

		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B cmd /C ".$GLOBALS['phpcommand']." ".dirname(__DIR__).DIRECTORY_SEPARATOR."worker.php start 1500000 >NUL 2>NUL", "r"));
		} else {
			exec($GLOBALS['phpcommand']." ".dirname(__DIR__).DIRECTORY_SEPARATOR."worker.php start 2500000 > /dev/null 2>&1 &");
		}

		shutdown($mysqlcon,4,"Update done. Wait for restart via cron/task.",FALSE);

	} else {
		enter_logfile(1,"  Files updated with at least one error. Please check the log!",$norotate);
		enter_logfile(2,"Update of the Ranksystem failed!",$norotate);
		enter_logfile(4,"Continue with normal work on old version.",$norotate);
	}

	$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='get_version';\n";
	return($sqlexec);
}