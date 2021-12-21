<?PHP

function check_shutdown($cfg) {
	if(!file_exists($cfg['logs_path']."pid")) {
		shutdown(NULL,$cfg,4,"Received signal to stop!");
	}
}

function db_connect($dbtype, $dbhost, $dbname, $dbuser, $dbpass, $exit=NULL, $persistent=NULL) {
	if($dbtype != "type") {
		$dbserver  = $dbtype.':host='.$dbhost.';dbname='.$dbname.';charset=utf8mb4';
		if ($dbtype == 'mysql' && $persistent!=NULL) {
			$dboptions = array(
				PDO::ATTR_PERSISTENT => true
			);
		} else {
			$dboptions = array();
		}
		try {
			$mysqlcon = new PDO($dbserver, $dbuser, $dbpass, $dboptions);
			return $mysqlcon;
		} catch (PDOException $e) {
			echo 'Delivered Parameter: '.$dbserver.'<br><br>';
			echo 'Database Connection failed: <b>'.$e->getMessage().'</b><br><br>Check:<br>- You have already installed the Ranksystem? Run <a href="../install.php">install.php</a> first!<br>- Is the database reachable?<br>- You have installed all needed PHP extenstions? Have a look here for <a href="//ts-ranksystem.com/#windows">Windows</a> or <a href="//ts-ranksystem.com/#linux">Linux</a>?'; $err_lvl = 3;
			if($exit!=NULL) exit;
		}
	}
}

function enter_logfile($cfg,$loglevel,$logtext,$norotate = false) {
	if($loglevel!=9 && $loglevel > $cfg['logs_debug_level']) return;
	$file = $cfg['logs_path'].'ranksystem.log';
	switch ($loglevel) {
		case 1: $loglevel = "  CRITICAL  "; break;
		case 2: $loglevel = "  ERROR     "; break;
		case 3: $loglevel = "  WARNING   "; break;
		case 4: $loglevel = "  NOTICE    "; break;
		case 5: $loglevel = "  INFO      "; break;
		case 6: $loglevel = "  DEBUG     "; break;
		default:$loglevel = "  NONE      ";
	}
	$loghandle = fopen($file, 'a');
	fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ").$loglevel.$logtext."\n");
	fclose($loghandle);
	if($norotate == false && filesize($file) > ($cfg['logs_rotation_size'] * 1048576)) {
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ")."  NOTICE    Logfile filesie of 5 MiB reached.. Rotate logfile.\n");
		fclose($loghandle);
		$file2 = "$file.old";
		if(file_exists($file2)) unlink($file2);
		rename($file, $file2);
		$loghandle = fopen($file, 'a');
		fwrite($loghandle, DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format("Y-m-d H:i:s.u ")."  NOTICE    Rotated logfile...\n");
		fclose($loghandle);
	}
}

function error_handling($msg,$type = NULL) {
	if(strstr($type, '#') && strstr($msg, '#####')) {
		$type_arr = explode('#', $type);
		$msg_arr = explode('#####', $msg);
		$cnt = 0;
		
		foreach($msg_arr as $msg) {
			switch ($type_arr[$cnt]) {
				case NULL: echo '<div class="alert alert-success alert-dismissible">'; break;
				case 0: echo '<div class="alert alert-success alert-dismissible">'; break;
				case 1: echo '<div class="alert alert-info alert-dismissible">'; break;
				case 2: echo '<div class="alert alert-warning alert-dismissible">'; break;
				case 3: echo '<div class="alert alert-danger alert-dismissible">'; break;
			}
			echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',$msg_arr[$cnt],'</div>';
			$cnt++;
		}
	} else {
		switch ($type) {
			case NULL: echo '<div class="alert alert-success alert-dismissible">'; break;
			case 0: echo '<div class="alert alert-success alert-dismissible">'; break;
			case 1: echo '<div class="alert alert-info alert-dismissible">'; break;
			case 2: echo '<div class="alert alert-warning alert-dismissible">'; break;
			case 3: echo '<div class="alert alert-danger alert-dismissible">'; break;
		}
		echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',$msg,'</div>';
	}
}

