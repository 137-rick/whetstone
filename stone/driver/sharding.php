<?php

namespace WhetStone\Stone\Driver;

class Sharding
{

    /**
     * 返回当前key对应的hash分片
     * 目前最高分成16片
     * @param $key string 要操作的key
     * @param string $hashType hash类型默认md5
     * @param int $len 参考取hash位数
     * @return int 对应分片index
     */
    public static function getHashId($key, $hashType = "md5", $len = 4)
    {
        $hash   = substr($hashType($key), 31 - $len, $len);
        $hashId = hexdec($hash) % 15;
        return $hashId;
    }
}