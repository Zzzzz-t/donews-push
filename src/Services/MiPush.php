<?php

namespace tlsss\DoNewsPush\Services;

use Singiu\Http\Request;


class MiPush
{
    private $_appPackageName;
    private $_appSecret;
    private $_request;
    private $_url = 'https://api.xmpush.xiaomi.com/v3/message/regid';
    private $_intent_uri;


    /**
     * MiPush constructor.
     *
     * @param null $config
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        if (!empty(config('push.platform.mi.app_package_name'))) {
            $this->_appPackageName = config('push.platform.mi.app_package_name');
        } else {
            throw new \Exception('Cannot found configuration: mi.app_package_name!');
        }

        if (!empty(config('push.platform.mi.app_secret'))) {
            $this->_appSecret = config('push.platform.mi.app_secret');
        } else {
            throw new \Exception('Cannot found configuration: mi.app_secret!');
        }

        if (!empty(config('push.platform.mi.intent_uri'))) {
            $this->_appSecret = config('push.platform.mi.intent_uri');
        } else {
            throw new \Exception('Cannot found configuration: mi.intent_uri!');
        }

        $this->_request = new Request();
    }

    /**
     * 发送推送通知。
     *
     * @param $deviceToken
     * @param $title
     * @param $message
     * @return \Singiu\Http\Response
     * @throws \Exception
     */
    public function sendMessage($deviceToken, $title, $message, $type = null, $id = null)
    {
        $payload = [
            'title' => $title, // 通知栏展示的通知的标题，这里统一不显示。
            'description' => $message,
            'pass_through' => 0, // 设定是否为透传消息，0 = 推送消息，1 = 透传消息。
            'payload' => $message, // 消息内容。
            'notify_type' => -1, // 提示通知默认设定，-1 = DEFAULT_ALL。
            'extra.notify_effect' => 2, // 预定义通知栏消息的点击行为，1 = 打开 app 的 Launcher Activity，2 = 打开 app 的任一 Activity（还需要 extra.intent_uri）,3 = 打开网页（还需要传入 extra.web_uri）
            'extra.intent_uri' => $this->_intent_uri,
            'restricted_package_name' => $this->_appPackageName,
            'registration_id' => $deviceToken,
            'extra.type' => $type,
            'extra.id' => $id,
        ];

        $response = $this->_request->post($this->_url, [
            'headers' => [
                'Authorization' => 'key=' . $this->_appSecret
            ],
            'data' => $payload
        ]);

        return $response;
    }
}