function getclientip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP']))
		return $_SERVER['HTTP_CLIENT_IP'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED']))
		return $_SERVER['HTTP_X_FORWARDED'];
	elseif(!empty($_SERVER['HTTP_FORWARDED_FOR']))
		return $_SERVER['HTTP_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_FORWARDED']))
		return $_SERVER['HTTP_FORWARDED'];
	elseif(!empty($_SERVER['REMOTE_ADDR']))
		return $_SERVER['REMOTE_ADDR'];
	else
		return false;
}

function getlog($cfg,$number_lines,$filters,$filter2,$inactivefilter = NULL) {
	$lines=array();
	if(file_exists($cfg['logs_path']."ranksystem.log")) {
		$fp = fopen($cfg['logs_path']."ranksystem.log", "r");
		$buffer=array();
		while($line = fgets($fp, 4096)) {
			array_push($buffer, htmlspecialchars($line));
		}
		fclose($fp);
		$buffer = array_reverse($buffer);
		$lastfilter = 'init';
		foreach($buffer as $line) {
			if(substr($line, 0, 2) != "20" && in_array($lastfilter, $filters)) {
				array_push($lines, htmlspecialchars($line));
				if (count($lines)>$number_lines) {
					break;
				}
				continue;
			}
			foreach($filters as $filter) {
				if(($filter != NULL && strstr($line, $filter) && $filter2 == NULL) || ($filter2 != NULL && strstr($line, $filter2) && $filter != NULL && strstr($line, $filter))) {
					if($filter == "CRITICAL" || $filter == "ERROR") {
						array_push($lines, '<span class="text-danger">'.htmlspecialchars($line).'</span>');
					} elseif($filter == "WARNING") {
						array_push($lines, '<span class="text-warning">'.htmlspecialchars($line).'</span>');
					} else {
						array_push($lines, htmlspecialchars($line));
					}
					$lastfilter = $filter;
					if (count($lines)>$number_lines) {
						break 2;
					}
					break;
				} elseif($inactivefilter != NULL) {
					foreach($inactivefilter as $defilter) {
						if($defilter != NULL && strstr($line, $defilter)) {
							$lastfilter = $defilter;
						}
					}
				}
			}
		}		
	} else {
		$lines[] = "No log entry found...\n";
		$lines[] = "The logfile will be created with next startup.\n";
	}
	return $lines;
}

function get_language($cfg) {
	$rspathhex = get_rspath();
	if(isset($_GET["lang"])) {
		if(is_dir(substr(__DIR__,0,-5).'languages/')) {
			foreach(scandir(substr(__DIR__,0,-5).'languages/') as $file) {
				if ('.' === $file || '..' === $file || is_dir($file)) continue;
				$sep_lang = preg_split("/[._]/", $file);
				if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
					if(strtolower($_GET["lang"]) == strtolower($sep_lang[1])) {
						$_SESSION[$rspathhex.'language'] = $sep_lang[1];
						return $sep_lang[1];
					}
				}
			}
		}
		
	}
	if(isset($_SESSION[$rspathhex.'language'])) {
		return $_SESSION[$rspathhex.'language'];
	}
	if(isset($cfg['default_language'])) {
		return $cfg['default_language'];
	}
	return "en";
}

function get_percentage($max_value, $value) {
	return (round(($value/$max_value)*100));
}

function get_rspath() {
	return 'rs_'.dechex(crc32(__DIR__)).'_';
}

function human_readable_size($bytes,$lang) {
	$size = array($lang['size_byte'],$lang['size_kib'],$lang['size_mib'],$lang['size_gib'],$lang['size_tib'],$lang['size_pib'],$lang['size_eib'],$lang['size_zib'],$lang['size_yib']);
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}

