<?php

namespace WhetStone\Stone\Server;

class Manager
{

    private $config = null;

    private $params = null;

    public function __construct($param, $config)
    {
        $this->params = $param;
        $this->config = $config;
    }

    public function start()
    {
        //register main server
        $serverClassName = $this->config["server"]["server"];
        $server          = new $serverClassName($this->config);
        \WhetStone\Stone\Di::set("server", $server);

        //register base event of swoole
        $baseRouter = new \WhetStone\Stone\Protocol\Base($server, $this->config);
        \WhetStone\Stone\Di::set("base_protocol", $baseRouter);

        //register main protocol
        $routerClassName = $this->config["server"]["protocol"];
        $router          = new $routerClassName($server, $this->config);
        \WhetStone\Stone\Di::set("main_protocol", $router);

        //bind listen


        //start server
        $server->start();
    }
}