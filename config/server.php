<?php

return array(
    //主服务，选主服务 建议按 websocket（http） > http > udp || tcp 顺序创建 ,websocket只能作为主进程
    "server" => array(
        "server_name" => "base",
        "host"        => "0.0.0.0",
        "port"        => 9587,
        "logpath"     => '/home/logs/server',

        "listen" => array(
            "httpserver" => array(
                "host"     => "0.0.0.0",
                "port"     => 9572,
                "protocol" => array(
                    'open_http_protocol' => 1,
                    'open_tcp_nodelay'   => 1,
                    'backlog'            => 3000,
                ),
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
        'max_request'             => 0,
        'task_max_request'        => 0,
        'discard_timeout_request' => false,
        'log_level'               => 2,
        //swoole 日志级别 Info
        'log_file'                => '/home/logs/server/baseserver.log',
        'task_tmpdir'             => '/dev/shm/',
        'pid_file'                => '/home/logs/server/baseserver.pid',
        'daemonize'               => 1,
    ),

);
