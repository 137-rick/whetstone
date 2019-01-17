<?php

namespace WhetStone;

class EventRegister
{

    public function __construct()
    {

        //on worker start init some event
        \WhetStone\Stone\Server\Event::register("worker_start", function () {

            //load all config
            \WhetStone\Stone\ConfigManager::loadAllConfig();

        });
    }
}