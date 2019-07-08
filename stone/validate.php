<?php

namespace WhetStone\Stone;

/**
 * 统一验证类，用于统一参数验证及文档生成
 * Class Validate
 * @package WhetStone\Stone
 */
class Validate
{

    //api访问网址或uri
    private $api;

    //接口功能介绍
    private $desc;

    //请求类型，可选post get del put rpcx等
    private $method;

    //接口调用演示数据，可多组
    private $demo = [];

    //参数规则
    private $rule = [];

    /**
     * 验证类
     * Validate constructor.
     * @param $api
     * @param $desc
     * @param $method
     */
    public function __construct($api, $desc, $method)
    {
        $this->api    = $api;
        $this->desc   = $desc;
        $this->method = $method;
    }

    /**
     * 添加演示参数
     * @param $param
     */
    public function addDemoParameter($param)
    {
        $this->demo[] = $param;
    }

    /**
     * 添加参数规则
     * @param string $key 参数key
     * @param string $type 参数类型
     * @param string $desc 参数用途介绍
     * @param bool $require 是否必填
     * @param string $default 默认参数
     * @param array $limit 可选项或长度限制
     * @throws \Exception
     */
    public function addRule($key, $type, $desc, $require = false, $default = "", $limit = [])
    {
        //key 必填
        if (empty($key)) {
            throw new \Exception("参数过滤规则必填", 3001);
        }

        //type 必填
        if (empty($type)) {
            throw new \Exception("参数类型必填", 3003);
        }

        //desc 必填
        if (empty($desc)) {
            throw new \Exception("请说明参数用途", 3005);
        }

        //记录rule规则
        $this->rule[$key] = [$type, $limit, $require, $default, $desc];
    }

    private function filterParam($key, $val, $type, $limit)
    {

        //check type
        switch (strtolower($type)) {
            case "bool":
                return filter_var($val, FILTER_VALIDATE_BOOLEAN);
            case "int":
                if (strlen($val) > 0 && !is_numeric($val)) {
                    throw new \Exception("参数" . $key . " 只接受数值", 3002);
                }
                if (!empty($limit) && ($val < $limit[0] || $val > $limit[1])) {
                    throw new \Exception("参数" . $key . " 值限制在" . $limit[0] . "-" . $limit[1] . "之间", 3002);
                }
                return intval($val);
            case "float":
                if (strlen($val) > 0 && !is_numeric($val)) {
                    throw new \Exception("参数" . $key . " 只接受浮点数值", 3002);
                }
                if (!empty($limit) && ($val < $limit[0] || $val > $limit[1])) {
                    throw new \Exception("参数" . $key . " 值限制在" . $limit[0] . "-" . $limit[1] . "之间", 3002);
                }
                return (float)($val);
            case "double":
                if (strlen($val) > 0 && !is_numeric($val)) {
                    throw new \Exception("参数" . $key . " 只接受double数值", 3002);
                }
                if (!empty($limit) && ($val < $limit[0] || $val > $limit[1])) {
                    throw new \Exception("参数" . $key . " 值限制在" . $limit[0] . "-" . $limit[1] . "之间", 3002);
                }
                return (double)($val);
            case "string":
                if (strlen($val) > 0 && !is_string($val)) {
                    throw new \Exception("参数" . $key . " 只接受string类型", 3002);
                }
                if (!empty($limit) && (strlen($val) < $limit[0] || strlen($val) > $limit[1])) {
                    throw new \Exception("参数" . $key . " 长度限制在" . $limit[0] . "-" . $limit[1] . "之间", 3002);
                }
                return $val;
            case "email":
                if (strlen($val) > 0 && !is_string($val)) {
                    throw new \Exception("参数" . $key . " 只接受string类型", 3002);
                }
                if (!empty($limit) && (strlen($val) < $limit[0] || strlen($val) > $limit[1])) {
                    throw new \Exception("参数" . $key . " 长度限制在" . $limit[0] . "-" . $limit[1] . "之间", 3002);
                }
                if (!($val = filter_var($val, FILTER_VALIDATE_EMAIL))) {
                    throw new \Exception("参数" . $key . " 只接受合法email格式数据", 3002);
                }
                return $val;
            case "enum":
                if (!in_array($val, $limit)) {
                    throw new \Exception("参数" . $key . " 选项不在有效可选范围内", 3002);
                }
                return $val;
            case "callback":
                if (is_callable($limit)) {
                    throw new \Exception("参数" . $key . " 验证规则非法", 3004);
                }

                return $limit($key, $val);
            default:
                //regx
                if (strpos($type, "regx:") === 0) {
                    //limit len
                    if (!empty($limit) && (strlen($val) < $limit[0] || strlen($val) > $limit[1])) {
                        throw new \Exception("参数" . $key . " 长度限制在" . $limit[0] . "-" . $limit[1] . "之间", 3002);
                    }

                    $regx = substr($type, 4);
                    if (!preg_match($regx, $val)) {
                        throw new \Exception("参数" . $key . " 只接受符合正则" . $type . "数据", 3002);
                    }
                    return $val;
                }

                throw new \Exception("参数" . $key . " 未知" . $type . "类型定义", 3009);
        }
    }

    public function checkParam($param)
    {
        $result = [];

        //interrupt the rest code
        if($param["tal_sec"]== "show_param_json"){
            echo $this->showDoc();
            exit;
        }

        //check parameters
        foreach ($this->rule as $key => $rule) {

            //empty
            if (!isset($param[$key]) || $param[$key] === "") {

                //require will alert
                if ($rule[2]) {
                    throw new \Exception($key . "参数必填", 3004);
                }

                //default
                if ($rule[3] != "") {
                    $result[$key] = $rule[3];
                }

            }

            $val = strval($param[$key]);

            $result[$key] = $this->filterParam($key, $val, $rule[0], $rule[3]);
        }

        return $result;
    }

    public function showDoc($format = "json")
    {
        return json_encode([
            "api"    => $this->api,
            "method" => $this->method,
            "desc"   => $this->desc,
            "demo"   => $this->demo,
            "param"  => $this->rule,
        ]);
    }


}