<?php

namespace WhetStone\Stone\Protocol;

use WhetStone\Stone\Server\Event;

/**
 * udp协议回调封装
 * Class udp
 * @package WhetStone\Stone\Protocol
 */
class UDP {
    protected $_server;
    protected $_config;
    protected $_name;

    public function __construct($server, $config, $name)
    {
        $this->_server = $server;
        $this->_config = $config;
        $this->_name = $name;

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
        Event::fire($this->_name . "_" . "packet", array(
            "server"  => $server,
            "from_id" => $client_info,
            "data"    => $data,
        ));
    }


}