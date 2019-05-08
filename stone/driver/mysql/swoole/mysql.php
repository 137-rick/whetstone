<?php

namespace WhetStone\Stone\Driver\Mysql\PDO;

class Mysql
{

    private $config = null;

    private $dbName = "";

    private $lastPingTime = 0;

    /**
     * @var \Swoole\Coroutine\MySQL
     */
    private $mysql = null;

    public function __construct($config, $dbName)
    {
        //config
        $this->config = $config;

        //dbname
        $this->dbName = $dbName;

        //connect
        $this->reconnect();
    }

    public function reconnect()
    {
        $this->mysql = new \Swoole\Coroutine\MySQL();
        $config      = array(
            "host"       => $this->config["host"],
            "port"       => $this->config["port"] ?? 3306,
            "user"       => $this->config["user"] ?? '',
            "password"   => $this->config["password"] ?? '',
            "database"   => $this->config["database"] ?? '',
            "timeout"    => $this->config["timeout"] ?? 3.0,
            "charset"    => $this->config["charset"] ?? "utf8mb4",
            "fetch_mode" => true,
        );
        $ret         = $this->mysql->connect($config);
        if (!$ret) {
            throw new \Exception($this->mysql->connect_error, $this->mysql->connect_errno);
        }

    }

    public function checkConnection()
    {

        if($this->lastPingTime + 5 < time()){
            $ret = $this->mysql->query("set charset " . ($this->config["charset"] ?? "utf8mb4"), 1.0);
            if (!$ret) {
                $this->reconnect();
            }
        }
    }

    public function beginTransaction()
    {
        return $this->mysql->begin();
    }

    public function commit()
    {
        return $this->mysql->commit();
    }

    public function rollBack()
    {
        return $this->mysql->rollback();
    }

    public function getLastInsertId()
    {
        return $this->mysql->insert_id();
    }

    public function getError(){
        return [
            "msg" => $this->mysql->error,
            "code" => $this->mysql->errno,
        ];
    }

    //todo:
    public function query($sql, $param)
    {
        //check connection
        $this->checkConnection();

        //modify count
        $result = array();
        $handle = $this->mysql->prepare($sql);
        try {
            $result["affect_count"] = $handle->execute($param);
        } catch (\PDOException $e) {
            //todo:错误日志
            //retry
            $this->reconnect();
            $result["affect_count"] = $handle->execute($param);
        }

        $result["result"] = $handle->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function exec($sql)
    {

        $result = 0;

        //check connection
        $this->checkConnection();

        try {
            $handle = $this->mysql->query($sql);
            $result = $handle->rowCount();
        } catch (\PDOException $e) {
            //todo:错误日志
            //retry
            $this->reconnect();
            $handle = $this->mysql->query($sql);
            $result = $handle->rowCount();
        }

        return $result;
    }

}