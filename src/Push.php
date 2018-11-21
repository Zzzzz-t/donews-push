<?php

namespace tlsss\DoNewsPush;

use \Illuminate\Redis\RedisManager as Redis;
use tlsss\DoNewsPush\Exceptions\PushException;
use tlsss\DoNewsPush\Contracts\DoNewsPusher;

class Push implements DoNewsPusher
{
    private static $_config = null;
    private static $_redis = null;
    private static $_platform = null;

    public function __construct()
    {
        if (!file_exists(config_path("push.php"))) {
            throw new PushException("配置文件: " . config("push.php") . " 不存在", 500);
        }
        static::$_config = config("push");
        static::$_platform = config("push.platform");
        static::$_redis = new Redis(config("push.redis.client"), config("push.redis"));
    }

    private static function getSerivce($platform)
    {
        switch ($platform) {
            case 'apple':
                $service = "ApnsPush";
                break;
            case 'mi':
                $service = "MiPush";
                break;
            case 'huawei':
                $service = "HmsPush";
                break;
            case 'umeng':
                $service = "UmengPush";
                break;
            case 'vivo':
                $service = "VivoPush";
                break;
            case 'meizu':
                $service = "MeizuPush";
                break;
            default:
                throw new PushException("platform 参数错误", 405);
                return null;
                break;
        }

        return "tlsss\\DoNewsPush\\Services\\" . $service;
    }

    /**
     * 统一推送接口。
     *
     * @param $deviceToken
     * @param $title
     * @param $message
     * @param $platform
     * @return mixed
     */
    public static function send($deviceToken, $title, $message, $platform, $type, $id)
    {
        $service = self::getSerivce($platform);

        $push = new $service(static::$_platform[$platform]);
        if (method_exists($push, 'sendMessage')) {
            return $push->sendMessage($deviceToken, $title, $message, $type, $id);
        }
        
        return false;
    }

    /**
     * 设置用户token
     */
    public function setToken($platform, $app_id, $user_id, $deviceToken)
    {
        static::$_redis->set($app_id . ":" . $user_id . ":regid:", $platform .":". $deviceToken);
        return $this->success();
    }


    /**
     * 获取用户token
     */
    public function getToken($platform, $app_id, $user_id)
    {
        if (!is_array($user_id)) {
            return false;
        }
        return static::$_redis->get($app_id . ":" . $user_id . ":regid:");
    }

    public function success()
    {
        throw new PushException("success", 200);
    }

    public function error()
    {
        throw new PushException("参数错误", 405);
    }
}