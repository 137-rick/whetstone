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
     * @param \WhetStone\Stone\Context $context
     * @param string $method
     * @param string $uri
     * @throws \Exception
     * @return string
     */
    public function dispatch($context, $method, $uri)
    {

        //解析路由
        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        //result status decide
        switch ($routeInfo[0]) {
            case\FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                //try default router
                return $this->defaultRouter($context, $uri);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                $context->getResponse()->setStatus(405);
                throw new \Exception("Request Method Not Allowed", 405);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars    = $routeInfo[2];
                //拿到request对象
                $request = $context->getRequest();
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
                    return $controller->$func($context, $request, $context->getResponse());

                } else if (is_callable($handler)) {
                    //call direct when router define an callable function
                    return call_user_func_array($handler, [$context, $request, $context->getResponse()]);
                } else {
                    throw new \Exception("Router Config error on handle." . $uri, -108);
                }
                break;
        }
        throw new \Exception("Unknow Fast Router decide " . $uri, -101);

    }

    /**
     * 默认路由方式
     * 如果fastrouter没有设置路由，那么会请求到这里
     * 默认路由会根据uri到对应目录找文件，找到会调用他
     * 这么做是为了方便开发，个性设置走个性设置，常规默认能工作
     * @param \WhetStone\Stone\Context $context
     * @param string $uri
     * @throws \Exception
     * @return string
     */
    public function defaultRouter($context, $uri)
    {

        if (empty($uri)) {
            throw new \Exception("uri is empty", -111);
        }

        $uri       = trim($uri, "/");
        $uri       = explode("/", $uri);

        if($uri[0] === ""){
            $className = "\\WhetStone\\Controller\\Index";

            if(class_exists($className) && method_exists($className,"index")){
                return $className->index($context, $context->getRequest(), $context->getResponse());
            }
            //找不到404
            \WhetStone\Stone\Context::getContext()->getResponse()->setStatus(404);
            throw new \Exception("Default Router index/index Handle define Class Not Found", -110);
        }

        $function  = array_pop($uri);
        $className = "\\WhetStone\\Controller\\" . implode("\\", $uri);

        //第一次尝试，直接找对应类，找不到再尝试默认index
        if (class_exists($className)) {

            $controller = new $className();

            if (method_exists($controller, $function)) {
                //invoke controller and get result
                return $controller->$function($context, $context->getRequest(), $context->getResponse());
            }

        }

        //找回分离出去的路径
        $className = $className . "\\" . $function;
        $function  = "index";

        //再次尝试
        if (class_exists($className)) {

            $controller = new $className();

            if (method_exists($controller, $function)) {
                //invoke controller and get result
                return $controller->$function($context, $context->getRequest(), $context->getResponse());
            }

        }

        //找不到了
        \WhetStone\Stone\Context::getContext()->getResponse()->setStatus(404);
        throw new \Exception("Default Router ".$className." Handle define Class Not Found", -109);

    }


}