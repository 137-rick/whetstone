<?php

namespace WhetStone\Stone\Protocol;

/**
 * websocket协议回调封装
 * Class Http
 * @package WhetStone\Stone\Protocol
 */
class Tcp {


    public function __construct($server,$config)
    {
        $this->_server = $server;
        $this->_config = $config;

        //sub listen event
        $this->_server->on('connect', array($this,"onConnect"));
        $this->_server->on('receive', array($this,"onReceive"));
        $this->_server->on('close', array($this,"onClose"));

    }


    /**
     * 新的连接回调事件--worker中
     */
    public function onConnect(\swoole_server $server, $fd, $from_id)
    {

    }

    /**
     * 收到数据时的回调,发生在worker中
     */
    public function onReceive(\swoole_server $server, $fd, $reactor_id, $data)
    {

    }


    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     */
    public function onClose(\swoole_server $server, $fd, $reactorId)
    {

    }


}