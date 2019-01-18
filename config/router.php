<?php

return array(
    //method   url  ControllerNameSpace@ControllerFunctionName
    //类型，网址，controller路径@函数名
    array('GET','/test', '\WhetStone\Controller\Test@index'),
    array('POST','/test', '\WhetStone\Controller\Testp@test'),
    array('GET','/test/{id:\d+}/{name}', '\WhetStone\Controller\Testp@test'),

);