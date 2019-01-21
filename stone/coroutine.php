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

    /**
     * 创建协程
     * @param callable $callback 要注册的回调函数，如果有use传入之前准备好
     * @param array ...$argument 回调函数参数
     * @throws \Exception
     */
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
                //这里是执行exit产生的事件，忽略即可
            }catch (\Throwable $e){
                //todo:这里没有处理啊,计划传递到外面去
            }
        });
    }
}