<?php

namespace WhetStone\Stone\Contexts;

class Pool
{

    public static $pool = [];


    public static function getContext()
    {
        $id = \WhetStone\Stone\Coroutine\Coroutine::getPid();
        if (isset(self::$pool[$id])) {
            return self::$pool[$id];
        }

        return null;
    }


    public static function clear($coId = 0)
    {
        if (empty($coId)) {
            $coId = \WhetStone\Stone\Coroutine\Coroutine::getPid();
        }

        if (isset(self::$pool[$coId])) {
            unset(self::$pool[$coId]);
        }
    }

    public static function set($context)
    {
        $id = \WhetStone\Stone\Coroutine\Coroutine::getPid();
        self::$pool[$id] = $context;
    }
}