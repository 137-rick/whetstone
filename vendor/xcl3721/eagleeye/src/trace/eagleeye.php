<?php

/**
 * Class EagleEye
 * 分布式链路 跟踪类
 */

namespace EagleEye\Trace;

class EagleEye
{
    //log dump mode:sync async
    static $_log_dump_mode = "sync";

    //set at first
    static $_server_ip = "";
    static $_version = "php-1.5";
    static $_department = "wheatstone";

    //running parameter
    //must reset every request
    static $_start_timestamp = 0;
    static $_trace_id = "";
    static $_rpc_id = "1";
    static $_rpc_id_seq = 1;
    static $_pid = "";

    //depend client var
    static $_context = array();
    static $_extra_context = array();

    //init 标志，如果不是1，所有埋点每次都会产生一个trace_id
    static $_init = 0;

    //disable 开关
    static $_disable = true;

    // local ip
    static $_serverIp = '0.0.0.0';

    public static $avalibleKey = array(
        "x_name" => "string",
        "x_trace_id" => "string",
        "x_rpc_id" => "string",
        "x_department" => "string",
        "x_version" => "string",
        "x_timestamp" => "int",
        "x_duration" => "float",
        "x_module" => "string",
        "x_source" => "string",
        "x_uid" => "string",
        "x_pid" => "string",
        "x_server_ip" => "string",
        "x_client_ip" => "string",
        "x_user_agent" => "string",
        "x_host" => "string",
        "x_db" => "string",
        "x_code" => "string",
        "x_msg" => "string",
        "x_backtrace" => "string",
        "x_action" => "string",
        "x_param" => "string",
        "x_file" => "string",
        "x_line" => "string",
        "x_response" => "string",
        "x_response_length" => "int",
        "x_dns_duration" => "float",
        "x_instance_name" => "string",
        //"x_extra" => "string", process on bottom

    );

    /**
     * 设置当前请求变量使用，会在被请求完毕后产生一条日志,此用于记录每次被请求情况的附加和可选参数的设置
     * 所有日志都会带上这些参数
     * @param string $key 可选项目 uid code client_ip action source user_agent param 非此选项则会记录在日志extra字段内
     * @param string $val 值内容
     */
    public static function setRequestLogInfo($key, $val)
    {
        if (trim($key) != "") {
            if (in_array($key, array("uid", "code", "client_ip", "action", "source", "user_agent", "param", "response",
                                     "response_length", "msg", "backtrace"))) {
                self::$_context[$key] = $val . "";
            } else {
                self::$_extra_context[$key] = $val;
            }
        }
    }

    /**
     * 批量设置当前附加变量
     * @param array $batchLogs
     */
    public static function batchSetRequestLogInfo($batchLogs)
    {
        foreach ($batchLogs as $key => $val) {
            self::setRequestLogInfo($key, $val);
        }
    }

    public static function getRequestLogInfo($key = "")
    {
        if (trim($key) != "") {
            if (in_array($key, array("uid", "code", "client_ip", "action", "source", "user_agent", "param", "response",
                                     "response_length", "msg", "backtrace"))) {
                if (isset(self::$_context[$key])) {
                    return self::$_context[$key];
                }
                return "";
            } else {
                return self::$_extra_context[$key];
            }
        }
        return self::$_extra_context;
    }

    public static function resetRequestLogInfo()
    {
        self::$_context = array();
        self::$_extra_context = array();
    }

    /**
     * 特殊情况下禁用trace日志
     * @param bool $disable 设置为true禁用，false启用
     */
    public static function disable($disable = true)
    {
        self::$_disable = $disable;
    }

    /**
     * 开启 trace 日志
     */
    public static function allow()
    {
        self::$_disable = false;
    }

    /**
     * 初始化相关参数
     */
    public static function init($serverIp)
    {
        if (empty($serverIp)) {
            self::$_server_ip = "0.0.0.0";
        } else {
            self::$_server_ip = $serverIp;
        }
    }

