<?php

return array(
    //hash分区redis
    "test_redis_shard" => array(
        "pool" => 20,
        "type" => "sharding",
        "connection" => array(
            "0" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "", //没有就两个双引号
                "prefix" => "" ,
                "timeout" => 3.0,
                "db"  => 0,
            ),
            "1" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "",
                "prefix" => "" ,
                "timeout" => 3.0,
                "db"  => 0,
            ),
            "2" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "",
                "prefix" => "" ,
                "timeout" => 3.0,
                "db"  => 0,
            ),
            "3" => array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "",
                "prefix" => "" ,
                "timeout" => 3.0,
                "db"  => 0,
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
        "pool" => 50,
        "connection" => array(
            array(
                "host" => "127.0.0.1",
                "port" => 6379,
                "auth" => "", //没有就两个双引号
                "prefix" => "" ,
                "timeout" => 3.0,
                "db"  => 0,
            )
        ),
    ),
    //需要主从就做两个配置即可
);