function mime2extension($mimetype) {
	$mimearr = [
		'image/bmp' => 'bmp',
		'image/x-bmp' => 'bmp',
		'image/x-bitmap' => 'bmp',
		'image/x-xbitmap' => 'bmp',
		'image/x-win-bitmap' => 'bmp',
		'image/x-windows-bmp' => 'bmp',
		'image/ms-bmp' => 'bmp',
		'image/x-ms-bmp' => 'bmp',
		'image/gif' => 'gif',
		'image/jpeg' => 'jpg',
		'image/pjpeg' => 'jpg',
		'image/x-portable-bitmap' => 'pbm',
		'image/x-portable-graymap' => 'pgm',
		'image/png' => 'png',
		'image/x-png' => 'png',
		'image/x-portable-pixmap' => 'ppm',
		'image/svg+xml' => 'svg',
		'image/x-xbitmap' => 'xbm',
		'image/x-xpixmap' => 'xpm'
	];
    return isset($mimearr[$mimetype]) ? $mimearr[$mimetype] : FALSE;
}

function pagination($keysort,$keyorder,$user_pro_seite,$seiten_anzahl_gerundet,$seite,$getstring) {
	$pagination = '<nav><div class="text-center"><ul class="pagination"><li><a href="?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite=1&amp;user='.$user_pro_seite.'&amp;search='.$getstring.'" aria-label="backward"><span aria-hidden="true"><span class="fas fa-caret-square-left" aria-hidden="true"></span>&nbsp;</span></a></li>';
	for($a=0; $a < $seiten_anzahl_gerundet; $a++) {
		$b = $a + 1;
		if($seite == $b) {
			$pagination .= '<li class="active"><a href="">'.$b.'</a></li>';
		} elseif ($b > $seite - 5 && $b < $seite + 5) {
			$pagination .= '<li><a href="?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite='.$b.'&amp;user='.$user_pro_seite.'&amp;search='.$getstring.'">'.$b.'</a></li>';
		}
	}
	$pagination .= '<li><a href="?sort='.$keysort.'&amp;order='.$keyorder.'&amp;seite='.$seiten_anzahl_gerundet.'&amp;user='.$user_pro_seite.'&amp;search='.$getstring.'" aria-label="forward"><span aria-hidden="true">&nbsp;<span class="fas fa-caret-square-right" aria-hidden="true"></span></span></a></li></ul></div></nav>';
	return $pagination;
}

function php_error_handling($err_code, $err_msg, $err_file, $err_line) {
	global $cfg;
	switch ($err_code) {
		case E_USER_ERROR: $loglevel = 2; break;
		case E_USER_WARNING: $loglevel = 3; break;
		case E_USER_NOTICE: $loglevel = 4; break;
		default: $loglevel = 4;
	}
	if(substr($err_msg, 0, 15) != "password_hash()" && substr($err_msg, 0, 11) != "fsockopen()") {
		enter_logfile($cfg,$loglevel,$err_code.": ".$err_msg." on line ".$err_line." in ".$err_file);
	}
	return true;
}

function rem_session_ts3() {
	$rspathhex = get_rspath();
	unset($_SESSION[$rspathhex.'admin']);
	unset($_SESSION[$rspathhex.'clientip']);
	unset($_SESSION[$rspathhex.'connected']);
	unset($_SESSION[$rspathhex.'inactivefilter']);
	unset($_SESSION[$rspathhex.'language']);
	unset($_SESSION[$rspathhex.'logfilter']);
	unset($_SESSION[$rspathhex.'logfilter2']);
	unset($_SESSION[$rspathhex.'multiple']);
	unset($_SESSION[$rspathhex.'newversion']);
	unset($_SESSION[$rspathhex.'number_lines']);
	unset($_SESSION[$rspathhex.'password']);
	unset($_SESSION[$rspathhex.'serverport']);
	unset($_SESSION[$rspathhex.'temp_cldbid']);
	unset($_SESSION[$rspathhex.'temp_name']);
	unset($_SESSION[$rspathhex.'temp_uuid']);
	unset($_SESSION[$rspathhex.'token']);
	unset($_SESSION[$rspathhex.'tsavatar']);
	unset($_SESSION[$rspathhex.'tscldbid']);
	unset($_SESSION[$rspathhex.'tsconnections']);
	unset($_SESSION[$rspathhex.'tscreated']);
	unset($_SESSION[$rspathhex.'tsname']);
	unset($_SESSION[$rspathhex.'tsuid']);
	unset($_SESSION[$rspathhex.'upinfomsg']);
	unset($_SESSION[$rspathhex.'username']);
	unset($_SESSION[$rspathhex.'uuid_verified']);
}

