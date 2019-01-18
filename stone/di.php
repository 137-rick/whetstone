<?php

namespace WhetStone\Stone;

class Di
{

    static $_data = array();

    public static function set($name, $value)
    {
        self::$_data[$name] = $value;
    }

    public static function get($name)
    {
        return self::$_data[$name];
    }

}