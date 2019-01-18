<?php

namespace WhetStone\Stone\Router;

/**
 * 请求路由解析
 * 内部使用fastrouter方式
 * Class Router
 * @package WhetStone\Stone\Router
 */
class Router
{

    private $config = array();

    private $dispatcher = null;

    /**
     * 初始化路由类，并加载路由配置
     * Router constructor.
     * @param $routerConfig
     */
    public function __construct($routerConfig)
    {
        //record routerConfig
        $this->config = $routerConfig;

        //init router by config
        $this->dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $routerCollector) {
            foreach ($this->config as $routerDefine) {
                $routerCollector->addRoute($routerDefine[0], $routerDefine[1], $routerDefine[2]);
            }
        });
    }

    /**
     * 根据method及uri调用对应配置的类
     * @param string $method
     * @param string $uri
     * @throws \Exception
     * @return string
     */
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

                //string rule is controllerName@functionName
                if (is_string($handler)) {
                    //decode handle setting
                    $handler = explode("@", $handler);
                    if (count($handler) != 2) {
                        throw new \Exception("Router Config error on handle.Handle only support two parameter with @" . $uri, -105);
                    }

                    $className = $handler[0];
                    $func      = $handler[1];

                    //class check
                    if (!class_exists($className)) {
                        throw new \Exception("Router $uri Handle definded Class Not Found", -106);
                    }

                    //new controller
                    $controller = new $className();

                    //method check
                    if (!method_exists($controller, $func)) {
                        throw new \Exception("Router $uri Handle definded $func Method Not Found", -107);
                    }

                    //invoke controller and get result
                    return $controller->$func();

                } else if (is_callable($handler)) {
                    //call direct
                    return call_user_func($handler);
                } else {
                    throw new \Exception("Router Config error on handle." . $uri, -108);
                }
                break;
        }
    }

    /**
     * 默认路由方式
     * @param $method
     * @param $uri
     * @throws \Exception
     */
    public function defaultRouter($method, $uri)
    {
        throw new \Exception("Url Router define Not Found", 404);
    }


}