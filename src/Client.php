<?php

namespace Githen\IflytekPhpSdk;

use Illuminate\Support\Str;
use Githen\IflytekPhpSdk\Traits\WebTextCorrectionTrait;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client as GuzzleHttpClient;

class Client
{
    use WebTextCorrectionTrait;

    /**
     * The API id
     */
    private $appId = "";

    /**
     * The API Secret
     */
    private $apiSecret = "";

    /**
     * The API Key
     */
    private $apiKey = "";

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     */
    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    /**
     * @return string
     */
    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    /**
     * @param string $apiSecret
     */
    public function setApiSecret(string $apiSecret): void
    {
        $this->apiSecret = $apiSecret;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Default constructor
     *
     * @param array $config Iflytek configuration data
     * @return void
     */
    public function __construct($config)
    {
        $this->setAppId($config['app_id']);
        $this->setApiKey($config['api_key']);
        $this->setApiSecret($config['api_secret']);
        return;
    }

    /**
     * 获取AuthURL
     * @param string $apiURL
     * @return string
     * @author nanjishidu
     */
    public function getAuthURL($apiURL)
    {
        $urls = parse_url($apiURL);
        $date = gmdate("D, d M Y H:i:s T");
        $preStr = "host: {$urls['host']}\ndate: $date\nPOST {$urls['path']} HTTP/1.1";
        $signature = base64_encode(hash_hmac('sha256', $preStr, $this->getApiSecret(), true));
        $authrization = base64_encode("api_key=\"{$this->getApiKey()}\",algorithm=\"hmac-sha256\",headers=\"host date request-line\",signature=\"$signature\"");
        $uri = $apiURL . '?' . http_build_query([
                'host' => $urls['host'],
                'date' => $date,
                'authorization' => $authrization
            ]);
        return $uri;

    }

    /**
     * @param $uri
     * @param $options
     * @return array|mixed
     */
    public function httpPost($uri, $options = [])
    {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
        $httpClient = new GuzzleHttpClient([
            'timeout' => 60,
            'verify' => false,
            'handler' => $handlerStack,
        ]);
        try {
            $response = $httpClient->request('POST',
                $uri,
                $options);
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        } catch (\Exception $e) {
            return $this->message($e->getCode(), $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * 最大重试次数
     */
    const MAX_RETRIES = 3;

    /**
     * 返回一个匿名函数, 匿名函数若返回false 表示不重试，反之则表示继续重试
     * @return \Closure
     */
    private function retryDecider()
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) {
            // 超过最大重试次数，不再重试
            if ($retries >= self::MAX_RETRIES) {
                return false;
            }

            // 请求失败，继续重试
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // 如果请求有响应，但是状态码不等于200，继续重试
                if ($response->getStatusCode() != 200) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * 返回一个匿名函数，该匿名函数返回下次重试的时间（毫秒）
     * @return \Closure
     */
    private function retryDelay()
    {
        return function ($numberOfRetries) {
            return 1000 * $numberOfRetries;
        };
    }

    /**
     * 封装消息
     * @param string $code
     * @param string $message
     * @param array $data
     * @return array
     */
    private function message($code, $message, $data = [])
    {
        return ['code' => $code, 'message' => $message, 'data' => $data];
    }

}
