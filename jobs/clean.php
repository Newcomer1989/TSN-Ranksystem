<?php

function clean($ts3, $mysqlcon, $lang, $cfg, $dbname, &$db_cache)
{
    $starttime = microtime(true);
    $nowtime = time();
    $sqlexec = '';

    // clean old clients out of the database
    if (intval($db_cache['job_check']['clean_clients']['timestamp']) < ($nowtime - $cfg['rankup_clean_clients_period'])) {
        $db_cache['job_check']['clean_clients']['timestamp'] = $nowtime;
        if ($cfg['rankup_clean_clients_switch'] == 1) {
            enter_logfile(4, $lang['clean']);
            $start = $countdel = $countts = 0;
            $break = 200;
            $count_tsuser['count'] = 0;
            $clientdblist = [];
            enter_logfile(5, '  Get TS3 Clientlist...');
            while ($getclientdblist = $ts3->clientListDb($start, $break)) {
                check_shutdown();
                $clientdblist = array_merge($clientdblist, $getclientdblist);
                $start = $start + $break;
                $count_tsuser = array_shift($getclientdblist);
                enter_logfile(6, '    Got TS3 Clientlist '.count($clientdblist).' of '.$count_tsuser['count'].' Clients.');
                if ($count_tsuser['count'] <= $start) {
                    break;
                }
                usleep($cfg['teamspeak_query_command_delay']);
            }
            enter_logfile(5, '  Get TS3 Clientlist [DONE]');
            foreach ($clientdblist as $uuidts) {
                $single_uuid = $uuidts['client_unique_identifier']->toString();
                $uidarrts[$single_uuid] = 1;
            }
            unset($clientdblist,$getclientdblist,$start,$break,$single_uuid);

            foreach ($db_cache['all_user'] as $uuid => $value) {
                if (isset($uidarrts[$uuid])) {
                    $countts++;
                } else {
                    $deleteuuids[] = $uuid;
                    $countdel++;
                }
            }
            enter_logfile(4, '  '.sprintf($lang['cleants'], $countts, $count_tsuser['count']));
            enter_logfile(4, '  '.sprintf($lang['cleanrs'], count($db_cache['all_user'])));
            unset($uidarrts,$count_tsuser,$countts);
            if (isset($deleteuuids)) {
                $alldeldata = '';
                $fsfilelist = opendir(dirname(__DIR__).DIRECTORY_SEPARATOR.'avatars');
                while (false !== ($fsfile = readdir($fsfilelist))) {
                    if ($fsfile != '.' && $fsfile != '..') {
                        $fsfilelistarray[$fsfile] = filemtime(dirname(__DIR__).DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR.$fsfile);
                    }
                }
                unset($fsfilelist,$fsfile);
                $avatarfilepath = dirname(__DIR__).DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR;
                $convert = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p'];
                foreach ($deleteuuids as $uuid) {
                    $alldeldata = $alldeldata."'".$uuid."',";
                    $uuidasbase16 = '';
                    for ($i = 0; $i < 20; $i++) {
                        $char = ord(substr(base64_decode($uuid), $i, 1));
                        $uuidasbase16 .= $convert[($char & 0xF0) >> 4];
                        $uuidasbase16 .= $convert[$char & 0x0F];
                    }
                    if (isset($fsfilelistarray[$uuidasbase16.'.png'])) {
                        if (unlink($avatarfilepath.$uuidasbase16.'.png') === false) {
                            enter_logfile(2, '  '.sprintf($lang['clean0002'], $uuidasbase16, $uuid).' '.sprintf($lang['errperm'], 'avatars'));
                        } else {
                            enter_logfile(4, '  '.sprintf($lang['clean0001'], $uuidasbase16, $uuid));
                        }
                    }
                }
                unset($deleteuuids,$avatarfilepath,$convert,$uuidasbase16);
                $alldeldata = substr($alldeldata, 0, -1);
                $alldeldata = '('.$alldeldata.')';
                if ($alldeldata != '') {
                    $sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_clients';\nUPDATE `$dbname`.`stats_user` AS `t` LEFT JOIN `$dbname`.`user` AS `u` ON `t`.`uuid`=`u`.`uuid` SET `t`.`removed`='1' WHERE `u`.`uuid` IS NULL;\nDELETE FROM `$dbname`.`user` WHERE `uuid` IN $alldeldata;\n";
                    enter_logfile(4, '  '.sprintf($lang['cleandel'], $countdel));
                    unset($$alldeldata);
                }
            } else {
                enter_logfile(4, '  '.$lang['cleanno']);
                $sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_clients';\n";
            }
        } else {
            enter_logfile(4, $lang['clean0004']);
            $sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_clients';\n";
        }
    }

    // clean usersnaps older then 1 month + clean old server usage - older then a year
    if (intval($db_cache['job_check']['clean_db']['timestamp']) < ($nowtime - 86400)) {
        $db_cache['job_check']['clean_db']['timestamp'] = $nowtime;
        $sqlexec .= "DELETE FROM `$dbname`.`server_usage` WHERE `timestamp` < (UNIX_TIMESTAMP() - 31536000);\nDELETE `b` FROM `$dbname`.`user` AS `a` RIGHT JOIN `$dbname`.`stats_user` AS `b` ON `a`.`uuid`=`b`.`uuid` WHERE `a`.`uuid` IS NULL;\nUPDATE `$dbname`.`job_check` SET `timestamp`='{$nowtime}' WHERE `job_name`='clean_db';\nDELETE FROM `$dbname`.`csrf_token` WHERE `timestamp` < (UNIX_TIMESTAMP() - 3600);\nDELETE `h` FROM `$dbname`.`user_iphash` AS `h` LEFT JOIN `$dbname`.`user` AS `u` ON `u`.`uuid` = `h`.`uuid` WHERE (`u`.`uuid` IS NULL OR `u`.`online`!=1);\n";
        enter_logfile(4, $lang['clean0003']);
    }

    // clean user_iphash
    if (intval($db_cache['job_check']['clean_user_iphash']['timestamp']) < ($nowtime - 3500)) {
        if (($sqlhashs = $mysqlcon->query("SELECT * FROM `$dbname`.`user_iphash`")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE)) === false) {
            enter_logfile(2, 'clean user_iphash:'.print_r($mysqlcon->errorInfo(), true));
        }

        $rem_uuids = '';
        foreach ($sqlhashs as $uuid => $values) {
            if (isset($db_cache['all_user'][$uuid]) && $db_cache['all_user'][$uuid]['lastseen'] < ($nowtime - 100)) {
                $rem_uuids .= "'".$uuid."',";
            }
        }
        if ($rem_uuids != '') {
            $rem_uuids = substr($rem_uuids, 0, -1);
            $sqlexec .= "DELETE FROM `$dbname`.`user_iphash` WHERE `uuid` IN ({$rem_uuids});\n";
        }
        $sqlexec .= "UPDATE `$dbname`.`job_check` SET `timestamp`='$nowtime' WHERE `job_name`='clean_user_iphash';\n";
    }

    enter_logfile(6, 'clean needs: '.(number_format(round((microtime(true) - $starttime), 5), 5)));

    return $sqlexec;
}
