<?php

namespace WhetStone\Stone\Protocol;

/**
 * Http协议回调封装
 * Class Http
 * @package WhetStone\Stone\Protocol
 */
class HTTP {


    public function __construct($server,$config)
    {
        $this->_server = $server;
        $this->_config = $config;

        //sub listen event
        $this->_server->on('Request', array($this,"onRequest"));
        $this->_server->set(array(
            "open_http_protocol" => true,
            "open_http2_protocol" => false,
            "open_websocket_protocol" => false,
            "open_mqtt_protocol" => false,
        ));
    }

    public function onRequest($request, $response){
        $response->end("yes");

    }

}