function select_channel($channellist, $cfg_cid) {
	if(isset($channellist) && count($channellist)>0) {
		$selectbox = '<select class="selectpicker form-control" data-live-search="true" data-actions-box="true" name="channelid[]">';
		$channelarr = sort_channel_tree($channellist);
		
		foreach ($channelarr as $cid => $channel) {
			if (isset($channel['sub_level'])) {
				$prefix = '';
				for($y=0; $y<$channel['sub_level']; $y++) {
					if(($y + 1) == $channel['sub_level'] && isset($channel['has_childs'])) {
						$prefix .= '<img src=\'../tsicons/arrow_down.png\' width=\'16\' height=\'16\'>';
						$prefix2 = '<img class=\'arrowtree\' src=\'../tsicons/arrow_down.png\' width=\'16\' height=\'16\'>';
					} else {
						$prefix .= '<img src=\'../tsicons/placeholder.png\' width=\'16\' height=\'16\'>';
						$prefix2 = '<img src=\'../tsicons/placeholder.png\' width=\'16\' height=\'16\'>';
					}
					
				}
			}
			$chname = htmlspecialchars($channel['channel_name']);
			if (isset($channel['iconid']) && $channel['iconid'] != 0) $iconid=$channel['iconid']."."; else $iconid="placeholder.png";
			if ($cid != 0) {
				if($cid == $cfg_cid) {
					$selectbox .= '<option selected="selected" data-content="';
				} else {
					$selectbox .= '<option data-content="';
				}
				if(preg_match("/\[[^\]]*spacer[^\]]*\]/", $channel['channel_name']) && $channel['pid']==0) {
					$exploded_chname = explode(']', $channel['channel_name']);
					$isspacer = FALSE;

					switch($exploded_chname[1]) {
						case "___":
							$chname = "<span class='tsspacer5 tsspacercolor tsspacerimg'></span>"; $isspacer = TRUE; break;
						case "---":
							$chname = "<span class='tsspacer4 tsspacercolor tsspacerimg'></span>"; $isspacer = TRUE; break;
						case "...":
							$chname = "<span class='tsspacer3 tsspacercolor tsspacerimg'></span>"; $isspacer = TRUE; break;
						case "-.-":
							$chname = "<span class='tsspacer2 tsspacercolor tsspacerimg'></span>"; $isspacer = TRUE; break;
						case "-..":
							$chname = "<span class='tsspacer1 tsspacercolor tsspacerimg'></span>"; $isspacer = TRUE; break;
						default:
							$chname = htmlspecialchars($exploded_chname[1]);
					}

					if($isspacer === FALSE && preg_match("/\[(.*)spacer.*\]/", $channel['channel_name'], $matches)) {
						switch($matches[1]) {
							case "*":
								$postfix = $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer margincid"'; break;
							case "c":
								$postfix = $prefix2.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer text-center margincid"'; break;
							case "r":
								$postfix = $prefix2.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer text-right margincid"'; break;
							default:
								$postfix = $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer margincid"';
						}
					} else {
						$postfix = $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="tsspacer margincid"';
					}
					$selectbox .= $postfix;
				} else {
					$selectbox .= $prefix.$chname.'<span class=\'text-muted labelcid small\'>ID:</span><span class=\'text-muted labelcid2 small\'>'.$cid.'</span>" class="margincid"';
				}
				$selectbox .= ' value="'.$cid.'"></option>';
			}
		}
		$selectbox .= '</select>';
	} else {
		$selectbox = '<input type="text" class="form-control" name="channelid" value="'.$cfg_cid.'">';
		$selectbox .= '<script>$("input[name=\'channelid\']").TouchSpin({
			min: 0,
			max: 2147483647,
			verticalbuttons: true,
			prefix: \'ID:\'
		});</script>';
	}
	return $selectbox;
}

