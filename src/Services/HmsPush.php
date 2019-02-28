<?php

namespace tlsss\DoNewsPush\Services;

use Singiu\Http\Http;
use Singiu\Http\Request;
use Singiu\Http\Response;
use Illuminate\Redis\RedisManager as Redis;

class HmsPush
{
    private $_accessToken;
    private $_clientId;
    private $_clientSecret;
    private $_http;
    private $_redis;
    private $_authUrl = "https://login.cloud.huawei.com/oauth2/v2/token";
    private	$_headers = array('Content-Type: application/x-www-form-urlencoded');

    /**
     * 构造函数。
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        if (!empty(config('push.platform.huawei.client_id'))) {
            $this->_clientId = config('push.platform.huawei.client_id');
        } else {
            throw new \Exception('Cannot found configuration: huawei.client_id!');
        }
        if (!empty(config('push.platform.huawei.client_secret'))) {
            $this->_clientSecret = config('push.platform.huawei.client_secret');
        } else {
            throw new \Exception('Cannot found configuration: hms.client_secret!');
        }
        $this->_redis = new Redis(config("push.redis.client"), config("push.redis"));
        $this->_http = new Request();
        $this->_http->setHttpVersion(Http::HTTP_VERSION_1_1);

    }

    /**
     * 请求新的 Access Token。
     */
    private function _getAccessToken()
    {
        $this->_accessToken = $this->_redis->get("huawei:authToekn:");
        if(!$this->_accessToken){
            $data = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->_clientId,
                'client_secret' => $this->_clientSecret
            ];

            $data = http_build_query($data);

            $res = self::curlPost($this->_authUrl, $this->_headers, $data);
            $res = json_decode($res,1);
            if(!isset($res['access_token'])){
                throw new \Exception($res['error_description']);
            }
            $this->_accessToken = $res['access_token'];
            $this->_redis->setex("huawei:authToekn:", $res['expires_in'],
                $this->_accessToken);
        }
    }

    private static function curlPost($url, $header, $data)
    {
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);//运行curl
        curl_close($ch);
        return $result;
    }

    /**
     * 发送华为推送消息。
     * @param $deviceToken
     * @param $title
     * @param $message
     * @return Response
     * @throws
     */
    public function sendMessage($deviceToken, $title, $message, $type, $id)
    {
        date_default_timezone_set('PRC'); //设置中国时区
        $time = time();
        // 构建 Payload
        if (is_array($message)) {
            $payload = json_encode($message, JSON_UNESCAPED_UNICODE);
        } else if (is_string($message)) {
            $payload = json_encode([
                'hps' => [
                    'msg' => [
                        'type' => 3,
                        'body' => [
                            'content' => $message,
                            'title' => $title
                        ],
                        'action' => [
                            'type' => 1,
                            'param' => [
                                'intent' => '#Intent;compo=com.wanmei.a9vg/.common.activitys.Activity;S.W=U;end'
                            ]
                        ]
                    ],
                    'ext' => [
                        'customize' => [
                            ['type' => $type],
                            ['id' => $id]
                        ]
                    ],
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $payload = '';
        }
        // 发送消息通知
        $this->_getAccessToken();

        $response = $this->_http->post('https://api.push.hicloud.com/pushsend.do', [
            'query' => [
                'nsp_ctx' => json_encode(['ver' => '1', 'appId' => $this->_clientId])
            ],
            'data' => [
                'access_token' => $this->_accessToken,
                'nsp_ts' => $time,
                'nsp_svc' => 'openpush.message.api.send',
                'device_token_list' => json_encode([$deviceToken]),
                'payload' => $payload
            ]
        ]);
        return $response;
    }
}