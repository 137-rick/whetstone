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
        $this->_server->on('Start', array($this, 'onStart'));
        $this->_server->on('Shutdown', array($this, 'onShutdown'));

        $this->_server->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->_server->on('WorkerError', array($this, 'onWorkerError'));
        $this->_server->on('WorkerStop', array($this, 'onWorkerStop'));

        $this->_server->on('ManagerStart', array($this, 'onManagerStart'));
        $this->_server->on('ManagerStop', array($this, 'onManagerStop'));

        $this->_server->on('Task', array($this, 'onTask'));
        $this->_server->on('Finish', array($this, 'onFinish'));

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


}