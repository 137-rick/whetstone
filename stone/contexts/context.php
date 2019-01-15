<?php

namespace WhetStone\Stone\Contexts;

class Context
{

    private $param;

    private $data = array();

    public function __construct($param)
    {
        $this->param = $param;
    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    public function getAll()
    {
        return $this->data;
    }
}