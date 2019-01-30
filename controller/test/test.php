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

        $redis = \WhetStone\Stone\Di::get("predis");

        $redis->set("a","aa");
        $ret = $redis->get("a");
        if($ret != "aa"){
            echo 12;
        }
        return $this->showJson("nothing to do",-123);
    }
}