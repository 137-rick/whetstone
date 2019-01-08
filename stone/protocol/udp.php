<?php

namespace WhetStone\Stone\Protocol;

/**
 * websocket协议回调封装
 * Class Http
 * @package WhetStone\Stone\Protocol
 */
class Udp {


    public function __construct($server,$config)
    {
        $this->_server = $server;
        $this->_config = $config;

        //sub listen event
        $this->_server->on('packet', array($this,"onPacket"));

    }


    /**
     * UDP数据回调
     */
    public function onPacket(\swoole_server $server, $data, $client_info)
    {

    }


}