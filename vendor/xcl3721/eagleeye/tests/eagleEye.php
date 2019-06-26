<?php
/**
 * Created by PhpStorm.
 * User: weizeng
 * Date: 2018/3/16
 * Time: 下午3:54
 */

require "../vendor/autoload.php";

use EagleEye\Classes\EagleEye;

EagleEye::allow();

EagleEye::requestStart($traceid, $rpcid);
$traceid = EagleEye::getTraceId();
$rpcid = EagleEye::getReciveRpcId();

//record this request
EagleEye::setRequestLogInfo("client_ip", "127.0.0.1");
EagleEye::setRequestLogInfo("action", "http://www.baidu.com");
EagleEye::setRequestLogInfo("param", json_encode(array("post" => $_POST, "get" => $_GET)));
EagleEye::setRequestLogInfo("source", "http_referer");
EagleEye::setRequestLogInfo("user_agent", "http_header_user-agent");
$string = "test_response";
EagleEye::setRequestLogInfo("response", $string);
EagleEye::setRequestLogInfo("response_length", strlen($string));
EagleEye::requestFinished();


EagleEye::requestStart($traceid, $rpcid);
$infos = [
    "client_ip" => "127.0.0.1",
    "action" => "http://www.baidu.com",
    "param" => json_encode(array("post" => $_POST, "get" => $_GET)),
    "source" => "http_referer",
    "user_agent" => "http_header_user-agent",
    "response" => $string,
    "response_length" => strlen($string),
];
EagleEye::batchSetRequestLogInfo($infos);
EagleEye::requestFinished();


