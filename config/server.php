<?php
ini_set("memory_limit","2G");
return array(
    //主服务，选主服务 建议按 websocket（http） > http > udp || tcp 顺序创建 ,websocket只能作为主进程
    "server" => array(
        "server"   => 'http',
        "protocol" => 'WhetStone\Stone\Protocol\http',

        "server_name" => "wt_stone",

        "host" => "0.0.0.0",
        "port" => 9980,

        "logpath" => '/home/logs/server',

        "listen" => array(
            //服务标识，就是下标，禁止叫main
            "api" => array(
                "server"   => 'tcp',
                "protocol" => 'WhetStone\Stone\Protocol\Tcp',

                "host"     => "0.0.0.0",
                "port"     => 6375,

                "set"      => array(

                ),
            ),
        ),
    ),

    "event" => array(
        //事件注册器，用于用户注册系统事件监听
        "register" => "\WhetStone\EventRegister",
    ),

    "swoole" => array(
        'user'               => 'nobody',
        'group'              => 'nobody',
        'dispatch_mode'      => 3,
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
        'worker_num'              => 4,
        'task_worker_num'         => 0,//task个数，目前默认0
        'max_request'             => 0,
        'task_max_request'        => 0,
        'discard_timeout_request' => false,
        'task_enable_coroutine'   => true,

        'max_coroutine'           => 10000,

        //swoole 日志级别 Info
        'log_level'               => 2,
        'log_file'                => '/var/log/stone.log',

        'task_tmpdir' => '/dev/shm/',

        'pid_file' => '/home/logs/server/stone.pid',

        'daemonize' => 1,
    ),

);
