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


            try{
                $context = \WhetStone\Stone\Context::getContext();
                $response = $context->get("response");
                $response->end("123");
                if(mt_rand(0,100000) == 1){
                    var_dump($context->getStastics());
                }
            }catch (\Throwable $e){

            }


        });
    }
}