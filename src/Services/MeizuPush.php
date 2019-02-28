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
    private $_appActivity;

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
            throw new \Exception('Cannot found configuration: meizu.appSecret!');
        }
        // if (!empty(config('push.platform.meizu.appActivity'))) {
        //     $this->_appActivity = config('push.platform.meizu.appActivity');
        // } else {
        //     throw new \Exception('Cannot found configuration: meizu.appActivity!');
        // }

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
                         ->setClickType(1)
                         ->setActivity('com.wanmei.a9vg.common.activitys.NotifyActivity')
                         ->setUrl(null)
                         ->setNoticeExpandType(1)
                         ->setNoticeExpandContent('扩展内容')
                         ->setOffLine(1)
                         ->setParameters(array('type'=>$type,'id'=>$id));

        return $this->_mzPush->varnishedPush(array($deviceToken),
            $varnishedMessage);
    }

}