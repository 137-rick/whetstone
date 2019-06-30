<?php

namespace WhetStone\Stone\Server;

use WhetStone\Stone\Di;

class Manager
{

    private $config = null;

    private $params = null;

    private $event = null;

    public function __construct($param, $config)
    {
        $this->params = $param;
        $this->config = $config;
    }

    /**
     * 根据config/server.php服务启动对应端口，协议
     * @throws \Exception
     */
    public function start()
    {
        //open coroutine
        \Swoole\Runtime::enableCoroutine(true);

        $processMode = SWOOLE_PROCESS;

        //是否开启debug模式，如果开启将会使用base模式执行
        if (isset($this->config["server"]["process_mode"]) && $this->config["server"]["process_mode"]) {
            $processMode = SWOOLE_BASE;
        }
        //register main server
        $serverType = strtolower($this->config["server"]["server"]);
        if ($serverType == "websocket") {
            $server = new \Swoole\WebSocket\Server($this->config["server"]["host"], $this->config["server"]["port"], $processMode);
        } elseif ($serverType == "http") {
            $server = new \Swoole\Http\Server($this->config["server"]["host"], $this->config["server"]["port"], $processMode);
        } elseif ($serverType == "tcp") {
            $server = new \Swoole\Server($this->config["server"]["host"], $this->config["server"]["port"], $processMode);
        } elseif ($serverType == "udp") {
            $server = new \Swoole\Server($this->config["server"]["host"], $this->config["server"]["port"], $processMode, SWOOLE_SOCK_UDP);
        } else {
            throw new \Exception("unknow config type of server server", -12);
        }

        echo "Server:" . $serverType . " host:" . $this->config["server"]["host"] . ":" . $this->config["server"]["port"] ." id:Main". PHP_EOL;

        //set option
        $server->set($this->config["swoole"]);

        //server config info
        Di::set("server_config", $this->config);

        //store server obj on di
        Di::set("server", $server);

        //register base event of swoole
        $baseProtocol = new \WhetStone\Stone\Protocol\Base($server, $this->config);
        Di::set("base_protocol", $baseProtocol);

        //register main protocol event
        $protocolClassName = $this->config["server"]["protocol"];
        $protocol          = new $protocolClassName($server, $this->config, 'Main');
        Di::set("main_protocol", $protocol);

        //bind port listen
        foreach ($this->config["server"]["listen"] as $serverName => $listenConfig) {
            $port = null;

            //deny user define the main for listen name
            if (strtolower($serverName) == "Main") {
                throw new \Exception("please don't set server name to Main for sub listen");
            }

            if ($listenConfig["server"] == "websocket") {
                $port = $server->addlistener($listenConfig["host"], $listenConfig["port"], SWOOLE_SOCK_TCP);
            } elseif ($listenConfig["server"] == "http") {
                $port = $server->addlistener($listenConfig["host"], $listenConfig["port"], SWOOLE_SOCK_TCP);
            } elseif ($listenConfig["server"] == "tcp") {
                $port = $server->addlistener($listenConfig["host"], $listenConfig["port"], SWOOLE_SOCK_TCP);
            } elseif ($listenConfig["server"] == "udp") {
                $port = $server->addlistener($listenConfig["host"], $listenConfig["port"], SWOOLE_SOCK_UDP);
            }
            echo "Server:" . $listenConfig["server"] . " host:" . $listenConfig["host"] . ":" . $listenConfig["port"] . " id:".$serverName .  PHP_EOL;

            //set the port obj
            Di::set("port_" . $serverName, $port);

            //set protocol obj
            $protocolClassName = $listenConfig["protocol"];
            $protocol          = new $protocolClassName($port, $this->config, $serverName);
            Di::set("port_protocol_" . $serverName, $protocol);

            //set the swoole user setting
            if (!empty($listenConfig["set"])) {
                $port->set($listenConfig["set"]);
            }

        }

        //这里开始初始化注册事件
        //放在这里是因为
        //用户的自定义注册会拦截系统错误一类
        //这里已经执行了加载了大部分框架所需
        //剩余的都是请求期间处理

        if (!empty($this->config["event"]["hook"])) {
            //invoke hook event
            $eventClassName = $this->config["event"]["hook"];
            $this->event    = new $eventClassName();
        }

        //start server
        $server->start();
    }

}