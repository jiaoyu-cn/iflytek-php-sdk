<?php

namespace Githen\IflytekPhpSdk\Traits;


use Illuminate\Support\Str;

/**
 *
 */
trait WebTextCorrectionTrait
{

    /**
     * 文本纠错
     * 接口文档: https://www.xfyun.cn/doc/nlp/textCorrection/API.html
     * uid与res_id可以到resIdGet上传和获取
     * @param string $text 文本数据
     * @param array $options 附加参数
     * @return array
     */
    public function webTextCorrection(string $text, $options = [])
    {
        if (empty($text)) {
            return $this->message('2000', '待检测内容不能为空');
        }
        if (mb_strlen($text) > 2000) {
            return $this->message('2000', '不得超过2000个字符');
        }
        $apiURL = "https://api.xf-yun.com/v1/private/s9a87e3ec";
        $uri = $this->getAuthURL($apiURL);
        $requestJson = [
            'header' => [
                'app_id' => $this->getAppId(),
                'status' => 3
            ],
            'parameter' => [
                's9a87e3ec' => [
                    'result' => [
                        'encoding' => 'utf8',
                        'compress' => 'raw',
                        'format' => 'json',
                    ]
                ]
            ],
            'payload' => [
                'input' => [
                    'encoding' => 'utf8',
                    'compress' => 'raw',
                    'format' => 'json',
                    'status' => 3,
                    'text' => base64_encode($text)
                ]
            ]
        ];
        if (!empty($options['uid'])) {
            $requestJson['header']['uid'] = $options['uid'];
        }
        if (!empty($options['res_id'])) {
            $requestJson['parameter']['s9a87e3ec']['res_id'] = $options['res_id'];
        }
        $resp = $this->httpPost($uri, ['headers' => ['Content-Type' => 'application/json;charset=UTF-8'], 'json' => $requestJson]);
        if (isset($resp['code']) && $resp['code'] != '0000') {
            return $resp;
        }
        if ($resp['header']['code'] != 0) {
            return $this->message('2000', $resp['header']['message']);
        }
        $data = [];
        $text = $resp['payload']['result']['text'] ?? '';
        if (!empty($text)) {
            $data = json_decode(base64_decode($text), true);
        }
        return $this->message('0000', '操作成功', $data);
    }

    /**
     * 文本纠错-黑白名单上传
     * 接口文档: https://www.xfyun.cn/doc/nlp/textCorrection/API.html#%E9%BB%91%E7%99%BD%E5%90%8D%E5%8D%95%E4%B8%8A%E4%BC%A0
     * 获取res_id
     * @param string $blackList 黑名单
     * @param string $whiteList 白名单
     * @param array $options 附加参数 uid 用于区分同一个appid下的不同用户 res_id 用于区分同一个appid下的不同资源
     * @return void
     */
    public function resIdGet($blackList = '', $whiteList = '', $options = [])
    {
        if (empty($blackList) || empty($whiteList)) {
            return $this->message('2000', '黑白名单参数不能同时为空');
        }
        $apiURL = "https://evo-gen.xfyun.cn/individuation/gen/upload";
        $uid = $options['uid'] ?? Str::uuid()->getHex();
        $resId = $options['res_id'] ?? Str::uuid()->getHex();
        $requestJson = [
            'common' => [
                'app_id' => $this->getAppId(),
                'uid' => $uid,

            ],
            'business' => [
                'res_id' => $resId
            ],
            'data' => base64_encode(json_encode([
                'white_list' => $whiteList,
                'black_list' => $blackList
            ], JSON_UNESCAPED_UNICODE))
        ];
        //业务数据流参数，上传json格式的黑白名单经过base64编码后的数据，大小不超过 4m
        if (strlen($requestJson['data']) > 4 * 1024 * 1024) {
            return $this->message('2000', '参数超过大小限制');
        }
        $resp = $this->httpPost($apiURL, ['headers' => ['Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'], 'json' => $requestJson]);
        if (isset($resp['code']) && $resp['code'] != 0) {
            return $this->message('2000', '操作失败');
        }
        return $this->message('0000', '操作成功', ['uid' => $uid, 'res_id' => $resId, 'sid' => $resp['sid']]);
    }
}
