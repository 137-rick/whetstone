<?php

namespace WhetStone\Stone\Protocol;

use WhetStone\Stone\Server\Event;

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

    public function __construct(\swoole_server $server, $config)
    {

        /**
         * @var \swoole_server
         */
        $this->_server = $server;
        $this->_config = $config;

        //main event
        $this->_server->on('Start', array(
            $this,
            'onStart'
        ));
        $this->_server->on('Shutdown', array(
            $this,
            'onShutdown'
        ));

        $this->_server->on('WorkerStart', array(
            $this,
            'onWorkerStart'
        ));
        $this->_server->on('WorkerError', array(
            $this,
            'onWorkerError'
        ));
        $this->_server->on('WorkerStop', array(
            $this,
            'onWorkerStop'
        ));

        $this->_server->on('ManagerStart', array(
            $this,
            'onManagerStart'
        ));
        $this->_server->on('ManagerStop', array(
            $this,
            'onManagerStop'
        ));

        $this->_server->on('Task', array(
            $this,
            'onTask'
        ));
        $this->_server->on('Finish', array(
            $this,
            'onFinish'
        ));

    }

    /**
     * Server启动在主进程的主线程回调此函数
     * @param \swoole_server $server
     */
    public function onStart(\swoole_server $server)
    {
        $this->setProcessName($this->_config["server"]["server_name"], "master");
        Event::fire("server_start", array(
            "server" => $server
        ));
    }

    public function setProcessName($prefix, $typeName)
    {
        if (empty($_SERVER['SSH_AUTH_SOCK']) || stripos($_SERVER['SSH_AUTH_SOCK'], 'apple') === false) {
            \swoole_set_process_name($prefix . ":" . $typeName);
        }
    }

    /**
     * server结束时回调事件
     * @param \swoole_server $server
     */
    public function onShutdown(\swoole_server $server)
    {
        Event::fire("server_shutdown", array(
            "server" => $server
        ));
    }


    /**
     * 事件在Worker进程/Task进程启动时发生
     * @param \swoole_server $server
     * @param $worker_id
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

        Event::fire("worker_start", array(
            "server"    => $server,
            "worker_id" => $worker_id
        ));
    }

    /**
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数。
     * @param \swoole_server $server
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     * @param $signal
     */
    public function onWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code, $signal)
    {
        Event::fire("worker_error", array(
            "server"     => $server,
            "worker_id"  => $worker_id,
            "worker_pid" => $worker_pid,
            "exit_code"  => $exit_code,
            "signal"     => $signal
        ));
    }

    /**
     * 事件在Worker进程/Task进程终止时发生
     * @param \swoole_server $server
     * @param $worker_id
     */
    public function onWorkerStop(\swoole_server $server, $worker_id)
    {
        Event::fire("worker_stop", array(
            "server"    => $server,
            "worker_id" => $worker_id,
        ));
    }

    /**
     * 当管理进程启动时回调事件
     * @param \swoole_server $server
     */
    public function onManagerStart(\swoole_server $server)
    {
        $this->setProcessName($this->_config["server"]["server_name"], "manager");

        Event::fire("manager_start", array(
            "server" => $server,
        ));
    }

    /**
     * 当管理进程结束时回调函数
     * @param \swoole_server $server
     */
    public function onManagerStop(\swoole_server $server)
    {
        Event::fire("manager_stop", array(
            "server" => $server,
        ));
    }

    /**
     * work中投递任务时发生的回调事件
     * @param \swoole_server $server
     * @param $task_id
     * @param $src_worker_id
     * @param $data
     */
    public function onTask(\swoole_server $server, \Swoole\Server\Task $task)
    {
        Event::fire("task", array(
            "server"    => $server,
            "task_id"   => $task->id,
            "worker_id" => $task->workerId,
            "data"      => $task->data,
        ));
    }

    /**
     * 当worker进程投递的任务在task_worker中完成时回调此函数
     * @param \swoole_server $server
     * @param $task_id
     * @param $data
     */
    public function onFinish(\swoole_server $server, $task_id, $data)
    {
        Event::fire("task_finish", array(
            "server"  => $server,
            "task_id" => $task_id,
            "data"    => $data,
        ));
    }


}