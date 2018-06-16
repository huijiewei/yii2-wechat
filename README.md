# yii2-wechat
Yii2 微信扩展 , 基于 [overtrue/wechat](https://github.com/overtrue/wechat).       


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
private $_wechatAuthorize;

/**
 * @return WechatAuthorize
 */
public function getWechatAuthorize()
{
    if ($this->_wechatAuthorize == null) {
        $this->_wechatAuthorize = new WechatAuthorize([
            'wechat' => \Yii::$app->get('wechat')->getApp(),
        ]);
    }
    
    return $this->_wechatAuthorize;
}

if (Wechat::getIsWechatClient() 
    && $this->getWechatAuthorize()->authorizeRequired()) {
        $this->_wechatUser = $this->getWechatAuthorize()->getWechatUser();
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
