<?php

return array(
    //hash分区redis
    "test_redis_shard" => array(
        "type" => "shard",
        "connection" => array(
            "0" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "test", //没有就两个双引号
            ),
            "1" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "test",
            ),
            "2" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "test",
            ),
            "3" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "test",
            ),

        ),

        "shard" => array(
            //0 ~ 3
            0, 0, 0, 0,

            //4 ~ 7
            1, 1, 1, 1,

            //8 ~ 11
            2, 2, 2, 2,

            //12 ~ 15
            3, 3, 3, 3,
        ),
    ),

    //普通单个redis链接方式
    "test_redis_common" => array(
        "type" => "common",
        "connection" => array(
            "host" => "127.0.0.1",
            "port" => 6379,
            "auth" => "test", //没有就两个双引号
        ),
    ),
    //需要主从就做两个配置即可
);