<?php

namespace WhetStone\Stone\Driver;

/**
 * 触发式连接池
 * 空余连接数不够才会增长连接数
 * 连接到达上限会阻塞等待指定秒数后抛异常
 * Class Fend_Pool
 */
abstract class Pool
{

    private $_pool = NULL;

    private $_config = NULL;

    //整体最多连接数
    private $_maxObjCount = 50;

    //正在被调用个数
    private $_invokeObjCount = 0;

    //如果连接池没有空余等待多久报错
    private $_waitDelay = 3.0;

    /////////////////////////////////////////////////////
    //获取对象，对象必须自带重连

    abstract public function getDriverObj();

    //对象报错时会调用这个函数整理错误
    abstract public function onError($obj, $e);
    /////////////////////////////////////////////////////

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
        $this->_pool        = new \Swoole\Coroutine\Channel($maxObjCount + 20);

        /*
        //cycle check connection count
        swoole_timer_tick(3000, function () {
            $this->heartBeat();
        });
        */
    }


    /*
    private function heartBeat()
    {

        //min count of connection
        for ($addCount = $this->_minObjCount - ($this->_invokeObjCount + $this->_pool->length()); $addCount > 0; $addCount--) {

            //make sure queue nerver jam
            if ($this->_pool->isFull()) {
                break;
            }

            //create obj and add to channel
            //if create fail throw exception
            $this->_pool->push($this->getDriverObj());
        }

    }*/

    private function fetchObj()
    {
        //pool is empty and have idle space
        if ($this->_pool->isEmpty() && $this->_invokeObjCount < $this->_maxObjCount) {
            $obj = $this->getDriverObj();
            $this->_invokeObjCount++;
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

        //fetch fail
        throw new \Exception("fetch pool obj fail... please increase the connection pool size.", -123);
    }

    private function recycleObj($obj): bool
    {
        if (empty($obj)) {
            return;
        }

        //decrease count
        $this->_invokeObjCount--;

        return $this->_pool->push($obj);
    }


    /**
     * 对象调用操作
     * @param string $name 动作名称
     * @param array $arguments 参数
     * @return mixed
     * @throws Exception
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


}