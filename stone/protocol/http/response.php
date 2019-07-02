<?php

namespace WhetStone\Stone\Protocol\Http;

/**
 * Http协议返回结果封装
 * Class Http
 * @package WhetStone\Stone\Protocol\Http\Response
 */
class Response
{

    /**
     * @var \Swoole\Http\Response
     */
    private $response = null;

    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * 批量设置header
     * @param array $headers 数组key value
     */
    public function setHeaders($headers)
    {
        foreach ($headers as $header) {
            $this->setHeader($header["key"], $header["value"]);
        }
    }

    /**
     * 单个设置header
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setHeader($key, $value)
    {
        return $this->response->header($key, $value, true);
    }

    /**
     * 设置cookie
     * @param string $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return mixed
     */
    public function setCookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 设置返回的http code
     * @param int $code
     * @return mixed
     */
    public function setHttpCode(int $code)
    {
        return $this->response->status($code);
    }

    /**
     * redirect url
     * @param $url
     * @param $http_code
     * @return mixed
     */
    public function redirect($url, $http_code = 302)
    {
        return $this->response->redirect($url, $http_code);
    }

    /**
     * 分chunk输出数据，最大长度取决于配置buffer_output_size
     * 执行完毕后，调用一下end结束请求
     * @param string $data
     * @return bool
     */
    public function write(string $data)
    {
        return $this->response->write($data);
    }

    /**
     * sendfile必须之前没有调用write
     * Content-Type在发送前设置好
     * @param string $filename
     * @param int $offset
     * @param int $length
     * @return mixed
     */
    public function sendfile(string $filename, int $offset = 0, int $length = 0)
    {
        return $this->response->sendfile($filename, $offset, $length);
    }

    /**
     * 设置返回 http code
     * @param int $httpCode
     * @return mixed
     */
    public function setStatus(int $httpCode){
        return $this->response->status($httpCode);
    }

    /**
     * 结束链接，返回所有数据，如果keepalive不会断开
     * @param string $data
     * @return mixed
     */
    public function end(string $data)
    {
        return $this->response->end($data);
    }

}