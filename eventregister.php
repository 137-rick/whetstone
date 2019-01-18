<?php

namespace WhetStone;

class EventRegister
{

    public function __construct()
    {

        //on worker start init some event
        \WhetStone\Stone\Server\Event::register("worker_start", function () {

            //load all config
            \WhetStone\Stone\Config::loadAllConfig();

        });

        //on worker start init some event
        \WhetStone\Stone\Server\Event::register("Main_request", function () {

            $context = \WhetStone\Stone\Context::getContext();
            $response = $context->get("response");
            $response->end("123");

        });
    }
}