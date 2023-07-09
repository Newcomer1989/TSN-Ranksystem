<?php

require_once '../other/_functions.php';
require_once '../other/config.php';

start_session($cfg);
$lang = set_language(get_language());

error_reporting(E_ALL);
ini_set('log_errors', 1);
set_error_handler('php_error_handling');
ini_set('error_log', $GLOBALS['logfile']);

header('Content-Type: application/json; charset=UTF-8');

if (isset($_GET['apikey'])) {
    $matchkey = 0;
    foreach ($cfg['stats_api_keys'] as $apikey => $desc) {
        if (hash_equals($apikey, $_GET['apikey'])) {
            $matchkey = 1;
        }
    }
    if ($matchkey == 0) {
        $json = [
            'Error' => [
                'invalid' => [
                    'apikey' => 'API Key is invalid',
                ],
            ],
        ];
        echo json_encode($json);
        exit;
    }
} else {
    $json = [
        'Error' => [
            'required' => [
                'apikey' => [
                    'desc' => 'API Key for authentification. API keys can be created inside the Ranksystem Webinterface',
                    'usage' => "Use \$_GET parameter 'apikey' and add as value a valid API key",
                    'example' => '/api/?apikey=XXXXX',
                ],
            ],
        ],
    ];
    echo json_encode($json);
    exit;
}

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 && $_GET['limit'] <= 1000) ? $_GET['limit'] : 100;
$sort = (isset($_GET['sort'])) ? htmlspecialchars_decode($_GET['sort']) : '1';
$order = (isset($_GET['order']) && strtolower($_GET['order']) == 'desc') ? 'DESC' : 'ASC';
$part = (isset($_GET['part']) && is_numeric($_GET['part']) && $_GET['part'] > 0) ? (($_GET['part'] - 1) * $limit) : 0;

