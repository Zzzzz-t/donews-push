<?php

namespace tlsss\DoNewsPush\Services;
use Illuminate\Redis\RedisManager as Redis;

class VivoPush
{
    private $_accessToken;
    private $_masterSecret;
    private $_appKey;
    private $_clickActionActivity;
    private $_http;
    private $_sign;
    private $_time;
    private $_redis;
    private $_url = "https://api.push.oppomobile.com/server/v1/";
	private	$_headers = array("Content-type: application/x-www-form-urlencoded;charset='utf-8'");

    /**
     * 构造函数。
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = null)
    {

        if (!empty(config('push.platform.oppo.appKey'))) {
            $this->_appKey = config('push.platform.oppo.appKey');
        } else {
            throw new \Exception('Cannot found configuration: oppo.appKey!');
        }
        if (!empty(config('push.platform.oppo.masterSecret'))) {
            $this->_masterSecret = config('push.platform.oppo.masterSecret');
        } else {
            throw new \Exception('Cannot found configuration: oppo.masterSecret!');
        }
        if (!empty(config('push.platform.oppo.clickActionActivity'))) {
            $this->_clickActionActivity = config('push.platform.oppo.clickActionActivity');
        } else {
            throw new \Exception('Cannot found configuration: oppo.clickActionActivity!');
        }
        $this->_redis = new Redis(config("push.redis.client"), config("push.redis"));

    }

    /**
     * 请求新的 Access Token。
     */
    private function _getAccessToken()
    {
    		$this->_getTime();
    		$sign = hash('sha256',$this->_appKey.$this->_time.$this->_masterSecret);

	    	$data['app_key'] = $this->_appKey;
			$data['timestamp'] = $this->_time;
			$data['sign'] = $sign;

			$res = $this->curlPost($this->_url.'auth', $data, null);
			$res = json_decode($res,1);

			if($res['code'] != '0'){
				throw new \Exception($res['desc']);
			}
			$this->_accessToken = $res['data']['authToken'];
    	}   
    }

    private function _getTime()
    {
    	list($msec, $sec) = explode(' ', microtime());
		$this->_time = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    /**
     * curlPost
     */
    private function curlPost($url, $data, $headers)
	{
		
	    $ch = curl_init();//初始化curl
		curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		$result = curl_exec($ch);//运行curl
		curl_close($ch);

	    return $result;
	}

    /**
     * 发送vivo推送消息。
     * @param $deviceToken
     * @param $title
     * @param $message
     * @return Response
     * @throws
     */
    public function sendMessage($deviceToken, $title, $message, $type, $id)
    {
    	$this->_getAccessToken();
        
        $notification['title'] = $title;
		$notification['content'] = $message;
		$notification['sub_title'] = $title;
		$notification['action_parameters'] = json_encode([
			'type' => $type,
			'id' => $id
		]);

		$notification['click_action_type'] = 1;
		$notification['click_action_activity'] = $this->_clickActionActivity;

		$message['target_type'] = 2;
		$message['target_value'] = $deviceToken;
		$message['notification'] = $notification;

		$data['auth_token'] = $this->_accessToken;
		$data['message'] = json_encode($message);
		$data = http_build_query($data);
		$url = $this->_url.'message/notification/unicast';

		$res = $this->curlPost($url, $data, $this->_headers);
		return json_decode($res,1);
    }
}