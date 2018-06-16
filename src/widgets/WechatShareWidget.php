<?php
/**
 * Created by PhpStorm.
 * User: huijiewei
 * Date: 2018/6/11
 * Time: 19:30
 */

namespace huijiewei\wechat\widgets;

use yii\helpers\Json;
use yii\web\View;

class WechatShareWidget extends WechatJsWidget
{
    public $shareUrl = '';
    public $shareIcon = '';
    public $shareTitle = '';
    public $shareDescription = '';

    public $shareType = '';
    public $shareDataUrl = '';

    public function wechatRun()
    {
        parent::wechatRun();

        $shareTimelineConfig = [];
        $shareAppMessageConfig = [];

        if (!empty($this->shareUrl)) {
            $shareTimelineConfig['link'] = $this->shareUrl;
            $shareAppMessageConfig['link'] = $this->shareUrl;
        }

        if (!empty($this->shareIcon)) {
            $shareTimelineConfig['imgUrl'] = $this->shareIcon;
            $shareAppMessageConfig['imgUrl'] = $this->shareIcon;
        }

        if (!empty($this->shareTitle)) {
            $shareTimelineConfig['title'] = $this->shareTitle;
            $shareAppMessageConfig['title'] = $this->shareTitle;
        }

        if (!empty($this->shareDescription)) {
            $shareAppMessageConfig['desc'] = $this->shareDescription;
        }

        if (!empty($this->shareType)) {
            $shareAppMessageConfig['type'] = $this->shareType;
        }

        if (!empty($this->shareDataUrl)) {
            $shareAppMessageConfig['dataUrl'] = $this->shareDataUrl;
        }

        $shareTimelineConfigJs = Json::encode($shareTimelineConfig);
        $shareAppMessageConfigJs = Json::encode($shareAppMessageConfig);

        $js = <<<EOD
    wx.ready(function() {
        wx.onMenuShareTimeline($shareTimelineConfigJs);
        wx.onMenuShareAppMessage($shareAppMessageConfigJs);
    });
EOD;
        $this->getView()->registerJs($js, View::POS_END);
    }
}
