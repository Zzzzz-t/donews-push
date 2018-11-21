<?php

namespace tlsss\DoNewsPush\Services;

use Illuminate\Redis\RedisManager as Redis;
use tlsss\DoNewsPush\MzPushSDk\MzPush;
use tlsss\DoNewsPush\MzPushSDk\VarnishedMessage;


class MeizuPush
{
    private $_mzPush;
    private $_appId;
    private $_appSecret;

    /**
     * 构造函数。
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        if (!empty(config('push.platform.meizu.appId'))) {
            $this->_appId = config('push.platform.meizu.appId');
        } else {
            throw new \Exception('Cannot found configuration: meizu.appId!');
        }
        if (!empty(config('push.platform.meizu.appSecret'))) {
            $this->_appSecret = config('push.platform.meizu.appSecret');
        } else {
            throw new \Exception('Cannot found configuration: vivo.appSecret!');
        }

        $this->_mzPush = new MzPush($this->_appId, $this->_appSecret);

    }




    /**
     * 发送魅族推送消息。
     * @param $deviceToken
     * @param $title
     * @param $message
     * @return Response
     * @throws
     */
    public function sendMessage($deviceToken, $title, $message, $type, $id)
    {
        $varnishedMessage = new VarnishedMessage();
        $varnishedMessage->setTitle($title)
                         ->setContent($message)
                         ->setClickType(0)
                         ->setUrl(null)
                         ->setNoticeExpandType(1)
                         ->setNoticeExpandContent('扩展内容')
                         ->setOffLine(1)
                         ->setParameters(array('type'=>$type,'id'=>$id));

        return $this->_mzPush->varnishedPush(array($deviceToken),
            $varnishedMessage);
    }

}