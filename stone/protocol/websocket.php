<?php

namespace WhetStone\Stone\Protocol;

use WhetStone\Stone\Server\Event;

/**
 * websocket协议回调封装
 * Class Websocket
 * @package WhetStone\Stone\Protocol
 */
class Websocket
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
        $this->_server->on('Open', array(
            $this,
            "onOpen"
        ));
        $this->_server->on('Message', array(
            $this,
            "onMessage"
        ));
        $this->_server->on('Close', array(
            $this,
            "onClose"
        ));

        $this->_server->set(array(
            "open_http_protocol"      => false,
            "open_http2_protocol"     => false,
            "open_websocket_protocol" => true,
            "open_mqtt_protocol"      => false,
        ));
    }


    /**
     * http-server的接受一个连接的时的回调函数
     */

    public function onRequest($request, $response)
    {
        Event::fire($this->_name . "_" . "request", array(
            "request"  => $request,
            "response" => $response,
        ));
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数。
     */
    public function onOpen(\swoole_websocket_server $server, swoole_http_request $request)
    {
        Event::fire($this->_name . "_" . "open", array(
            "server"  => $server,
            "request" => $request,
        ));
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数。
     */
    public function onMessage(\swoole_server $server, swoole_websocket_frame $frame)
    {
        Event::fire($this->_name . "_" . "message", array(
            "server" => $server,
            "frame"  => $frame,
        ));
    }

    /**
     * 客户端连接关闭后，在worker进程中回调此函数
     */
    public function onClose(\swoole_server $server, $fd, $reactorId)
    {
        Event::fire($this->_name . "_" . "close", array(
            "server"  => $server,
            "from_id" => $reactorId,
            "fd"      => $fd,
        ));
    }

}