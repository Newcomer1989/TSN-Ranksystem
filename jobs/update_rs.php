<?PHP
function update_rs($mysqlcon,$lang,$cfg,$dbname,$phpcommand,$norotate=NULL) {
	$nowtime = time();
	$sqlexec = '';
	$norotate = true;
	enter_logfile($cfg,4,"  Start updating the Ranksystem...",$norotate);
	enter_logfile($cfg,4,"    Backup the database due cloning tables...",$norotate);
	$countbackuperr = 0;
	
	$tables = array('addons_config','cfg_params','groups','job_check','server_usage','stats_server','stats_user','user','user_snapshot');
	
	foreach ($tables as $table) {
		try {
			if($mysqlcon->query("SELECT 1 FROM `$dbname`.`bak_$table` LIMIT 1") !== false) {
				if($mysqlcon->exec("DROP TABLE `$dbname`.`bak_$table`") === false) {
					enter_logfile($cfg,1,"      Error due deleting old backup table bak_".$table.".",$norotate);
					$countbackuperr++;
				} else {
					enter_logfile($cfg,4,"      Old backup table bak_".$table." successfully removed.",$norotate);
				}
			}
		} catch (Exception $e) { }
	}
	
	foreach ($tables as $table) {
		if($mysqlcon->exec("CREATE TABLE `$dbname`.`bak_$table` LIKE `$dbname`.`$table`") === false) {
			enter_logfile($cfg,1,"      Error due creating table bak_".$table.".",$norotate);
			$countbackuperr++;
		} else {
			if($mysqlcon->exec("INSERT `$dbname`.`bak_$table` SELECT * FROM `$dbname`.`$table`") === false) { 
				enter_logfile($cfg,1,"      Error due inserting data from table ".$table.".",$norotate);
				$countbackuperr++;
			} else {
				enter_logfile($cfg,4,"      Table ".$table." successfully cloned.",$norotate);
			}
		}
	}

	if($countbackuperr != 0) {
		enter_logfile($cfg,4,"    Backup failed. Please check your database permissions.",$norotate);
		enter_logfile($cfg,4,"  Update failed. Go on with normal work on old version.",$norotate);
		return;
	} else {
		enter_logfile($cfg,4,"    Database-tables successfully backuped.",$norotate);
	}
	
	if(!is_file(substr(__DIR__,0,-4).'update/ranksystem_'.$cfg['version_latest_available'].'.zip')) {
		enter_logfile($cfg,4,"    Downloading new update...",$norotate);
		$newUpdate = file_get_contents('https://ts-n.net/downloads/ranksystem_'.$cfg['version_latest_available'].'.zip');
		if(!is_dir(substr(__DIR__,0,-4).'update/')) {
			mkdir (substr(__DIR__,0,-4).'update/');
		}
		$dlHandler = fopen(substr(__DIR__,0,-4).'update/ranksystem_'.$cfg['version_latest_available'].'.zip', 'w');
		if(!fwrite($dlHandler,$newUpdate)) {
			enter_logfile($cfg,1,"    Could not save new update. Please check the permissions for folder 'update'.",$norotate);
			enter_logfile($cfg,4,"  Update failed. Go on with normal work on old version.",$norotate);
			return;
		}
		if(!is_file(substr(__DIR__,0,-4).'update/ranksystem_'.$cfg['version_latest_available'].'.zip')) {
			enter_logfile($cfg,4,"    Something gone wrong with downloading/saving the new update file.",$norotate);
			enter_logfile($cfg,4,"  Update failed. Go on with normal work on old version.",$norotate);
			return;
		}
		fclose($dlHandler);
		enter_logfile($cfg,4,"    New update successfully saved.",$norotate);
	} else {
		enter_logfile($cfg,5,"    New update file (update/ranksystem_".$cfg['version_latest_available'].".zip) already here...",$norotate);
	}

	$countwrongfiles = 0;
	$countchangedfiles = 0;
	
	$zip = new ZipArchive;

	if($zip->open(substr(__DIR__,0,-4).'update/ranksystem_'.$cfg['version_latest_available'].'.zip')) {
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$thisFileName = $zip->getNameIndex($i);
			$thisFileDir = dirname($thisFileName);
			enter_logfile($cfg,6,"      Parent directory: ".$thisFileDir,$norotate);
			enter_logfile($cfg,6,"      File/Dir: ".$thisFileName,$norotate);
			
			if(substr($thisFileName,-1,1) == '/' || substr($thisFileName,-1,1) == '\\') {
				enter_logfile($cfg,6,"      Check folder is existing: ".$thisFileName,$norotate);
				if(!is_dir(substr(__DIR__,0,-4).substr($thisFileName,0,-1))) {
					enter_logfile($cfg,5,"        Create folder: ".substr(__DIR__,0,-4).substr($thisFileName,0,-1),$norotate);
					if(mkdir((substr(__DIR__,0,-4).substr($thisFileName,0,-1)), 0740, true)) {
						enter_logfile($cfg,4,"      Created new folder ".substr(__DIR__,0,-4).substr($thisFileName,0,-1),$norotate);
					} else {
						enter_logfile($cfg,2,"      Error by creating folder ".substr(__DIR__,0,-4).substr($thisFileName,0,-1).". Please check the permissions on the folder one level above.",$norotate);
						$countwrongfiles++;
					}				
				} else {
					enter_logfile($cfg,6,"        Folder still existing.",$norotate);
				}
				continue;
			}

			if(!is_dir(substr(__DIR__,0,-4).'/'.$thisFileDir)) {
				enter_logfile($cfg,6,"      Check parent folder is existing: ".$thisFileDir,$norotate);
				if(mkdir(substr(__DIR__,0,-4).$thisFileDir, 0740, true)) {
					enter_logfile($cfg,4,"      Created new folder ".$thisFileDir,$norotate);
				} else {
					enter_logfile($cfg,2,"      Error by creating folder ".$thisFileDir.". Please check the permissions on your folder ".substr(__DIR__,0,-4),$norotate);
					$countwrongfiles++;
				}
			} else {
				enter_logfile($cfg,6,"        Parent folder still existing.",$norotate);
			}

			enter_logfile($cfg,6,"      Check file: ".substr(__DIR__,0,-4).$thisFileName,$norotate);
			if(!is_dir(substr(__DIR__,0,-4).$thisFileName)) {
				$contents = $zip->getFromName($thisFileName);
				$updateThis = '';
				if($thisFileName == 'other/dbconfig.php' || $thisFileName == 'install.php' || $thisFileName == 'other/phpcommand.php' || $thisFileName == 'logs/autostart_deactivated') {
					enter_logfile($cfg,5,"      Did not touch ".$thisFileName,$norotate);
				} else {
					if(($updateThis = fopen(substr(__DIR__,0,-4).'/'.$thisFileName, 'w')) === false) {
						enter_logfile($cfg,2,"      Failed to open file ".$thisFileName,$norotate);
						$countwrongfiles++;
					} elseif(!fwrite($updateThis, $contents)) {
						enter_logfile($cfg,2,"      Failed to write file ".$thisFileName,$norotate);
						$countwrongfiles++;
					} else {
						enter_logfile($cfg,4,"      Replaced file ".$thisFileName,$norotate);
						$countchangedfiles++;
					}
					fclose($updateThis);
					unset($contents);
				}
			} else {
				enter_logfile($cfg,2,"      Unkown thing happened.. Is the parent directory existing? ".$thisFileDir." # ".$thisFileName,$norotate);
				$countwrongfiles++;
			}
		}

		$zip->close();
		unset($zip);
		sleep(1);
	} else {
		enter_logfile($cfg,2,"      Error with downloaded Zip file happened. Is the file inside the folder 'update' valid and readable?",$norotate);
		$countwrongfiles++;
	}

	if(!unlink(substr(__DIR__,0,-4).'update/ranksystem_'.$cfg['version_latest_available'].'.zip')) {
		enter_logfile($cfg,3,"    Could not clean update folder. Please remove the unneeded file ".substr(__DIR__,0,-4)."update/ranksystem_".$cfg['version_latest_available'].".zip",$norotate);
	} else {
		enter_logfile($cfg,5,"    Cleaned update folder.",$norotate);
	}

	if($countwrongfiles == 0 && $countchangedfiles != 0) {
		$sqlexec .= "UPDATE `$dbname`.`cfg_params` SET `value`='{$cfg['version_latest_available']}' WHERE `param`='version_latest_available';\n";

		$path = substr(__DIR__, 0, -4);

		if (file_exists($cfg['logs_path'].'pid')) {
			unlink($cfg['logs_path'].'pid');
		}

		enter_logfile($cfg,4,"  Files updated successfully.",$norotate);

		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B cmd /C ".$phpcommand." ".$path."worker.php start 1500000 >NUL 2>NUL", "r"));
		} else {
			exec($phpcommand." ".$path."worker.php start 2500000 > /dev/null 2>&1 &");
		}

		shutdown($mysqlcon,$cfg,4,"Update done. Wait for restart via cron/task.",FALSE);

	} else {
		enter_logfile($cfg,1,"  Files updated with at least one error. Please check the log!",$norotate);
		enter_logfile($cfg,2,"Update of the Ranksystem failed!",$norotate);
		enter_logfile($cfg,4,"Continue with normal work on old version.",$norotate);
	}

	$sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='get_version';\n";
	return($sqlexec);
}