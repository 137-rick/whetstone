<?php

namespace WhetStone\Stone\Protocol;

/**
 * tcp fixedheader协议回调封装
 * Class tcpfixedheader
 * @package WhetStone\Stone\Protocol
 */
class TCPFixedHeader {

    protected $_server;
    protected $_config;
    protected $_name;

    public function __construct($server, $config, $name)
    {
        $this->_server = $server;
        $this->_config = $config;
        $this->_name = $name;

        //sub listen event
        $this->_server->on('connect', array($this,"onConnect"));
        $this->_server->on('receive', array($this,"onReceive"));
        $this->_server->on('close', array($this,"onClose"));
        $this->_server->set(array(
            "open_http_protocol" => false,
            "open_http2_protocol" => false,
            "open_websocket_protocol" => false,
            "open_mqtt_protocol" => false,
            'open_length_check' => true,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 2,
        ));
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