function set_language($language) {
	if(is_dir(substr(__DIR__,0,-5).'languages/')) {
		foreach(scandir(substr(__DIR__,0,-5).'languages/') as $file) {
			if ('.' === $file || '..' === $file || is_dir($file)) continue;
			$sep_lang = preg_split("/[._]/", $file);
			if(isset($sep_lang[0]) && $sep_lang[0] == 'core' && isset($sep_lang[1]) && strlen($sep_lang[1]) == 2 && isset($sep_lang[4]) && strtolower($sep_lang[4]) == 'php') {
				if(strtolower($language) == strtolower($sep_lang[1])) {
					include(substr(__DIR__,0,-5).'languages/core_'.$sep_lang[1].'_'.$sep_lang[2].'_'.$sep_lang[3].'.'.$sep_lang[4]);
					$_SESSION[get_rspath().'language'] = $sep_lang[1];
					$required_lang = 1;
					break;
				}
			}
		}
	}
	if(!isset($required_lang)) {
		include('../languages/core_en_english_gb.php');
	}
	return $lang;
}

function set_session_ts3($mysqlcon,$cfg,$lang,$dbname) {
	$hpclientip = getclientip();
	$rspathhex = get_rspath();
	
	$allclients = $mysqlcon->query("SELECT `u`.`uuid`,`u`.`cldbid`,`u`.`name`,`u`.`firstcon`,`s`.`total_connections` FROM `$dbname`.`user` AS `u` LEFT JOIN `$dbname`.`stats_user` AS `s` ON `u`.`uuid`=`s`.`uuid` WHERE `online`='1'")->fetchAll();
	$iptable = $mysqlcon->query("SELECT `uuid`,`iphash`,`ip` FROM `$dbname`.`user_iphash`")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
	$_SESSION[$rspathhex.'connected'] = 0;
	$_SESSION[$rspathhex.'tsname'] = $lang['stag0016'];
	$_SESSION[$rspathhex.'serverport'] = $cfg['teamspeak_voice_port'];
	$_SESSION[$rspathhex.'multiple'] = array();
	
	if($cfg['rankup_hash_ip_addresses_mode'] == 2) {
		$salt = md5(dechex(crc32(substr(__DIR__,0,-5))));
		$hashedip = crypt($hpclientip, '$2y$10$'.$salt.'$');
	}

    foreach ($allclients as $client) {
		if(isset($_SESSION[$rspathhex.'uuid_verified']) && $_SESSION[$rspathhex.'uuid_verified'] != $client['uuid']) {
			continue;
		}
		$verify = FALSE;
		if($cfg['rankup_hash_ip_addresses_mode'] == 1) {
			if (isset($iptable[$client['uuid']]['iphash']) && $iptable[$client['uuid']]['iphash'] != NULL && password_verify($hpclientip, $iptable[$client['uuid']]['iphash'])) {
				$verify = TRUE;
			}
		} elseif($cfg['rankup_hash_ip_addresses_mode'] == 2) {
			if (isset($iptable[$client['uuid']]['iphash']) && $hashedip == $iptable[$client['uuid']]['iphash'] && $iptable[$client['uuid']]['iphash'] != NULL) {
				$verify = TRUE;
			}
		} else {
			if (isset($iptable[$client['uuid']]['ip']) && $hpclientip == $iptable[$client['uuid']]['ip'] && $iptable[$client['uuid']]['ip'] != NULL) {
				$verify = TRUE;
			}
		}
		if ($verify == TRUE) {
			$_SESSION[$rspathhex.'tsname'] = htmlspecialchars($client['name']);
			if(isset($_SESSION[$rspathhex.'tsuid']) && $_SESSION[$rspathhex.'tsuid'] != $client['uuid']) {
				$_SESSION[$rspathhex.'multiple'][$client['uuid']] = htmlspecialchars($client['name']);
				$_SESSION[$rspathhex.'tsname'] = "verification needed (multiple)!";
				unset($_SESSION[$rspathhex.'admin']);
			} elseif (!isset($_SESSION[$rspathhex.'tsuid'])) {
				$_SESSION[$rspathhex.'multiple'][$client['uuid']] = htmlspecialchars($client['name']);
			}
			$_SESSION[$rspathhex.'tsuid'] = $client['uuid'];
			if(isset($cfg['webinterface_admin_client_unique_id_list']) && $cfg['webinterface_admin_client_unique_id_list'] != NULL) {
				foreach(array_flip($cfg['webinterface_admin_client_unique_id_list']) as $auuid) {
					if ($_SESSION[$rspathhex.'tsuid'] == $auuid) {
						$_SESSION[$rspathhex.'admin'] = TRUE;
					}
				}
			}
			$_SESSION[$rspathhex.'tscldbid'] = $client['cldbid'];
			if ($client['firstcon'] == 0) {
				$_SESSION[$rspathhex.'tscreated'] = "unkown";
			} else {
				$_SESSION[$rspathhex.'tscreated'] = date('d-m-Y', $client['firstcon']);
			}
			if ($client['total_connections'] != NULL) {
				$_SESSION[$rspathhex.'tsconnections'] = $client['total_connections'];
			} else {
				$_SESSION[$rspathhex.'tsconnections'] = 0;
			}
			$convert = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p');
			$uuidasbase16 = '';
			for ($i = 0; $i < 20; $i++) {
				$char = ord(substr(base64_decode($_SESSION[$rspathhex.'tsuid']), $i, 1));
				$uuidasbase16 .= $convert[($char & 0xF0) >> 4];
				$uuidasbase16 .= $convert[$char & 0x0F];
			}
			if (is_file('../avatars/' . $uuidasbase16 . '.png')) {
				$_SESSION[$rspathhex.'tsavatar'] = $uuidasbase16 . '.png';
			} else {
				$_SESSION[$rspathhex.'tsavatar'] = "none";
			}
			$_SESSION[$rspathhex.'connected'] = 1;
			$_SESSION[$rspathhex.'language'] = $cfg['default_language'];
		}
	}
}

