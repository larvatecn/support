<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Support;

use Larva\Support\Traits\HasHttpRequest;

/**
 * HTTP Client
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HttpClient
{
    use HasHttpRequest;

    /**
     * HttpClient constructor.
     */
    public function __construct()
    {
        $this->options = [
            'http_errors' => false,
        ];
        $this->init();
    }

    /**
     * init
     */
    public function init()
    {

    }

    /**
     * @return $this
     */
    public static function make(): HttpClient
    {
        return new static();
    }

    /**
     * 获取服务器证书
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return array|false
     */
    public static function getCertificate(string $host, $port = 443, $timeout = 60)
    {
        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'verify_peer', false);//不验证证书合法
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);//不验证主机名是否对应
        stream_context_set_option($context, 'ssl', 'capture_peer_cert', true);//获取证书详情
        try {
            $resource = stream_socket_client('ssl://' . $host . ':' . $port, $errno, $errStr, $timeout, STREAM_CLIENT_CONNECT, $context);
            $cert = stream_context_get_params($resource);
            return openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 获取 响应的 Header
     * @param string $url 目标Url
     * @param array $headers Headers
     * @param int $timeout 超时时间
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getHeaders(string $url, $headers = [], $timeout = 5)
    {
        $client = static::make();
        $client->withoutVerifying();
        $client->withHeaders($headers);
        $client->timeout($timeout);
        $response = $client->get($url);
        return $response->toPsrResponse()->getHeaders();
    }

    /**
     * 检查 CORS 跨域
     * @param string $url 检查的Url
     * @param string $origin 来源
     * @param int $timeout 超时时间
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function checkCors(string $url, string $origin, $timeout = 5): bool
    {
        $headers = static::getHeaders($url, ['Referer' => $origin, 'Origin' => $origin], $timeout);
        if (isset($headers['Access-Control-Allow-Origin']) && in_array($headers['Access-Control-Allow-Origin'][0], [$origin, '*'])) {
            return true;
        }
        return false;
    }
}
