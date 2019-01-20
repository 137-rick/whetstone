<?php

namespace WhetStone\Stone\Server;

/**
 * Class Event
 * @package WhetStone\Stone\Server
 */
class Event
{

    private static $_eventList = array();

    /////////////
    //框架自带事件
    /////////////

    //server_shutdown
    //server_start
    //manager_start
    //manager_stop
    //worker_start
    //worker_error
    //worker_stop
    //task
    //task_finish

    /////////////
    /// 通讯事件
    /////////////
    /// 来自于/config/server.php
    /// 主服务使用Main_事件
    /// 子监听使用 子端口服务名称_事件
    /// 具体事件在stone/protocol/下查看
    ////////////

    /**
     * 注册Event
     * @param $event
     * @param $callable
     * @throws \Exception
     */
    public static function register($event, $callable)
    {
        if (!is_callable($callable)) {
            throw new \Exception("hook register an wrong callable", -443);
        }
        self::$_eventList[$event][] = $callable;
    }

    /**
     * 触发event
     * @param $event
     * @param $param
     */
    public static function fire($event, $param)
    {
        if (isset(self::$_eventList[$event])) {
            foreach (self::$_eventList[$event] as $func) {
                $func($param);
            }
        }
    }

    /**
     * 取消所有已注册事件
     * @param $event
     */
    public static function clean($event){
        unset(self::$_eventList[$event]);
    }

    /**
     * 检测是否注册自定义事件，如没有注册框架可以自行处理
     * @param $event
     * @return bool 如果已经存在，返回true
     */
    public static function checkRegisted($event){
        if (isset(self::$_eventList[$event])
            && !empty(self::$_eventList[$event])) {
            return true;
        }
        return false;
    }
}