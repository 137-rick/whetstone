<?php

namespace WhetStone\Stone\Protocol;

/**
 * udp协议回调封装
 * Class udp
 * @package WhetStone\Stone\Protocol
 */
class UDP {


    public function __construct($server,$config)
    {
        $this->_server = $server;
        $this->_config = $config;

        //sub listen event
        $this->_server->on('packet', array($this,"onPacket"));
        $this->_server->set(array(
            "open_http_protocol" => false,
            "open_http2_protocol" => false,
            "open_websocket_protocol" => false,
            "open_mqtt_protocol" => false,
        ));
    }


    /**
     * UDP数据回调
     */
    public function onPacket(\swoole_server $server, $data, $client_info)
    {

    }


}