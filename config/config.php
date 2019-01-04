<?php

$config = array(
    //主服务，选主服务 建议按 websocket（http） > http > udp || tcp 顺序创建 ,websocket只能作为主进程
    "server" => array(
        "server_name"    => "base",
        "host"           => "0.0.0.0",
        "port"           => 9587,
        "class"          => "swoole_websocket_server",
        //可选项：swoole_websocket_server/swoole_http_server/swoole_server
        "socket"         => SWOOLE_SOCK_TCP,
        "dispatcher"     => "fend/dispatcher/websocket.php",
        'classname'      => 'Fend_Displatcher_Websocket',
        //"loglevel" => Fend_Log::LOG_TYPE_INFO,
        "recordpath"     => './memtable.db',
        "tablesize"      => 1024,
        "logpath"        => '/home/logs/xeslog',
        "logAppId"       => '2001181',
        "processtimeout" => 10,
        //进程超时时间，超过10秒自动kill
    ),

    "listen" => array(
        "httpserver" => array(
            "host"       => "0.0.0.0",
            "port"       => 9572,
            "socket"     => SWOOLE_SOCK_TCP,
            "dispatcher" => "fend/dispatcher/http.php",
            'classname'  => 'Fend_Displatcher_Http',
            'domain'     => 'livearts.xueersi.com',
            "protocol"   => array(
                'open_http_protocol' => 1,
                'open_tcp_nodelay'   => 1,
                'backlog'            => 3000,
            ),
        ),
    ),

    "swoole" => array(
        'user'               => 'nobody',
        'group'              => 'nobody',
        'dispatch_mode'      => 2,
        'package_max_length' => 2097152,
        // 1024 * 1024 * 2,
        'buffer_output_size' => 3145728,
        //1024 * 1024 * 3,
        'pipe_buffer_size'   => 33554432,
        //1024 * 1024 * 32,

        'backlog'                  => 30000,
        'open_tcp_nodelay'         => 1,
        'heartbeat_idle_time'      => 180,
        'heartbeat_check_interval' => 60,

        'open_cpu_affinity'       => 1,
        'worker_num'              => 50,
        'task_worker_num'         => 10,
        'max_request'             => 100000,
        'task_max_request'        => 10000,
        'discard_timeout_request' => false,
        'log_level'               => 2,
        //swoole 日志级别 Info
        'log_file'                => '/home/logs/xeslog/baseserver.log',
        //swoole 系统日志，任何代码内echo都会在这里输出
        'task_tmpdir'             => '/dev/shm/',
        //task 投递内容过长时，会临时保存在这里，请将tmp设置使用内存
        'pid_file'                => '/home/logs/xeslog/baseserver.pid',
        //进程pid保存文件路径，请写绝对路径
        'daemonize'               => 1,
    ),

);