function sendmessage($ts3, $cfg, $uuid, $msg, $targetmode, $targetid=NULL, $erromsg=NULL, $loglevel=NULL, $successmsg=NULL, $nolog=NULL) {
	try {
		if(strlen($msg) > 1024) {
			$fragarr = explode("##*##", wordwrap($msg, 1022, "##*##", TRUE), 1022);
			foreach($fragarr as $frag) {
				usleep($cfg['teamspeak_query_command_delay']);
				if ($targetmode==2 && $targetid!=NULL) {
					$ts3->serverGetSelected()->channelGetById($targetid)->message("\n".$frag);
					if($nolog==NULL) enter_logfile($cfg,6,"sendmessage fragment to channel (ID: $targetid): ".$frag);
				} elseif ($targetmode==3) {
					$ts3->serverGetSelected()->message("\n".$frag);
					if($nolog==NULL) enter_logfile($cfg,6,"sendmessage fragment to server: ".$frag);
				} elseif ($targetmode==1 && $targetid!=NULL) {
					$ts3->serverGetSelected()->clientGetById($targetid)->message("\n".$frag);
					if($nolog==NULL) enter_logfile($cfg,6,"sendmessage fragment to connectionID $targetid (uuid $uuid): ".$frag);
				} else {
					$ts3->serverGetSelected()->clientGetByUid($uuid)->message("\n".$frag);
					if($nolog==NULL) enter_logfile($cfg,6,"sendmessage fragment to uuid $uuid (connectionID $targetid): ".$frag);
				}
			}
		} else {
			usleep($cfg['teamspeak_query_command_delay']);
			if ($targetmode==2 && $targetid!=NULL) {
				$ts3->serverGetSelected()->channelGetById($targetid)->message($msg);
				if($nolog==NULL) enter_logfile($cfg,6,"sendmessage to channel (ID: $targetid): ".$msg);
			} elseif ($targetmode==3) {
				$ts3->serverGetSelected()->message($msg);
				if($nolog==NULL) enter_logfile($cfg,6,"sendmessage to server: ".$msg);
			} elseif ($targetmode==1 && $targetid!=NULL) {
				$ts3->serverGetSelected()->clientGetById($targetid)->message($msg);
				if($nolog==NULL) enter_logfile($cfg,6,"sendmessage to connectionID $targetid (uuid $uuid): ".$msg);
			} else {
				$ts3->serverGetSelected()->clientGetByUid($uuid)->message($msg);
				if($nolog==NULL) enter_logfile($cfg,6,"sendmessage to uuid $uuid (connectionID $targetid): ".$msg);
			}
			
		}
		if($successmsg!=NULL) {
			enter_logfile($cfg,5,$successmsg);
		}
	} catch (Exception $e) {
		if($loglevel!=NULL) {
			enter_logfile($cfg,$loglevel,$erromsg." TS3: ".$e->getCode().': '.$e->getMessage());
		} else {
			enter_logfile($cfg,3,"sendmessage: ".$e->getCode().': '.$e->getMessage().", targetmode: $targetmode, targetid: $targetid");
		}
	}
}

