<?php
namespace WhetStone\Controller\Test;

class Test extends \WhetStone\Stone\Controller
{

    public function index()
    {
        return $this->showJson("success",0);
    }

    public function info()
    {
        return $this->showJson("nothing to do",-123);
    }
}