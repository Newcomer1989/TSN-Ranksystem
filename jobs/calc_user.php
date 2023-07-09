<?php

function calc_user($ts3, $mysqlcon, $lang, $cfg, $dbname, $allclients, $phpcommand, &$db_cache)
{
    $starttime = microtime(true);
    $nowtime = time();
    $sqlexec = '';

    if (empty($cfg['rankup_definition'])) {
        shutdown($mysqlcon, 1, 'calc_user:'.$lang['wiconferr']);
    }

    $addtime = $nowtime - intval($db_cache['job_check']['calc_user_lastscan']['timestamp']);

    if ($addtime > 1800) {
        enter_logfile(4, 'Much time gone since last scan.. set addtime to 1 second.');
        $addtime = 1;
    } elseif ($addtime < 0) {
        enter_logfile(3, 'Negative time valie (now < last scan).. Error in your machine time!.. set addtime to 1 second.');
        $addtime = 1;
    }

    $db_cache['job_check']['calc_user_lastscan']['timestamp'] = $nowtime;
    $sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`=$nowtime WHERE `job_name`='calc_user_lastscan';\nUPDATE `$dbname`.`user` SET `online`=0 WHERE `online`=1;\n";

    $multipleonline = $updatedata = $insertdata = [];

    if (isset($db_cache['admin_addtime']) && count($db_cache['admin_addtime']) != 0) {
        foreach ($db_cache['admin_addtime'] as $uuid => $value) {
            if (isset($db_cache['all_user'][$uuid])) {
                $isonline = 0;
                foreach ($allclients as $client) {
                    if ($client['client_unique_identifier'] == $uuid) {
                        $isonline = 1;
                        $temp_cldbid = $client['client_database_id'];
                    }
                }
                if ($value['timecount'] == 0 && $value['timestamp'] == 4273093200) {
                    //remove user
                    if (($user = $mysqlcon->query("SELECT `uuid`,`cldbid`,`name` FROM `$dbname`.`user` WHERE `uuid`='{$uuid}'")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE)) === false) {
                        enter_logfile(2, 'Database error on selecting user (admin function remove user): '.print_r($mysqlcon->errorInfo(), true));
                    } else {
                        $temp_cldbid = $user[$uuid]['cldbid'];
                        $sqlexec .= "DELETE FROM `$dbname`.`addon_assign_groups` WHERE `uuid`='{$uuid}';\nDELETE FROM `$dbname`.`admin_addtime` WHERE `uuid`='{$uuid}';\nDELETE FROM `$dbname`.`stats_user` WHERE `uuid`='{$uuid}';\nDELETE FROM `$dbname`.`user` WHERE `uuid`='{$uuid}';\nDELETE FROM `$dbname`.`user_iphash` WHERE `uuid`='{$uuid}';\nDELETE FROM `$dbname`.`user_snapshot` WHERE `cldbid`='{$temp_cldbid}';\n";
                        enter_logfile(4, sprintf($lang['wihladm45'], $user[$uuid]['name'], $uuid, $temp_cldbid).' ('.$lang['wihladm46'].')');
                        if (isset($db_cache['all_user'][$uuid])) {
                            unset($db_cache['all_user'][$uuid]);
                        }
                        if (isset($db_cache['admin_addtime'][$uuid])) {
                            unset($db_cache['admin_addtime'][$uuid]);
                        }
                        if (isset($db_cache['addon_assign_groups'][$uuid])) {
                            unset($db_cache['addon_assign_groups'][$uuid]);
                        }
                    }
                    unset($user);
                } else {
                    $db_cache['all_user'][$uuid]['count'] += $value['timecount'];
                    if ($db_cache['all_user'][$uuid]['count'] < 0) {
                        $db_cache['all_user'][$uuid]['count'] = $db_cache['all_user'][$uuid]['idle'] = 0;
                    } elseif ($db_cache['all_user'][$uuid]['idle'] > $db_cache['all_user'][$uuid]['count']) {
                        $db_cache['all_user'][$uuid]['idle'] = $db_cache['all_user'][$uuid]['count'];
                    }
                    if ($isonline != 1) {
                        if (($user = $mysqlcon->query("SELECT `uuid`,`cldbid` FROM `$dbname`.`user` WHERE `uuid`='{$uuid}'")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE)) === false) {
                            enter_logfile(2, 'Database error on selecting user (admin function remove/add time): '.print_r($mysqlcon->errorInfo(), true));
                        } else {
                            $temp_cldbid = $user[$uuid]['cldbid'];
                            $sqlexec .= "UPDATE `$dbname`.`user` SET `count`='{$db_cache['all_user'][$uuid]['count']}', `idle`='{$db_cache['all_user'][$uuid]['idle']}' WHERE `uuid`='{$uuid}';\n";
                        }
                    }
                    if ($mysqlcon->exec("DELETE FROM `$dbname`.`admin_addtime` WHERE `timestamp`=".$value['timestamp']." AND `uuid`='$uuid';") === false) {
                        enter_logfile(2, 'Database error on updating user (admin function remove/add time): '.print_r($mysqlcon->errorInfo(), true));
                    }
                    if (($usersnap = $mysqlcon->query("SELECT `id`,`cldbid`,`count`,`idle` FROM `$dbname`.`user_snapshot` WHERE `cldbid`={$temp_cldbid}")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE)) === false) {
                        enter_logfile(2, 'Database error on selecting user (admin function remove/add time): '.print_r($mysqlcon->errorInfo(), true));
                    } else {
                        foreach ($usersnap as $id => $valuesnap) {
                            $valuesnap['count'] += $value['timecount'];
                            if ($valuesnap['count'] < 0) {
                                $valuesnap['count'] = $valuesnap['idle'] = 0;
                            } elseif ($valuesnap['idle'] > $valuesnap['count']) {
                                $valuesnap['idle'] = $valuesnap['count'];
                            }
                            $sqlexec .= "UPDATE `$dbname`.`user_snapshot` SET `count`='{$valuesnap['count']}', `idle`='{$valuesnap['idle']}' WHERE `cldbid`='{$temp_cldbid}' AND `id`='{$id}';\n";
                        }
                    }
                    enter_logfile(4, sprintf($lang['sccupcount2'], $value['timecount'], $uuid).' ('.$lang['wihladm46'].')');
                    unset($user, $usersnap);
                }
            }
        }
        unset($db_cache['admin_addtime']);
    }

    foreach ($allclients as $client) {
        $client_groups_rankup = [];
        $name = $mysqlcon->quote((mb_substr(mb_convert_encoding($client['client_nickname'], 'UTF-8', 'auto'), 0, 30)), ENT_QUOTES);
        $uid = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
        $sgroups = array_flip(explode(',', $client['client_servergroups']));
        if ($client['client_country'] !== null && strlen($client['client_country']) > 2 || $client['client_country'] === null) {
            $client['client_country'] = 'XX';
        }
        $client['client_platform'] = mb_substr($client['client_platform'], 0, 32);
        $client['client_version'] = mb_substr($client['client_version'], 0, 64);

        if (! isset($multipleonline[$uid]) && $client['client_version'] != 'ServerQuery' && $client['client_type'] != '1') {
            $clientidle = floor($client['client_idle_time'] / 1000);
            if (isset($cfg['rankup_ignore_idle_time']) && $clientidle < $cfg['rankup_ignore_idle_time']) {
                $clientidle = 0;
            }
            $multipleonline[$uid] = 0;
            if (isset($cfg['rankup_excepted_unique_client_id_list'][$uid])) {
                $except = 3;
            } elseif ($cfg['rankup_excepted_group_id_list'] != null && array_intersect_key($sgroups, $cfg['rankup_excepted_group_id_list'])) {
                $except = 2;
            } else {
                if (isset($db_cache['all_user'][$uid]['except']) && ($db_cache['all_user'][$uid]['except'] == 3 || $db_cache['all_user'][$uid]['except'] == 2) && $cfg['rankup_excepted_mode'] == 2) {
                    $db_cache['all_user'][$uid]['count'] = 0;
                    $db_cache['all_user'][$uid]['idle'] = 0;
                    enter_logfile(5, sprintf($lang['resettime'], $client['client_nickname'], $uid, $client['client_database_id']));
                    $sqlexec .= "DELETE FROM `$dbname`.`user_snapshot` WHERE `cldbid`='{$client['client_database_id']}';\n";
                }
                $except = 0;
            }
            if (isset($db_cache['all_user'][$uid])) {
                $idle = $db_cache['all_user'][$uid]['idle'] + $clientidle;
                if ($db_cache['all_user'][$uid]['cldbid'] != $client['client_database_id'] && $cfg['rankup_client_database_id_change_switch'] == 1) {
                    enter_logfile(5, sprintf($lang['changedbid'], $client['client_nickname'], $uid, $client['client_database_id'], $db_cache['all_user'][$uid]['cldbid']));
                    $count = 1;
                    $idle = 0;
                    $boosttime = 0;
                } else {
                    $hitboost = 0;
                    $boosttime = $db_cache['all_user'][$uid]['boosttime'];
                    if (isset($cfg['rankup_boost_definition']) && $cfg['rankup_boost_definition'] != null) {
                        foreach ($cfg['rankup_boost_definition'] as $boost) {
                            if (isset($sgroups[$boost['group']])) {
                                $hitboost = 1;
                                if ($db_cache['all_user'][$uid]['boosttime'] == 0) {
                                    $boosttime = $nowtime;
                                } else {
                                    if ($nowtime > $db_cache['all_user'][$uid]['boosttime'] + $boost['time'] && (! isset($db_cache['all_user'][$uid]['grouperror']) || $db_cache['all_user'][$uid]['grouperror'] < ($nowtime - 300))) {
                                        usleep($cfg['teamspeak_query_command_delay']);
                                        try {
                                            $ts3->serverGroupClientDel($boost['group'], $client['client_database_id']);
                                            $boosttime = 0;
                                            enter_logfile(5, sprintf($lang['sgrprm'], $db_cache['groups'][$boost['group']]['sgidname'], $boost['group'], $client['client_nickname'], $uid, $client['client_database_id']).' [Boost-Group]');
                                        } catch (Exception $e) {
                                            enter_logfile(2, 'TS3 error: '.$e->getCode().': '.$e->getMessage().' ; '.sprintf($lang['sgrprerr'], $client['client_nickname'], $uid, $client['client_database_id'], $db_cache['groups'][$db_cache['all_user'][$uid]['grpid']]['sgidname'], $db_cache['all_user'][$uid]['grpid']));
                                            $db_cache['all_user'][$uid]['grouperror'] = $nowtime;
                                        }
                                    }
                                }
                                $count = $addtime * $boost['factor'] + $db_cache['all_user'][$uid]['count'];
                                if ($clientidle > $addtime) {
                                    $idle = $addtime * $boost['factor'] + $db_cache['all_user'][$uid]['idle'];
                                }
                            }
                        }
                    }
                    if ($cfg['rankup_boost_definition'] == 0 or $hitboost == 0) {
                        $count = $addtime + $db_cache['all_user'][$uid]['count'];
                        $boosttime = 0;
                        if ($clientidle > $addtime) {
                            $idle = $addtime + $db_cache['all_user'][$uid]['idle'];
                        }
                    }
                }
                $dtF = new DateTime('@0');
                if ($cfg['rankup_time_assess_mode'] == 1) {
                    $activetime = $count - $idle;
                } else {
                    $activetime = $count;
                }
                $dtT = new DateTime('@'.round($activetime));

                foreach ($sgroups as $clientgroup => $dummy) {
                    foreach ($cfg['rankup_definition'] as $rank) {
                        if ($rank['group'] == $clientgroup && $rank['keep'] == 0) {
                            $client_groups_rankup[$clientgroup] = 0;
                        }
                    }
                }

                $grpcount = 0;
                foreach ($cfg['rankup_definition'] as $rank) {
                    $grpcount++;
                    if (isset($cfg['rankup_excepted_channel_id_list'][$client['cid']]) || (($db_cache['all_user'][$uid]['except'] == 3 || $db_cache['all_user'][$uid]['except'] == 2) && $cfg['rankup_excepted_mode'] == 1)) {
                        $count = $db_cache['all_user'][$uid]['count'];
                        $idle = $db_cache['all_user'][$uid]['idle'];
                        if ($except != 2 && $except != 3) {
                            $except = 1;
                        }
                    } elseif ($activetime > $rank['time'] && ! isset($cfg['rankup_excepted_unique_client_id_list'][$uid]) && ($cfg['rankup_excepted_group_id_list'] == null || ! array_intersect_key($sgroups, $cfg['rankup_excepted_group_id_list']))) {
                        if (! isset($sgroups[$rank['group']])) {
                            if ($db_cache['all_user'][$uid]['grpid'] != null && $db_cache['all_user'][$uid]['grpid'] != 0 && isset($sgroups[$db_cache['all_user'][$uid]['grpid']])) {
                                $donotremove = 0;
                                foreach ($cfg['rankup_definition'] as $rank2) {
                                    if ($rank2['group'] == $db_cache['all_user'][$uid]['grpid'] && $rank2['keep'] == 1 && $activetime > $rank2['time']) {
                                        $donotremove = 1;
                                        break;
                                    }
                                }
                                if ($donotremove == 0 && (! isset($db_cache['all_user'][$uid]['grouperror']) || $db_cache['all_user'][$uid]['grouperror'] < ($nowtime - 300))) {
                                    usleep($cfg['teamspeak_query_command_delay']);
                                    try {
                                        $ts3->serverGroupClientDel($db_cache['all_user'][$uid]['grpid'], $client['client_database_id']);
                                        enter_logfile(5, sprintf($lang['sgrprm'], $db_cache['groups'][$db_cache['all_user'][$uid]['grpid']]['sgidname'], $db_cache['all_user'][$uid]['grpid'], $client['client_nickname'], $uid, $client['client_database_id']));
                                        if (isset($client_groups_rankup[$db_cache['all_user'][$uid]['grpid']])) {
                                            unset($client_groups_rankup[$db_cache['all_user'][$uid]['grpid']]);
                                        }
                                    } catch (Exception $e) {
                                        enter_logfile(2, 'TS3 error: '.$e->getCode().': '.$e->getMessage().' ; '.sprintf($lang['sgrprerr'], $client['client_nickname'], $uid, $client['client_database_id'], $db_cache['groups'][$db_cache['all_user'][$uid]['grpid']]['sgidname'], $db_cache['all_user'][$uid]['grpid']));
                                        $db_cache['all_user'][$uid]['grouperror'] = $nowtime;
                                    }
                                }
                            }
                            if (! isset($db_cache['all_user'][$uid]['grouperror']) || $db_cache['all_user'][$uid]['grouperror'] < ($nowtime - 300)) {
                                usleep($cfg['teamspeak_query_command_delay']);
                                try {
                                    $ts3->serverGroupClientAdd($rank['group'], $client['client_database_id']);
                                    $db_cache['all_user'][$uid]['grpsince'] = $nowtime;
                                    enter_logfile(5, sprintf($lang['sgrpadd'], $db_cache['groups'][$rank['group']]['sgidname'], $rank['group'], $client['client_nickname'], $uid, $client['client_database_id']));
                                    if ($cfg['rankup_message_to_user_switch'] == 1) {
                                        $days = $dtF->diff($dtT)->format('%a');
                                        $hours = $dtF->diff($dtT)->format('%h');
                                        $mins = $dtF->diff($dtT)->format('%i');
                                        $secs = $dtF->diff($dtT)->format('%s');
                                        sendmessage($ts3, $cfg, $uid, sprintf($cfg['rankup_message_to_user'], $days, $hours, $mins, $secs, $db_cache['groups'][$rank['group']]['sgidname'], $client['client_nickname']), 1, null, sprintf($lang['sgrprerr'], $name, $uid, $client['client_database_id'], $db_cache['groups'][$rank['group']]['sgidname'], $rank['group']), 2);
                                    }
                                } catch (Exception $e) {
                                    enter_logfile(2, 'TS3 error: '.$e->getCode().': '.$e->getMessage().' ; '.sprintf($lang['sgrprerr'], $client['client_nickname'], $uid, $client['client_database_id'], $db_cache['groups'][$rank['group']]['sgidname'], $rank['group']));
                                    $db_cache['all_user'][$uid]['grouperror'] = $nowtime;
                                }
                            }
                        }
                        if ($grpcount == 1) {
                            $db_cache['all_user'][$uid]['nextup'] = 0;
                        }
                        $db_cache['all_user'][$uid]['grpid'] = $rank['group'];
                        if ($rank['keep'] != 1) {
                            break;
                        }
                    } else {
                        $db_cache['all_user'][$uid]['nextup'] = $rank['time'] - $activetime;
                    }
                }

                foreach ($client_groups_rankup as $removegroup => $dummy) {
                    if ($removegroup != null && $removegroup != 0 && $removegroup != $db_cache['all_user'][$uid]['grpid'] && (! isset($db_cache['all_user'][$uid]['grouperror']) || $db_cache['all_user'][$uid]['grouperror'] < ($nowtime - 300))) {
                        try {
                            usleep($cfg['teamspeak_query_command_delay']);
                            $ts3->serverGroupClientDel($removegroup, $client['client_database_id']);
                            enter_logfile(5, sprintf('Removed WRONG servergroup %s (ID: %s) from user %s (unique Client-ID: %s; Client-database-ID %s).', $db_cache['groups'][$removegroup]['sgidname'], $removegroup, $client['client_nickname'], $uid, $client['client_database_id']));
                        } catch (Exception $e) {
                            enter_logfile(2, 'TS3 error: '.$e->getCode().': '.$e->getMessage().' ; '.sprintf($lang['sgrprerr'], $client['client_nickname'], $uid, $client['client_database_id'], $db_cache['groups'][$removegroup]['sgidname'], $removegroup));
                            $db_cache['all_user'][$uid]['grouperror'] = $nowtime;
                        }
                    } elseif ($cfg['rankup_excepted_remove_group_switch'] == 1 && $removegroup != null && $removegroup != 0 && $removegroup == $db_cache['all_user'][$uid]['grpid'] && $cfg['rankup_excepted_mode'] == 0 && ($db_cache['all_user'][$uid]['except'] == 3 || $db_cache['all_user'][$uid]['except'] == 2) && (! isset($db_cache['all_user'][$uid]['grouperror']) || $db_cache['all_user'][$uid]['grouperror'] < ($nowtime - 300))) {
                        usleep($cfg['teamspeak_query_command_delay']);
                        $ts3->serverGroupClientDel($removegroup, $client['client_database_id']);
                        enter_logfile(5, sprintf('Removed servergroup %s (ID: %s) cause EXCEPTED (%s) from user %s (unique Client-ID: %s; Client-database-ID %s).', $db_cache['groups'][$removegroup]['sgidname'], $removegroup, exception_client_code($db_cache['all_user'][$uid]['except']), $client['client_nickname'], $uid, $client['client_database_id']));
                    }
                }
                unset($client_groups_rankup);
                $updatedata[] = [
                    'uuid' => $mysqlcon->quote($client['client_unique_identifier'], ENT_QUOTES),
                    'cldbid' => $client['client_database_id'],
                    'count' => $count,
                    'name' => $name,
                    'lastseen' => $nowtime,
                    'grpid' => $db_cache['all_user'][$uid]['grpid'],
                    'nextup' => $db_cache['all_user'][$uid]['nextup'],
                    'idle' => $idle,
                    'cldgroup' => $client['client_servergroups'],
                    'boosttime' => $boosttime,
                    'platform' => $client['client_platform'],
                    'nation' => $client['client_country'],
                    'version' => $client['client_version'],
                    'except' => $except,
                    'grpsince' => $db_cache['all_user'][$uid]['grpsince'],
                    'cid' => $client['cid'],
                ];
                $db_cache['all_user'][$uid]['count'] = $count;
                $db_cache['all_user'][$uid]['idle'] = $idle;
                $db_cache['all_user'][$uid]['boosttime'] = $boosttime;
                $db_cache['all_user'][$uid]['except'] = $except;
            } else {
                $db_cache['all_user'][$uid]['grpid'] = 0;
                foreach ($cfg['rankup_definition'] as $rank) {
                    if (isset($sgroups[$rank['group']])) {
                        $db_cache['all_user'][$uid]['grpid'] = $rank['group'];
                        break;
                    }
                }
                $insertdata[] = [
                    'uuid' => $mysqlcon->quote($client['client_unique_identifier'], ENT_QUOTES),
                    'cldbid' => $client['client_database_id'],
                    'count' => $addtime,
                    'name' => $name,
                    'lastseen' => $nowtime,
                    'grpid' => $db_cache['all_user'][$uid]['grpid'],
                    'nextup' => (key($cfg['rankup_definition']) - $addtime),
                    'idle' => 0,
                    'cldgroup' => $client['client_servergroups'],
                    'boosttime' => 0,
                    'platform' => $client['client_platform'],
                    'nation' => $client['client_country'],
                    'version' => $client['client_version'],
                    'firstcon' => $nowtime,
                    'except' => $except,
                    'grpsince' => 0,
                    'cid' => $client['cid'],
                ];
                $db_cache['all_user'][$uid]['cldbid'] = $client['client_database_id'];
                $db_cache['all_user'][$uid]['count'] = $addtime;
                $db_cache['all_user'][$uid]['idle'] = 0;
                $db_cache['all_user'][$uid]['nextup'] = (key($cfg['rankup_definition']) - $addtime);
                $db_cache['all_user'][$uid]['firstcon'] = $nowtime;
                $db_cache['all_user'][$uid]['boosttime'] = 0;
                $db_cache['all_user'][$uid]['grpsince'] = 0;
                $db_cache['all_user'][$uid]['except'] = $except;
                enter_logfile(5, sprintf($lang['adduser'], $client['client_nickname'], $uid, $client['client_database_id']));
            }
            $db_cache['all_user'][$uid]['name'] = $client['client_nickname'];
            $db_cache['all_user'][$uid]['lastseen'] = $nowtime;
            $db_cache['all_user'][$uid]['cldgroup'] = $client['client_servergroups'];
            $db_cache['all_user'][$uid]['platform'] = $client['client_platform'];
            $db_cache['all_user'][$uid]['nation'] = $client['client_country'];
            $db_cache['all_user'][$uid]['version'] = $client['client_version'];
            $db_cache['all_user'][$uid]['cid'] = $client['cid'];
        }
    }
    unset($multipleonline,$allclients,$client);

    if ($updatedata != null) {
        $sqlinsertvalues = '';
        foreach ($updatedata as $updatearr) {
            $sqlinsertvalues .= '('.$updatearr['uuid'].','.$updatearr['cldbid'].','.$updatearr['count'].','.$updatearr['name'].','.$updatearr['lastseen'].','.$updatearr['grpid'].','.$updatearr['nextup'].','.$updatearr['idle'].",'".$updatearr['cldgroup']."',".$updatearr['boosttime'].",'".$updatearr['platform']."','".$updatearr['nation']."','".$updatearr['version']."',".$updatearr['except'].','.$updatearr['grpsince'].','.$updatearr['cid'].',1),';
        }
        $sqlinsertvalues = substr($sqlinsertvalues, 0, -1);
        $sqlexec .= "INSERT INTO `$dbname`.`user` (`uuid`,`cldbid`,`count`,`name`,`lastseen`,`grpid`,`nextup`,`idle`,`cldgroup`,`boosttime`,`platform`,`nation`,`version`,`except`,`grpsince`,`cid`,`online`) VALUES $sqlinsertvalues ON DUPLICATE KEY UPDATE `cldbid`=VALUES(`cldbid`),`count`=VALUES(`count`),`name`=VALUES(`name`),`lastseen`=VALUES(`lastseen`),`grpid`=VALUES(`grpid`),`nextup`=VALUES(`nextup`),`idle`=VALUES(`idle`),`cldgroup`=VALUES(`cldgroup`),`boosttime`=VALUES(`boosttime`),`platform`=VALUES(`platform`),`nation`=VALUES(`nation`),`version`=VALUES(`version`),`except`=VALUES(`except`),`grpsince`=VALUES(`grpsince`),`cid`=VALUES(`cid`),`online`=VALUES(`online`);\n";
        unset($updatedata, $sqlinsertvalues);
    }

    if ($insertdata != null) {
        $sqlinsertvalues = '';
        foreach ($insertdata as $updatearr) {
            $sqlinsertvalues .= '('.$updatearr['uuid'].','.$updatearr['cldbid'].','.$updatearr['count'].','.$updatearr['name'].','.$updatearr['lastseen'].','.$updatearr['grpid'].','.$updatearr['nextup'].','.$updatearr['idle'].",'".$updatearr['cldgroup']."',".$updatearr['boosttime'].",'".$updatearr['platform']."','".$updatearr['nation']."','".$updatearr['version']."',".$updatearr['except'].','.$updatearr['grpsince'].','.$updatearr['cid'].',1,'.$updatearr['firstcon'].'),';
        }
        $sqlinsertvalues = substr($sqlinsertvalues, 0, -1);
        $sqlexec .= "INSERT INTO `$dbname`.`user` (`uuid`,`cldbid`,`count`,`name`,`lastseen`,`grpid`,`nextup`,`idle`,`cldgroup`,`boosttime`,`platform`,`nation`,`version`,`except`,`grpsince`,`cid`,`online`,`firstcon`) VALUES $sqlinsertvalues ON DUPLICATE KEY UPDATE `cldbid`=VALUES(`cldbid`),`count`=VALUES(`count`),`name`=VALUES(`name`),`lastseen`=VALUES(`lastseen`),`grpid`=VALUES(`grpid`),`nextup`=VALUES(`nextup`),`idle`=VALUES(`idle`),`cldgroup`=VALUES(`cldgroup`),`boosttime`=VALUES(`boosttime`),`platform`=VALUES(`platform`),`nation`=VALUES(`nation`),`version`=VALUES(`version`),`except`=VALUES(`except`),`grpsince`=VALUES(`grpsince`),`cid`=VALUES(`cid`),`online`=VALUES(`online`),`firstcon`=VALUES(`firstcon`);\n";
        unset($insertdata, $sqlinsertvalues);
    }

    enter_logfile(6, 'calc_user needs: '.(number_format(round((microtime(true) - $starttime), 5), 5)));

    return $sqlexec;
}
