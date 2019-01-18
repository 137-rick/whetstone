<?php

namespace WhetStone\Stone\Protocol\Request;

class Http
{

    /**
     * @var \Swoole\Http\Request
     */
    private $request = null;

    public function __construct(\Swoole\Http\Request $request)
    {
        $this->request = $request;
    }

    /**
     * 获取Header信息
     * @param string $name 不指定获取所有header，指定获取指定key的header
     * @return string
     */
    public function getHeader($name = '')
    {
        if ($name == '') {
            return $this->request->header;
        }

        if (!isset($this->request->header[$name])) {
            return '';
        }

        return $this->request->header[$name];
    }

    /**
     * 获取Server信息
     * @param string $name 不指定获取所有server，指定获取指定key的server
     * @return string
     */
    public function getServer($name = '')
    {
        if ($name == '') {
            return $this->request->server;
        }

        if (!isset($this->request->server[$name])) {
            return '';
        }

        return $this->request->server[$name];
    }

    /**
     * 获取Get内容
     * @param string $name 不指定获取所有get，指定获取指定key的get
     * @return string
     */
    public function getGet($name = '')
    {
        if ($name == '') {
            return $this->request->get;
        }

        if (!isset($this->request->get[$name])) {
            return '';
        }

        return $this->request->get[$name];
    }

    /**
     * 获取Post内容
     * @param string $name 不指定获取所有post，指定获取指定key的post
     * @return string
     */
    public function getPost($name = '')
    {
        if ($name == '') {
            return $this->request->post;
        }

        if (!isset($this->request->post[$name])) {
            return '';
        }

        return $this->request->post[$name];
    }

    /**
     * 获取cookie
     * @param string $name 不指定获取所有cookie，指定获取指定key的cookie
     * @return string
     */
    public function getCookie($name = '')
    {

        if ($name == '') {
            return $this->request->cookie;
        }

        if (!isset($this->request->cookie[$name])) {
            return '';
        }

        return $this->request->cookie[$name];

    }

    /**
     * 获取所有上传文件
     * @return mixed
     */
    public function getUploadFiles()
    {
        return $this->request->files;
    }

    /**
     * 获取原始body，不做任何解析
     * @return mixed
     */
    public function getRawBody()
    {
        return $this->request->rawContent();
    }

    /**
     * 获取原始请求报文 header+body
     * @return mixed
     */
    public function getRawRequest()
    {
        return $this->request->getData();
    }

    /**
     * 获取当前请求fd
     * @return int
     */
    public function getFd()
    {
        return $this->request->fd;
    }
}