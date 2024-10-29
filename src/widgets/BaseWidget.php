<?php

namespace huijiewei\wechat\widgets;

use EasyWeChat\Kernel\Exceptions\HttpException;
use EasyWeChat\OfficialAccount\Application;
use huijiewei\wechat\Wechat;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Url;
use yii\web\View;

abstract class BaseWidget extends Widget
{
    public string|Application $wechat = 'wechat';
    public bool $debug = false;
    private Application|null $_wechat;

    /**
     * @throws InvalidConfigException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws HttpException
     */
    public function run(): void
    {
        parent::run();

        if (!Wechat::getIsWechatClient()) {
            return;
        }

        $this->getView()->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.6.0.js');

        $apiConfig = $this->getWechat()->getUtils()->buildJsSdkConfig(
            Url::current([], true),
            [
                'onMenuShareTimeline', 'onMenuShareAppMessage',
                'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone',
                'startRecord', 'stopRecord', 'onVoiceRecordEnd',
                'playVoice', 'pauseVoice', 'stopVoice',
                'onVoicePlayEnd', 'uploadVoice', 'downloadVoice',
                'chooseImage', 'previewImage', 'uploadImage', 'downloadImage',
                'translateVoice', 'getNetworkType',
                'openLocation', 'getLocation',
                'hideOptionMenu', 'showOptionMenu',
                'hideMenuItems', 'showMenuItems',
                'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',
                'closeWindow', 'scanQRCode',
                'chooseWXPay', 'openProductSpecificView',
                'addCard', 'chooseCard', 'openCard'
            ],
            [],
            $this->debug);

        $apiConfigJson = json_encode($apiConfig);

        $js = <<<EOD
    wx.config($apiConfigJson);
EOD;
        $this->getView()->registerJs($js, View::POS_END);

        $this->wechatRun();
    }

    /**
     * @return Application|null
     * @throws InvalidConfigException
     */
    public function getWechat(): Application|null
    {
        if ($this->_wechat == null) {
            $this->_wechat = $this->wechat instanceof Application ? $this->wechat : Yii::$app->get($this->wechat)->getApp();
        }

        return $this->_wechat;
    }

    abstract public function wechatRun();
}
