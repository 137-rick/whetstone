<?php
require_once "../init.php";

function help()
{
    $helpDom = "=======================================" . PHP_EOL;
    $helpDom .= "==   Whetstone Controller Console   ==" . PHP_EOL;
    $helpDom .= "=======================================" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "CMD: php whetstone.php -c ../config/server.php start" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "Option:" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;
    $helpDom .= "\t-h\t\tthis help info" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\t-c config.php\t set config file" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\t-d\t\tshow php debug info on console,and no daemon,and swoole base mode. only for debug" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\t-p\t\tpid file path" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "CMD:" . PHP_EOL;

    $helpDom .= "\tstart\t\tstart server" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\tstop\t\tstop server" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\tkill\t\tkill all server process" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\trestart\t\trestart server" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "\treload\t\treload file for worker" . PHP_EOL;
    $helpDom .= "" . PHP_EOL;

    $helpDom .= "---------------------------------------" . PHP_EOL;

    echo $helpDom;
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

$extensionList = array(
    "pcntl",
    "mysqli",
    "memcached",
    "pdo_mysql",
    "mbstring",
    "json",
    "curl",
    "bcmath",
    "redis",
);
foreach ($extensionList as $extenName){
    if(!extension_loaded($extenName)){
        die($extenName . ' extension must install' . PHP_EOL);
    }
}

//parser argument
$params = getopt('hvdc:p:');

//config load
if (isset($params["c"])) {
    if (!file_exists($params["c"])) {
        die("config file not found..".PHP_EOL);
    }

    echo "loading special config:" . $params["c"] . PHP_EOL;
    $config = include($params["c"]);
} else {
    die("You must special the config file with -c option.".PHP_EOL);
}

//debug mode
if (isset($params['d'])) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
    //no daemonize
    $config["swoole"]["daemonize"] = 0;
    //base server
    $config["server"]["process_mode"] = SWOOLE_BASE;
    //one worker
    $config["swoole"]["worker_num"] = 1;
    //max_request
    $config["swoole"]["max_request"] = 1;

    echo "opened the debug info for console.." . PHP_EOL;
} else {
    ini_set("display_errors", "Off");
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

//pid file
if (isset($params["p"])) {
    $config["swoole"]["pid_file"] = $params["p"];
}


//get action of cmd
$count = count($argv);
if ($argv[$count - 1] == '&') {
    $funcName = $argv[$count - 2];
} else {
    $funcName = $argv[$count - 1];
}

switch ($funcName) {
    case 'start':
        {
            // 开启进程
            echo 'start:' . $config['server']['server_name'] . PHP_EOL;
            $server = new \WhetStone\Stone\Server\Manager($params, $config);
            $server->start();
            break;
        }
    case 'stop':
        {
            // 给 master 进程发送信号量 15
            echo 'stop:' . $config['server']['server_name'] . PHP_EOL;
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
            $pid = file_get_contents($config['swoole']['pid_file']);
            echo 'reload:' . $config['server']['server_name'] ." pid:" . $pid . PHP_EOL;

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

            echo 'stop:' . $config['server']['server_name'] . " pid:". $pid . PHP_EOL;
            if($pid > 0){
                $cmd = "kill  $pid";
                exec($cmd, $outStr);
            }

            sleep(3);

            //reload pid
            echo 'start:' . $config['server']['server_name'] . PHP_EOL;
            $server = new \WhetStone\Stone\Server\Manager($params, $config);
            $server->start();

            break;
        }
    case 'kill':
        {
            //暴力kill
            $name = $config['server']['server_name'];
            echo 'kill:' . $name . PHP_EOL;
            $cmd = "ps -ef | grep $name | grep -v grep | cut -c 9-15 | xargs kill -s 9 ";
            exec($cmd, $outStr);
            break;
        }
    default:
        {
            help();
            exit;
        }
}