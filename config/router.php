<?php

return array(
    //method   url  ControllerNameSpace@ControllerFunctionName
    //类型，网址，controller路径@函数名
    //暂时只支持callable和ControllerName@functionName
    array('GET','/test', '\WhetStone\Controller\Test\Test@info'),
    array('POST','/test', '\WhetStone\Controller\Test\Test@info'),
    array('GET','/test/{id:\d+}/{name}', '\WhetStone\Controller\Test\Test@info'),

);