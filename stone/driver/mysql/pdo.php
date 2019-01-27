<?php

namespace WhetStone\Stone\Driver\Mysql;

class PDO
{

    private $config = null;

    private $dbName = "";

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
        $this->mysql = new \PDO(
            "mysql:host=" . $this->config["host"]
            . ";port=" . $this->config["port"]
            . ";dbname=" . $this->config["database"]
            . ";charset=" . $this->config["charset"],
            $this->config["user"],
            $this->config["password"]
        );

        $this->mysql->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->mysql->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $this->mysql->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_NATURAL);
        //convert number to string set no
        $this->mysql->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);

    }

    public function beginTransaction(){
        return $this->mysql->beginTransaction();
    }

    public function commit(){
        return $this->mysql->commit();
    }

    public function rollBack(){
        return $this->mysql->rollBack();
    }

    public function getLastInsertId(){
        return $this->mysql->lastInsertId();
    }

    public function query($sql, $param)
    {
        //modify count
        $result = array();
        $handle = $this->mysql->prepare($sql);
        try{
            $result["affect_count"] = $handle->execute($param);
        }catch (\PDOException $e){
            //todo:错误日志
            //retry
            $this->reconnect();
            $result["affect_count"] = $handle->execute($param);
        }

        $result["result"] = $handle->fetchAll(PDO::FETCH_ASSOC);
    }

}