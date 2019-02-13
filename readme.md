# DoNews APP推送
## 安装
```
composer require tlsss/donews-push
```

## 发布
```
php artisan vendor:publish
```
- 找到对应 `tlsss\DoNewsPush\DoNewsPushServiceProvider` 前面的序号, 发布配置文件

## 配置
- 本拓展包中使用 redis 保存 客户端Token , 所以需要在配置文件 `config/push.php` 中 配置 Redis 相关配置

## 使用
- 设置 `DeviceToken` 
    - 需要调用setToken方法 传入$platform, $app_id, $user_id, $deviceToken;
- 设置 `获取DeviceToken` 
    - 需要调用setToken方法 app_id user_id;

- 发布时, 可以直接注入 `tlsss\\DoNewsPush\Push` 类, 调用 `send()` 方法