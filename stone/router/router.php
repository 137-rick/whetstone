<?php

namespace WhetStone\Stone\Router;

use Co\Exception;

class Router
{

    private $config = array();

    private $dispatcher = null;

    public function __construct($routerConfig)
    {
        //record routerConfig
        $this->config     = $routerConfig;

        //init router by config
        $this->dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $routerCollector) {
            foreach ($this->config as $routerDefine) {
                $routerCollector->addRoute($routerDefine[0], $routerDefine[1], $routerDefine[2]);
            }
        });
    }

    public function dispatch($method, $uri)
    {

        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0]) {
            case\FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                return $this->defaultRouter($method, $uri);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                throw new \Exception("Request Method Not Allowed", 405);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars    = $routeInfo[2];

                $context = \WhetStone\Stone\Context::getContext();
                //拿到request对象
                $request = $context->get("request");
                //设置网址内包含的参数
                $request->setRequestUrlParam($vars);

                var_dump($handler);
                //todo:router与controller调用
                // ... call $handler with $vars
                break;
        }
    }

    public function defaultRouter($method, $uri)
    {
        throw new \Exception("Url Router define Not Found", 404);
    }


}