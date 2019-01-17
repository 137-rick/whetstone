<?php

namespace WhetStone\Stone;

class Context
{
    ///////全局context管理//////////////

    //全局context列表
    private static $_contextList = array();

    //key为协程id,value为父id
    private static $_parentIdMap = array();

    //父类context使用列表
    private static $_parentIdChildren = array();

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
     * 创建当前协程context
     * @param int $parentCid 根协程id
     * @return object
     * @throws \Exception
     */
    public static function createContext($parentCid = -1)
    {
        $cid = \Swoole\Coroutine::getCid();
        if ($cid == -1) {
            throw new \Exception("传入非协程id", -332);
        }

        //已经存在不创建
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

            //协程退出，清理痕迹
            defer(function () use ($cid) {
                self::delContext($cid);
            });

            return self::$_contextList[$cid];
        }

        //但根协程存在，当前子协程
        if ($parentCid != $cid
            && isset(self::$_contextList[$parentCid])) {

            //记录父映射关系
            self::$_parentIdMap[$cid] = $parentCid;

            //记录children 引用
            if (!in_array($cid, self::$_parentIdChildren[$parentCid])) {
                self::$_parentIdChildren[$parentCid][] = $cid;
            }

            //不创建context

            //当前协程退出，清理痕迹
            defer(function () use ($cid) {
                self::delContext($cid);
            });
            return self::$_contextList[$parentCid];
        }

        //这里理论上不会达到，如果有，那么报错
        //可能是根协程不存在导致
        throw new \Exception("创建context失败", -322);
    }

    /**
     * 获取当前协程Context
     * @return \WhetStone\Stone\Context
     * @throws \Exception
     */
    public static function getContext()
    {
        $cid = \Swoole\Coroutine::getCid();
        if ($cid == -1) {
            throw new \Exception("当前在非协程状态", -334);
        }

        //获取根pid
        $pid = self::getPid($cid);
        if ($pid == -1) {
            throw new \Exception("没有找到根context cid", -335);
        }

        //没有父context返回null
        if (!isset(self::$_contextList[$pid])) {
            throw new \Exception("没有找到根context", -336);
        }

        //这里认为context已经创建了，直接返回

        //根据父id获取context，然后传递过去
        return self::$_contextList[$pid];

    }

    public static function delContext($cid)
    {

        //todo:这里还没有处理$_parentIdChildren呢
        //根context 那么直接从数组中干掉
        //子协程如果还在用这个context没有关系,引用计数会好点吧
        if (self::$_parentIdMap[$cid] == $cid) {
            unset(self::$_parentIdMap[$cid]);
            unset(self::$_contextList[$cid]);
            return;
        }

        //非根协程，只能干掉父映射
        unset(self::$_parentIdMap[$cid]);
    }

    /////////////单个context管理///////////////


    private $data = array();

    public function __construct()
    {

    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function setAll($param)
    {
        $this->data = $param;
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    public function getAll()
    {
        return $this->data;
    }

    public function setContextPid($cid){
        $this->data["__co_pid"] = $cid;
    }

    public function getContextPid(){
        return $this->data["__co_pid"];
    }
}