    /**
     * 每次框架被请求开始调用，用来初始化
     * @param string $trace_id 如果其他接口传递过来traceid设置此值
     * @param string $rpc_id 如果其他接口传递过来rpcid 设置此值
     */
    public static function requestStart($trace_id = "", $rpc_id = "", $serverIp = "")
    {
        self::init($serverIp);

        //切换为trace_id模式
        self::$_init = 1;

        //get local ip
        self::getServerIp();

        //get my pid
        self::$_pid = getmypid();

        //set trace id by parameter
        if ($trace_id == "") {
            //general trace id
            self::generalTraceId();
        } else {
            self::$_trace_id = $trace_id;
        }

        //reset rpc id and seq
        if ($rpc_id == "") {
            self::$_rpc_id = "1";
        } else {
            self::$_rpc_id = $rpc_id;
        }
        self::$_rpc_id_seq = 1;


        //record start timestamp
        self::$_start_timestamp = microtime(true);

        self::resetRequestLogInfo();
    }


    /**
     * 请求结束后调用
     * 用于记录请求信息、结果
     */
    public static function requestFinished()
    {
        //set at first
        $log = array(
            "x_name" => "request.info",
            "x_version" => self::$_version,
            "x_trace_id" => self::$_trace_id,
            "x_rpc_id" => self::getReciveRpcId().".1",
            "x_department" => self::$_department,
            "x_server_ip" => self::getServerIp(),
            "x_timestamp" => (int)self::$_start_timestamp,
            "x_duration" => round(microtime(true) - self::$_start_timestamp, 4),
            "x_pid" => self::$_pid . "",
            "x_module" => "php_request_end",
            "x_extra" => self::$_extra_context
        );

        //option value added
        foreach (self::$_context as $key => $val) {
            $log["x_" . $key] = $val;
        }
        $log = self::formatLog($log);
        self::recordlog($log);

    }

    /**
     * EagleEye其他类型日志记录函数，自动填写公用日志内容
     * 如mysql、redis、memcache、websocket、http的 连接、查询、关闭、错误
     * @param array $param 日志内容，非规定字段会加到extra
     * @param string $rpc_id 如果设置，那么此次日志使用设置值作为rpcid进行记录，用于请求其他资源时先生成rpcid
     */
    public static function baseLog($param, $rpc_id = "")
    {
        if (self::$_disable) {
            return;
        }

        //set at first
        $log = array(
            "x_version"     => self::$_version,
            "x_trace_id"    => self::$_trace_id,
            "x_department"  => self::$_department,
            "x_server_ip"   => self::getServerIp(),
            "x_timestamp"   => time(),
            "x_pid"         => getmypid(),
            "x_uid"         => self::getRequestLogInfo("uid"),
            "x_client_ip"   => self::getRequestLogInfo("client_ip"),
        );

        //rpc id value decide
        if ($rpc_id == "") {
            $log["x_rpc_id"] = self::getNextRpcId();
        } else {
            $log["x_rpc_id"] = $rpc_id;
        }

        //format input log
        $log = self::formatLog(array_merge($log,$param));

        //record log
        self::recordlog($log);
    }

    /**
     * 统一日志格式化函数
     * @param $log
     * @return array
     */
    public static function formatLog($log)
    {

        $format_log = array();
        $unknow_field = array();

        //foreach and filter the field
        foreach ($log as $key => $val) {

            if ($key == "x_extra") {
                continue;
            }

            if (isset(self::$avalibleKey[$key])) {
                //convert the value type
                if (self::$avalibleKey[$key] === "string") {
                    if (is_numeric($val)) {
                        $val = $val . "";
                    } else if (is_array($val)) {
                        $val = json_encode($val);
                    }
                } elseif (self::$avalibleKey[$key] === "int") {
                    $val = intval($val);
                } elseif (self::$avalibleKey[$key] === "float") {
                    $val = floatval($val);
                }
                $format_log[$key] = $val;
            } else {
                $unknow_field[$key] = $val;
            }
        }

        //append unknow field

        if (isset($log["x_extra"]) && is_array($log["x_extra"])) {
            $log["x_extra"]["unknow"] = $unknow_field;
            $format_log["x_extra"] = json_encode($log["x_extra"]);
        } else if (isset($log["x_extra"]) && is_string($log["x_extra"])) {
            $log["x_extra"] = json_decode($log["x_extra"],true);
            $log["x_extra"]["unknow"] = $unknow_field;
            $format_log["x_extra"] = json_encode($log["x_extra"]);
        } else {
            $log["x_extra"]["unknow"] = $unknow_field;
            $format_log["x_extra"] = json_encode($log["x_extra"]);

        }

        return $format_log;
    }

