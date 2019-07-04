<?php
declare(strict_types=1);

namespace WhetStone\Stone;
/**
 * 验证参数，并且根据规则对数据进行转换
 * Class ValidateFilter
 * @package WhetStone\Stone
 */
class ValidateFilter
{
    public static function getParam($param, $rule)
    {
        $result = [];

        foreach ($rule["rule"] as $ruleItem) {

            //filter value
            if(isset($ruleItem["require"])){
                $param[$ruleItem["key"]] = strval($param[$ruleItem["key"]]);
            }

            //require check
            if (isset($ruleItem["require"]) && $ruleItem["require"] == 1 && (!isset($param[$ruleItem["key"]]) || strlen($param[$ruleItem["key"]]) == 0)) {
                throw new \Exception("参数" . $ruleItem["key"] . " 必填", 3001);
            }

            //other empty value will not check
            if (!isset($param[$ruleItem["key"]]) || empty($param[$ruleItem["key"].""])) {
                //return default
                if (isset($ruleItem["default"])) {
                    $result[$ruleItem["key"]] = $ruleItem["default"];
                }
                //ignore
                continue;
            }
            $limit = isset($ruleItem["limit"])?$ruleItem["limit"]:null;

            //filter var
            $result[$ruleItem["key"]] = self::filterParam($ruleItem["key"], $param[$ruleItem["key"]], $ruleItem["type"], $limit);
        }
        return $result;
    }

    private static function filterParam($key, $val, $type, $limit)
    {

        //check type
        switch (strtolower($type)) {
            case "bool":
                return ($val === true || strtolower($val) == "true" || intval($val) === 1) ? true : false;
            case "int":
                if (strlen($val) > 0 && !is_numeric($val)) {
                    throw new \Exception("参数" . $key . " 只接受数值", 3002);
                }
                return intval($val);
            case "float":
                if (strlen($val) > 0 && !is_numeric($val)) {
                    throw new \Exception("参数" . $key . " 只接受浮点数值", 3002);
                }
                return (float)($val);
            case "double":
                if (strlen($val) > 0 && !is_numeric($val)) {
                    throw new \Exception("参数" . $key . " 只接受double数值", 3002);
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
                if(!in_array($val,$limit)){
                    throw new \Exception("参数" . $key . " 选项不在有效可选范围内", 3002);
                }
                return $val;
            default:
                //regx
                if (strpos($type, "reg:") === 0) {
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
}
