<?php

namespace tlsss\DoNewsPush\Contracts;

interface DoNewsPusher
{
    public static function send($deviceToken, $title, $message, $platform, $type, $id);

   public static function setToken($platform, $app_id, $user_id, $deviceToken);

   public static function getToken($app_id, $user_id);

   public static function setDeviceToken($platform, $app_id, $device_id, $deviceToken);

   public static function getDeviceToken($app_id, $list_name, $page = 1, $pageSize = 100)

   public static function getListLen($app_id, $list_name)

}