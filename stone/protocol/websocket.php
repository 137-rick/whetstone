<?php

namespace WhetStone\Stone\Protocol;

/**
 * websocket协议回调封装
 * Class Http
 * @package WhetStone\Stone\Protocol
 */
class Websocket {


    public function __construct($server,$config)
    {
        $this->_server = $server;
        $this->_config = $config;

        //sub listen event
        $this->_server->on('Request', array($this,"onRequest"));
        $this->_server->on('Open', array($this,"onOpen"));
        $this->_server->on('Message', array($this,"onMessage"));

    }


    /**
     * http-server的接受一个连接的时的回调函数
     */

    public function onRequest($request, $response)
    {
        $response->end("websocket");
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数。
     */
    public function onOpen(\swoole_websocket_server $svr, swoole_http_request $req)
    {

    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数。
     */
    public function onMessage(\swoole_server $server, swoole_websocket_frame $frame)
    {

    }

}