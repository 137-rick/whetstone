<?php

namespace WhetStone\Stone;

/**
 * 协程go封装
 * 不要直接调用go，会导致context无法正常工作
 * Class Coroutine
 * @package WhetStone\Stone
 */
class Coroutine
{

    public static function create($callback, ...$argument)
    {
        //get context
        $context = Context::getContext();
        $pid     = $context->getContextPid();

        go(function () use ($pid, $callback, $argument) {
            //todo:exception must try
            $context = Context::createContext($pid);

            try {
                $callback(...$argument);
            }catch (\Swoole\ExitException $e){
                //do nothing
            }catch (\Throwable $e){

            }
        });
    }
}