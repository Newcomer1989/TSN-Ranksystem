<?PHP
function calc_user_snapshot($cfg,$dbname,&$db_cache) {
	$starttime = microtime(true);
	$nowtime = time();
	$sqlexec = '';

	// Event Handling each 6 hours
	// Duplicate users Table in snapshot Table
	if(($nowtime - intval($db_cache['job_check']['last_snapshot_time']['timestamp'])) > 21600) {
		if(isset($db_cache['all_user'])) {
			$db_cache['job_check']['last_snapshot_id']['timestamp'] = $nextid = intval($db_cache['job_check']['last_snapshot_id']['timestamp']) + 1;
			if ($nextid > 121) $nextid = $nextid - 121;
			
			$allinsertsnap = '';
			foreach ($db_cache['all_user'] as $uuid => $insertsnap) {
				if(isset($insertsnap['cldbid']) && $insertsnap['cldbid'] != NULL) {
					$allinsertsnap = $allinsertsnap . "({$nextid},{$insertsnap['cldbid']},".round($insertsnap['count']).",".round($insertsnap['idle'])."),";
				}
			}
			$allinsertsnap = substr($allinsertsnap, 0, -1);
			if ($allinsertsnap != '') {
				$sqlexec .= "DELETE FROM `$dbname`.`user_snapshot` WHERE `id`={$nextid};\nINSERT INTO `$dbname`.`user_snapshot` (`id`,`cldbid`,`count`,`idle`) VALUES $allinsertsnap;\nUPDATE `$dbname`.`job_check` SET `timestamp`={$nextid} WHERE `job_name`='last_snapshot_id';\nUPDATE `$dbname`.`job_check` SET `timestamp`={$nowtime} WHERE `job_name`='last_snapshot_time';\n";
			}
			unset($allinsertsnap);
		}
	}

	enter_logfile(6,"calc_user_snapshot needs: ".(number_format(round((microtime(true) - $starttime), 5),5)));
	return($sqlexec);
}