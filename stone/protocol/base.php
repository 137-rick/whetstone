<?php

namespace WhetStone\Stone\Protocol;

/**
 * 基础协议注册
 * 框架初始化的时候统一在这里处理公共event
 * Class Base
 * @package WhetStone\Stone\Protocol
 */
class Base
{

    protected $_server = null;
    protected $_config = null;

    public function __construct($server,$config)
    {

        $this->_server = $server;
        $this->_config = $config;

        //main event
        $this->_server->getServer()->on('Start', array($this, 'onStart'));
        $this->_server->getServer()->on('Shutdown', array($this, 'onShutdown'));

        $this->_server->getServer()->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->_server->getServer()->on('WorkerError', array($this, 'onWorkerError'));
        $this->_server->getServer()->on('WorkerStop', array($this, 'onWorkerStop'));

        $this->_server->getServer()->on('ManagerStart', array($this, 'onManagerStart'));
        $this->_server->getServer()->on('ManagerStop', array($this, 'onManagerStop'));

        $this->_server->getServer()->on('Task', array($this, 'onTask'));
        $this->_server->getServer()->on('Finish', array($this, 'onFinish'));

        $this->_server->getServer()->on('Close', array($this, 'onClose'));
    }

    public function setProcessName($prefix, $typeName)
    {
        if (empty($_SERVER['SSH_AUTH_SOCK']) || stripos($_SERVER['SSH_AUTH_SOCK'], 'apple') === false) {
            \swoole_set_process_name($prefix . ":" . $typeName);
        }
    }

    /**
     * Server启动在主进程的主线程回调此函数
     */
    public function onStart(\swoole_server $server)
    {
        $this->setProcessName($this->_config["server"]["server_name"], "master");
    }

    /**
     * server结束时回调事件
     */
    public function onShutdown(\swoole_server $server)
    {

    }

    /**
     * 事件在Worker进程/Task进程启动时发生
     */
    public function onWorkerStart(\swoole_server $server, $worker_id)
    {
        if (!$server->taskworker) {
            //worker
            $this->setProcessName($this->_config["server"]["server_name"], "worker");
        } else {
            //task
            $this->setProcessName($this->_config["server"]["server_name"], "task");
        }
    }

    /**
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数。
     */
    public function onWorkerError(\swoole_server $serv, $worker_id, $worker_pid, $exit_code, $signal)
    {

    }

    /**
     * 事件在Worker进程/Task进程终止时发生
     */
    public function onWorkerStop(\swoole_server $server, $worker_id)
    {

    }

    /**
     * 当管理进程启动时回调事件
     */
    public function onManagerStart(\swoole_server $serv)
    {
        $this->setProcessName($this->_config["server"]["server_name"], "manager");
    }

    /**
     * 当管理进程结束时回调函数
     */
    public function onManagerStop(\swoole_server $serv)
    {
    }

    //////////////////////////////////////////////////// 后面都是协议注册 //后续删除

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
     * UDP数据回调
     */
    public function onPacket(\swoole_server $server, $data, $client_info)
    {

    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     */
    public function onClose(\swoole_server $server, $fd, $reactorId)
    {

    }

    /**
     * work中投递任务时发生的回调事件
     */
    public function onTask(\swoole_server $serv, $task_id, $src_worker_id, $data)
    {


    }

    /**
     * 当worker进程投递的任务在task_worker中完成时回调此函数
     */
    public function onFinish(\swoole_server $serv, $task_id, $data)
    {

    }

    /**
     * http-server的接受一个连接的时的回调函数
     */

    public function onRequest($request, $response)
    {

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