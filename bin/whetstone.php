<?php
require_once("../init.php");

function help()
{
    $helpDom = "=======================================" . PHP_EOL;
    $helpDom .= "==   Whetstone Controller Console   ==" . PHP_EOL;
    $helpDom .= "=======================================" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "CMD: php whetstone.php -c ../config/config.php start" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "Option:" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;
    $helpDom .= "\t-h\t\t查看帮助文件" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\t-c config.php\t 使用指定配置文件" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\t-v\t\t 开启debug模式" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "CMD:" . PHP_EOL;

    $helpDom .= "\tstart\t\t服务启动" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\tstop\t\t停止服务" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\tkill\t\t直接杀死服务" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\trestart\t\t重启服务" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\treload\t\t平滑reload服务" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "---------------------------------------" . PHP_EOL;

    echo $helpDom;
}

function parseArgvs($argv)
{
    $params = getopt('hvc:');
    $count  = count($argv);
    if ($argv[$count - 1] == '&') {
        $params['action'] = $argv[$count - 2];
    } else {
        $params['action'] = $argv[$count - 1];
    }
    return $params;
}


/////////////////////////
/// Main
////////////////////////

//check the swoole extension is exist
if (!extension_loaded('swoole')) {
    die('swoole extension was not found' . PHP_EOL);
}

//check version of swoole
if (swoole_version() < 4) {
    die('swoole extension version is wrong. you must run this on 4.x version' . PHP_EOL);
}

//parser argument
$params = parseArgvs($argv);

//config load
if (isset($params["c"]) && file_exists($params["c"])) {
    echo "loading special config:" . $params["c"] . PHP_EOL;
    require_once($params["c"]);
}

//debug mode
if (isset($params['v'])) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
} else {
    ini_set("display_errors", "Off");
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

$funcName = $params['action'];
switch ($funcName) {
    case 'start':
        {
            // 开启进程
            echo 'start:' . 'st_' . $config['server']['server_name'] . PHP_EOL;
            $server = new \WhetStone\Stone\Server($config);
            $server->start();
            break;
        }
    case 'stop':
        {
            // 给 master 进程发送信号量 15
            echo 'stop:' . 'st_' . $config['server']['server_name'] . PHP_EOL;
            $pid = file_get_contents($config['swoole']['pid_file']);
            if ($pid > 0) {
                $ret = swoole_process::kill($pid);
                echo "kill the server pid: " . $pid . " ret: " . $ret . PHP_EOL;
            } else {
                echo "pid file not found" . PHP_EOL;
            }
            break;
        }
    case 'reload':
        {
            // SIGUSR1
            echo 'reload:' . 'st_' . $config['server']['server_name'] . PHP_EOL;
            $pid = file_get_contents($config['swoole']['pid_file']);
            if ($pid > 0) {
                $cmd = "kill -s 10 $pid";
                $ret = exec($cmd, $outStr);
                echo "send signal to pid:" . $pid . " ret:" . $ret . " output:" . PHP_EOL;
                var_dump($outStr);
            } else {
                echo "pid file not found" . PHP_EOL;
            }

            break;
        }
    case 'restart':
        {
            // stop、start
            $pid = file_get_contents($config['swoole']['pid_file']);

            echo 'stop:' . 'st_' . $config['server']['server_name'] . PHP_EOL;
            $cmd = "kill  $pid";
            exec($cmd, $outStr);

            sleep(3);

            //reload pid
            echo 'start:' . 'st_' . $config['server']['server_name'] . PHP_EOL;
            $server = new \WhetStone\Stone\Server($config);
            $server->start();

            break;
        }
    case 'kill':
        {
            //暴力kill
            $name = 'st_' . $config['server']['server_name'];
            echo 'kill:' . 'st_' . $config['server']['server_name'] . PHP_EOL;
            $cmd = "ps -ef | grep $name | grep -v grep | cut -c 9-15 | xargs kill -s 9 ";
            exec($cmd, $outStr);
            break;
        }
}