<?php

namespace Core;

class Action
{
    /**
     * 默认错误响应体信息.
     *
     * @var array
     */
    protected $_ERR_MSG = [
        400 => 'Problems parsing JSON',
        401 => 'Bad credentials',
        403 => "Counldn't access this resource",
        404 => 'There is no this resource',
        422 => 'Validation Failed',
    ];

    /**
     * 帮助地址
     *
     * @var string
     */
    protected $_docURL;

    /**
     * 请求类.
     *
     * @var RequestInterface
     */
    protected $_request;

    /**
     * 响应类.
     *
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * 路由参数.
     *
     * @var array
     */
    protected $_args;

    /**
     * Model 实例.
     *
     * @var \Core\Model
     */
    protected $_model;

    /**
     * 容器实例
     *
     * @var Slim\Container
     */
    protected $_container;

    /**
     * init Response and help URL.
     *
     * @param \Slim\Container $container
     */
    // public function __construct(\Psr\Http\Message\ResponseInterface $response)
    public function __construct(\Slim\Container $container)
    {
        $this->_container = $container;
        // 对操作请求进行，并返回响应
        $this->_request = $container->get('request');
        $this->_response = $container->get('response');
        // 获取需要操作的路由参数
        $this->_args = $container->get('args');
        // 获取模型实例
        $reflection = new \ReflectionClass($this);
        $modelname = '\\App\\Model\\'.$reflection->getShortName();
        $this->_model = new $modelname();
        // 初始化帮助地址
        // TODO
        $this->_docURL = SERVER_URL.'/help';
    }

    /**
     * get Response.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * setting set-cookie header.
     *
     * @param \Slim\Http\Cookies $cookie
     * @param array|string       $value
     * @param string             $path
     * @param string             $expires
     * @param bool               $httponly
     */
    protected function cookie($cookie, $value, $path = '/', $expires = null, $httponly = false)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $cookie->set($k, [
                    'value' => $v,
                    'path' => $path,
                    'expires' => $expires,
                    'httponly' => $httponly,
                ]);
            }
            foreach ($cookie->toHeaders() as $c) {
                $this->_response = $this->_response->withAddedHeader('Set-Cookie', $c);
            }
        } else {
            $this->_response = $this->_response->withAddedHeader('Set-Cookie', $value);
        }
    }

    /**
     * 正确响应设置.
     *
     * @param array $data 正确数据
     *
     * @return ResponseInterface
     */
    protected function success($data, $code = 200)
    {
        // 设置响应体
        $this->_response = $this->_response->withJson($data, $code);

        return $this->_response;
    }

    /**
     * 错误响应设置.
     *
     * @param int    $code      错误码
     * @param string $info=null 错误信息
     *
     * @return ResponseInterface
     */
    protected function error($code, $info = null)
    {
        // code is not the http status code
        if (!is_numeric($code) || $code < 200 || $code > 600) {
            // undefined system error
            $code = 400;
        }

        return $this->_response->withJson([
            'message' => is_null($info) ? (array_key_exists($code, $this->_ERR_MSG) ? $this->_ERR_MSG[$code] : 'Unknow Operation') : $info,
            'documentation' => $this->_docURL,
        ], $code);
    }
}
