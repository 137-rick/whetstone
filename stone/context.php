<?php

namespace WhetStone\Stone;

/**
 * context类，协程版本不允许使用global
 * 使用这个替代
 * Class Context
 * @package WhetStone\Stone
 */
class Context
{
    ///////全局context管理//////////////

    //全局context列表
    private static $_contextList = array();

    //key为协程id,value为父id
    private static $_parentIdMap = array();

    //父类context使用列表
    private static $_parentIdChildren = array();

    //context 里面存储的数据
    private $data = array();

    //根协程cid
    private $pid = -1;

    public function __construct()
    {

    }

    /**
     * 创建当前协程context
     * @param int $parentCid 根协程id
     * @return object
     * @throws \Exception
     */
    public static function createContext($parentCid = -1)
    {
        $cid = \Swoole\Coroutine::getUid();
        if ($cid == -1) {
            throw new \Exception("目前并没有在协程内", -332);
        }

        //已经存在context不创建
        if (isset(self::$_parentIdMap[$cid]) && isset(self::$_contextList[self::$_parentIdMap[$cid]])) {
            return self::$_contextList[self::$_parentIdMap[$cid]];
        }

        //不存在或者数据不全情况

        //判断是根，那么创建根协程context
        if ($parentCid == -1 || $parentCid == $cid) {
            //记录根协程id映射
            self::$_parentIdMap[$cid] = $cid;

            //创建context并且放到列表
            self::$_contextList[$cid] = new self();

            //保存当前根pid到context
            self::$_contextList[$cid]->setContextPid($cid);

            //初始化根协程id子协程序列表
            //理论上，子小协程还没结束
            //用这个做是否释放context依据
            self::$_parentIdChildren[$cid] = array($cid);

            //根协程退出，清理所有痕迹
            \Swoole\Coroutine::defer(function () use ($cid) {
                self::delContext($cid);
            });

            return self::$_contextList[$cid];
        }

        //根协程存在，当前为根下创建的子协程
        if ($parentCid != $cid && isset(self::$_contextList[$parentCid])) {

            //记录当前协程的父映射关系
            self::$_parentIdMap[$cid] = $parentCid;

            //记录children 引用
            if (!in_array($cid, self::$_parentIdChildren[$parentCid])) {
                self::$_parentIdChildren[$parentCid][] = $cid;
            }

            //不创建context

            //当前子协程退出，清理痕迹
            \Swoole\Coroutine::defer(function () use ($cid) {
                self::delContext($cid);
            });
            return self::$_contextList[$parentCid];
        }

        //这里理论上不会达到，如果有，那么报错
        //可能是根协程不存在导致
        throw new \Exception("创建context失败", -322);
    }

    /**
     * 清理指定cid的context
     * @param $cid
     */
    public static function delContext($cid)
    {

        //todo:这里还没有处理$_parentIdChildren呢，直接释放暴力了一点
        //根context 那么直接从数组中干掉
        //子协程如果还在用这个context没有关系,引用计数会好点吧
        if (self::$_parentIdMap[$cid] == $cid) {
            unset(self::$_parentIdMap[$cid]);
            unset(self::$_contextList[$cid]);
            unset(self::$_parentIdChildren[$cid]);
            return;
        }

        //非根协程，只能干掉父映射
        unset(self::$_parentIdMap[$cid]);
    }

    /////////////单个context管理///////////////

    /**
     * 获取当前协程Context
     * @return \WhetStone\Stone\Context
     * @throws \Exception
     */
    public static function getContext()
    {
        $cid = \Swoole\Coroutine::getUid();
        if ($cid == -1) {
            throw new \Exception("当前在非协程状态", -334);
        }

        //获取根协程cid
        $pid = self::getPid($cid);
        if ($pid == -1) {
            throw new \Exception("没有找到根context cid", -335);
        }

        //没有父context报错
        //todo:这里有争议，如果子协程还没跑完，父协程已经退出了，可能会有问题，需要和上面delcontext引用计数配合
        if (!isset(self::$_contextList[$pid])) {
            throw new \Exception("没有找到根context", -336);
        }

        //这里认为context已经创建了，直接返回
        //根据父id获取context，然后传递过去
        return self::$_contextList[$pid];

    }

    /**
     * 获取根协程id
     * @param int $cid 当前协程号
     * @return int|mixed 没有返回-1
     * @throws \Exception 错误参数返回
     */
    public static function getPid($cid)
    {
        if ($cid == -1) {
            throw new \Exception("传入非协程id", -333);
        }

        if (isset(self::$_parentIdMap[$cid])) {
            return self::$_parentIdMap[$cid];
        }

        return -1;
    }

    /**
     * 设置context附加信息
     * @param $key
     * @param $val
     */
    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    /**
     * 清空，然后覆盖掉所有变量
     * @param $param
     */
    public function setAll($param)
    {
        $this->data = $param;
    }

    /**
     * 获取到request
     * @return mixed
     */
    public function getRequest(){
        return $this->data["request"];
    }

    /**
     * 获取到response
     * @return mixed
     */
    public function getResponse(){
        return $this->data["response"];
    }

    /**
     * 获取附加信息
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * 获取所有暂存变量
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * 修改记录根协程cid
     * @param $cid
     */
    public function setContextPid($cid)
    {
        $this->pid = $cid;
    }

    /**
     * 获取当前协程-根协程cid
     * @return mixed
     */
    public function getContextPid()
    {
        return $this->pid;
    }

    /**
     * 用于查看context暂存数据多大
     * @return array
     */
    public function getStastics()
    {
        return array(
            "context_count" => count(self::$_contextList),
            "parent_id"     => count(self::$_parentIdMap),
            "children_id"   => count(self::$_parentIdChildren),
            "data"          => count($this->data),
        );
    }
}