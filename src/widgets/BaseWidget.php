<?php
/**
 * Created by PhpStorm.
 * User: huijiewei
 * Date: 2018/6/11
 * Time: 18:26
 */

namespace huijiewei\wechat\widgets;

use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;
use huijiewei\wechat\Wechat;
use yii\base\Widget;
use yii\web\View;

abstract class BaseWidget extends Widget
{
    public $wechat = 'wechat';
    public $debug = false;

    /* @var $_wechat Application */
    private $_wechat;

    public function run()
    {
        parent::run();

        if (!Wechat::getIsWechatClient()) {
            return;
        }

        $this->getView()->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.4.0.js');

        $apiConfig = $this->getWechat()->jssdk->buildConfig([
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
        ], $this->debug);

        $js = <<<EOD
    wx.config($apiConfig);
EOD;
        $this->getView()->registerJs($js, View::POS_END);

        $this->wechatRun();
    }

    /**
     * @return Application|null
     */
    public function getWechat()
    {
        if ($this->_wechat == null) {
            $this->_wechat = $this->wechat instanceof Factory ? $this->wechat : \Yii::$app->get($this->wechat)->getApp();
        }

        return $this->_wechat;
    }

    abstract public function wechatRun();
}
