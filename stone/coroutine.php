<?php

namespace WhetStone\Stone;

class Coroutine
{

    public static function create($callback, ...$argument)
    {
        //get context
        $context = Context::getContext();
        $pid = $context->getContextPid();

        go(function () use ($pid, $callback, $argument) {
            //todo:exception must try
            Context::createContext($pid);
            $callback(...$argument);
        });
    }
}