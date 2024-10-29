<?php

namespace huijiewei\wechat\widgets;

use yii\helpers\Json;
use yii\web\View;

class WechatShareWidget extends WechatJsWidget
{
    public string $shareUrl = '';
    public string $shareIcon = '';
    public string $shareTitle = '';
    public string $shareDescription = '';

    public string $shareType = '';
    public string $shareDataUrl = '';

    public function wechatRun(): void
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
