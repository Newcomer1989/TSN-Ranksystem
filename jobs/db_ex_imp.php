<?php

function db_ex_imp($ts3, $mysqlcon, $lang, $cfg, $dbname, &$db_cache)
{
    $starttime = microtime(true);
    //enter_logfile(5,"  started DB Export");

    if (in_array(intval($db_cache['job_check']['database_export']['timestamp']), [1, 2])) {
        enter_logfile(4, 'DB Export job(s) started');

        $err = 0;

        if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='2' WHERE `job_name`='database_export';") === false) {
            $err++;
            enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
        } else {
            $db_cache['job_check']['database_export']['timestamp'] = 2;
            enter_logfile(4, "  Started job '".$lang['wihladmex']."'");
        }

        $datetime = date('Y-m-d_H-i-s', time());
        $filepath = $GLOBALS['logpath'].'db_export_'.$datetime;
        $filename = 'db_export_'.$datetime;
        $limit_entries = 10000;

        if (($tables = $mysqlcon->query('SHOW TABLES')->fetchALL(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC)) === false) {
            $err++;
            enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
        } else {
            $dump = fopen($filepath.'.sql', 'a');
            fwrite($dump, "-- Ranksystem SQL Export\n-- from $datetime\n\nSTART TRANSACTION;\n\n");
            fclose($dump);

            foreach ($tables as $table => $value) {
                $out = '';
                if (substr($table, 0, 4) == 'bak_') {
                    continue;
                }
                //if($table == 'user_snapshot') continue;
                if (($numColumns = $mysqlcon->query("SELECT * FROM `$dbname`.`$table` LIMIT 1")->columnCount()) === false) {
                    $err++;
                    enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
                }

                $out .= 'DROP TABLE IF EXISTS `'.$table.'`;'."\n\n";

                if (($create_table = $mysqlcon->query("SHOW CREATE TABLE `$dbname`.`$table`")->fetch(PDO::FETCH_ASSOC)) === false) {
                    $err++;
                    enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
                }
                $out .= $create_table['Create Table'].';'."\n\n";

                if (($maxvalues = $mysqlcon->query("SELECT COUNT(*) FROM `$dbname`.`$table`;")->fetch()) === false) {
                    $err++;
                    enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
                }

                $dump = fopen($filepath.'.sql', 'a');
                fwrite($dump, $out);
                fclose($dump);

                unset($out);

                $loops = $maxvalues[0] / $limit_entries;
                for ($i = 0; $i <= $loops; $i++) {
                    $out = '';
                    $offset = $i * $limit_entries;
                    if (($sqldata = $mysqlcon->query("SELECT * FROM `$dbname`.`$table` LIMIT {$limit_entries} OFFSET {$offset}")->fetchALL(PDO::FETCH_NUM)) === false) {
                        $err++;
                        enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
                    }

                    if (count($sqldata) != 0) {
                        $out .= "INSERT INTO `$table` VALUES";
                        foreach ($sqldata as $row) {
                            $out .= '(';
                            for ($j = 0; $j < $numColumns; $j++) {
                                if (isset($row[$j])) {
                                    $out .= $mysqlcon->quote(($row[$j]), ENT_QUOTES);
                                } else {
                                    $out .= '""';
                                }
                                if ($j < ($numColumns - 1)) {
                                    $out .= ',';
                                }
                            }
                            $out .= '),';
                        }
                        $out = substr($out, 0, -1);
                        $out .= ';';
                    }
                    $out .= "\n\n";

                    $dump = fopen($filepath.'.sql', 'a');
                    fwrite($dump, $out);
                    fclose($dump);

                    unset($out, $sqldata);

                    if (($job_status = $mysqlcon->query("SELECT `timestamp` FROM `$dbname`.`job_check` WHERE `job_name`='database_export';")->fetch(PDO::FETCH_ASSOC)) === false) {
                        $err++;
                        enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
                    } elseif ($job_status['timestamp'] == 3) {
                        $db_cache['job_check']['database_export']['timestamp'] = 3;
                        enter_logfile(4, 'DB Export job(s) canceled by request');
                        $dump = fopen($filepath.'.sql', 'a');
                        fwrite($dump, "\nROLLBACK;\n\n-- Canceled export");
                        fclose($dump);

                        return;
                    }
                }
            }

            $dump = fopen($filepath.'.sql', 'a');
            fwrite($dump, "COMMIT;\n\n-- Finished export");
            fclose($dump);

            $zip = new ZipArchive();
            if ($zip->open($filepath.'.sql.zip', ZipArchive::CREATE) !== true) {
                $err++;
                enter_logfile(2, "  Cannot create $filepath.sql.zip!");
            } else {
                $zip->addFile($filepath.'.sql', $filename.'.sql');
                if (version_compare(phpversion(), '7.2', '>=') && version_compare(phpversion('zip'), '1.2.0', '>=')) {
                    try {
                        $zip->setEncryptionName($filename.'.sql', ZipArchive::EM_AES_256, $cfg['teamspeak_query_pass']);
                    } catch (Exception $e) {
                        enter_logfile(2, '  Error due creating secured zip-File: '.$e->getCode().': '.$e->getMessage().' ..Update PHP to Version 7.2 or above and update libzip to version 1.2.0 or above.');
                    }
                }
                $zip->close();
                if (! unlink($filepath.'.sql')) {
                    $err++;
                    enter_logfile(2, "  Cannot remove SQL file $filepath.sql!");
                }
            }
        }

        if ($err == 0) {
            if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='4' WHERE `job_name`='database_export';") === false) {
                enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
            } else {
                $db_cache['job_check']['database_export']['timestamp'] = 4;
                enter_logfile(4, "  Finished job '".$lang['wihladmex']."'");
            }
        } else {
            if ($mysqlcon->exec("UPDATE `$dbname`.`job_check` SET `timestamp`='3' WHERE `job_name`='database_export';") === false) {
                enter_logfile(2, '  Executing SQL commands failed: '.print_r($mysqlcon->errorInfo(), true));
            } else {
                $db_cache['job_check']['database_export']['timestamp'] = 3;
            }
        }

        enter_logfile(4, 'DB Export job(s) finished');
    }
    enter_logfile(6, 'db_ex_imp needs: '.(number_format(round((microtime(true) - $starttime), 5), 5)));
}
