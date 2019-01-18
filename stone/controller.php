<?php

namespace WhetStone\Stone;

class Controller
{
    public function showJson($msg, $code, $data = array())
    {
        return json_encode(array(
                "code" => $code,
                "msg"  => $msg,
                "data" => $data,
            ),JSON_BIGINT_AS_STRING);
    }
}