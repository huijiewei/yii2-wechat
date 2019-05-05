# yii2-wechat
Yii2 微信扩展

本扩展优化了微信网页授权流程

基于 [overtrue/wechat](https://github.com/overtrue/wechat).       

## 安装
```
composer require huijiewei/yii2-wechat
```

## 配置

增加 `component` 配置到 `config/main.php`:

```php

'components' => [
	// ...
	// componentId 可以自定义多个微信公众号进行配置
	'wechat' => [
		'class' => 'huijiewei\wechat\Wechat',
		// 'appConfig' => [],  # 公众号配置
		// 'paymentConfig' => [],  # 支付配置
		// 'miniProgramConfig' => [],  # 小程序配置
		// 'openPlatformConfig' => [],  # 开放平台配置
		// 'workConfig' => [],  # 企业微信配置
	],
	// ...
]
```

[配置文档](https://www.easywechat.com/docs/master/zh-CN/official-account/configuration)

## 配置数据库
```bash
php yii migrate --migrationPath=@vendor/huijiewei/yii2-wechat/src/migrations
```

## 用法

##### 微信网页授权:
```php
if(Wechat::getIsWechatClient()) {
    $wechatAuthroize = new WechatAuthorize([
        'wechat' => 'wechat', // componentId, 默认是 wechat
    ]);
    
    if(!wechatAuthorize()->isAuthorized()) {
        return $wechatAuthorize()->authorizeRequired()->send();
    }
}
```

##### JSSDK
```php
WechatShareWidget::widget([
    'wechat' => 'wechat', // componentId, 默认是 wechat
    'shareUrl' => '分享链接',
    'shareIcon' => '分享图标',
    'shareTitle' => '分享标题',
    'shareDescription' => '分享描述',
]);
```

### 更多文档
查阅 [EasyWeChat 文档](https://www.easywechat.com/docs/master).

感谢 `overtrue/wechat`
