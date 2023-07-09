<?php

function handle_messages(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
    global $lang,$cfg,$mysqlcon,$dbname,$phpcommand,$ts3,$whoami;
    enter_logfile(6, 'whoami: '.print_r($host->whoami(), true));
    if ($event['targetmode'] == 1) {
        $targetid = $event['invokerid'];
    } elseif ($event['targetmode'] == 2) {
        $targetid = $host->whoami()['client_channel_id'];
    } else {
        $targetid = null;
    }

    enter_logfile(6, 'event: '.print_r($event, true));

    if ($host->whoami()['client_id'] != $event['invokerid'] && substr($event['msg'], 0, 1) === '!') {
        $uuid = $event['invokeruid'];
        $admin = 0;
        foreach (array_flip($cfg['webinterface_admin_client_unique_id_list']) as $auuid) {
            if ($uuid == $auuid) {
                $admin = 1;
            }
        }

        enter_logfile(6, 'Client '.$event['invokername'].' ('.$event['invokeruid'].') sent textmessage: '.$event['msg']);

        if ((strstr($event['msg'], '!nextup') || strstr($event['msg'], '!next')) && $cfg['rankup_next_message_mode'] != 0) {
            if (($user = $mysqlcon->query("SELECT `count`,`nextup`,`idle`,`except`,`name`,`rank`,`grpsince`,`grpid` FROM `$dbname`.`user` WHERE `uuid`='$uuid'")->fetch()) === false) {
                enter_logfile(2, 'handle_messages 1:'.print_r($mysqlcon->errorInfo(), true));
            }

            if (($sqlhisgroup = $mysqlcon->query("SELECT `sgid`,`sgidname` FROM `$dbname`.`groups`")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE)) === false) {
                enter_logfile(2, 'handle_messages 2:'.print_r($mysqlcon->errorInfo(), true));
            }

            ksort($cfg['rankup_definition']);
            $countgrp = count($cfg['rankup_definition']);
            $grpcount = 0;

            foreach ($cfg['rankup_definition'] as $rank) {
                if ($cfg['rankup_time_assess_mode'] == 1) {
                    $nextup = $rank['time'] - $user['count'] + $user['idle'];
                } else {
                    $nextup = $rank['time'] - $user['count'];
                }
                $dtF = new DateTime('@0');
                $dtT = new DateTime('@'.round($nextup));
                $days = $dtF->diff($dtT)->format('%a');
                $hours = $dtF->diff($dtT)->format('%h');
                $mins = $dtF->diff($dtT)->format('%i');
                $secs = $dtF->diff($dtT)->format('%s');
                $name = $user['name'];
                $grpcount++;
                if ($nextup > 0 && $nextup < $rank['time'] || $grpcount == $countgrp && $nextup <= 0) {
                    if ($grpcount == $countgrp && $nextup <= 0) {
                        $msg = sprintf($cfg['rankup_next_message_2'], $days, $hours, $mins, $secs, $sqlhisgroup[$rank['group']]['sgidname'], $name, $user['rank'], $sqlhisgroup[$user['grpid']]['sgidname'], date('Y-m-d H:i:s', $user['grpsince']));
                    } elseif ($user['except'] == 2 || $user['except'] == 3) {
                        $msg = sprintf($cfg['rankup_next_message_3'], $days, $hours, $mins, $secs, $sqlhisgroup[$rank['group']]['sgidname'], $name, $user['rank'], $sqlhisgroup[$user['grpid']]['sgidname'], date('Y-m-d H:i:s', $user['grpsince']));
                    } else {
                        $msg = sprintf($cfg['rankup_next_message_1'], $days, $hours, $mins, $secs, $sqlhisgroup[$rank['group']]['sgidname'], $name, $user['rank'], $sqlhisgroup[$user['grpid']]['sgidname'], date('Y-m-d H:i:s', $user['grpsince']));
                    }
                    $targetid = $event['invokerid'];
                    sendmessage($host, $cfg, $event['invokeruid'], $msg, 1, $targetid);
                    if ($cfg['rankup_next_message_mode'] == 1) {
                        break;
                    }
                }
            }
            krsort($cfg['rankup_definition']);

            return;
        }

        if (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'version')) {
            if (version_compare($cfg['version_latest_available'], $cfg['version_current_using'], '>') && $cfg['version_latest_available'] != '') {
                sendmessage($host, $cfg, $event['invokeruid'], sprintf($lang['upmsg'], $cfg['version_current_using'], $cfg['version_latest_available'], 'https://ts-ranksystem.com/#changelog'), $event['targetmode'], $targetid);
            } else {
                sendmessage($host, $cfg, $event['invokeruid'], sprintf($lang['msg0001'], $cfg['version_current_using']), $event['targetmode'], $targetid);
            }

            return;
        }

        if (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'help') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'info') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'commands') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'cmd')) {
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0002'], $event['targetmode'], $targetid);

            return;
        }

        if (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'news')) {
            if (isset($cfg['teamspeak_news_bb']) && $cfg['teamspeak_news_bb'] != '') {
                sendmessage($host, $cfg, $event['invokeruid'], "Latest News:\n".$cfg['teamspeak_news_bb'], $event['targetmode'], $targetid);
            } else {
                sendmessage($host, $cfg, $event['invokeruid'], 'No News available yet.', $event['targetmode'], $targetid);
            }

            return;
        }

        if ((strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'shutdown') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'quit') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'stop')) && $admin == 1) {
            enter_logfile(5, sprintf($lang['msg0004'], $event['invokername'], $event['invokeruid']));
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0005'], $event['targetmode'], $targetid);
            if (substr(php_uname(), 0, 7) == 'Windows') {
                exec('start '.$phpcommand.' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.php stop');
            } else {
                exec($phpcommand.' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.php stop > /dev/null &');
            }
            file_put_contents($GLOBALS['autostart'], '');
            shutdown($mysql, $cfg, 4, 'Stop command received!');
        } elseif (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'shutdown') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'quit') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'stop')) {
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0003'], $event['targetmode'], $targetid);

            return;
        }

        if ((strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'restart') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'reboot')) && $admin == 1) {
            enter_logfile(5, sprintf($lang['msg0007'], $event['invokername'], $event['invokeruid'], 'restart'));
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0006'], $event['targetmode'], $targetid);
            if (substr(php_uname(), 0, 7) == 'Windows') {
                exec('start '.$phpcommand.' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.php restart');
            } else {
                exec($phpcommand.' '.dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.php restart > /dev/null 2>/dev/null &');
            }

            return;
        } elseif (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'restart') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'reboot')) {
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0003'], $event['targetmode'], $targetid);

            return;
        }

        if ((strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'checkupdate') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'update')) && $admin == 1) {
            if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='0' WHERE `job_name` IN ('check_update','get_version','calc_server_stats','calc_donut_chars')") === false) {
                enter_logfile(4, 'handle_messages 13:'.print_r($mysqlcon->errorInfo(), true));
            }
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0008'], $event['targetmode'], $targetid);

            return;
        } elseif (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'checkupdate') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'update')) {
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0003'], $event['targetmode'], $targetid);

            return;
        }

        if ((strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'clean')) && $admin == 1) {
            enter_logfile(5, sprintf($lang['msg0007'], $event['invokername'], $event['invokeruid'], 'clean'));
            if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='0' WHERE `job_name` IN ('clean_db','clean_clients')") === false) {
                enter_logfile(4, 'handle_messages 13:'.print_r($mysqlcon->errorInfo(), true));
            }
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0009'].' '.$lang['msg0010'], $event['targetmode'], $targetid);

            return;
        } elseif (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'clean')) {
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0003'], $event['targetmode'], $targetid);

            return;
        }

        if ((strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'reloadgroups') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'reloadicons')) && $admin == 1) {
            if ($mysqlcon->exec("DELETE FROM `$dbname`.`groups`") === false) {
                enter_logfile(4, 'handle_messages 14:'.print_r($mysqlcon->errorInfo(), true));
            } else {
                if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`=1 WHERE `job_name`='reload_trigger';") === false) {
                    enter_logfile(4, 'handle_messages 15:'.print_r($mysqlcon->errorInfo(), true));
                }
            }
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0011'].' '.$lang['msg0010'], $event['targetmode'], $targetid);

            return;
        } elseif (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'reloadgroups')) {
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0003'], $event['targetmode'], $targetid);

            return;
        }

        if (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'online') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'uptime')) {
            sendmessage($host, $cfg, $event['invokeruid'], sprintf('Bot is online since %s, now %s.', (DateTime::createFromFormat('U', $cfg['temp_last_botstart'])->setTimeZone(new DateTimeZone($cfg['logs_timezone']))->format('Y-m-d H:i:s')), (new DateTime('@0'))->diff(new DateTime('@'.(time() - $cfg['temp_last_botstart'])))->format($cfg['default_date_format'])), $event['targetmode'], $targetid);

            return;
        }

        if (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'runtime') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'runtimes')) {
            sendmessage($host, $cfg, $event['invokeruid'], sprintf("Last 10 runtimes (in seconds):\n%s\n\nØ %s sec. (Σ %s)", str_replace(';', "\n", $cfg['temp_last_laptime']), round(($cfg['temp_whole_laptime'] / $cfg['temp_count_laptime']), 5), $cfg['temp_count_laptime']), $event['targetmode'], $targetid);

            return;
        }

        if (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'memory')) {
            sendmessage($host, $cfg, $event['invokeruid'], sprintf("Allocated memory of PHP for the Ranksystem Bot..\ncurrent using: %s KiB\npeak using: %s KiB", round((memory_get_usage() / 1024), 2), round((memory_get_peak_usage() / 1024), 2)), $event['targetmode'], $targetid);

            return;
        }

        if ((strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'logs') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'log')) && $admin == 1) {
            $parameter = explode(' ', $event['msg']);
            if (isset($parameter[1]) && $parameter[1] > 0 && $parameter[1] < 1000) {
                $number_lines = $parameter[1];
            } else {
                $number_lines = 5;
            }
            $filters = explode(',', 'CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG,NONE');
            $filter2 = $lastfilter = '';
            $lines = [];
            if (file_exists($GLOBALS['logfile'])) {
                $fp = fopen($GLOBALS['logfile'], 'r');
                $buffer = [];
                while ($line = fgets($fp, 4096)) {
                    array_push($buffer, $line);
                }
                fclose($fp);
                $buffer = array_reverse($buffer);
                foreach ($buffer as $line) {
                    if (substr($line, 0, 2) != '20' && in_array($lastfilter, $filters)) {
                        array_push($lines, $line);
                        if (count($lines) >= $number_lines) {
                            break;
                        }
                        continue;
                    }
                    foreach ($filters as $filter) {
                        if (($filter != null && strstr($line, $filter) && $filter2 == null) || ($filter2 != null && strstr($line, $filter2) && $filter != null && strstr($line, $filter))) {
                            if ($filter == 'CRITICAL' || $filter == 'ERROR') {
                                array_push($lines, '[COLOR=red]'.$line.'[/COLOR]');
                            } else {
                                array_push($lines, $line);
                            }
                            $lastfilter = $filter;
                            if (count($lines) >= $number_lines) {
                                break 2;
                            }
                            break;
                        }
                    }
                }
            } else {
                $lines[] = "Perhaps the logfile got rotated or something goes wrong due opening the file!\n";
                $lines[] = "No log entry found...\n";
            }
            $lines = array_reverse($lines);
            $message = "\n";
            foreach ($lines as $line) {
                $message .= $line;
            }
            $targetid = $event['invokerid'];
            sendmessage($host, $cfg, $event['invokeruid'], $message, 1, $targetid, null, null, null, $nolog = 1);
        } elseif (strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'logs') || strstr($event['msg'], $cfg['teamspeak_chatcommand_prefix'].'log')) {
            sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0003'], $event['targetmode'], $targetid);
        }

        sendmessage($host, $cfg, $event['invokeruid'], $lang['msg0002'], $event['targetmode'], $targetid);
    }
}