if (isset($_GET['bot'])) {
    if (! isset($_GET['check']) && ! isset($_GET['restart']) && ! isset($_GET['start']) && ! isset($_GET['stop'])) {
        $json = [
            'usage' => [
                '_desc' => [
                    '0' => 'You are able to use bot commands with this function (start, stop, ..).',
                    '1' => 'Use the Parameter, which are described below!',
                    '2' => '',
                    '3' => 'Return values are:',
                    '4' => "- 'rc'",
                    '5' => "- 'msg'",
                    '6' => "- 'ranksystemlog'",
                    '7' => '',
                    '8' => '# RC',
                    '9' => 'The return Code of the transaction (i.e. start process):',
                    '10' => '0 - EXIT_SUCCESS',
                    '11' => '1 - EXIT_FAILURE',
                    '12' => '',
                    '13' => '# MSG',
                    '14' => 'An additional message of the process. In case of EXIT_FAILURE, you will receive here an error message.',
                    '15' => '',
                    '16' => '# RANKSYSTEMLOG',
                    '17' => 'A short log extract of the last rows of the Ranksystem logfile to get more information about the Bot itself.',
                ],
                'check' => [
                    'desc' => 'Check the Ranksystem Bot is running. If not, it will be started with this.',
                    'usage' => "Use \$_GET parameter 'check' without any value",
                    'example' => '/api/?bot&check',
                ],
                'restart' => [
                    'desc' => 'Restarts the Ranksystem Bot.',
                    'usage' => "Use \$_GET parameter 'restart' without any value",
                    'example' => '/api/?bot&restart',
                ],
                'start' => [
                    'desc' => 'Starts the Ranksystem Bot.',
                    'usage' => "Use \$_GET parameter 'start' without any value",
                    'example' => '/api/?bot&start',
                ],
                'stop' => [
                    'desc' => 'Stops the Ranksystem Bot',
                    'usage' => "Use \$_GET parameter 'stop' without any value",
                    'example' => '/api/?bot&stop',
                ],
            ],
        ];
    } else {
        $check_permission = 0;
        foreach ($cfg['stats_api_keys'] as $apikey => $desc) {
            if (hash_equals($apikey, $_GET['apikey']) && $desc['perm_bot'] == 1) {
                $check_permission = 1;
                break;
            }
        }
        if ($check_permission == 1) {
            if (isset($_GET['check'])) {
                $result = bot_check();
            } elseif (isset($_GET['restart'])) {
                $result = bot_restart();
            } elseif (isset($_GET['start'])) {
                $result = bot_start();
            } elseif (isset($_GET['stop'])) {
                $result = bot_stop();
            }
            if (isset($result['log']) && $result['log'] != null) {
                $ranksystemlog = $result['log'];
            } else {
                $ranksystemlog = 'NULL';
            }
            $json = [
                'rc' => $result['rc'],
                'msg' => $result['msg'],
                'ranksystemlog' => $ranksystemlog,
            ];
        } else {
            $json = [
                'Error' => [
                    'invalid' => [
                        'permissions' => 'API Key is not permitted to start/stop the Ranksystem Bot',
                    ],
                ],
            ];
            echo json_encode($json);
            exit;
        }
    }
} elseif (isset($_GET['groups'])) {
    $sgidname = $all = '----------_none_selected_----------';
    $sgid = -1;
    if (isset($_GET['all'])) {
        $all = 1;
    }
    if (isset($_GET['sgid'])) {
        $sgid = htmlspecialchars_decode($_GET['sgid']);
    }
    if (isset($_GET['sgidname'])) {
        $sgidname = htmlspecialchars_decode($_GET['sgidname']);
    }

    if ($sgid == -1 && $sgidname == '----------_none_selected_----------' && $all == '----------_none_selected_----------') {
        $json = [
            'usage' => [
                'all' => [
                    'desc' => 'Get details about all TeamSpeak servergroups',
                    'usage' => "Use \$_GET parameter 'all' without any value",
                    'example' => '/api/?groups&all',
                ],
                'limit' => [
                    'desc' => 'Define a number that limits the number of results. Maximum value is 1000. Default is 100.',
                    'usage' => "Use \$_GET parameter 'limit' and add as value a number above 1",
                    'example' => '/api/?groups&limit=10',
                ],
                'order' => [
                    'desc' => "Define a sorting order. Value of 'sort' param is necessary.",
                    'usage' => "Use \$_GET parameter 'order' and add as value 'asc' for ascending or 'desc' for descending",
                    'example' => '/api/?groups&all&sort=sgid&order=asc',
                ],
                'sgid' => [
                    'desc' => 'Get details about TeamSpeak servergroups by the servergroup TS-database-ID',
                    'usage' => "Use \$_GET parameter 'sgid' and add as value the servergroup TS-database-ID",
                    'example' => '/api/?groups&sgid=123',
                ],
                'sgidname' => [
                    'desc' => 'Get details about TeamSpeak servergroups by servergroup name or a part of it',
                    'usage' => "Use \$_GET parameter 'sgidname' and add as value a name or a part of it",
                    'example' => [
                        '1' => [
                            'desc' => 'Filter by servergroup name',
                            'url' => '/api/?groups&sgidname=Level01',
                        ],
                        '2' => [
                            'desc' => 'Filter by servergroup name with a percent sign as placeholder',
                            'url' => '/api/?groups&sgidname=Level%',
                        ],
                    ],
                ],
                'sort' => [
                    'desc' => 'Define a sorting. Available is each column name, which is given back as a result.',
                    'usage' => "Use \$_GET parameter 'sort' and add as value a column name",
                    'example' => [
                        '1' => [
                            'desc' => 'Sort by servergroup name',
                            'url' => '/api/?groups&all&sort=sgidname',
                        ],
                        '2' => [
                            'desc' => 'Sort by TeamSpeak sort-ID',
                            'url' => '/api/?groups&all&sort=sortid',
                        ],
                    ],
                ],
            ],
        ];
    } else {
        if ($all == 1) {
            $dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`groups` ORDER BY {$sort} {$order} LIMIT :start, :limit");
        } else {
            $dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`groups` WHERE (`sgidname` LIKE :sgidname OR `sgid` LIKE :sgid) ORDER BY {$sort} {$order} LIMIT :start, :limit");
            $dbdata->bindValue(':sgidname', '%'.$sgidname.'%', PDO::PARAM_STR);
            $dbdata->bindValue(':sgid', (int) $sgid, PDO::PARAM_INT);
        }
        $dbdata->bindValue(':start', (int) $part, PDO::PARAM_INT);
        $dbdata->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $dbdata->execute();
        $json = $dbdata->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
        foreach ($json as $sgid => $sqlpart) {
            if ($sqlpart['icondate'] != 0 && $sqlpart['sgidname'] == 'ServerIcon') {
                $json[$sgid]['iconpath'] = './tsicons/servericon.'.$sqlpart['ext'];
            } elseif ($sqlpart['icondate'] == 0 && $sqlpart['iconid'] > 0 && $sqlpart['iconid'] < 601) {
                $json[$sgid]['iconpath'] = './tsicons/'.$sqlpart['iconid'].'.'.$sqlpart['ext'];
            } elseif ($sqlpart['icondate'] != 0) {
                $json[$sgid]['iconpath'] = './tsicons/'.$sgid.'.'.$sqlpart['ext'];
            } else {
                $json[$sgid]['iconpath'] = '';
            }
        }
    }
} elseif (isset($_GET['rankconfig'])) {
    $dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`cfg_params` WHERE `param` in ('rankup_definition', 'rankup_time_assess_mode')");
    $dbdata->execute();
    $sql = $dbdata->fetchAll(PDO::FETCH_KEY_PAIR);
    $json = [];
    if ($sql['rankup_time_assess_mode'] == 1) {
        $modedesc = 'active time';
    } else {
        $modedesc = 'online time';
    }
    $json['rankup_time_assess_mode'] = [
        'mode' => $sql['rankup_time_assess_mode'],
        'mode_desc' => $modedesc,
    ];
    $count = 0;
    foreach (explode(',', $sql['rankup_definition']) as $entry) {
        list($key, $value) = explode('=>', $entry);
        $addnewvalue1[$count] = [
            'grpid' => $value,
            'seconds' => $key,
        ];
        $count++;
        $json['rankup_definition'] = $addnewvalue1;
    }
} elseif (isset($_GET['server'])) {
    $dbdata = $mysqlcon->prepare("SELECT 0 as `row`, `$dbname`.`stats_server`.* FROM `$dbname`.`stats_server`");
    $dbdata->execute();
    $json = $dbdata->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
} elseif (isset($_GET['user'])) {
    $filter = ' WHERE';
    if (isset($_GET['cldbid'])) {
        $cldbid = htmlspecialchars_decode($_GET['cldbid']);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `cldbid` LIKE :cldbid';
    }
    if (isset($_GET['groupid'])) {
        $groupid = htmlspecialchars_decode($_GET['groupid']);
        $explode_groupid = explode(',', $groupid);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' (';
        $cnt = 0;
        foreach ($explode_groupid as $groupid) {
            if ($cnt > 0) {
                $filter .= ' OR ';
            }
            $filter .= '`cldgroup` = :groupid'.$cnt;
            $cnt++;
            $filter .= ' OR `cldgroup` LIKE (:groupid'.$cnt.')';
            $cnt++;
            $filter .= ' OR `cldgroup` LIKE (:groupid'.$cnt.')';
            $cnt++;
            $filter .= ' OR `cldgroup` LIKE (:groupid'.$cnt.')';
            $cnt++;
        }
        $filter .= ')';
    }
    if (isset($_GET['name'])) {
        $name = htmlspecialchars_decode($_GET['name']);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `name` LIKE :name';
    }
    if (! isset($_GET['sort'])) {
        $sort = '`rank`';
    }
    if (isset($_GET['status']) && $_GET['status'] == strtolower('online')) {
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `online`=1';
    } elseif (isset($_GET['status']) && $_GET['status'] == strtolower('offline')) {
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `online`=0';
    }
    if (isset($_GET['uuid'])) {
        $uuid = htmlspecialchars_decode($_GET['uuid']);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `uuid` LIKE :uuid';
    }
    if ($filter == ' WHERE') {
        $filter = '';
    }

    if ($filter == '' && ! isset($_GET['all']) && ! isset($_GET['cldbid']) && ! isset($_GET['name']) && ! isset($_GET['uuid'])) {
        $json = [
            'usage' => [
                'all' => [
                    'desc' => 'Get details about all TeamSpeak user. Result is limited by 100 entries.',
                    'usage' => "Use \$_GET parameter 'all' without any value",
                    'example' => '/api/?user&all',
                ],
                'cldbid' => [
                    'desc' => 'Get details about TeamSpeak user by client TS-database ID',
                    'usage' => "Use \$_GET parameter 'cldbid' and add as value a single client TS-database ID",
                    'example' => '/api/?user&cldbid=7775',
                ],
                'groupid' => [
                    'desc' => 'Get only user, which are in the given servergroup database ID',
                    'usage' => "Use \$_GET parameter 'groupid' and add as value a database ID of a servergroup. Multiple servergroups can be specified comma-separated.",
                    'example' => [
                        '1' => [
                            'desc' => 'Filter by a single servergroup database ID',
                            'url' => '/api/?userstats&groupid=6',
                        ],
                        '2' => [
                            'desc' => 'Filter by multiple servergroup database IDs. Only one of the specified groups must apply to get the concerned user.',
                            'url' => '/api/?userstats&groupid=6,9,48',
                        ],
                    ],
                ],
                'limit' => [
                    'desc' => 'Define a number that limits the number of results. Maximum value is 1000. Default is 100.',
                    'usage' => "Use \$_GET parameter 'limit' and add as value a number above 1",
                    'example' => '/api/?user&all&limit=10',
                ],
                'name' => [
                    'desc' => 'Get details about TeamSpeak user by client nickname',
                    'usage' => "Use \$_GET parameter 'name' and add as value a name or a part of it",
                    'example' => [
                        '1' => [
                            'desc' => 'Filter by client nickname',
                            'url' => '/api/?user&name=Newcomer1989',
                        ],
                        '2' => [
                            'desc' => 'Filter by client nickname with a percent sign as placeholder',
                            'url' => '/api/?user&name=%user%',
                        ],
                    ],
                ],
                'order' => [
                    'desc' => 'Define a sorting order.',
                    'usage' => "Use \$_GET parameter 'order' and add as value 'asc' for ascending or 'desc' for descending",
                    'example' => '/api/?user&all&order=asc',
                ],
                'part' => [
                    'desc' => 'Define, which part of the result you want to get. This is needed, when more then 100 clients are inside the result. At default you will get the first 100 clients. To get the next 100 clients, you will need to ask for part 2.',
                    'usage' => "Use \$_GET parameter 'part' and add as value a number above 1",
                    'example' => '/api/?user&name=TeamSpeakUser&part=2',
                ],
                'sort' => [
                    'desc' => 'Define a sorting. Available is each column name, which is given back as a result.',
                    'usage' => "Use \$_GET parameter 'sort' and add as value a column name",
                    'example' => [
                        '1' => [
                            'desc' => 'Sort by online time',
                            'url' => '/api/?user&all&sort=count',
                        ],
                        '2' => [
                            'desc' => 'Sort by active time',
                            'url' => '/api/?user&all&sort=(count-idle)',
                        ],
                        '3' => [
                            'desc' => 'Sort by rank',
                            'url' => '/api/?user&all&sort=rank',
                        ],
                    ],
                ],
                'status' => [
                    'desc' => 'List only clients, which status is online or offline.',
                    'usage' => "Use \$_GET parameter 'status' and add as value 'online' or 'offline'",
                    'example' => '/api/?userstats&status=online',
                ],
                'uuid' => [
                    'desc' => 'Get details about TeamSpeak user by unique client ID',
                    'usage' => "Use \$_GET parameter 'uuid' and add as value one unique client ID or a part of it",
                    'example' => '/api/?user&uuid=xrTKhT/HDl4ea0WoFDQH2zOpmKg=',
                ],
            ],
        ];
    } else {
        $dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`user` {$filter} ORDER BY {$sort} {$order} LIMIT :start, :limit");
        if (isset($_GET['cldbid'])) {
            $dbdata->bindValue(':cldbid', (int) $cldbid, PDO::PARAM_INT);
        }
        if (isset($_GET['groupid'])) {
            $groupid = htmlspecialchars_decode($_GET['groupid']);
            $explode_groupid = explode(',', $groupid);
            $cnt = 0;
            foreach ($explode_groupid as $groupid) {
                $dbdata->bindValue(':groupid'.$cnt, $groupid, PDO::PARAM_STR);
                $cnt++;
                $dbdata->bindValue(':groupid'.$cnt, $groupid.',%', PDO::PARAM_STR);
                $cnt++;
                $dbdata->bindValue(':groupid'.$cnt, '%,'.$groupid.',%', PDO::PARAM_STR);
                $cnt++;
                $dbdata->bindValue(':groupid'.$cnt, '%,'.$groupid, PDO::PARAM_STR);
                $cnt++;
            }
        }
        if (isset($_GET['name'])) {
            $dbdata->bindValue(':name', '%'.$name.'%', PDO::PARAM_STR);
        }
        if (isset($_GET['uuid'])) {
            $dbdata->bindValue(':uuid', '%'.$uuid.'%', PDO::PARAM_STR);
        }

        $dbdata->bindValue(':start', (int) $part, PDO::PARAM_INT);
        $dbdata->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $dbdata->execute();
        $json = $dbdata->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
    }
} elseif (isset($_GET['userstats'])) {
    $filter = ' WHERE';
    if (isset($_GET['cldbid'])) {
        $cldbid = htmlspecialchars_decode($_GET['cldbid']);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `cldbid` LIKE :cldbid';
    }
    if (isset($_GET['groupid'])) {
        $groupid = htmlspecialchars_decode($_GET['groupid']);
        $explode_groupid = explode(',', $groupid);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' (';
        $cnt = 0;
        foreach ($explode_groupid as $groupid) {
            if ($cnt > 0) {
                $filter .= ' OR ';
            }
            $filter .= '`user`.`cldgroup` = :groupid'.$cnt;
            $cnt++;
            $filter .= ' OR `user`.`cldgroup` LIKE (:groupid'.$cnt.')';
            $cnt++;
            $filter .= ' OR `user`.`cldgroup` LIKE (:groupid'.$cnt.')';
            $cnt++;
            $filter .= ' OR `user`.`cldgroup` LIKE (:groupid'.$cnt.')';
            $cnt++;
        }
        $filter .= ')';
    }
    if (isset($_GET['name'])) {
        $name = htmlspecialchars_decode($_GET['name']);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `user`.`name` LIKE :name';
    }
    if (! isset($_GET['sort'])) {
        $sort = '`count_week`';
    }
    if (isset($_GET['status']) && $_GET['status'] == strtolower('online')) {
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `user`.`online`=1';
    } elseif (isset($_GET['status']) && $_GET['status'] == strtolower('offline')) {
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `user`.`online`=0';
    }
    if (isset($_GET['uuid'])) {
        $uuid = htmlspecialchars_decode($_GET['uuid']);
        if ($filter != ' WHERE') {
            $filter .= ' AND';
        }
        $filter .= ' `user`.`uuid` LIKE :uuid';
    }
    if ($filter == ' WHERE') {
        $filter = '';
    }

    if ($filter == '' && ! isset($_GET['all']) && ! isset($_GET['cldbid']) && ! isset($_GET['name']) && ! isset($_GET['uuid'])) {
        $json = [
            'usage' => [
                'all' => [
                    'desc' => 'Get additional statistics about all TeamSpeak user. Result is limited by 100 entries.',
                    'usage' => "Use \$_GET parameter 'all' without any value",
                    'example' => '/api/?userstats&all',
                ],
                'cldbid' => [
                    'desc' => 'Get details about TeamSpeak user by client TS-database ID',
                    'usage' => "Use \$_GET parameter 'cldbid' and add as value a single client TS-database ID",
                    'example' => '/api/?userstats&cldbid=7775',
                ],
                'groupid' => [
                    'desc' => 'Get only user, which are in the given servergroup database ID',
                    'usage' => "Use \$_GET parameter 'groupid' and add as value a database ID of a servergroup. Multiple servergroups can be specified comma-separated.",
                    'example' => [
                        '1' => [
                            'desc' => 'Filter by a single servergroup database ID',
                            'url' => '/api/?userstats&groupid=6',
                        ],
                        '2' => [
                            'desc' => 'Filter by multiple servergroup database IDs. Only one of the specified groups must apply to get the concerned user.',
                            'url' => '/api/?userstats&groupid=6,9,48',
                        ],
                    ],
                ],
                'limit' => [
                    'desc' => 'Define a number that limits the number of results. Maximum value is 1000. Default is 100.',
                    'usage' => "Use \$_GET parameter 'limit' and add as value a number above 1",
                    'example' => '/api/?userstats&limit=10',
                ],
                'name' => [
                    'desc' => 'Get details about TeamSpeak user by client nickname',
                    'usage' => "Use \$_GET parameter 'name' and add as value a name or a part of it",
                    'example' => [
                        '1' => [
                            'desc' => 'Filter by client nickname',
                            'url' => '/api/?userstats&name=Newcomer1989',
                        ],
                        '2' => [
                            'desc' => 'Filter by client nickname with a percent sign as placeholder',
                            'url' => '/api/?userstats&name=%user%',
                        ],
                    ],
                ],
                'order' => [
                    'desc' => 'Define a sorting order.',
                    'usage' => "Use \$_GET parameter 'order' and add as value 'asc' for ascending or 'desc' for descending",
                    'example' => '/api/?userstats&all&order=asc',
                ],
                'part' => [
                    'desc' => 'Define, which part of the result you want to get. This is needed, when more then 100 clients are inside the result. At default you will get the first 100 clients. To get the next 100 clients, you will need to ask for part 2.',
                    'usage' => "Use \$_GET parameter 'part' and add as value a number above 1",
                    'example' => '/api/?userstats&all&part=2',
                ],
                'sort' => [
                    'desc' => 'Define a sorting. Available is each column name, which is given back as a result.',
                    'usage' => "Use \$_GET parameter 'sort' and add as value a column name",
                    'example' => [
                        '1' => [
                            'desc' => 'Sort by online time of the week',
                            'url' => '/api/?userstats&all&sort=count_week',
                        ],
                        '2' => [
                            'desc' => 'Sort by active time of the week',
                            'url' => '/api/?userstats&all&sort=(count_week-idle_week)',
                        ],
                        '3' => [
                            'desc' => 'Sort by online time of the month',
                            'url' => '/api/?userstats&all&sort=count_month',
                        ],
                    ],
                ],
                'status' => [
                    'desc' => 'List only clients, which status is online or offline.',
                    'usage' => "Use \$_GET parameter 'status' and add as value 'online' or 'offline'",
                    'example' => '/api/?userstats&status=online',
                ],
                'uuid' => [
                    'desc' => 'Get additional statistics about TeamSpeak user by unique client ID',
                    'usage' => "Use \$_GET parameter 'uuid' and add as value one unique client ID or a part of it",
                    'example' => '/api/?userstats&uuid=xrTKhT/HDl4ea0WoFDQH2zOpmKg=',
                ],
            ],
        ];
    } else {
        $dbdata = $mysqlcon->prepare("SELECT * FROM `$dbname`.`stats_user` INNER JOIN `$dbname`.`user` ON `user`.`uuid` = `stats_user`.`uuid` {$filter} ORDER BY {$sort} {$order} LIMIT :start, :limit");
        if (isset($_GET['cldbid'])) {
            $dbdata->bindValue(':cldbid', (int) $cldbid, PDO::PARAM_INT);
        }
        if (isset($_GET['groupid'])) {
            $groupid = htmlspecialchars_decode($_GET['groupid']);
            $explode_groupid = explode(',', $groupid);
            $cnt = 0;
            foreach ($explode_groupid as $groupid) {
                $dbdata->bindValue(':groupid'.$cnt, $groupid, PDO::PARAM_STR);
                $cnt++;
                $dbdata->bindValue(':groupid'.$cnt, $groupid.',%', PDO::PARAM_STR);
                $cnt++;
                $dbdata->bindValue(':groupid'.$cnt, '%,'.$groupid.',%', PDO::PARAM_STR);
                $cnt++;
                $dbdata->bindValue(':groupid'.$cnt, '%,'.$groupid, PDO::PARAM_STR);
                $cnt++;
            }
        }
        if (isset($_GET['name'])) {
            $dbdata->bindValue(':name', '%'.$name.'%', PDO::PARAM_STR);
        }
        if (isset($_GET['uuid'])) {
            $dbdata->bindValue(':uuid', '%'.$uuid.'%', PDO::PARAM_STR);
        }

        $dbdata->bindValue(':start', (int) $part, PDO::PARAM_INT);
        $dbdata->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $dbdata->execute();
        $json = $dbdata->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
    }
} else {
    $json = [
        'usage' => [
            'bot' => [
                'desc' => 'Use this to trigger Bot commands as starting or stopping the Ranksystem Bot.',
                'usage' => "Use \$_GET parameter 'bot'",
                'example' => '/api/?bot',
            ],
            'groups' => [
                'desc' => 'Get details about the TeamSpeak servergroups',
                'usage' => "Use \$_GET parameter 'groups'",
                'example' => '/api/?groups',
            ],
            'rankconfig' => [
                'desc' => 'Get the rankup definition, which contains the assignment of (needed) time to servergroup',
                'usage' => "Use \$_GET parameter 'rankconfig'",
                'example' => '/api/?rankconfig',
            ],
            'server' => [
                'desc' => 'Get details about the TeamSpeak server',
                'usage' => "Use \$_GET parameter 'server'",
                'example' => '/api/?server',
            ],
            'user' => [
                'desc' => 'Get details about the TeamSpeak user',
                'usage' => "Use \$_GET parameter 'user'",
                'example' => '/api/?user',
            ],
            'userstats' => [
                'desc' => 'Get additional statistics about the TeamSpeak user',
                'usage' => "Use \$_GET parameter 'userstats'",
                'example' => '/api/?userstats',
            ],
        ],
    ];
}

echo json_encode($json);
