<?php
namespace WhetStone\Controller\Test;

use WhetStone\Stone\Context;

class Test extends \WhetStone\Stone\Controller
{

    public function index()
    {
        return $this->showJson("success",0);
    }

    public function info()
    {
        $context = Context::getContext();
        $request = $context->getRequest();
        $param = $request->getGet();

        $redis = \WhetStone\Stone\Driver\Redis\Redis::Factory("test_redis_shard");

        mt_srand(time());
        $redis->set("a".mt_rand(1,1000000),"aa");
        /*$ret = $redis->get("a");
        if($ret != "aa"){
            echo 12;
        }*/
        return $this->showJson("nothing to do",-123,$param);
    }

    public function status()
    {
        $redis = \WhetStone\Stone\Driver\Redis\Redis::Factory("test_redis_shard");
        $connection = $redis->getConnectionStatus();
        return $this->showJson("OK",0,$connection);
    }
}