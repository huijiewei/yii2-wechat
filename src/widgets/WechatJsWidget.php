<?php

namespace huijiewei\wechat\widgets;

use yii\helpers\Json;
use yii\web\View;

class WechatJsWidget extends BaseWidget
{
    public array $hideMenuItems = []; //批量隐藏功能按钮接口
    public array $showMenuItems = []; //批量显示功能按钮接口
    public bool $hideOptionMenu = false; //隐藏右上角菜单接口
    public bool $hideAllNonBaseMenuItem = false; //隐藏所有非基础按钮接口
    public bool $showAllNonBaseMenuItem = false; //显示所有功能按钮接口

    public function wechatRun(): void
    {
        $hideOptionMenuJs = '';

        if ($this->hideOptionMenu) {
            $hideOptionMenuJs = <<<EOD
            wx.hideOptionMenu();
EOD;
        }

        $hideAllNonBaseMenuItemJs = '';

        if ($this->hideAllNonBaseMenuItem) {
            $hideAllNonBaseMenuItemJs = <<<EOD
            wx.hideAllNonBaseMenuItem();
EOD;
        }

        $showAllNonBaseMenuItemJs = '';

        if ($this->showAllNonBaseMenuItem) {
            $showAllNonBaseMenuItemJs = <<<EOD
            wx.showAllNonBaseMenuItem();
EOD;
        }

        $hideMenuItemsJs = '';

        if (!empty($this->hideMenuItems)) {
            $hideMenuItems = Json::encode($this->hideMenuItems);

            $hideMenuItemsJs = <<<EOD
                wx.hideMenuItems({
                    menuList: $hideMenuItems
                });
EOD;
        }

        $showMenuItemsJs = '';

        if (!empty($this->showMenuItems)) {
            $showMenuItems = Json::encode($this->showMenuItems);

            $hideMenuItemsJs = <<<EOD
                wx.showMenuItems({
                    menuList: $showMenuItems
                });
EOD;
        }

        $js = <<<EOD
    wx.ready(function() {
        $hideOptionMenuJs
        $hideAllNonBaseMenuItemJs
        $showAllNonBaseMenuItemJs
        $hideMenuItemsJs
        $showMenuItemsJs
    });
EOD;
        $this->getView()->registerJs($js, View::POS_END);
    }
}
