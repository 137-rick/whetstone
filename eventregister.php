<?php

namespace WhetStone;

class EventRegister
{

    public function __construct()
    {

        //on worker start init some event
        \WhetStone\Stone\Server\Event::register("worker_start", function () {

            //load all config
            \WhetStone\Stone\Config::loadAllConfig();

            $routerConfig = \WhetStone\Stone\Config::getConfig("router");
            \WhetStone\Stone\Di::set("router", new \WhetStone\Stone\Router\Router($routerConfig));

        });

        //on worker start init some event
        \WhetStone\Stone\Server\Event::register("Main_request", function () {

            try{
                $router = \WhetStone\Stone\Di::get("router");
                $context = \WhetStone\Stone\Context::getContext();

                $request = $context->get("request");
                $response = $context->get("response");

                $method = $request->getMethod();
                $uri = $request->getUri();

                $result = $router->dispatch($method, $uri);

                $response->end($result);

            }catch (\Throwable $e){
                //todo:这里如何返回接口结果
                var_dump($e->getMessage(),$e->getTraceAsString());
            }


        });
    }
}