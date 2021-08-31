<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Larva\Support\Exception\ConnectionException;
use Larva\Support\Traits\HasHttpRequest;

/**
 * HTTP 客户端
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HttpClient extends BaseObject
{
    use HasHttpRequest;

    /**
     * @var mixed
     */
    protected $guzzle_handler;

    /**
     * 设置 guzzle handler
     * @param mixed $handler
     * @return $this
     */
    public function setGuzzleHandler($handler): HttpClient
    {
        $this->guzzle_handler = $handler;
        return $this;
    }

    /**
     * Issue a GET request to the given URL.
     *
     * @param string $url
     * @param array|string|null $query
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function getJSON(string $url, $query = null)
    {
        $this->acceptJson();
        $response = $this->get($url, $query);
        return $response->json();
    }

    /**
     * Issue a POST request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function postJSON(string $url, array $data = [])
    {
        $this->acceptJson();
        $this->asJson();
        $response = $this->post($url, $data);
        return $response->json();
    }

    /**
     * Issue a PATCH request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function patchJSON(string $url, $data = [])
    {
        $this->acceptJson();
        $this->asJson();
        $response = $this->patch($url, $data);
        return $response->json();
    }

    /**
     * Issue a PUT request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function putJSON(string $url, $data = [])
    {
        $this->acceptJson();
        $this->asJson();
        $response = $this->put($url, $data);
        return $response->json();
    }

    /**
     * Issue a DELETE request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function deleteJSON(string $url, $data = [])
    {
        $this->acceptJson();
        $this->asJson();
        $response = $this->delete($url, $data);
        return $response->json();
    }

    /**
     * @return $this
     */
    public static function make(): HttpClient
    {
        return new static();
    }

    /**
     * 仅使用IPV4
     */
    public static function onlyIPv4()
    {
        self::$defaultOptions[RequestOptions::FORCE_IP_RESOLVE] = 'v4';
    }

    /**
     * 仅使用IPV6
     */
    public static function onlyIPv6()
    {
        self::$defaultOptions[RequestOptions::FORCE_IP_RESOLVE] = 'v6';
    }

    /**
     * 获取 SSL 证书链
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return array|false
     */
    public static function getSSLCertChain(string $host, int $port = 443, int $timeout = 60)
    {
        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'verify_peer', false);//不验证证书合法
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);//不验证主机名是否对应
        stream_context_set_option($context, 'ssl', 'capture_peer_cert_chain', true);//获取证书链
        try {
            $stream = stream_socket_client('ssl://' . $host . ':' . $port, $errno, $errStr, $timeout, STREAM_CLIENT_CONNECT, $context);
            $params = stream_context_get_params($stream);
            stream_socket_shutdown($stream, STREAM_SHUT_WR);
            $certificateChain = [];
            foreach ($params['options']['ssl']['peer_certificate_chain'] as $cert) {
                $certificateChain[] = SSLCertificate::make($cert);
            }
            return $certificateChain;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 获取服务器 SSL 证书
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return SSLCertificate|false
     */
    public static function getSSLCert(string $host, int $port = 443, int $timeout = 60)
    {
        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'verify_peer', false);//不验证证书合法
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);//不验证主机名是否对应
        stream_context_set_option($context, 'ssl', 'capture_peer_cert', true);//获取证书详情
        try {
            $stream = stream_socket_client('ssl://' . $host . ':' . $port, $errno, $errStr, $timeout, STREAM_CLIENT_CONNECT, $context);
            $params = stream_context_get_params($stream);
            stream_socket_shutdown($stream, STREAM_SHUT_WR);
            return SSLCertificate::make($params['options']['ssl']['peer_certificate']);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 模拟浏览器下载远程文件内容(不保存)
     * @param string $url
     * @param int $timeout
     * @return false|string
     */
    public static function getRemoteContent(string $url, int $timeout = 5)
    {
        try {
            return static::make()
                ->withoutVerifying()
                ->timeout($timeout)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36 Edg/84.0.522.59')
                ->get($url)->body();
        } catch (\Exception | \Throwable $e) {
            return false;
        }
    }

    /**
     * 保存远程文件到本地目录
     * @param string $url 远程Url
     * @param string $path 保存路径
     * @param int $mode
     * @param false $lock
     * @return bool|int
     * @throws GuzzleException
     */
    public static function saveRemoteFileAs(string $url, string $path, int $mode = 0755, bool $lock = false)
    {
        try {
            return static::make()
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36 Edg/84.0.522.59')
                ->get($url)->throw()->saveAs($path, $mode, $lock);
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
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public static function getHeaders(string $url, array $headers = [], int $timeout = 5): array
    {
        return static::make()
            ->withoutVerifying()
            ->withHeaders($headers)
            ->timeout($timeout)
            ->get($url)
            ->getHeaders();
    }

    /**
     * 检查 CORS 跨域
     * @param string $url 检查的Url
     * @param string $origin 来源
     * @param int $timeout 超时时间
     * @return bool
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public static function checkCORS(string $url, string $origin, int $timeout = 5): bool
    {
        $headers = static::getHeaders($url, ['Referer' => $origin, 'Origin' => $origin], $timeout);
        if (isset($headers['Access-Control-Allow-Origin']) && in_array($headers['Access-Control-Allow-Origin'][0], [$origin, '*'])) {
            return true;
        }
        return false;
    }

    /**
     * 从 Url 中抽取 主机名
     * @param string $url
     * @return string
     */
    public static function getUrlHostname(string $url)
    {
        try {
            return (new Url($url))->getHostName();
        } catch (Exception\InvalidUrlException $e) {
            return false;
        }
    }

    /**
     * 获取网页TDK
     * @param string $url
     * @param int $timeout
     * @return array|false
     */
    public static function getTDK(string $url, $timeout = 5)
    {
        $info = [
            'hostname' => '',
            'ip' => '',
            'https' => false,
            'title' => '',
            'keywords' => '',
            'description' => ''
        ];
        //解析IP
        if (($info['hostname'] = static::getUrlHostname($url)) == false) {
            return false;
        }
        if (($info['ip'] = IPHelper::getHostIpV4($info['hostname'])) == false) {
            return false;
        }
        if (($body = static::getRemoteContent("https://" . $info['hostname'])) != false) {
            $info['https'] = true;
            $heads = HtmlHelper::getHeadTags($body);
            $info = array_merge($info, $heads);
        } elseif (($body = static::getRemoteContent("http://" . $info['hostname'])) != false) {
            $info['https'] = false;
            $heads = HtmlHelper::getHeadTags($body);
            $info = array_merge($info, $heads);
        } else {
            return false;
        }

        return $info;
    }
}
