<?php

namespace tlsss\DoNewsPush\Services;
use Illuminate\Redis\RedisManager as Redis;

class VivoPush
{
    private $_accessToken;
    private $_appId;
    private $_appKey;
    private $_appSecret;
    private $_http;
    private $_sign;
    private $_time;
    private $_redis;
    private $_url = "https://api-push.vivo.com.cn";
	private	$_headers = array("Content-type: application/json;charset='utf-8'");

    /**
     * 构造函数。
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = null)
    {

        if (!empty(config('push.platform.vivo.appId'))) {
            $this->_appId = config('push.platform.vivo.appId');
        } else {
            throw new \Exception('Cannot found configuration: vivo.appId!');
        }
        if (!empty(config('push.platform.vivo.appKey'))) {
            $this->_appKey = config('push.platform.vivo.appKey');
        } else {
            throw new \Exception('Cannot found configuration: vivo.appKey!');
        }
        if (!empty(config('push.platform.vivo.appSecret'))) {
            $this->_appSecret = config('push.platform.vivo.appSecret');
        } else {
            throw new \Exception('Cannot found configuration: vivo.appSecret!');
        }
        $this->_redis = new Redis(config("push.redis.client"), config("push.redis"));

    }

    /**
     * 请求新的 Access Token。
     */
    private function _getAccessToken()
    {
    	$this->_accessToken = $this->_redis->get("vivo:authToekn:");
    	if(!$this->_accessToken){
    		$this->_getTime();
    		$sign = md5($this->_appId.$this->_appKey.$this->_time.$this->_appSecret);

	    	$data = [
			    "appId" => $this->_appId,
			    "appKey" => $this->_appKey,
			    "timestamp" => $this->_time,
			    "sign" => $sign,
			];

			$res = $this->curlPost($this->_url.'/message/auth', json_encode($data), $this->_headers);
			$res = json_decode($res,1);

			if($res['result'] != '0'){
				throw new \Exception($res['desc']);
			}
			$this->_accessToken = $res['authToken'];

            $this->_redis->setex("vivo:authToekn:",3600,$this->_accessToken);
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
        $data = [
		    //用户ID
		    'regId' => $deviceToken,
		    "notifyType" => '2',
		    "title" => $title,
		    "content" => $message,
		    "skipType" => "1",
		    "requestId" => $this->_accessToken,
		    //自定义参数
		    "clientCustomMap" => array(
		        'type'=>$type,
		        'id' => $id,
		    ),
		];

		$this->_headers[] = "authToken:".$this->_accessToken;

		$res = $this->curlPost($this->_url.'/message/send', json_encode($data), $this->_headers);
		return json_decode($res,1);
    }
}