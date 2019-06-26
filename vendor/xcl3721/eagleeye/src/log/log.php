<?php
/**
 * Class Log
 * 分布式链路 分级日志
 */

namespace EagleEye\Log;

class Log
{
    //debug
    const LOG_TYPE_DEBUG = 1;

    //trace
    const LOG_TYPE_TRACE = 2;

    //notice
    const LOG_TYPE_NOTICE = 3;

    //info 信息
    const LOG_TYPE_INFO = 4;

    //错误 信息
    const LOG_TYPE_ERROR = 5;

    //警报 信息
    const LOG_TYPE_EMEGENCY = 6;

    //异常 信息
    const LOG_TYPE_EXCEPTION = 7;


    private static $log_level = self::LOG_TYPE_INFO;

    /**
     * 写入日志
     * 老方式，留着兼容，这个方式是作为一些特殊业务日志用的
     * @param  array|string $msg 日志内容
     * @param  string $dumpFile 文件名
     * @param  int $debug 是否直接显示，0不显示，1直接抛出
     * @return void
     **/
    public static function write($msg, $dumpFile = './logs/common/common.log', $debug = 0)
    {
        // 将数据转换为字符串
        is_array($msg) && $msg = var_export($msg, true);

        // 如果是debug则直接输出
        if ($debug) {
            echo $msg . "\n";
        }

        // 写入日志
        file_put_contents($dumpFile, date("Y-m-d H:i:s > ") . "{$msg}\n", FILE_APPEND);
    }

    /**
     * 书写原始日志文件，输入是什么就输出什么，数组会转成字符串
     * @param $msg
     * @param string $dumpFile
     * @param int $debug
     */
    public static function rawwrite($msg, $dumpFile = './logs/raw/raw.log', $debug = 0)
    {
        // 将数据转换为字符串
        is_array($msg) && $msg = var_export($msg, true);

        // 如果是debug则直接输出
        if ($debug) {
            echo $msg . "\n";
        }
        // 写入日志
        file_put_contents($dumpFile, $msg . "\n", FILE_APPEND);
    }


    /**
     * 设置当前系统最低记录日志级别
     * @param int $level 日志级别，建议使用本类常量
     */
    public static function setLogLevel($level)
    {
        //set the log level
        if ($level > 9 || $level < 1) {
            return;
        } else {
            //init other parameter from config
            self::$log_level = $level;
        }
    }

    /**
     * debug 日志输出,用于线下寻找bug使用，日志量很多 平时不要开
     * @param string $tag 标识符，可以用module_function_action 形式
     * @param string $file 文件路径可以使用__FILE__作为传入
     * @param string $line 当前产生日志的代码行数，可以使用__LINE__
     * @param string $msg 警报文字原因
     * @param array $extra 附加数据
     */
    public static function debug($tag, $msg, $file = "", $line = "", $extra = array())
    {
        //如果没有写line和file自己检测，不过性能不好
        if ($file == "" || $line == "") {
            $trace = debug_backtrace();
            $index = count($trace) - 1;
            $file = $trace[$index]['file'];
            $line = $trace[$index]['line'];
        }
        //ignore the level log
        if (self::$log_level > self::LOG_TYPE_DEBUG) {
            return;
        }

        self::recordLog("log.debug", $tag, $file, $line, $msg, $extra);
    }

    /**
     * trace 跟踪信息,用于线下数据过程变量内容输出，日志量很多 平时不要开
     * @param string $tag 标识符，可以用module_function_action 形式
     * @param string $file 文件路径可以使用__FILE__作为传入
     * @param string $line 当前产生日志的代码行数，可以使用__LINE__
     * @param string $msg 警报文字原因
     * @param array $extra 附加数据
     */
    public static function trace($tag, $msg, $file = "", $line = "", $extra = array())
    {
        //如果没有写line和file自己检测，不过性能不好
        if ($file == "" || $line == "") {
            $trace = debug_backtrace();
            $index = count($trace) - 1;
            $file = $trace[$index]['file'];
            $line = $trace[$index]['line'];
        }
        //ignore the level log
        if (self::$log_level > self::LOG_TYPE_TRACE) {
            return;
        }
        self::recordLog("log.trace", $tag, $file, $line, $msg, $extra);
    }

    /**
     * 注意信息,用于线上线下警告数据，生产环境推荐开到info
     * @param string $tag 标识符，可以用module_function_action 形式
     * @param string $file 文件路径可以使用__FILE__作为传入
     * @param string $line 当前产生日志的代码行数，可以使用__LINE__
     * @param string $msg 警报文字原因
     * @param array $extra 附加数据
     */
    public static function notice($tag, $msg, $file = "", $line = "", $extra = array())
    {
        //如果没有写line和file自己检测，不过性能不好
        if ($file == "" || $line == "") {
            $trace = debug_backtrace();
            $index = count($trace) - 1;
            $file = $trace[$index]['file'];
            $line = $trace[$index]['line'];
        }
        //ignore the level log
        if (self::$log_level > self::LOG_TYPE_NOTICE) {
            return;
        }
        self::recordLog("log.notice", $tag, $file, $line, $msg, $extra);
    }

