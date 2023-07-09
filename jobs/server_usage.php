<?php

function server_usage($mysqlcon, $cfg, $dbname, $serverinfo, &$db_cache)
{
    $starttime = microtime(true);
    $nowtime = time();
    $sqlexec = '';

    // Stats for Server Usage
    if (key($db_cache['max_timestamp_server_usage']) === '' || ($nowtime - key($db_cache['max_timestamp_server_usage'])) > 898) { // every 15 mins
        unset($db_cache['max_timestamp_server_usage']);
        $db_cache['max_timestamp_server_usage'][$nowtime] = '';

        //Calc time next rankup
        enter_logfile(6, 'Calc next rankup for offline user');
        $upnextuptime = $nowtime - 1800;
        $server_used_slots = $serverinfo['virtualserver_clientsonline'] - $serverinfo['virtualserver_queryclientsonline'];
        if (($uuidsoff = $mysqlcon->query("SELECT `uuid`,`idle`,`count` FROM `$dbname`.`user` WHERE `online`<>1 AND `lastseen`>$upnextuptime")->fetchAll(PDO::FETCH_ASSOC)) === false) {
            enter_logfile(2, 'calc_serverstats 13:'.print_r($mysqlcon->errorInfo(), true));
        }
        if (count($uuidsoff) != 0) {
            foreach ($uuidsoff as $uuid) {
                $count = $uuid['count'];
                if ($cfg['rankup_time_assess_mode'] == 1) {
                    $activetime = $count - $uuid['idle'];
                    $dtF = new DateTime('@0');
                    $dtT = new DateTime('@'.round($activetime));
                } else {
                    $activetime = $count;
                    $dtF = new DateTime('@0');
                    $dtT = new DateTime('@'.round($count));
                }
                $grpcount = 0;
                foreach ($cfg['rankup_definition'] as $time => $dummy) {
                    $grpcount++;
                    if ($activetime > $time) {
                        if ($grpcount == 1) {
                            $nextup = 0;
                        }
                        break;
                    } else {
                        $nextup = $time - $activetime;
                    }
                }
                $updatenextup[] = [
                    'uuid' => $uuid['uuid'],
                    'nextup' => $nextup,
                ];
            }
            unset($uuidsoff);
        }

        if (isset($updatenextup)) {
            $allupdateuuid = $allupdatenextup = '';
            foreach ($updatenextup as $updatedata) {
                $allupdateuuid = $allupdateuuid."'".$updatedata['uuid']."',";
                $allupdatenextup = $allupdatenextup."WHEN '".$updatedata['uuid']."' THEN ".$updatedata['nextup'].' ';
            }
            $allupdateuuid = substr($allupdateuuid, 0, -1);
            $sqlexec .= "INSERT INTO `$dbname`.`server_usage` (`timestamp`,`clients`,`channel`) VALUES ($nowtime,$server_used_slots,{$serverinfo['virtualserver_channelsonline']});\nUPDATE `$dbname`.`user` SET `nextup`= CASE `uuid` $allupdatenextup END WHERE `uuid` IN ($allupdateuuid);\n";
            unset($allupdateuuid, $allupdatenextup);
        } else {
            $sqlexec .= "INSERT INTO `$dbname`.`server_usage` (`timestamp`,`clients`,`channel`) VALUES ($nowtime,$server_used_slots,{$serverinfo['virtualserver_channelsonline']});\n";
        }
        enter_logfile(6, 'Calc next rankup for offline user [DONE]');
    }

    enter_logfile(6, 'server_usage needs: '.(number_format(round((microtime(true) - $starttime), 5), 5)));

    return $sqlexec;
}