function shutdown($mysqlcon,$cfg,$loglevel,$reason,$nodestroypid = TRUE) {
	if($nodestroypid === TRUE) {
		if (file_exists($cfg['logs_path'].'pid')) {
			unlink($cfg['logs_path'].'pid');
		}
	}
	enter_logfile($cfg,$loglevel,$reason." Shutting down!");
	enter_logfile($cfg,9,"###################################################################");
	if(isset($mysqlcon)) {
		$mysqlcon = null;
	}
	exit;
}

function sort_channel_tree($channellist) {
	foreach($channellist as $cid => $results) {
		$channel['channel_order'][$results['pid']][$results['channel_order']] = $cid;
		$channel['pid'][$results['pid']][] = $cid;
	}

	foreach($channel['pid'] as $pid => $pid_value) {
		$channel_order = 0;
		$count_pid = count($pid_value);
		for($y=0; $y<$count_pid; $y++) {
			foreach($channellist as $cid => $value) {
				if(isset($channel['channel_order'][$pid][$channel_order]) && $channel['channel_order'][$pid][$channel_order] == $cid) {
					$channel['sorted'][$pid][$cid] = $channellist[$cid];
					$channel_order = $cid;
				}
			}
		}
	}

	function channel_list($channel, $channel_list, $pid, $sub) {
		if($channel['sorted'][$pid]) {
			foreach($channel['sorted'][$pid] as $cid => $value) {
				$channel_list[$cid] = $value;
				$channel_list[$cid]['sub_level'] = $sub;
				if(isset($channel['pid'][$cid])) {
					$sub++;
					$channel_list[$cid]['has_childs'] = 1;
					$channel_list = channel_list($channel, $channel_list, $cid, $sub);
					$sub--;
				}
			}
		}
		return $channel_list;
	}

	$sorted_channel = channel_list($channel, array(), 0, 1);
	return $sorted_channel;
}

function start_session($cfg) {
	ini_set('session.cookie_httponly', 1);
	ini_set('session.use_strict_mode', 1);
	ini_set('session.sid_length', 128);
	if(isset($cfg['default_header_xss'])) {
		header("X-XSS-Protection: ".$cfg['default_header_xss']);
	} else {
		header("X-XSS-Protection: 1; mode=block");
	}
	if(!isset($cfg['default_header_contenttyp']) || $cfg['default_header_contenttyp'] == 1) {
		header("X-Content-Type-Options: nosniff");
	}
	if(isset($cfg['default_header_frame']) && $cfg['default_header_frame'] != NULL) {
		header("X-Frame-Options: ".$cfg['default_header_frame']);
	}

	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
		$prot = 'https';
		ini_set('session.cookie_secure', 1);
		if(!headers_sent()) {
			header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload;");
		}
	} else {
		$prot = 'http';
	}

	if(isset($cfg['default_header_origin']) && $cfg['default_header_origin'] != NULL && $cfg['default_header_origin'] != 'null') {
		if(strstr($cfg['default_header_origin'], ',')) {
			$origin_arr = explode(',', $cfg['default_header_origin']);
			if(isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $origin_arr)) {
				header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
			}
		} else {
			header("Access-Control-Allow-Origin: ".$cfg['default_header_origin']);
		}
	}

	if(version_compare(PHP_VERSION, '7.3.0', '>=')) {
		ini_set('session.cookie_samesite', $cfg['default_session_sametime']);
	}

	session_start();
	return $prot;
}
?>