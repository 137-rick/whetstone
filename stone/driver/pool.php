<?php

namespace WhetStone\Stone\Driver;

/**
 * 触发式连接池
 * 空余连接数不够才会增长连接数
 * 连接到达上限会阻塞等待指定秒数后抛异常
 */
abstract class Pool
{

    private $_pool = NULL;

    protected $_config = NULL;

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
        $this->_pool        = new \Swoole\Coroutine\Channel($maxObjCount);

        //一次性创建好所有连接
        //失败一个会异常抛出
        for ($count = 0; $count < $maxObjCount; $count++) {
            $obj = $this->getDriverObj();
            $this->_pool->push($obj);
        }
    }

    //对象报错时会调用这个函数整理错误

    /**
     * 对象调用操作
     * @param string $name 动作名称
     * @param array $arguments 参数
     * @return mixed
     * @throws
     */
    public function __call($name, $arguments)
    {
        $obj = null;

        try {
            $obj = $this->fetchObj();

            $result = call_user_func_array(array(
                $obj,
                $name
            ), $arguments);

            $this->recycleObj($obj);

            return $result;
        } catch (\Exception $e) {
            $this->onError($obj, $e);
            throw $e;
        }
    }

    /////////////////////////////////////////////////////

    private function fetchObj()
    {
        //pool is empty and have idle space
        if ($this->_pool->isEmpty() && ($this->_invokeObjCount + $this->_pool->length() < $this->_maxObjCount)) {
            try {
                $this->_invokeObjCount++;
                $obj = $this->getDriverObj();
            } catch (\Exception $e) {
                $this->_invokeObjCount--;
                throw $e;
            }

            return $obj;
        }

        //channel have obj
        //fetch obj by 3 second wait
        $obj = $this->_pool->pop(3.0);

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
    abstract public function getDriverObj();

    private function recycleObj($obj)
    {
        if (empty($obj)) {
            return;
        }

        //decrease count
        $this->_invokeObjCount--;

        return $this->_pool->push($obj);
    }

    abstract public function onError($obj, $e);


}