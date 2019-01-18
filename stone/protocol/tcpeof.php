<?php

namespace WhetStone\Stone\Protocol;

use WhetStone\Stone\Server\Event;

/**
 * tcp eof协议回调封装
 * Class tcpeof
 * @package WhetStone\Stone\Protocol
 */
class TCPEOF
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
        $this->_server->on('connect', array(
            $this,
            "onConnect"
        ));
        $this->_server->on('receive', array(
            $this,
            "onReceive"
        ));
        $this->_server->on('close', array(
            $this,
            "onClose"
        ));
        $this->_server->set(array(
            "open_http_protocol"      => false,
            "open_http2_protocol"     => false,
            "open_websocket_protocol" => false,
            "open_mqtt_protocol"      => false,
            'open_eof_split'          => true,
            'package_eof'             => "\r\n|\r\n",
        ));
    }


    /**
     * 新的连接回调事件--worker中
     */
    public function onConnect(\swoole_server $server, $fd, $from_id)
    {
        $context = \WhetStone\Stone\Context::createContext();

        $context->setAll(array(
                "server"  => $server,
                "fd"      => $fd,
                "from_id" => $from_id,
            ));

        Event::fire($this->_name . "_" . "connect", $context);
    }

    /**
     * 收到数据时的回调,发生在worker中
     */
    public function onReceive(\swoole_server $server, $fd, $reactor_id, $data)
    {
        $context = \WhetStone\Stone\Context::createContext();

        $context->setAll(array(
                "server"  => $server,
                "fd"      => $fd,
                "from_id" => $reactor_id,
                "data"    => $data,
            ));

        Event::fire($this->_name . "_" . "receive", $context);
    }


    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     */
    public function onClose(\swoole_server $server, $fd, $reactor_id)
    {
        $context = \WhetStone\Stone\Context::createContext();

        $context->setAll(array(
                "server"  => $server,
                "fd"      => $fd,
                "from_id" => $reactor_id,
            ));

        Event::fire($this->_name . "_" . "close", $context);
    }


}