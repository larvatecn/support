<?php
/**
 * @copyright Copyright (c) 2018 Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Support\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Larva\Support\Exception\ConnectionException;
use Larva\Support\HttpResponse;
use Larva\Support\StringHelper;

/**
 * Trait HasHttpRequest
 */
trait HasHttpRequest
{
    /**
     * The base URL for the request.
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * The request body format.
     *
     * @var string
     */
    protected $bodyFormat;

    /**
     * The raw body for the request.
     *
     * @var string
     */
    protected $pendingBody;

    /**
     * The pending files for the request.
     *
     * @var array
     */
    protected $pendingFiles = [];

    /**
     * The request cookies.
     *
     * @var array
     */
    protected $cookies;

    /**
     * The transfer stats for the request.
     *
     * \GuzzleHttp\TransferStats
     */
    protected $transferStats;

    /**
     * The request options.
     *
     * @var array
     */
    public $options = [];

    /**
     * The middleware callables added by users that will handle requests.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * 设置待处理请求的基本URL
     *
     * @param string $url
     * @return $this
     */
    public function baseUrl(string $url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * 将原始内容附加到请求中
     *
     * @param resource|string $content
     * @param string $contentType
     * @return $this
     */
    public function withBody($content, $contentType)
    {
        $this->bodyFormat('body');
        $this->pendingBody = $content;
        $this->contentType($contentType);
        return $this;
    }

    /**
     * 设置请求是JSON
     *
     * @return $this
     */
    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    /**
     * 设置请求为表单
     *
     * @return $this
     */
    public function asForm()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    /**
     * 添加文件到请求
     *
     * @param string|array $name
     * @param string $contents
     * @param string|null $filename
     * @param array $headers
     * @return $this
     */
    public function attach($name, $contents = '', $filename = null, array $headers = [])
    {
        if (is_array($name)) {
            foreach ($name as $file) {
                $this->attach(...$file);
            }

            return $this;
        }

        $this->asMultipart();

        $this->pendingFiles[] = array_filter([
            'name' => $name,
            'contents' => $contents,
            'headers' => $headers,
            'filename' => $filename,
        ]);

        return $this;
    }

    /**
     * 设置该请求是一个多部分表单
     *
     * @return $this
     */
    public function asMultipart()
    {
        return $this->bodyFormat('multipart');
    }

    /**
     * Specify the body format of the request.
     *
     * @param string $format
     * @return $this
     */
    public function bodyFormat(string $format)
    {
        $this->bodyFormat = $format;
        return $this;
    }

    /**
     * 指定请求的内容类型
     *
     * @param string $contentType
     * @return $this
     */
    public function contentType(string $contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * 设置希望服务器应返回JSON
     *
     * @return $this
     */
    public function acceptJson()
    {
        return $this->accept('application/json');
    }

    /**
     * 设置希望服务器应返回的内容类型
     *
     * @param string $contentType
     * @return $this
     */
    public function accept(string $contentType)
    {
        return $this->withHeaders(['Accept' => $contentType]);
    }

    /**
     * 添加 Headers 到请求
     *
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        $this->options = array_merge_recursive($this->options, [
            RequestOptions::HEADERS => $headers,
        ]);
        return $this;
    }

    /**
     * 设置请求的基本身份验证用户名和密码。
     *
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function withBasicAuth(string $username, string $password)
    {
        $this->options[RequestOptions::AUTH] = [$username, $password];
        return $this;
    }

    /**
     * 设置请求的摘要身份验证用户名和密码。
     *
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function withDigestAuth(string $username, string $password)
    {
        $this->options[RequestOptions::AUTH] = [$username, $password, 'digest'];
        return $this;
    }

    /**
     * 设置请求的授权令牌。
     *
     * @param string $token
     * @param string $type
     * @return $this
     */
    public function withToken(string $token, $type = 'Bearer')
    {
        $this->options[RequestOptions::HEADERS]['Authorization'] = trim($type . ' ' . $token);
        return $this;
    }

    /**
     * 设置请求UA
     *
     * @param string $userAgent
     * @return $this
     */
    public function withUserAgent($userAgent)
    {
        return $this->withHeaders(['User-Agent' => $userAgent]);
    }

    /**
     * 设置请求Cookie
     *
     * @param array $cookies
     * @param string $domain
     * @return $this
     */
    public function withCookies(array $cookies, string $domain)
    {
        $this->options = array_merge_recursive($this->options, [
            RequestOptions::COOKIES => CookieJar::fromArray($cookies, $domain),
        ]);
        return $this;
    }

    /**
     * 设置请求仅使用 IPV4
     *
     * @return $this
     */
    public function withOnlyIPv4()
    {
        $this->options[RequestOptions::FORCE_IP_RESOLVE] = 'v4';
        return $this;
    }

    /**
     * 设置请求仅使用 IPV6
     *
     * @return $this
     */
    public function withOnlyIPv6()
    {
        $this->options[RequestOptions::FORCE_IP_RESOLVE] = 'v6';
        return $this;
    }

    /**
     * 设置请求为不跟随重定向
     *
     * @return $this
     */
    public function withoutRedirecting()
    {
        $this->options[RequestOptions::ALLOW_REDIRECTS] = false;
        return $this;
    }

    /**
     * 设置请求不验证证书有效性
     *
     * @return $this
     */
    public function withoutVerifying()
    {
        $this->options[RequestOptions::VERIFY] = false;
        return $this;
    }

    /**
     * Specify the path where the body of the response should be stored.
     *
     * @param string|resource $to
     * @return $this
     */
    public function sink($to)
    {
        $this->options[RequestOptions::SINK] = $to;
        return $this;
    }

    /**
     * 设置请求的超时时间
     *
     * @param int $seconds
     * @return $this
     */
    public function timeout(int $seconds)
    {
        $this->options[RequestOptions::TIMEOUT] = $seconds;
        return $this;
    }

    /**
     * 合并设置到客户端
     *
     * @param array $options
     * @return $this
     */
    public function withOptions(array $options)
    {
        $this->options = array_merge_recursive($this->options, $options);
        return $this;
    }

    /**
     * Add new middleware the client handler stack.
     *
     * @param callable $middleware
     * @return $this
     */
    public function withMiddleware(callable $middleware)
    {
        array_push($this->middlewares, $middleware);
        return $this;
    }

    /**
     * Issue a GET request to the given URL.
     *
     * @param string $url
     * @param array|string|null $query
     * @return HttpResponse
     * @throws GuzzleException
     */
    public function get(string $url, $query = null)
    {
        return $this->send('GET', $url, [
            'query' => $query,
        ]);
    }

    /**
     * Issue a HEAD request to the given URL.
     *
     * @param string $url
     * @param array|string|null $query
     * @return HttpResponse
     * @throws GuzzleException
     */
    public function head(string $url, $query = null)
    {
        return $this->send('HEAD', $url, [
            'query' => $query,
        ]);
    }

    /**
     * Issue a POST request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return HttpResponse
     * @throws GuzzleException
     */
    public function post(string $url, array $data = [])
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PATCH request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return HttpResponse
     * @throws GuzzleException
     */
    public function patch(string $url, $data = [])
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PUT request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return HttpResponse
     * @throws GuzzleException
     */
    public function put(string $url, $data = [])
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a DELETE request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return HttpResponse
     * @throws GuzzleException
     */
    public function delete(string $url, $data = [])
    {
        return $this->send('DELETE', $url, empty($data) ? [] : [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Send the request to the given URL.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return HttpResponse
     * @throws GuzzleException
     */
    public function send(string $method, string $url, array $options = [])
    {
        if (property_exists($this, 'baseUri') && !is_null($this->baseUri)) {
            $options['base_uri'] = $this->baseUri;
        }
        if (isset($options[$this->bodyFormat])) {
            if ($this->bodyFormat === 'multipart') {
                $options[$this->bodyFormat] = $this->parseMultipartBodyFormat($options[$this->bodyFormat]);
            } elseif ($this->bodyFormat === 'body') {
                $options[$this->bodyFormat] = $this->pendingBody;
            }
            if (is_array($options[$this->bodyFormat])) {
                $options[$this->bodyFormat] = array_merge(
                    $options[$this->bodyFormat], $this->pendingFiles
                );
            }
        }

        [$this->pendingBody, $this->pendingFiles] = [null, []];
        try {
            $response = new HttpResponse($this->buildClient()->request(strtoupper($method), $url, $this->mergeOptions([
                'on_stats' => function ($transferStats) {
                    $this->transferStats = $transferStats;
                },
            ], $options)));
            $response->transferStats = $this->transferStats;
            return $response;
        } catch (ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Parse multi-part form data.
     *
     * @param array $data
     * @return array|array[]
     */
    protected function parseMultipartBodyFormat(array $data)
    {
        $data = array_map(function ($value, $key) {
            return is_array($value) ? $value : ['name' => $key, 'contents' => $value];
        }, $data);
        return array_values($data);
    }

    /**
     * Build the Guzzle client.
     *
     * @return Client
     */
    public function buildClient(): Client
    {
        return new Client([
            'handler' => $this->buildHandlerStack(),
            'cookies' => true,
        ]);
    }

    /**
     * Build the before sending handler stack.
     *
     * @return HandlerStack
     */
    public function buildHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        if (method_exists($this, 'buildBeforeSendingHandler')) {
            $stack->push($this->buildBeforeSendingHandler());
        }
        foreach ($this->middlewares as $middleware) {
            $stack->push($middleware);
        }
        return $stack;
    }

    /**
     * Merge the given options with the current request options.
     *
     * @param array $options
     * @return array
     */
    public function mergeOptions(...$options): array
    {
        return array_merge_recursive($this->options, ...$options);
    }
}
