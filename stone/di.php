<?php

namespace WhetStone\Stone;

/**
 * 依赖注入，这里只能放全局无状态具柄、配置
 * 业务不要在这里设置任何记录
 * 这里可以放redis、mysql连接池，debug标志
 *
 * Class Di
 * @package WhetStone\Stone
 */
class Di
{

    static $_data = array();

    /**
     * 保存一个具柄
     * @param $name
     * @param $value
     */
    public static function set($name, $value)
    {
        self::$_data[$name] = $value;
    }

    /**
     * 获取一个具柄对象
     * @param $name
     * @return mixed
     */
    public static function get($name)
    {
        return self::$_data[$name];
    }

}