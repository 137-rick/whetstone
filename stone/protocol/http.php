<?php

namespace WhetStone\Stone\Protocol;

use WhetStone\Stone\Server\Event;

/**
 * Http协议回调封装
 * Class Http
 * @package WhetStone\Stone\Protocol
 */
class HTTP
{

    protected $_server;
    protected $_config;
    protected $_name;

    public function __construct($server, $config, $name)
    {
        $this->_server = $server;
        $this->_config = $config;
        $this->_name   = $name;

        //sub listen event
        $this->_server->on('Request', array(
            $this,
            "onRequest"
        ));
        $this->_server->set(array(
            "open_http_protocol"      => true,
            "open_http2_protocol"     => false,
            "open_websocket_protocol" => false,
            "open_mqtt_protocol"      => false,
            "http_compression"        => true,

        ));
    }

    public function onRequest($request, $response)
    {
        $context = \WhetStone\Stone\Context::createContext();

        $context->setAll(
            array(
                "request" => $request,
                "response" => $response,
            )
        );

        Event::fire($this->_name . "_" . "request", $context);
    }

}