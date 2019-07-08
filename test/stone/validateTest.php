<?php
declare(strict_types=1);

namespace WhetStone\Test\Stone;

use mysql_xdevapi\Exception;
use PHPUnit\Framework\TestCase;
use WhetStone\Stone\Validate;

class ValidateTest extends TestCase
{
    public function testValidate()
    {
        $param = [
            "must"     => true,
            "string"   => "wahahah",
            "int"      => "3244",
            "float"    => "43.6",
            "double"   => "123.1",
            "email"    => "test@qq.com",
            "enum"     => "yes",
            "callback" => "ahaha",
        ];

        $validate = new Validate("http://www.test.php/user/info", "根据学生id查找学生信息", "get");
        $validate->addDemoParameter([["uid" => 12312], ["uid" => 123]]);

        $validate->addRule("must", "bool", "bool类型，必填字段", true);
        $validate->addRule("default", "int", "int类型，非必填，默认1", false, 1);
        $validate->addRule("string", "string", "用户uid", false, "", [1, 10]);
        $validate->addRule("int", "int", "用户uid的int写法", false, "", [1, 20000]);
        $validate->addRule("float", "float", "float类型", false, "", [1, 20000]);
        $validate->addRule("double", "double", "double", false, "", [1, 20000]);
        $validate->addRule("email", "email", "email检测", false);
        $validate->addRule("enum", "string", "enum检测:yes代表xx，no代表xx", false, "", ["yes", "no"]);
        $validate->addRule("callback", "callback", "用户回调规则", false, "", function ($key, $val) {
            if ($val != "ahaha") {
                throw new Exception("嗯错误了");
            }
            return $val;
        });

        $result = $validate->checkParam($param);
        var_dump($result);


    }
}