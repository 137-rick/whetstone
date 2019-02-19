<?php

namespace WhetStone\Stone\Driver;

/**
 * 触发式连接池
 * 空余连接数不够才会增长连接数
 * 连接到达上限会阻塞等待指定秒数后抛异常
 * 这个只是基础类，继承后方可使用
 */
abstract class Pool
{

    private $_pool = array();

    protected $_config = NULL;

    protected $_waitDelay = 3.0;

    //整体最多连接数
    private $_maxObjCount = 50;

    //正在被调用个数
    private $_invokeObjCount = 0;

    /////////////////////////////////////////////////////
    //获取对象，对象必须自带重连

    /**
     * Fend_Pool constructor.
     * @param int $maxObjCount 最大连接数
     * @param float $waitTimeout 连接池满等待超时时间
     * @param array $config 数据连接配置
     */
    public function __construct($maxObjCount = 50, $waitTimeout = 3.0, $config = array())
    {
        $this->_maxObjCount = $maxObjCount;
        $this->_waitDelay   = $waitTimeout;
        $this->_config      = $config;

        //如果不是分片默认就是0分片
        for ($shardid = 0; $shardid < count($this->_config["connection"]); $shardid++) {

            //create shard obj pool
            $this->_pool[$shardid] = new \Swoole\Coroutine\Channel($maxObjCount);

            //一次性创建好所有连接
            //失败一个会异常抛出

            for ($count = 0; $count < $maxObjCount; $count++) {
                $obj = $this->getDriverObj($shardid);
                $this->_pool[$shardid]->push($obj);
            }
        }
    }

    /**
     * 获取连接池连接个数情况
     * @return array
     */
    public function getConnectionStatus(){
        $result = array();
        foreach ($this->_pool as $key => $queue){
            $result[$key] = $queue->length();
        }
        return $result;
    }


    /**
     * 对象调用操作
     * @param string $name 动作名称
     * @param array $arguments 参数
     * @return mixed
     * @throws
     */
    public function __call($name, $arguments)
    {
        $obj     = null;
        $shardId = 0;

        //分区模式才会计算分区，否则都用0区域
        if ($this->_config["type"] == "sharding") {
            $key     = current($arguments);
            $shardId = Sharding::getHashId($key);
            $shardId = $this->_config["shard"][$shardId];
        }

        try {
            $obj = $this->fetchObj($shardId);

            $result = call_user_func_array(array(
                $obj,
                $name
            ), $arguments);

            $this->recycleObj($shardId, $obj);

            return $result;
        } catch (\Exception $e) {
            $this->onError($obj, $e);
            throw $e;
        }
    }

    /////////////////////////////////////////////////////

    /**
     * 从池中拉获取一个可用redis连接
     * @return mixed
     * @throws \Exception
     */
    public function fetchObj(int $shardId)
    {
        //pool is empty and have idle space
        if ($this->_pool[$shardId]->isEmpty() && ($this->_invokeObjCount + $this->_pool[$shardId]->length() < $this->_maxObjCount)) {
            try {
                $this->_invokeObjCount++;
                $obj = $this->getDriverObj($shardId);
            } catch (\Exception $e) {
                $this->_invokeObjCount--;
                throw $e;
            }

            return $obj;
        }

        //channel have obj
        //fetch obj by 3 second wait
        $obj = $this->_pool[$shardId]->pop($this->_waitDelay);

        if ($obj !== FALSE) {
            //increase count
            $this->_invokeObjCount++;
            return $obj;
        }

        //fetch fail max arrive
        throw new \Exception("fetch pool obj fail... please increase the connection pool size.", -123);
    }

    /**
     * 获取数据对象，return对象即可
     * @return mixed
     */
    abstract public function getDriverObj(int $shard);

    /**
     * 回收一个连接
     * @param $obj
     * @return mixed|void
     */
    public function recycleObj(int $shardId, $obj)
    {
        if (empty($obj)) {
            return;
        }

        //decrease count
        $this->_invokeObjCount--;

        return $this->_pool[$shardId]->push($obj);
    }

    //对象报错时会调用这个函数整理错误
    abstract public function onError($obj, $e);


}