    /**
     * 提示信息，用于一些需要注意的日志信息
     * @param string $tag 标识符，可以用module_function_action 形式
     * @param string $file 文件路径可以使用__FILE__作为传入
     * @param string $line 当前产生日志的代码行数，可以使用__LINE__
     * @param string $msg 警报文字原因
     * @param array $extra 附加数据
     */
    public static function info($tag, $msg, $file = "", $line = "", $extra = array())
    {
        //如果没有写line和file自己检测，不过性能不好
        if ($file == "" || $line == "") {
            $trace = debug_backtrace();
            $index = count($trace) - 1;
            $file = $trace[$index]['file'];
            $line = $trace[$index]['line'];
        }
        //ignore the level log
        if (self::$log_level > self::LOG_TYPE_INFO) {
            return;
        }
        self::recordLog("log.info", $tag, $file, $line, $msg, $extra);
    }

    /**
     * 线上错误信息
     * @param string $tag 标识符，可以用module_function_action 形式
     * @param string $file 文件路径可以使用__FILE__作为传入
     * @param string $line 当前产生日志的代码行数，可以使用__LINE__
     * @param string $msg 警报文字原因
     * @param array $extra 附加数据
     */
    public static function error($tag, $msg, $file = "", $line = "", $extra = array())
    {
        //如果没有写line和file自己检测，不过性能不好
        if ($file == "" || $line == "") {
            $trace = debug_backtrace();
            $index = count($trace) - 1;
            $file = $trace[$index]['file'];
            $line = $trace[$index]['line'];
        }
        //ignore the level log
        if (self::$log_level > self::LOG_TYPE_ERROR) {
            return;
        }
        self::recordLog("log.error", $tag, $file, $line, $msg, $extra);
    }

    /**
     * 线上警报信息，后续会对这个进行合并发送警报邮件
     * @param string $tag 标识符，可以用module_function_action 形式
     * @param string $file 文件路径可以使用__FILE__作为传入
     * @param string $line 当前产生日志的代码行数，可以使用__LINE__
     * @param string $msg 警报文字原因
     * @param array $extra 附加数据
     */
    public static function alarm($tag, $msg, $file = "", $line = "", $extra = array())
    {
        //如果没有写line和file自己检测，不过性能不好
        if ($file == "" || $line == "") {
            $trace = debug_backtrace();
            $index = count($trace) - 1;
            $file = $trace[$index]['file'];
            $line = $trace[$index]['line'];
        }
        //ignore the level log
        if (self::$log_level > self::LOG_TYPE_EMEGENCY) {
            return;
        }
        self::recordLog("log.alarm", $tag, $file, $line, $msg, $extra);
    }

    /**
     * 线上警报信息，后续会对这个进行合并发送警报邮件
     * @param string $tag 标识符，可以用module_function_action 形式
     * @param string $file 文件路径可以使用__FILE__作为传入
     * @param string $line 当前产生日志的代码行数，可以使用__LINE__
     * @param string $msg 具体错误信息
     * @param string $code 错误码
     * @param string $backtrace 具体错误信息
     * @param array $extra 附加数据
     */
    public static function exception($tag, $msg, $file = "", $line = "", $code = "", $backtrace = "", $extra = array())
    {
        //如果没有写line和file自己检测，不过性能不好
        if ($file == "" || $line == "") {
            $trace = debug_backtrace();
            $index = count($trace) - 1;
            $file = $trace[$index]['file'];
            $line = $trace[$index]['line'];
        }
        $extra["code"] = $code;
        $extra["backtrace"] = $backtrace;
        self::recordLog("log.exception", $tag, $file, $line, $msg, $extra);
    }

    private static function recordLog($logname, $tag, $file, $line, $msg, $extra = array())
    {

        //错误信息如果是数组，那么会强转json
        if (is_array($msg)) {
            $msg = json_encode($msg);
        }

        $log = array(
            "x_name" => $logname,
            "x_trace_id" => \EagleEye\Trace\EagleEye::getTraceId(),
            "x_rpc_id" => \EagleEye\Trace\EagleEye::getCurrentRpcId(),
            "x_version" => \EagleEye\Trace\EagleEye::getVersion(),
            "x_timestamp" => time(),
            "x_module" => $tag,
            "x_uid" => \EagleEye\Trace\EagleEye::getRequestLogInfo("uid"),
            "x_pid" => getmypid(),
            "x_file" => basename($file),
            "x_line" => $line,
            "x_msg" => $msg,
        );

        $extraList = array();

        //非指定常量，那么会放到extra里面
        if ($extra) {
            //addtional log
            foreach ($extra as $key => $val) {
                if (in_array($key, array("duration", "code", "backtrace", "dns_duration", "source", "uid", "server_ip",
                    "client_ip", "user_agent", "host", "instance_name", "db", "action", "param",
                    "response", "response_length"))) {
                    //为了防止和业务名称重复，固定字段都必须有x_前缀
                    $log["x_" . $key] = $val;
                } else {
                    $extraList[$key] = $val;
                }
            }
        }

        $log["x_extra"] = json_encode($extraList);

        \EagleEye\Dump\LogAgent::log($log);
    }


    public static function getLogLevelName($level)
    {
        switch ($level) {
            case 1:
                return "log.debug";
                break;
            case 2:
                return "log.trace";
                break;
            case 3:
                return "log.notice";
                break;
            case 4:
                return "log.info";
                break;
            case 5:
                return "log.error";
                break;
            case 6:
                return "log.alarm";
                break;
            case 7:
                return "log.exception";
                break;
            default:
                return "log.unknow:" . $level;
        }
    }
}

