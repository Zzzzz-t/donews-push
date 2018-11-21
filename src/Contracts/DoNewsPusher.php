<?php

namespace tlsss\DoNewsPush\Contracts;

interface DoNewsPusher
{
    public static function send($deviceToken, $title, $message, $platform, $type, $id);

   public static function setToken($platform, $app_id, $user_id, $deviceToken);

   public static function getToken($app_id, $user_id);
}