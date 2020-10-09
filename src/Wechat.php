<?php
/**
 * Created by PhpStorm.
 * User: huijiewei
 * Date: 2018/6/11
 * Time: 11:27
 */

namespace huijiewei\wechat;

use EasyWeChat\Factory;
use yii\base\Component;

class Wechat extends Component
{
    public $appConfig = [];
    public $paymentConfig = [];
    public $miniProgramConfig = [];
    public $openPlatformConfig = [];
    public $workConfig = [];

    private $_app;
    private $_payment;
    private $_miniProgram;
    private $_openPlatform;
    private $_work;

    /**
     * @return bool
     */
    public static function getIsWechatClient()
    {
        return stripos(\Yii::$app->getRequest()->getUserAgent(), 'micromessenger') !== false;
    }

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function getApp()
    {
        if (!$this->_app instanceof Factory) {
            $this->_app = Factory::officialAccount($this->mergeConfig($this->appConfig));
        }

        return $this->_app;
    }

    /**
     * @param $config
     * @return array
     */
    private function mergeConfig($config)
    {
        return $config;
    }

    /**
     * @return \EasyWeChat\Payment\Application
     */
    public function getPayment()
    {
        if (!$this->_payment instanceof Factory) {
            $this->_payment = Factory::payment($this->mergeConfig($this->paymentConfig));
        }

        return $this->_payment;
    }

    /**
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function getMiniProgram()
    {
        if (!$this->_miniProgram instanceof Factory) {
            $this->_miniProgram = Factory::miniProgram($this->mergeConfig($this->miniProgramConfig));
        }

        return $this->_miniProgram;
    }

    /**
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public function getOpenPlatform()
    {
        if (!$this->_openPlatform instanceof Factory) {
            $this->_openPlatform = Factory::openPlatform($this->mergeConfig($this->openPlatformConfig));
        }

        return $this->_openPlatform;
    }

    /**
     * @return \EasyWeChat\Work\Application
     */
    public function getWork()
    {
        if (!$this->_work instanceof Factory) {
            $this->_work = Factory::work($this->mergeConfig($this->workConfig));
        }

        return $this->_work;
    }
}
