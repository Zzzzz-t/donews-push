<?php

namespace tlsss\DoNewsPush\Contracts;

interface PushInterface
{
    public function sendMessage($deviceToken, $title, $message, $type, $id);
}