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
        $serverType = strtolower($this->config["server"]["server"]);
        if ($serverType == "websocket") {
            $server = new \Swoole\WebSocket\Server($this->config["server"]["host"], $this->config["server"]["port"]);
        } elseif ($serverType == "http") {
            $server = new \Swoole\Http\Server($this->config["server"]["host"], $this->config["server"]["port"]);
        } elseif ($serverType == "tcp") {
            $server = new \Swoole\Server($this->config["server"]["host"], $this->config["server"]["port"]);
        } elseif ($serverType == "udp") {
            $server = new \Swoole\Server($this->config["server"]["host"], $this->config["server"]["port"], SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        } else {
            throw new \Exception("unknow config type of server server", -12);
        }

        //set option
        $server->set($this->config["swoole"]);

        //store server obj on di
        \WhetStone\Stone\Di::set("server", $server);

        //register base event of swoole
        $baseRouter = new \WhetStone\Stone\Protocol\Base($server, $this->config);
        \WhetStone\Stone\Di::set("base_protocol", $baseRouter);

        //register main protocol event
        $routerClassName = $this->config["server"]["protocol"];
        $router          = new $routerClassName($server, $this->config);
        \WhetStone\Stone\Di::set("main_protocol", $router);

        //bind listen


        //start server
        $server->start();
    }
}