    /**
     * 通过shell命令获取当前ip列表，并找出a\b\c类ip地址。
     * 用于自动识别当前服务器ip地址
     * 建议使用root权限服务使用
     * @param string $localIP
     * @return string
     */
    public static function getLocalIp()
    {
        if (isset($_SERVER)) {
            if ($_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip ? $server_ip : "0.0.0.0";
//        if (!extension_loaded('swoole')) {
//            $serverIps = swoole_get_local_ip();
//        }
//        $serverIps = swoole_get_local_ip();
//        $patternArray = array(
//            '10\.',
//            '172\.1[6-9]\.',
//            '172\.2[0-9]\.',
//            '172\.31\.',
//            '192\.168\.'
//        );
//
//        foreach ($serverIps as $serverIp) {
//            // 匹配内网IP
//            if (preg_match('#^' . implode('|', $patternArray) . '#', $serverIp)) {
//                return trim($serverIp);
//            }
//        }
//        //can't found ok use first
//        return $localIP;
    }

    public static function getServerIp()
    {
        return self::$_server_ip;
    }

    /**
     * 获取当前traceid
     * @return string
     */
    public static function getTraceId()
    {
        //如果没有初始化，那么每次请求都会用一个trace_id
        if (self::$_trace_id == "" || self::$_init == 0) {
            self::generalTraceId();
        }
        return self::$_trace_id;

    }

    /**
     * 刷新重新生成当前TraceID
     * @return string
     */
    public static function generalTraceId()
    {
        self::$_trace_id = self::$_server_ip . "_" . getmypid() . "_" . (microtime(true) - strtotime("2017-01-01")) . "_" . mt_rand(0, 255);
        return self::$_trace_id;
    }

    /**
     * 获取当前RPCID前段，不含自增值
     * @return string
     */
    public static function getReciveRpcId()
    {
        return self::$_rpc_id;
    }

    /**
     * 获取当前rpcid 包括当前计数
     * @return string
     */
    public static function getCurrentRpcId()
    {
        return self::$_rpc_id . "." . self::$_rpc_id_seq;
    }

    /**
     * 获取下一个RPC ID,发送给被调用方
     * @return string
     */
    public static function getNextRpcId()
    {
        if (self::$_init == 0) {
            return "1.1";
        }
        self::$_rpc_id_seq++;
        return self::$_rpc_id . "." . self::$_rpc_id_seq;
    }

    /**
     * 设置当前服务版本
     * @param $version
     */
    public static function setVersion($version)
    {
        self::$_version = $version;
    }

    /**
     * 获取当前服务版本
     * @return string
     */
    public static function getVersion()
    {
        return self::$_version;
    }

    /**
     * 性能记录开始，会返回耗时时间
     * 找个地方记录好这个返回
     * @return mixed
     */
    public static function startDuration()
    {
        return microtime(true);
    }

    /**
     * 性能记录结束，传入之前开始返回值，会返回耗时时间
     * @param $startPoint
     * @return mixed
     */
    public static function endDuration($startPoint)
    {
        return microtime(true) - $startPoint;
    }

    /**
     * 写日志
     * @param $log
     */
    private static function recordlog($log)
    {
        if (!self::$_disable) {
            \EagleEye\Dump\LogAgent::log($log);
        }
    }


}