<?php

namespace WhetStone\Test\Stone;

use PHPUnit\Framework\TestCase;

class ValidateFilterTest extends TestCase
{
    public function testParamValidate()
    {

        //key type default require default desc
        //int32 int64 float32 double string email tel mobile timestamp url ip
        $param = [
            "bool"   => "-1",
            "uid"    => "1232324",
            "float"  => 23.1,
            "double" => "23.1",
            "string" => "string",
            "email"  => "xcl_rockman@qq.com",
            "enum"   => 3,
        ];
        $rule  = [
            "url"    => "http://www.tal.com/haha/info",
            "method" => "post",
            "desc"   => "这个接口是用来展示用的",
            "demo"   => [
                [
                    'bool'   => false,
                    'uid'    => 1232324,
                    'float'  => 23.1,
                    'double' => 23.1,
                    'email'  => 'xcl_rockman@qq.com',
                    'string' => 'string',
                    'tel'    => 1,
                    'enum'   => 3,
                ]
            ],
            "rule"   => [
                [
                    "key"     => "bool",
                    "require" => 1,
                    "type"    => "bool",
                    "desc"    => "bool"
                ],
                [
                    "key"     => "uid",
                    "require" => 1,
                    "type"    => "int",
                    "default" => 1,
                    "desc"    => "用户uid"
                ],
                [
                    "key"     => "float",
                    "require" => 1,
                    "type"    => "float",
                    "limit"   => [1, PHP_INT_MAX],
                    "desc"    => "数字测试"
                ],
                [
                    "key"  => "double",
                    "type" => "double",
                    "desc" => "double数字测试"
                ],
                [
                    "key"     => "email",
                    "require" => 1,
                    "type"    => "email",
                    "desc"    => "邮箱"
                ],
                [
                    "key"     => "string",
                    "require" => 1,
                    "type"    => "string",
                    "default" => 1,
                    "desc"    => "string"
                ],
                [
                    "key"     => "tel",
                    "type"    => "tel",
                    "default" => 1,
                    "desc"    => "用户uid"
                ],
                [
                    "key"   => "enum",
                    "type"  => "enum",
                    "limit" => [1, 2, 3, 4],
                    "desc"  => "可选项目"
                ],

            ]
        ];
        $eg    = [
            'bool'   => false,
            'uid'    => 1232324,
            'float'  => 23.1,
            'double' => 23.1,
            'email'  => 'xcl_rockman@qq.com',
            'string' => 'string',
            'tel'    => 1,
            'enum'   => 3,
        ];


        $result = \WhetStone\Stone\ValidateFilter::getParam($param, $rule);
        self::assertEmpty(array_diff_assoc($result, $eg));
    }
}